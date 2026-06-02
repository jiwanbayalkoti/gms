<?php

namespace App\Http\Controllers;

use App\Models\SocialMediaPost;
use App\Services\SocialMediaPublisherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SocialMediaController extends BaseController
{
    public function index(Request $request)
    {
        $this->authorizePermission('notifications.create');

        $perPage = (int) $request->input('per_page', 15);
        if (!in_array($perPage, [10, 15, 20, 50], true)) {
            $perPage = 15;
        }

        $query = SocialMediaPost::with('creator')->latest();
        $posts = $this->applyGymFilter($query)
            ->paginate($perPage)
            ->withQueryString();

        if ($request->ajax()) {
            return view('social-media.partials.posts-list', compact('posts', 'perPage'));
        }

        return view('social-media.index', compact('posts', 'perPage'));
    }

    public function show(SocialMediaPost $socialMediaPost)
    {
        $this->authorizePermission('notifications.create');
        $this->validateGymAccess($socialMediaPost->gym_id);

        $socialMediaPost->load('creator');

        return view('social-media.show', ['post' => $socialMediaPost]);
    }

    public function edit(SocialMediaPost $socialMediaPost)
    {
        $this->authorizePermission('notifications.create');
        $this->validateGymAccess($socialMediaPost->gym_id);

        return view('social-media.edit', ['post' => $socialMediaPost]);
    }

    public function update(Request $request, SocialMediaPost $socialMediaPost)
    {
        $this->authorizePermission('notifications.create');
        $this->validateGymAccess($socialMediaPost->gym_id);

        $validated = $request->validate($this->socialPostValidationRules($request, $socialMediaPost));

        $allowedPaths = $socialMediaPost->getMediaPathsList();
        $remove = array_values(array_intersect($request->input('remove_media', []), $allowedPaths));
        foreach ($remove as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $mediaPaths = array_values(array_diff($allowedPaths, $remove));
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                if ($file && $file->isValid()) {
                    $mediaPaths[] = $file->store('social-media/images', 'public');
                }
            }
        }

        $videoPath = $socialMediaPost->video_path;
        if ($request->hasFile('video')) {
            if (!empty($videoPath) && Storage::disk('public')->exists($videoPath)) {
                Storage::disk('public')->delete($videoPath);
            }
            $videoPath = $request->file('video')->store('social-media/videos', 'public');
        } elseif ($request->boolean('remove_video')) {
            if (!empty($videoPath) && Storage::disk('public')->exists($videoPath)) {
                Storage::disk('public')->delete($videoPath);
            }
            $videoPath = null;
        }

        $socialMediaPost->update(array_merge([
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'media_paths' => $mediaPaths !== [] ? $mediaPaths : null,
            'media_path' => $mediaPaths[0] ?? null,
            'video_path' => $videoPath,
            'platforms' => $validated['platforms'],
        ], $this->youtubeOptionFields($validated)));

        return redirect()->route('social-media.index')
            ->with('success', 'Post updated successfully.');
    }

    public function store(Request $request)
    {
        $this->authorizePermission('notifications.create');

        $validated = $request->validate($this->socialPostValidationRules($request));

        $mediaPaths = [];
        if ($request->hasFile('media')) {
            foreach ($request->file('media') as $file) {
                if ($file && $file->isValid()) {
                    $mediaPaths[] = $file->store('social-media/images', 'public');
                }
            }
        }

        $videoPath = null;
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('social-media/videos', 'public');
        }

        SocialMediaPost::create(array_merge([
            'gym_id' => Auth::user()->gym_id,
            'created_by' => Auth::id(),
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'media_paths' => $mediaPaths !== [] ? $mediaPaths : null,
            'media_path' => $mediaPaths[0] ?? null,
            'video_path' => $videoPath,
            'platforms' => $validated['platforms'],
            'status' => 'draft',
        ], $this->youtubeOptionFields($validated)));

        return redirect()->route('social-media.index')
            ->with('success', 'Post draft created. Click Publish to post on selected platforms.');
    }

    public function publish(SocialMediaPost $socialMediaPost, SocialMediaPublisherService $publisher)
    {
        $this->authorizePermission('notifications.create');
        $this->validateGymAccess($socialMediaPost->gym_id);

        $previousResults = is_array($socialMediaPost->publish_results)
            ? $socialMediaPost->publish_results
            : [];

        $results = $publisher->publish($socialMediaPost);
        $hasFailure = collect($results)->contains(fn ($result) => ($result['success'] ?? false) === false);
        $hasSuccess = collect($results)->contains(fn ($result) => ($result['success'] ?? false) === true);

        $socialMediaPost->update([
            'publish_results' => $results,
            'status' => $hasSuccess ? ($hasFailure ? 'partial_failed' : 'published') : 'failed',
            'published_at' => now(),
        ]);

        $skippedPlatforms = collect($socialMediaPost->platforms ?? [])
            ->filter(fn ($platform) => ($previousResults[$platform]['success'] ?? false) === true
                && ($results[$platform]['success'] ?? false) === true)
            ->map(fn ($platform) => ucfirst($platform))
            ->values();

        $message = 'Publish attempted. Check result details in the table.';
        if ($skippedPlatforms->isNotEmpty()) {
            $message = 'Republish complete. Skipped (already published): '
                . $skippedPlatforms->implode(', ')
                . '. Only failed platforms were retried.';
        }

        return redirect()->route('social-media.index')
            ->with('success', $message);
    }

    public function clearMedia(SocialMediaPost $socialMediaPost)
    {
        $this->authorizePermission('notifications.create');
        $this->validateGymAccess($socialMediaPost->gym_id);

        if (!$socialMediaPost->hasStoredMedia()) {
            return redirect()->back()
                ->with('error', 'This post has no local image or video files to remove.');
        }

        $socialMediaPost->deleteStoredMediaFiles();
        $socialMediaPost->clearStoredMediaAttributes();

        return redirect()->back()
            ->with('success', 'Local image/video files removed. Post record and publish history are kept. Nothing was deleted from Facebook, Instagram, or YouTube.');
    }

    public function destroy(SocialMediaPost $socialMediaPost)
    {
        $this->authorizePermission('notifications.create');
        $this->validateGymAccess($socialMediaPost->gym_id);

        $socialMediaPost->deleteStoredMediaFiles();
        $socialMediaPost->delete();

        return redirect()->route('social-media.index')
            ->with('success', 'Post removed from this system (including local files). Posts on Facebook, Instagram, or YouTube are not deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function socialPostValidationRules(Request $request, ?SocialMediaPost $existing = null): array
    {
        $platforms = $request->input('platforms', []);
        $youtubeSelected = is_array($platforms) && in_array('youtube', $platforms, true);
        $hasVideo = $request->hasFile('video')
            || ($existing && !empty($existing->video_path) && !$request->boolean('remove_video'));

        return [
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:5000',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'in:facebook,instagram,youtube',
            'youtube_privacy' => [
                Rule::requiredIf($youtubeSelected),
                'nullable',
                'in:public,private,unlisted',
            ],
            'youtube_format' => [
                Rule::requiredIf($youtubeSelected),
                'nullable',
                'in:video,shorts',
            ],
            'media' => 'nullable|array|max:10',
            'media.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'remove_media' => 'nullable|array',
            'remove_media.*' => 'string|max:500',
            'remove_video' => 'nullable|boolean',
            'video' => [
                Rule::requiredIf($youtubeSelected && !$hasVideo),
                'nullable',
                'file',
                'mimes:mp4,mov,avi,mkv,webm',
                'max:51200',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, string>
     */
    private function youtubeOptionFields(array $validated): array
    {
        $platforms = $validated['platforms'] ?? [];
        if (!is_array($platforms) || !in_array('youtube', $platforms, true)) {
            return [
                'youtube_privacy' => 'public',
                'youtube_format' => 'video',
            ];
        }

        return [
            'youtube_privacy' => $validated['youtube_privacy'] ?? 'public',
            'youtube_format' => $validated['youtube_format'] ?? 'video',
        ];
    }
}

