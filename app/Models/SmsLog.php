<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone_number',
        'message',
        'status',
        'provider',
        'provider_response',
        'cost',
        'gym_id',
        'sent_by',
        'sent_at',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'cost' => 'decimal:2',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the gym that owns this SMS log.
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the user who sent this SMS.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }

    /**
     * Scope a query to only include sent SMS.
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope a query to only include failed SMS.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope a query to filter by date.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('sent_at', Carbon::today());
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('sent_at', [$startDate, $endDate]);
    }

    /**
     * Get daily SMS count for a gym.
     */
    public static function getDailyCount($gymId = null, $date = null)
    {
        $date = $date ?? Carbon::today();
        $query = self::whereDate('sent_at', $date)->where('status', 'sent');
        
        if ($gymId) {
            $query->where('gym_id', $gymId);
        }
        
        return $query->count();
    }

    /**
     * Get daily SMS cost for a gym.
     */
    public static function getDailyCost($gymId = null, $date = null)
    {
        $date = $date ?? Carbon::today();
        $query = self::whereDate('sent_at', $date)->where('status', 'sent');
        
        if ($gymId) {
            $query->where('gym_id', $gymId);
        }
        
        return $query->sum('cost');
    }
}
