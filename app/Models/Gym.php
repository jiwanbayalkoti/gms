<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Gym Model
 * 
 * Represents a gym/tenant in the multi-tenant system.
 * Each gym has its own data isolated by gym_id.
 */
class Gym extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'address',
        'email',
        'phone',
        'logo',
        'status',
        'subscription_plan',
        'subscription_ends_at',
    ];

    protected $casts = [
        'subscription_ends_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get all users belonging to this gym
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get gym admin for this gym
     */
    public function admin()
    {
        return $this->hasOne(User::class)->where('role', 'GymAdmin');
    }

    /**
     * Get all members of this gym
     */
    public function members()
    {
        return $this->hasMany(User::class)->where('role', 'Member');
    }

    /**
     * Get all trainers of this gym
     */
    public function trainers()
    {
        return $this->hasMany(User::class)->where('role', 'Trainer');
    }

    /**
     * Check if gym is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if subscription is valid
     */
    public function hasValidSubscription(): bool
    {
        if (!$this->subscription_ends_at) {
            return true; // No expiry date means unlimited
        }
        
        return $this->subscription_ends_at->isFuture();
    }

    /**
     * Scope to get only active gyms
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}

