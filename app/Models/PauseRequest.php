<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PauseRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'payment_id',
        'pause_start_date',
        'pause_end_date',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
        'gym_id',
    ];

    protected $casts = [
        'pause_start_date' => 'date',
        'pause_end_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the member who requested the pause.
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Get the payment associated with this pause request.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the admin who reviewed the request.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Get the gym that owns this pause request.
     */
    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Check if the request is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    /**
     * Check if the request is approved.
     */
    public function isApproved(): bool
    {
        return $this->status === 'Approved';
    }

    /**
     * Check if the request is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === 'Rejected';
    }

    /**
     * Calculate the number of days in the pause period.
     */
    public function getDaysCount(): int
    {
        return $this->pause_start_date->diffInDays($this->pause_end_date) + 1;
    }
}
