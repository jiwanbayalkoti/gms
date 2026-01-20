<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendance';

    protected $fillable = [
        'member_id',
        'check_in_time',
        'check_out_time',
        'class_id',
        'notes',
        'gym_id',
        'created_by',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
    ];

    /**
     * Get the member who checked in.
     */
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Get the class that the member attended, if any.
     */
    public function gymClass()
    {
        return $this->belongsTo(GymClass::class, 'class_id');
    }

    /**
     * Scope a query to only include attendance records for today.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('check_in_time', now()->toDateString());
    }

    /**
     * Scope a query to only include attendance records for a specific date.
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('check_in_time', $date);
    }

    /**
     * Scope a query to only include attendance records for a specific date range.
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereDate('check_in_time', '>=', $startDate)
                     ->whereDate('check_in_time', '<=', $endDate);
    }

    /**
     * Get the gym that owns this attendance record.
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the user who created this attendance record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
