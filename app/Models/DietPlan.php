<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DietPlan extends Model
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
        'breakfast',
        'lunch',
        'dinner',
        'snacks',
        'gym_id',
        'created_by',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the trainer who created the diet plan.
     */
    public function trainer()
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    /**
     * Get the member to whom the diet plan is assigned.
     */
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }
    
    /**
     * Scope a query to only include default diet plans.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
    
    /**
     * Scope a query to only include active diet plans.
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->whereNull('end_date')
              ->orWhere('end_date', '>=', now());
        });
    }

    /**
     * Get the gym that owns this diet plan.
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the user who created this diet plan.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
