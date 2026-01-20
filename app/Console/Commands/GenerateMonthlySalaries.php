<?php

namespace App\Console\Commands;

use App\Models\Salary;
use App\Models\SalaryPayment;
use App\Models\User;
use App\Services\NepalTaxCalculator;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GenerateMonthlySalaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'salaries:generate-monthly';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically generate monthly salary payments for all active staff with active salary configurations';

    protected $taxCalculator;

    public function __construct()
    {
        parent::__construct();
        $this->taxCalculator = new NepalTaxCalculator();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting monthly salary generation...');
        
        // Get current month period
        $periodStart = Carbon::now()->startOfMonth();
        $periodEnd = Carbon::now()->endOfMonth();
        
        // Get all active salaries
        $activeSalaries = Salary::where('status', 'active')
            ->where(function($query) use ($periodStart) {
                $query->whereNull('end_date')
                      ->orWhere('end_date', '>=', $periodStart);
            })
            ->where('start_date', '<=', $periodEnd)
            ->with('employee')
            ->get();
        
        $generated = 0;
        $skipped = 0;
        $errors = 0;
        
        foreach ($activeSalaries as $salary) {
            try {
                // Check if salary payment already exists for this period
                $existingPayment = SalaryPayment::where('salary_id', $salary->id)
                    ->whereYear('payment_period_start', $periodStart->year)
                    ->whereMonth('payment_period_start', $periodStart->month)
                    ->first();
                
                if ($existingPayment) {
                    $this->warn("Salary payment already exists for {$salary->employee->name} (Period: {$periodStart->format('M Y')})");
                    $skipped++;
                    continue;
                }
                
                // Calculate payment amounts based on salary type
                $amounts = $this->calculatePaymentAmounts($salary, $periodStart, $periodEnd);
                
                // Get employee marital status
                $maritalStatus = $salary->employee->marital_status ?? 'single';
                
                // Calculate tax
                $taxAmount = $this->taxCalculator->calculateTaxForPeriod(
                    $amounts['base_amount'],
                    $amounts['commission_amount'] ?? 0,
                    $amounts['bonus_amount'] ?? 0,
                    $maritalStatus
                );
                
                // Calculate net amount (after tax and deductions)
                $grossAmount = ($amounts['base_amount'] ?? 0) + 
                              ($amounts['commission_amount'] ?? 0) + 
                              ($amounts['bonus_amount'] ?? 0);
                
                $deductions = $amounts['deductions'] ?? 0;
                $netAmount = $grossAmount - $taxAmount - $deductions;
                
                // Create salary payment
                $salaryPayment = SalaryPayment::create([
                    'salary_id' => $salary->id,
                    'employee_id' => $salary->employee_id,
                    'payment_period_start' => $periodStart,
                    'payment_period_end' => $periodEnd,
                    'base_amount' => $amounts['base_amount'] ?? 0,
                    'commission_amount' => $amounts['commission_amount'] ?? 0,
                    'bonus_amount' => $amounts['bonus_amount'] ?? 0,
                    'deductions' => $deductions,
                    'tax_amount' => $taxAmount,
                    'net_amount' => max(0, round($netAmount, 2)),
                    'payment_method' => 'Bank Transfer',
                    'payment_status' => 'Pending',
                    'payment_date' => null,
                    'transaction_id' => null,
                    'notes' => 'Auto-generated monthly salary',
                    'gym_id' => $salary->gym_id,
                    'created_by' => 1, // System user
                ]);
                
                $this->info("Generated salary payment for {$salary->employee->name}: NPR " . number_format($netAmount, 2));
                $generated++;
                
            } catch (\Exception $e) {
                $this->error("Error generating salary for {$salary->employee->name}: " . $e->getMessage());
                Log::error("Salary generation error", [
                    'salary_id' => $salary->id,
                    'employee_id' => $salary->employee_id,
                    'error' => $e->getMessage()
                ]);
                $errors++;
            }
        }
        
        $this->info("\nSalary generation completed!");
        $this->info("Generated: {$generated}");
        $this->info("Skipped: {$skipped}");
        $this->info("Errors: {$errors}");
        
        return Command::SUCCESS;
    }
    
    /**
     * Calculate payment amounts based on salary type
     */
    protected function calculatePaymentAmounts(Salary $salary, Carbon $periodStart, Carbon $periodEnd): array
    {
        $amounts = [
            'base_amount' => 0,
            'commission_amount' => 0,
            'bonus_amount' => 0,
            'deductions' => 0,
        ];
        
        switch ($salary->salary_type) {
            case 'fixed':
                $amounts['base_amount'] = $salary->base_salary ?? 0;
                break;
                
            case 'hourly':
                // For hourly, we need to calculate based on hours worked
                // For now, assuming full-time (8 hours/day * working days)
                $workingDays = $periodStart->diffInWeekdays($periodEnd) + 1;
                $hoursWorked = $workingDays * 8; // Assuming 8 hours per day
                $amounts['base_amount'] = ($salary->hourly_rate ?? 0) * $hoursWorked;
                break;
                
            case 'commission':
                // Calculate commission from payments in the period
                $payments = \App\Models\Payment::where('gym_id', $salary->gym_id)
                    ->whereBetween('payment_date', [$periodStart, $periodEnd])
                    ->where('payment_status', 'Completed')
                    ->sum('amount');
                
                $amounts['commission_amount'] = ($payments * ($salary->commission_percentage ?? 0)) / 100;
                break;
                
            case 'hybrid':
                $amounts['base_amount'] = $salary->base_salary ?? 0;
                
                // Calculate commission
                $payments = \App\Models\Payment::where('gym_id', $salary->gym_id)
                    ->whereBetween('payment_date', [$periodStart, $periodEnd])
                    ->where('payment_status', 'Completed')
                    ->sum('amount');
                
                $amounts['commission_amount'] = ($payments * ($salary->commission_percentage ?? 0)) / 100;
                break;
        }
        
        return $amounts;
    }
}
