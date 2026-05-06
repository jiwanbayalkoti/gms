<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\SocialMediaPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Client\ConnectionException;

class SocialMediaPublisherService
{
    public function publish(SocialMediaPost $post): array
    {
        $settings = Setting::current();
        $results = [];

        foreach (($post->platforms ?? []) as $platform) {
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

                return ['success' => false, 'message' => $this->extractApiError($response->json(), $response->body())];
            }

            // If image is provided, publish as Facebook photo post.
            if (!empty($post->media_path) && Storage::disk('public')->exists($post->media_path)) {
                $imageContent = Storage::disk('public')->get($post->media_path);
                $response = Http::timeout(30)->attach(
                    'source',
                    $imageContent,
                    basename($post->media_path)
                )->post(
                    "https://graph.facebook.com/v20.0/{$settings->facebook_page_id}/photos",
                    [
                        'caption' => $post->content,
                        'access_token' => $settings->facebook_page_access_token,
                    ]
                );

                if ($response->successful()) {
                    return ['success' => true, 'response' => $response->json()];
                }

                return ['success' => false, 'message' => $this->extractApiError($response->json(), $response->body())];
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

            return ['success' => false, 'message' => $this->extractApiError($response->json(), $response->body())];
        } catch (ConnectionException $e) {
            return ['success' => false, 'message' => 'Network/DNS error while connecting to Facebook API: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Facebook publish failed: ' . $e->getMessage()];
        }
    }

    private function publishToInstagram(SocialMediaPost $post, Setting $settings): array
    {
        try {
            if (empty($settings->facebook_page_access_token) || empty($settings->instagram_business_account_id)) {
                return ['success' => false, 'message' => 'Instagram credentials are not configured in settings.'];
            }

            if (empty($post->media_path)) {
                return ['success' => false, 'message' => 'Instagram post requires an image.'];
            }

            $imageUrl = asset('storage/' . ltrim($post->media_path, '/'));
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
            if (empty($settings->youtube_access_token)) {
                return ['success' => false, 'message' => 'YouTube access token is not configured in settings.'];
            }

            if (empty($post->video_path) || !Storage::disk('public')->exists($post->video_path)) {
                return ['success' => false, 'message' => 'YouTube post requires a valid uploaded video file.'];
            }

            $videoContent = Storage::disk('public')->get($post->video_path);
            $meta = [
                'snippet' => [
                    'title' => $post->title ?: 'Gym Update',
                    'description' => $post->content,
                    'categoryId' => '22',
                ],
                'status' => [
                    'privacyStatus' => 'public',
                ],
            ];

            $uploadRes = Http::timeout(60)->withHeaders([
                'Authorization' => 'Bearer ' . $settings->youtube_access_token,
            ])->attach(
                'metadata',
                json_encode($meta),
                'metadata.json'
            )->attach(
                'video',
                $videoContent,
                basename($post->video_path)
            )->post('https://www.googleapis.com/upload/youtube/v3/videos?part=snippet,status&uploadType=multipart');

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

    private function extractApiError(?array $json, string $fallback): string
    {
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

