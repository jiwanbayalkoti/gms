<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MembershipPlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Build features array based on plan capabilities
        $features = ['Gym Access'];
        if ($this->allows_class_booking) {
            $features[] = 'Class Booking';
            if ($this->allowed_bookings_per_week) {
                $features[] = "{$this->allowed_bookings_per_week} Classes/Week";
            }
        }
        if ($this->has_discount && $this->isDiscountActive()) {
            $features[] = 'Special Discount';
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'duration_days' => (int) $this->duration_days,
            'features' => $features,
            'status' => $this->is_active ? 'active' : 'inactive',
            'is_active' => (bool) $this->is_active,
            'allows_class_booking' => (bool) $this->allows_class_booking,
            'allowed_bookings_per_week' => $this->allowed_bookings_per_week ? (int) $this->allowed_bookings_per_week : null,
            'has_discount' => (bool) $this->has_discount,
            'discount_percentage' => $this->discount_percentage ? (float) $this->discount_percentage : null,
            'discount_amount' => $this->discount_amount ? (float) $this->discount_amount : null,
            'discount_start_date' => $this->discount_start_date ? $this->discount_start_date->format('Y-m-d') : null,
            'discount_end_date' => $this->discount_end_date ? $this->discount_end_date->format('Y-m-d') : null,
            'discount_description' => $this->discount_description,
            'discounted_price' => $this->getDiscountedPrice(),
            'discount_amount_value' => $this->getDiscountAmount(),
            'is_discount_active' => $this->isDiscountActive(),
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}

