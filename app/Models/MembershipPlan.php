<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'duration_days',
        'price',
        'is_active',
        'allows_class_booking',
        'allowed_bookings_per_week',
        'gym_id',
        'created_by',
        'has_discount',
        'discount_percentage',
        'discount_amount',
        'discount_start_date',
        'discount_end_date',
        'discount_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'allows_class_booking' => 'boolean',
        'has_discount' => 'boolean',
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_start_date' => 'date',
        'discount_end_date' => 'date',
    ];

    /**
     * Get the payments associated with this membership plan.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Scope a query to only include active membership plans.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the gym that owns this membership plan.
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the user who created this membership plan.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if discount is currently active.
     */
    public function isDiscountActive(): bool
    {
        if (!$this->has_discount) {
            return false;
        }

        $now = now()->toDateString();
        
        // Check if discount dates are valid
        if ($this->discount_start_date && $now < $this->discount_start_date) {
            return false;
        }
        
        if ($this->discount_end_date && $now > $this->discount_end_date) {
            return false;
        }

        // Check if discount has value (either percentage or amount)
        return ($this->discount_percentage > 0) || ($this->discount_amount > 0);
    }

    /**
     * Get the discounted price.
     */
    public function getDiscountedPrice(): float
    {
        if (!$this->isDiscountActive()) {
            return (float) $this->price;
        }

        $discountedPrice = (float) $this->price;

        // Apply percentage discount first
        if ($this->discount_percentage > 0) {
            $discountedPrice = $discountedPrice * (1 - ($this->discount_percentage / 100));
        }

        // Then apply fixed amount discount
        if ($this->discount_amount > 0) {
            $discountedPrice = max(0, $discountedPrice - $this->discount_amount);
        }

        return round($discountedPrice, 2);
    }

    /**
     * Get the total discount amount.
     */
    public function getDiscountAmount(): float
    {
        if (!$this->isDiscountActive()) {
            return 0;
        }

        return round((float) $this->price - $this->getDiscountedPrice(), 2);
    }

    /**
     * Scope a query to only include plans with active discounts.
     */
    public function scopeWithActiveDiscount($query)
    {
        $now = now()->toDateString();
        return $query->where('has_discount', true)
            ->where(function($q) use ($now) {
                $q->whereNull('discount_start_date')
                  ->orWhere('discount_start_date', '<=', $now);
            })
            ->where(function($q) use ($now) {
                $q->whereNull('discount_end_date')
                  ->orWhere('discount_end_date', '>=', $now);
            })
            ->where(function($q) {
                $q->where('discount_percentage', '>', 0)
                  ->orWhere('discount_amount', '>', 0);
            });
    }
}
