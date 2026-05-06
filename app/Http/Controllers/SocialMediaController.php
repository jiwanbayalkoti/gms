<?php

namespace App\Http\Controllers;

use App\Models\SocialMediaPost;
use App\Services\SocialMediaPublisherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SocialMediaController extends BaseController
{
    public function index()
    {
        $this->authorizePermission('notifications.create');

        $query = SocialMediaPost::with('creator')->latest();
        $posts = $this->applyGymFilter($query)->paginate(20);

        return view('social-media.index', compact('posts'));
    }

    public function store(Request $request)
    {
        $this->authorizePermission('notifications.create');

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'required|string|max:5000',
            'platforms' => 'required|array|min:1',
            'platforms.*' => 'in:facebook,instagram,youtube',
            'media' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'video' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:51200',
        ]);

        $mediaPath = null;
        $videoPath = null;
        if ($request->hasFile('media')) {
            $mediaPath = $request->file('media')->store('social-media/images', 'public');
        }
        if ($request->hasFile('video')) {
            $videoPath = $request->file('video')->store('social-media/videos', 'public');
        }

        SocialMediaPost::create([
            'gym_id' => Auth::user()->gym_id,
            'created_by' => Auth::id(),
            'title' => $validated['title'] ?? null,
            'content' => $validated['content'],
            'media_path' => $mediaPath,
            'video_path' => $videoPath,
            'platforms' => $validated['platforms'],
            'status' => 'draft',
        ]);

        return redirect()->route('social-media.index')
            ->with('success', 'Post draft created. Click Publish to post on selected platforms.');
    }

    public function publish(SocialMediaPost $socialMediaPost, SocialMediaPublisherService $publisher)
    {
        $this->authorizePermission('notifications.create');
        $this->validateGymAccess($socialMediaPost->gym_id);

        $results = $publisher->publish($socialMediaPost);
        $hasFailure = collect($results)->contains(fn ($result) => ($result['success'] ?? false) === false);
        $hasSuccess = collect($results)->contains(fn ($result) => ($result['success'] ?? false) === true);

        $socialMediaPost->update([
            'publish_results' => $results,
            'status' => $hasSuccess ? ($hasFailure ? 'partial_failed' : 'published') : 'failed',
            'published_at' => now(),
        ]);

        return redirect()->route('social-media.index')
            ->with('success', 'Publish attempted. Check result details in the table.');
    }

    public function destroy(SocialMediaPost $socialMediaPost)
    {
        $this->authorizePermission('notifications.create');
        $this->validateGymAccess($socialMediaPost->gym_id);

        if (!empty($socialMediaPost->media_path) && Storage::disk('public')->exists($socialMediaPost->media_path)) {
            Storage::disk('public')->delete($socialMediaPost->media_path);
        }

        if (!empty($socialMediaPost->video_path) && Storage::disk('public')->exists($socialMediaPost->video_path)) {
            Storage::disk('public')->delete($socialMediaPost->video_path);
        }

        $socialMediaPost->delete();

        return redirect()->route('social-media.index')
            ->with('success', 'Social media post deleted successfully.');
    }
}

