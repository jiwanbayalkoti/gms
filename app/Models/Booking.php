<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'class_id',
        'status',
        'notes',
        'reminder_sent',
        'gym_id',
        'created_by',
    ];

    protected $casts = [
        'reminder_sent' => 'boolean',
    ];

    /**
     * Get the member who made the booking.
     */
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Get the class that was booked.
     */
    public function gymClass()
    {
        return $this->belongsTo(GymClass::class, 'class_id');
    }

    /**
     * Scope a query to only include bookings with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include confirmed bookings.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'Confirmed');
    }

    /**
     * Scope a query to only include upcoming bookings.
     */
    public function scopeUpcoming($query)
    {
        return $query->whereHas('gymClass', function($q) {
            $q->where('start_time', '>', now())
              ->where('status', 'Active');
        })->where('status', 'Confirmed');
    }

    /**
     * Get the gym that owns this booking.
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the user who created this booking.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
