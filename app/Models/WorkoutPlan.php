<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'trainer_id',
        'member_id',
        'is_default',
        'start_date',
        'end_date',
        'notes',
        'gym_id',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the trainer who created the workout plan.
     */
    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    /**
     * Get the member to whom the workout plan is assigned.
     */
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
    
    /**
     * Scope a query to only include default workout plans.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
    
    /**
     * Scope a query to only include active workout plans.
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', now());
        });
    }

    /**
     * Get the gym that owns this workout plan.
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the user who created this workout plan.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
