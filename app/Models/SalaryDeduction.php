<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'salary_payment_id',
        'deduction_type',
        'amount',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    /**
     * Get the salary payment this deduction belongs to.
     */
    public function salaryPayment()
    {
        return $this->belongsTo(SalaryPayment::class);
    }
}
