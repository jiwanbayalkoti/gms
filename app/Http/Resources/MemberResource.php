<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // Get active membership plan from latest payment
        $latestPayment = $this->payments()
            ->where('payment_status', 'Completed')
            ->with('membershipPlan')
            ->latest('payment_date')
            ->first();
        
        $membershipPlan = null;
        $membershipStatus = 'inactive';
        
        if ($latestPayment && $latestPayment->membershipPlan) {
            $membershipPlan = [
                'id' => $latestPayment->membershipPlan->id,
                'name' => $latestPayment->membershipPlan->name,
                'price' => (float) $latestPayment->membershipPlan->price,
                'duration_days' => (int) $latestPayment->membershipPlan->duration_days,
            ];
            
            // Check if membership is still active (not expired)
            if ($latestPayment->expiry_date && !$latestPayment->hasExpired()) {
                $membershipStatus = 'active';
            } elseif (!$latestPayment->expiry_date) {
                $membershipStatus = 'active';
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_photo' => $this->profile_photo ? asset('storage/' . $this->profile_photo) : null,
            'status' => $this->active ? 'active' : 'inactive',
            'active' => (bool) $this->active,
            'membership_plan' => $membershipPlan,
            'membership_status' => $membershipStatus,
            'membership_expiry_date' => $latestPayment && $latestPayment->expiry_date ? $latestPayment->expiry_date->format('Y-m-d') : null,
            'gym_id' => $this->gym_id,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}

