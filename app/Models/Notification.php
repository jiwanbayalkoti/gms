<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'message',
        'type',
        'target_audience',
        'gym_id',
        'created_by',
        'is_published',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the gym that owns this notification.
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the user who created this notification.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all users who have read this notification.
     */
    public function readers()
    {
        return $this->belongsToMany(User::class, 'notification_reads', 'notification_id', 'user_id')
                    ->withPivot('read_at')
                    ->withTimestamps();
    }

    /**
     * Check if a user has read this notification.
     */
    public function isReadBy($userId)
    {
        return $this->readers()->where('user_id', $userId)->exists();
    }

    /**
     * Mark notification as read by a user.
     */
    public function markAsRead($userId)
    {
        if (!$this->isReadBy($userId)) {
            $this->readers()->attach($userId, ['read_at' => now()]);
        }
    }

    /**
     * Scope a query to only include published notifications.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->where(function($q) {
                         $q->whereNull('published_at')
                           ->orWhere('published_at', '<=', now());
                     })
                     ->where(function($q) {
                         $q->whereNull('expires_at')
                           ->orWhere('expires_at', '>=', now());
                     });
    }

    /**
     * Scope a query to only include urgent notifications.
     */
    public function scopeUrgent($query)
    {
        return $query->where('type', 'urgent');
    }

    /**
     * Scope a query to only include active notifications (not expired).
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>=', now());
        });
    }

    /**
     * Scope a query to filter by target audience.
     */
    public function scopeForAudience($query, $role)
    {
        return $query->where(function($q) use ($role) {
            $q->where('target_audience', 'all')
              ->orWhere('target_audience', $role);
        });
    }

    /**
     * Check if notification is active.
     */
    public function isActive()
    {
        if (!$this->is_published) {
            return false;
        }

        if ($this->published_at && $this->published_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if notification is urgent.
     */
    public function isUrgent()
    {
        return $this->type === 'urgent' && $this->isActive();
    }

    /**
     * Publish the notification.
     */
    public function publish()
    {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * Unpublish the notification.
     */
    public function unpublish()
    {
        $this->update([
            'is_published' => false,
        ]);
    }
}
