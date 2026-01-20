<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'membership_plan_id',
        'amount',
        'payment_method',
        'transaction_id',
        'payment_status',
        'notes',
        'payment_date',
        'expiry_date',
        'gym_id',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'expiry_date' => 'date',
    ];

    /**
     * Get the member who made the payment.
     */
    public function member()
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Get the membership plan that was purchased.
     */
    public function membershipPlan()
    {
        return $this->belongsTo(MembershipPlan::class);
    }

    /**
     * Scope a query to only include payments with a specific status.
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope a query to only include successful payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('payment_status', 'Completed');
    }

    /**
     * Scope a query to only include payments within a specific date range.
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereDate('payment_date', '>=', $startDate)
                     ->whereDate('payment_date', '<=', $endDate);
    }

    /**
     * Check if the payment has expired.
     */
    public function hasExpired(): bool
    {
        return $this->expiry_date && now()->greaterThan($this->expiry_date);
    }

    /**
     * Get the gym that owns this payment.
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the user who created this payment.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
