<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'event_date',
        'event_time',
        'location',
        'status',
        'gym_id',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
        'event_time' => 'datetime',
    ];

    /**
     * Get the gym that owns the event.
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the user who created the event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the users who responded to the event.
     */
    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_attendees')
            ->withPivot('response')
            ->withTimestamps();
    }

    /**
     * Get count of attendees by response type.
     */
    public function getAttendingCountAttribute(): int
    {
        return $this->attendees()->wherePivot('response', 'Attending')->count();
    }

    public function getNotAttendingCountAttribute(): int
    {
        return $this->attendees()->wherePivot('response', 'Not Attending')->count();
    }

    public function getNotSureCountAttribute(): int
    {
        return $this->attendees()->wherePivot('response', 'Not Sure')->count();
    }

    public function getUserResponseAttribute(): ?string
    {
        if (!auth()->check()) {
            return null;
        }
        
        $attendee = $this->attendees()->where('user_id', auth()->id())->first();
        return $attendee ? $attendee->pivot->response : null;
    }

    /**
     * Check if event is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'Published';
    }

    /**
     * Check if event is in the past.
     */
    public function isPast(): bool
    {
        $eventDateTime = \Carbon\Carbon::parse($this->event_date->format('Y-m-d') . ' ' . $this->event_time->format('H:i:s'));
        return $eventDateTime->isPast();
    }
}
