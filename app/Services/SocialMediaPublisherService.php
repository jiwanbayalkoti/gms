<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SocialMediaPost;
use App\Services\YouTubeOAuthService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Client\ConnectionException;

class SocialMediaPublisherService
{
    public function __construct(
        private readonly YouTubeOAuthService $youtubeOAuth
    ) {
    }
    public function publish(SocialMediaPost $post): array
    {
        $results = [];
        $previous = is_array($post->publish_results) ? $post->publish_results : [];

        if (empty($post->gym_id)) {
            foreach (($post->platforms ?? []) as $platform) {
                if ($this->platformAlreadySucceeded($previous, $platform)) {
                    $results[$platform] = $previous[$platform];
                    continue;
                }
                $results[$platform] = ['success' => false, 'message' => 'Post has no gym assigned.'];
            }

            return $results;
        }

        $settings = Setting::forGym((int) $post->gym_id);

        foreach (($post->platforms ?? []) as $platform) {
            if ($this->platformAlreadySucceeded($previous, $platform)) {
                $results[$platform] = $previous[$platform];
                continue;
            }

            if ($platform === 'facebook') {
                $results['facebook'] = $this->publishToFacebook($post, $settings);
            } elseif ($platform === 'instagram') {
                $results['instagram'] = $this->publishToInstagram($post, $settings);
            } elseif ($platform === 'youtube') {
                $results['youtube'] = $this->publishToYoutube($post, $settings);
            }
        }

        return $results;
    }

    /**
     * On republish (e.g. partial_failed), do not post again to platforms that already succeeded.
     *
     * @param  array<string, mixed>  $previous
     */
    private function platformAlreadySucceeded(array $previous, string $platform): bool
    {
        return ($previous[$platform]['success'] ?? false) === true;
    }

    private function publishToFacebook(SocialMediaPost $post, Setting $settings): array
    {
        try {
            if (empty($settings->facebook_page_access_token) || empty($settings->facebook_page_id)) {
                return ['success' => false, 'message' => 'Facebook credentials are not configured in settings.'];
            }

            // If video is provided, publish as Facebook video post.
            if (!empty($post->video_path) && Storage::disk('public')->exists($post->video_path)) {
                $videoContent = Storage::disk('public')->get($post->video_path);
                $response = Http::timeout(30)->attach(
                    'source',
                    $videoContent,
                    basename($post->video_path)
                )->post(
                    "https://graph.facebook.com/v20.0/{$settings->facebook_page_id}/videos",
                    [
                        'description' => $post->content,
                        'access_token' => $settings->facebook_page_access_token,
                    ]
                );

                if ($response->successful()) {
                    return ['success' => true, 'response' => $response->json()];
                }

                return ['success' => false, 'message' => $this->facebookErrorMessage($response->json(), $response->body())];
            }

            $imagePaths = $this->existingImagePaths($post);
            if ($imagePaths !== []) {
                if (count($imagePaths) === 1) {
                    return $this->publishFacebookSinglePhoto($settings, $imagePaths[0], $post->content);
                }

                return $this->publishFacebookMultiplePhotos($settings, $imagePaths, $post->content);
            }

            // Fallback text post.
            $response = Http::timeout(30)->asForm()->post(
                "https://graph.facebook.com/v20.0/{$settings->facebook_page_id}/feed",
                [
                    'message' => $post->content,
                    'access_token' => $settings->facebook_page_access_token,
                ]
            );

            if ($response->successful()) {
                return ['success' => true, 'response' => $response->json()];
            }

            return ['success' => false, 'message' => $this->facebookErrorMessage($response->json(), $response->body())];
        } catch (ConnectionException $e) {
            return ['success' => false, 'message' => 'Network/DNS error while connecting to Facebook API: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Facebook publish failed: ' . $e->getMessage()];
        }
    }

    /**
     * @return list<string>
     */
    private function existingImagePaths(SocialMediaPost $post): array
    {
        $paths = [];
        foreach ($post->getMediaPathsList() as $path) {
            if ($path !== '' && Storage::disk('public')->exists($path)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    private function publishFacebookSinglePhoto(Setting $settings, string $mediaPath, string $caption): array
    {
        $imageContent = Storage::disk('public')->get($mediaPath);
        $response = Http::timeout(30)->attach(
            'source',
            $imageContent,
            basename($mediaPath)
        )->post(
            "https://graph.facebook.com/v20.0/{$settings->facebook_page_id}/photos",
            [
                'caption' => $caption,
                'access_token' => $settings->facebook_page_access_token,
            ]
        );

        if ($response->successful()) {
            return ['success' => true, 'response' => $response->json()];
        }

        return ['success' => false, 'message' => $this->facebookErrorMessage($response->json(), $response->body())];
    }

    /**
     * Multi-image post via unpublished uploads + feed attached_media.
     */
    private function publishFacebookMultiplePhotos(Setting $settings, array $imagePaths, string $message): array
    {
        $pageId = $settings->facebook_page_id;
        $token = $settings->facebook_page_access_token;
        $photoIds = [];

        foreach ($imagePaths as $path) {
            $imageContent = Storage::disk('public')->get($path);
            $upload = Http::timeout(60)->attach(
                'source',
                $imageContent,
                basename($path)
            )->post(
                "https://graph.facebook.com/v20.0/{$pageId}/photos",
                [
                    'published' => 'false',
                    'access_token' => $token,
                ]
            );

            if (!$upload->successful() || empty($upload->json('id'))) {
                return ['success' => false, 'message' => $this->facebookErrorMessage($upload->json(), $upload->body())];
            }

            $photoIds[] = (string) $upload->json('id');
        }

        $attachedMedia = json_encode(array_map(fn ($id) => ['media_fbid' => $id], $photoIds));

        $feed = Http::timeout(45)->asForm()->post(
            "https://graph.facebook.com/v20.0/{$pageId}/feed",
            [
                'message' => $message,
                'attached_media' => $attachedMedia,
                'access_token' => $token,
            ]
        );

        if ($feed->successful()) {
            return ['success' => true, 'response' => $feed->json()];
        }

        $singlePost = $this->publishFacebookSinglePhoto($settings, $imagePaths[0], $message);
        if (($singlePost['success'] ?? false) === true) {
            $singlePost['message'] = 'Facebook: posted first image only (multi-image album failed: '
                . $this->extractApiError($feed->json(), $feed->body()) . ')';

            return $singlePost;
        }

        return ['success' => false, 'message' => $this->facebookErrorMessage($feed->json(), $feed->body())];
    }

    /**
     * Facebook returns publish_actions errors when the *token* was created with that deprecated scope — our app never requests it.
     */
    private function facebookErrorMessage(?array $json, string $fallback): string
    {
        if ($this->isMetaAccessTokenExpired($json)) {
            return 'Facebook/Meta access token has expired. Go to Settings → Facebook & Instagram → Connect with Meta and sign in again, then retry publishing.';
        }

        $msg = $this->extractApiError($json, $fallback);
        if (stripos($msg, 'publish_actions') !== false) {
            $msg .= ' Token fix: In Meta Graph API Explorer, remove deprecated scopes, then generate a new Page access token with only: pages_show_list, pages_read_engagement, pages_manage_posts (add instagram_basic + instagram_content_publish if you use Instagram). Paste that token in Settings; do not use publish_actions.';
        }

        return $msg;
    }

    /**
     * Meta Graph API: 190 / 463 = session expired; similar subcodes need re-auth.
     */
    private function isMetaAccessTokenExpired(?array $json): bool
    {
        if (!is_array($json) || empty($json['error'])) {
            return false;
        }

        $error = $json['error'];
        $code = (int) ($error['code'] ?? 0);
        $subcode = (int) ($error['error_subcode'] ?? 0);
        if ($code === 190 && in_array($subcode, [463, 460, 467], true)) {
            return true;
        }

        $message = (string) ($error['message'] ?? '');

        return stripos($message, 'session has expired') !== false
            || stripos($message, 'error validating access token') !== false;
    }

    private function publishToInstagram(SocialMediaPost $post, Setting $settings): array
    {
        try {
            if (empty($settings->facebook_page_access_token) || empty($settings->instagram_business_account_id)) {
                return ['success' => false, 'message' => 'Instagram credentials are not configured in settings.'];
            }

            $firstImage = $post->firstImagePath();
            if (empty($firstImage) || !Storage::disk('public')->exists($firstImage)) {
                return ['success' => false, 'message' => 'Instagram post requires an image.'];
            }

            $imageUrl = asset('storage/' . ltrim($firstImage, '/'));
            $token = $settings->facebook_page_access_token;
            $igId = $settings->instagram_business_account_id;

            $createRes = Http::timeout(30)->asForm()->post("https://graph.facebook.com/v20.0/{$igId}/media", [
                'image_url' => $imageUrl,
                'caption' => $post->content,
                'access_token' => $token,
            ]);

            if (!$createRes->successful() || empty($createRes->json('id'))) {
                return ['success' => false, 'message' => $this->extractApiError($createRes->json(), $createRes->body())];
            }

            $creationId = $createRes->json('id');
            $publishRes = Http::timeout(30)->asForm()->post("https://graph.facebook.com/v20.0/{$igId}/media_publish", [
                'creation_id' => $creationId,
                'access_token' => $token,
            ]);

            if ($publishRes->successful()) {
                return ['success' => true, 'response' => $publishRes->json()];
            }

            return ['success' => false, 'message' => $this->extractApiError($publishRes->json(), $publishRes->body())];
        } catch (ConnectionException $e) {
            return ['success' => false, 'message' => 'Network/DNS error while connecting to Instagram API: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Instagram publish failed: ' . $e->getMessage()];
        }
    }

    private function publishToYoutube(SocialMediaPost $post, Setting $settings): array
    {
        try {
            $accessToken = $this->youtubeOAuth->getValidAccessToken($settings);
            if (empty($accessToken)) {
                return ['success' => false, 'message' => 'YouTube is not connected. Go to Settings → Connect with YouTube (Google OAuth).'];
            }

            if (empty($post->video_path) || !Storage::disk('public')->exists($post->video_path)) {
                return ['success' => false, 'message' => 'YouTube post requires a valid uploaded video file.'];
            }

            $videoContent = Storage::disk('public')->get($post->video_path);
            $meta = $this->buildYoutubeVideoMetadata($post);

            $uploadRes = $this->uploadYoutubeMultipart($accessToken, $meta, $videoContent, basename($post->video_path));

            if ($uploadRes->status() === 401) {
                $refreshed = $this->youtubeOAuth->refreshAccessToken($settings);
                if ($refreshed) {
                    $uploadRes = $this->uploadYoutubeMultipart($refreshed, $meta, $videoContent, basename($post->video_path));
                }
            }

            if ($uploadRes->successful()) {
                return ['success' => true, 'response' => $uploadRes->json()];
            }

            return ['success' => false, 'message' => $this->extractApiError($uploadRes->json(), $uploadRes->body())];
        } catch (ConnectionException $e) {
            return ['success' => false, 'message' => 'Network/DNS error while connecting to YouTube API: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'YouTube publish failed: ' . $e->getMessage()];
        }
    }

    /**
     * Build YouTube API metadata for videos.insert (regular video vs Short).
     *
     * @return array<string, mixed>
     */
    private function buildYoutubeVideoMetadata(SocialMediaPost $post): array
    {
        $privacy = in_array($post->youtube_privacy, ['public', 'private', 'unlisted'], true)
            ? $post->youtube_privacy
            : 'public';

        $isShort = ($post->youtube_format ?? 'video') === 'shorts';

        $title = trim((string) ($post->title ?: 'Gym Update'));
        $description = trim((string) $post->content);

        if ($isShort) {
            if (!preg_match('/#shorts/i', $title)) {
                $title = rtrim($title) . ' #Shorts';
            }
            if ($description !== '' && !preg_match('/#shorts/i', $description)) {
                $description = rtrim($description) . "\n\n#Shorts";
            } elseif ($description === '') {
                $description = '#Shorts';
            }
        }

        $snippet = [
            'title' => mb_substr($title, 0, 100),
            'description' => $description,
            'categoryId' => '22',
        ];

        if ($isShort) {
            $snippet['tags'] = ['Shorts'];
        }

        return [
            'snippet' => $snippet,
            'status' => [
                'privacyStatus' => $privacy,
            ],
        ];
    }

    /**
     * YouTube expects multipart/related (not multipart/form-data).
     */
    private function uploadYoutubeMultipart(string $accessToken, array $meta, string $videoBinary, string $filename)
    {
        $boundary = 'yt_' . bin2hex(random_bytes(8));
        $metaJson = json_encode($meta, JSON_UNESCAPED_UNICODE);
        $mime = str_ends_with(strtolower($filename), '.webm') ? 'video/webm' : 'video/mp4';

        $body = "--{$boundary}\r\n"
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . $metaJson . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: {$mime}\r\n\r\n"
            . $videoBinary . "\r\n"
            . "--{$boundary}--";

        return Http::timeout(600)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'multipart/related; boundary=' . $boundary,
                'Content-Length' => (string) strlen($body),
            ])
            ->withBody($body, 'multipart/related; boundary=' . $boundary)
            ->post('https://www.googleapis.com/upload/youtube/v3/videos?uploadType=multipart&part=snippet,status');
    }

    private function extractApiError(?array $json, string $fallback): string
    {
        if ($this->isMetaAccessTokenExpired($json)) {
            return 'Facebook/Meta access token has expired. Go to Settings → Facebook & Instagram → Connect with Meta and sign in again.';
        }

        if (is_array($json)) {
            if (!empty($json['error']['message'])) {
                $errorText = $json['error']['message'];
                if (!empty($json['error']['code'])) {
                    $errorText .= ' (code: ' . $json['error']['code'] . ')';
                }
                if (!empty($json['error']['error_subcode'])) {
                    $errorText .= ', subcode: ' . $json['error']['error_subcode'];
                }
                return $errorText;
            }

            if (!empty($json['message'])) {
                return (string) $json['message'];
            }
        }

        return $fallback;
    }
}

