<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GymClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'description',
        'trainer_id',
        'capacity',
        'current_bookings',
        'start_time',
        'end_time',
        'location',
        'status',
        'recurring',
        'recurring_pattern',
        'recurring_end_date',
        'gym_id',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'recurring' => 'boolean',
        'recurring_end_date' => 'date',
    ];

    /**
     * Get the trainer who teaches the class.
     */
    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    /**
     * Get the bookings for the class.
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'class_id');
    }

    /**
     * Get the attendances for the class.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    /**
     * Check if the class is full.
     */
    public function isFull(): bool
    {
        return $this->current_bookings >= $this->capacity;
    }

    /**
     * Scope a query to only include active classes.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope a query to only include upcoming classes.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now())
                    ->where('status', 'Active')
                    ->orderBy('start_time');
    }

    /**
     * Scope a query to only include past classes.
     */
    public function scopePast($query)
    {
        return $query->where('end_time', '<', now())
                    ->orderBy('start_time', 'desc');
    }

    /**
     * Get the gym that owns this class.
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the user who created this class.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
