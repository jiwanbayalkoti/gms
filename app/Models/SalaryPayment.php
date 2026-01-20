<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SalaryPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_id',
        'employee_id',
        'payment_period_start',
        'payment_period_end',
        'base_amount',
        'commission_amount',
        'bonus_amount',
        'deductions',
        'tax_amount',
        'net_amount',
        'payment_method',
        'payment_status',
        'payment_date',
        'transaction_id',
        'payment_receipt',
        'notes',
        'gym_id',
        'created_by',
    ];

    protected $casts = [
        'payment_period_start' => 'date',
        'payment_period_end' => 'date',
        'payment_date' => 'date',
        'base_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'bonus_amount' => 'decimal:2',
        'deductions' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    /**
     * Get the salary this payment belongs to.
     */
    public function salary()
    {
        return $this->belongsTo(Salary::class);
    }

    /**
     * Get the employee (user) who received this payment.
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
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

    /**
     * Get all deductions for this payment.
     */
    public function salaryDeductions()
    {
        return $this->hasMany(SalaryDeduction::class);
    }

    /**
     * Calculate net amount.
     */
    public function calculateNetAmount(): float
    {
        $total = ($this->base_amount ?? 0) + 
                 ($this->commission_amount ?? 0) + 
                 ($this->bonus_amount ?? 0);
        
        $total -= ($this->deductions ?? 0);
        $total -= ($this->tax_amount ?? 0);
        
        return max(0, round($total, 2));
    }

    /**
     * Mark payment as paid.
     */
    public function markAsPaid($paymentDate = null)
    {
        $this->payment_status = 'Paid';
        $this->payment_date = $paymentDate ?? now();
        $this->save();
    }

    /**
     * Scope a query to only include paid payments.
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'Paid');
    }

    /**
     * Scope a query to only include pending payments.
     */
    public function scopePending($query)
    {
        return $query->where('payment_status', 'Pending');
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereDate('payment_period_start', '>=', $startDate)
                     ->whereDate('payment_period_end', '<=', $endDate);
    }
}
