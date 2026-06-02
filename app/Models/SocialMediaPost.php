<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SocialMediaPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'gym_id',
        'created_by',
        'title',
        'content',
        'media_path',
        'media_paths',
        'video_path',
        'platforms',
        'youtube_privacy',
        'youtube_format',
        'publish_results',
        'status',
        'published_at',
    ];

    protected $casts = [
        'platforms' => 'array',
        'publish_results' => 'array',
        'media_paths' => 'array',
        'published_at' => 'datetime',
    ];

    /**
     * All stored image paths (JSON media_paths + legacy single media_path).
     *
     * @return list<string>
     */
    public function getMediaPathsList(): array
    {
        $paths = $this->media_paths;
        if (!is_array($paths)) {
            $paths = [];
        }
        $paths = array_values(array_filter(array_unique($paths)));
        if ($paths === [] && !empty($this->media_path)) {
            $paths = [$this->media_path];
        }

        return $paths;
    }

    public function firstImagePath(): ?string
    {
        $paths = $this->getMediaPathsList();

        return $paths[0] ?? null;
    }

    public function hasStoredMedia(): bool
    {
        if (!empty($this->video_path)) {
            return true;
        }

        return count($this->getMediaPathsList()) > 0;
    }

    /**
     * Human-readable summary of local files still on disk.
     */
    public function storedMediaSummary(): string
    {
        if (!$this->hasStoredMedia()) {
            return 'No files';
        }

        $parts = [];
        $imageCount = count($this->getMediaPathsList());
        if ($imageCount > 0) {
            $parts[] = $imageCount . ' ' . ($imageCount === 1 ? 'image' : 'images');
        }
        if (!empty($this->video_path)) {
            $parts[] = 'video';
        }

        return implode(', ', $parts);
    }

    /**
     * Delete uploaded files from storage only (not from social platforms).
     */
    public function deleteStoredMediaFiles(): void
    {
        foreach ($this->getMediaPathsList() as $path) {
            if (!empty($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        if (!empty($this->video_path) && Storage::disk('public')->exists($this->video_path)) {
            Storage::disk('public')->delete($this->video_path);
        }
    }

    public function clearStoredMediaAttributes(): void
    {
        $this->media_paths = null;
        $this->media_path = null;
        $this->video_path = null;
        $this->save();
    }

    public function normalizedStatus(): string
    {
        $status = strtolower(trim((string) ($this->status ?? '')));

        return match ($status) {
            'draft', 'scheduled', '' => 'draft',
            'published' => 'published',
            'partial_failed' => 'partial_failed',
            'failed' => 'failed',
            default => $status !== '' ? $status : 'draft',
        };
    }

    public function canPublish(): bool
    {
        return !in_array($this->normalizedStatus(), ['published'], true);
    }

    public function isPublishedStatus(): bool
    {
        return in_array($this->normalizedStatus(), ['published', 'partial_failed'], true);
    }

    public function statusLabel(): string
    {
        return match ($this->normalizedStatus()) {
            'draft' => 'Draft',
            'published' => 'Published',
            'partial_failed' => 'Partial failed',
            'failed' => 'Failed',
            default => ucfirst(str_replace('_', ' ', $this->normalizedStatus())),
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->normalizedStatus()) {
            'draft' => 'badge-secondary',
            'published' => 'badge-success',
            'partial_failed' => 'badge-warning',
            'failed' => 'badge-danger',
            default => 'badge-secondary',
        };
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }
}

