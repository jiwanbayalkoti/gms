<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\Payment;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'salary_type',
        'base_salary',
        'hourly_rate',
        'commission_percentage',
        'payment_frequency',
        'start_date',
        'end_date',
        'status',
        'notes',
        'gym_id',
        'created_by',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'commission_percentage' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the employee (user) who has this salary.
     */
    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    /**
     * Get the gym that owns this salary.
     */
    public function gym()
    {
        return $this->belongsTo(Gym::class);
    }

    /**
     * Get the user who created this salary.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all salary payments for this salary.
     */
    public function salaryPayments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    /**
     * Check if the salary is currently active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               (!$this->end_date || $this->end_date->isFuture());
    }

    /**
     * Scope a query to only include active salaries.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where(function($q) {
                         $q->whereNull('end_date')
                           ->orWhere('end_date', '>=', now()->toDateString());
                     });
    }

    /**
     * Calculate salary for a given period.
     */
    public function calculateSalaryForPeriod(Carbon $periodStart, Carbon $periodEnd): array
    {
        $baseAmount = 0;
        $commissionAmount = 0;
        
        switch ($this->salary_type) {
            case 'fixed':
                // For fixed salary, calculate based on payment frequency
                $daysInPeriod = $periodStart->diffInDays($periodEnd) + 1;
                
                if ($this->payment_frequency === 'monthly') {
                    $daysInMonth = $periodStart->daysInMonth;
                    $baseAmount = ($this->base_salary / $daysInMonth) * $daysInPeriod;
                } elseif ($this->payment_frequency === 'weekly') {
                    $baseAmount = ($this->base_salary / 7) * $daysInPeriod;
                } elseif ($this->payment_frequency === 'bi-weekly') {
                    $baseAmount = ($this->base_salary / 14) * $daysInPeriod;
                } elseif ($this->payment_frequency === 'daily') {
                    $baseAmount = $this->base_salary * $daysInPeriod;
                }
                break;
                
            case 'hourly':
                // Calculate from attendance records
                $hoursWorked = $this->getHoursWorked($periodStart, $periodEnd);
                $baseAmount = $hoursWorked * $this->hourly_rate;
                break;
                
            case 'commission':
                // Calculate commission from member payments
                $commissionAmount = $this->calculateCommission($periodStart, $periodEnd);
                break;
                
            case 'hybrid':
                // Base salary + commission
                $daysInPeriod = $periodStart->diffInDays($periodEnd) + 1;
                if ($this->payment_frequency === 'monthly') {
                    $daysInMonth = $periodStart->daysInMonth;
                    $baseAmount = ($this->base_salary / $daysInMonth) * $daysInPeriod;
                } else {
                    $baseAmount = $this->base_salary;
                }
                $commissionAmount = $this->calculateCommission($periodStart, $periodEnd);
                break;
        }
        
        return [
            'base_amount' => round($baseAmount, 2),
            'commission_amount' => round($commissionAmount, 2),
        ];
    }

    /**
     * Get hours worked from attendance for a period.
     */
    protected function getHoursWorked(Carbon $periodStart, Carbon $periodEnd): float
    {
        // TODO: Implement attendance integration
        // For now, return 0
        return 0;
    }

    /**
     * Calculate commission from member payments.
     */
    protected function calculateCommission(Carbon $periodStart, Carbon $periodEnd): float
    {
        if (!$this->commission_percentage) {
            return 0;
        }

        // Get payments within the period
        $payments = Payment::where('gym_id', $this->gym_id)
            ->whereBetween('payment_date', [$periodStart, $periodEnd])
            ->where('payment_status', 'Completed')
            ->sum('amount');

        return ($payments * $this->commission_percentage) / 100;
    }
}
