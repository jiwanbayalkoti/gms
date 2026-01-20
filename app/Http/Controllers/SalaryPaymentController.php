<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\SalaryPayment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * SalaryPaymentController
 * 
 * Handles salary payment/payroll management with gym-based data isolation.
 */
class SalaryPaymentController extends BaseController
{
    /**
     * Display a listing of salary payments.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('salary-payments.view');

        $query = SalaryPayment::with(['employee', 'salary', 'gym']);

        // Filter by date range if provided
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateBetween($request->start_date, $request->end_date);
        }

        // Filter by employee if provided
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        // Filter by status if provided
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $payments = $this->applyGymFilter($query)->latest('payment_period_start')->get();

        // Get employees for filter
        $user = Auth::user();
        $employeesQuery = User::whereIn('role', ['Trainer', 'Staff']);
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $employeesQuery->where('gym_id', $user->gym_id);
        }
        $employees = $employeesQuery->get();

        // If AJAX request, return only the table body
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('salary-payments._table-body', compact('payments'))->render()
            ]);
        }

        return view('salary-payments.index', compact('payments', 'employees'));
    }

    /**
     * Show form to generate salary payment.
     */
    public function generate(Request $request)
    {
        $this->authorizePermission('salary-payments.create');

        $user = Auth::user();

        // Get active salaries
        $salariesQuery = Salary::with('employee')->where('status', 'active');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $salariesQuery->where('gym_id', $user->gym_id);
        }
        $salaries = $salariesQuery->get();

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('salary-payments._generate-form', compact('salaries'))->render()
            ]);
        }

        return view('salary-payments.generate-page', compact('salaries'));
    }

    /**
     * Get next period to generate for a salary.
     */
    public function getNextPeriod(Request $request, $salaryId)
    {
        $this->authorizePermission('salary-payments.create');

        $salary = Salary::with('employee')->findOrFail($salaryId);
        $user = Auth::user();
        $this->validateGymAccess($salary->gym_id);

        // Get the last payment date (where status is Paid or Pending)
        $lastPayment = SalaryPayment::where('salary_id', $salaryId)
            ->whereIn('payment_status', ['Paid', 'Pending'])
            ->orderBy('payment_period_end', 'desc')
            ->first();

        // Determine start date
        if ($lastPayment) {
            // Start from the day after the last payment period end
            $periodStart = Carbon::parse($lastPayment->payment_period_end)->addDay();
        } else {
            // Start from salary start date or current month start
            $periodStart = $salary->start_date 
                ? Carbon::parse($salary->start_date) 
                : now()->startOfMonth();
        }

        // End date is end of current month or salary end date (whichever is earlier)
        $periodEnd = now()->endOfMonth();
        if ($salary->end_date && Carbon::parse($salary->end_date)->lt($periodEnd)) {
            $periodEnd = Carbon::parse($salary->end_date);
        }

        // If start date is in the future, return null
        if ($periodStart->isFuture() || $periodStart->gt($periodEnd)) {
            return response()->json([
                'success' => false,
                'message' => 'No pending period to generate.'
            ]);
        }

        return response()->json([
            'success' => true,
            'period_start' => $periodStart->toDateString(),
            'period_end' => $periodEnd->toDateString(),
        ]);
    }

    /**
     * Generate and store salary payment based on salary configuration.
     * Generates separate payments for each month in the period.
     */
    public function storeGenerated(Request $request)
    {
        $this->authorizePermission('salary-payments.create');

        $validated = $request->validate([
            'salary_id' => 'required|exists:salaries,id',
            'payment_period_start' => 'required|date',
            'payment_period_end' => 'required|date|after_or_equal:payment_period_start',
            'bonus_amount' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $salary = Salary::with('employee')->findOrFail($validated['salary_id']);
        $this->validateGymAccess($salary->gym_id);

        $periodStart = Carbon::parse($validated['payment_period_start']);
        $periodEnd = Carbon::parse($validated['payment_period_end']);

        // Split period into monthly chunks
        $generatedPayments = [];
        $currentStart = $periodStart->copy();
        
        // Count total months to split bonus and deductions
        $totalMonths = 0;
        $tempStart = $periodStart->copy();
        while ($tempStart->lte($periodEnd)) {
            $totalMonths++;
            $tempStart->addMonth()->startOfMonth();
        }
        
        $monthlyBonus = $totalMonths > 0 ? ($validated['bonus_amount'] ?? 0) / $totalMonths : 0;
        $monthlyDeductions = $totalMonths > 0 ? ($validated['deductions'] ?? 0) / $totalMonths : 0;
        
        while ($currentStart->lte($periodEnd)) {
            // Get end of current month or period end, whichever is earlier
            $monthEnd = $currentStart->copy()->endOfMonth();
            $currentEnd = $monthEnd->lt($periodEnd) ? $monthEnd : $periodEnd->copy();
            
            // Check if payment already exists for this month
            $existingPayment = SalaryPayment::where('salary_id', $validated['salary_id'])
                ->where('payment_period_start', $currentStart->toDateString())
                ->where('payment_period_end', $currentEnd->toDateString())
                ->first();

            if (!$existingPayment) {
                // Calculate salary for this month
                $calculated = $salary->calculateSalaryForPeriod($currentStart, $currentEnd);
                
                // Calculate tax
                $employee = $salary->employee;
                $maritalStatus = $employee->marital_status ?? 'single';
                $grossAmount = $calculated['base_amount'] + 
                              ($calculated['commission_amount'] ?? 0) + 
                              $monthlyBonus;
                
                $taxCalculator = app(\App\Services\NepalTaxCalculator::class);
                $taxAmount = $taxCalculator->calculateTaxForPeriod(
                    $calculated['base_amount'],
                    $calculated['commission_amount'] ?? 0,
                    $monthlyBonus,
                    $maritalStatus
                );
                
                // Calculate net amount
                $netAmount = $grossAmount - $taxAmount - $monthlyDeductions;

                $paymentData = [
                    'salary_id' => $validated['salary_id'],
                    'employee_id' => $salary->employee_id,
                    'payment_period_start' => $currentStart->toDateString(),
                    'payment_period_end' => $currentEnd->toDateString(),
                    'base_amount' => $calculated['base_amount'],
                    'commission_amount' => $calculated['commission_amount'] ?? 0,
                    'bonus_amount' => round($monthlyBonus, 2),
                    'deductions' => round($monthlyDeductions, 2),
                    'tax_amount' => $taxAmount,
                    'net_amount' => max(0, round($netAmount, 2)),
                    'payment_status' => 'Pending',
                    'notes' => $validated['notes'] ?? null,
                    'gym_id' => $salary->gym_id,
                    'created_by' => $user->id,
                ];

                $payment = SalaryPayment::create($paymentData);
                $generatedPayments[] = $payment;
            }
            
            // Move to next month
            $currentStart = $currentStart->copy()->addMonth()->startOfMonth();
        }

        if (empty($generatedPayments)) {
            return response()->json([
                'success' => false,
                'message' => 'All payments for this period already exist.'
            ], 422);
        }

        $message = count($generatedPayments) > 1 
            ? count($generatedPayments) . ' salary payments generated successfully.' 
            : 'Salary payment generated successfully.';

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => $message,
                'payments' => $generatedPayments
            ]);
        }

        return redirect()->route('salary-payments.index')
            ->with('success', $message);
    }

    /**
     * Show the form for creating a manual salary payment.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('salary-payments.create');

        $user = Auth::user();

        // Get active salaries
        $salariesQuery = Salary::with('employee')->where('status', 'active');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $salariesQuery->where('gym_id', $user->gym_id);
        }
        $salaries = $salariesQuery->get();

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('salary-payments._manual-payment-form', compact('salaries'))->render()
            ]);
        }

        return view('salary-payments.create-page', compact('salaries'));
    }

    /**
     * Store a manually created salary payment.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('salary-payments.create');

        $validated = $request->validate([
            'salary_id' => 'required|exists:salaries,id',
            'payment_period_start' => 'required|date',
            'payment_period_end' => 'required|date|after_or_equal:payment_period_start',
            'base_amount' => 'required|numeric|min:0',
            'commission_amount' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'net_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Cash,Bank Transfer,Cheque,Online',
            'payment_status' => 'required|in:Pending,Paid,Failed,Cancelled',
            'payment_date' => 'nullable|date',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $salary = Salary::findOrFail($validated['salary_id']);
        $this->validateGymAccess($salary->gym_id);

        $paymentData = [
            'salary_id' => $validated['salary_id'],
            'employee_id' => $salary->employee_id,
            'payment_period_start' => $validated['payment_period_start'],
            'payment_period_end' => $validated['payment_period_end'],
            'base_amount' => $validated['base_amount'],
            'commission_amount' => $validated['commission_amount'] ?? 0,
            'bonus_amount' => $validated['bonus_amount'] ?? 0,
            'deductions' => $validated['deductions'] ?? 0,
            'net_amount' => $validated['net_amount'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_status'],
            'payment_date' => $validated['payment_date'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'gym_id' => $salary->gym_id,
            'created_by' => $user->id,
        ];

        $payment = SalaryPayment::create($paymentData);

        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'message' => 'Salary payment created successfully.',
                'payment' => $payment
            ]);
        }

        return redirect()->route('salary-payments.index')
            ->with('success', 'Salary payment created successfully.');
    }

    /**
     * Display the specified salary payment.
     */
    public function show(Request $request, string $id)
    {
        $this->authorizePermission('salary-payments.view');

        $payment = SalaryPayment::with(['employee', 'salary', 'gym', 'salaryDeductions'])->findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'payment' => $payment,
                'html' => view('salary-payments.show', compact('payment'))->render()
            ]);
        }

        return view('salary-payments.show', compact('payment'));
    }

    /**
     * Mark salary payment as paid.
     */
    public function markAsPaid(Request $request, string $id)
    {
        $this->authorizePermission('salary-payments.update');

        $payment = SalaryPayment::findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        $validated = $request->validate([
            'payment_date' => 'nullable|date',
            'payment_method' => 'required|in:Cash,Bank Transfer,Cheque,Online',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $payment->payment_status = 'Paid';
        $payment->payment_date = $validated['payment_date'] ?? now();
        $payment->payment_method = $validated['payment_method'];
        $payment->transaction_id = $validated['transaction_id'] ?? null;
        $payment->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Salary payment marked as paid.',
                'payment' => $payment
            ]);
        }

        return redirect()->route('salary-payments.show', $id)
            ->with('success', 'Salary payment marked as paid.');
    }

    /**
     * Update payment status from list.
     */
    public function updateStatus(Request $request, string $id)
    {
        $this->authorizePermission('salary-payments.update');
        
        $payment = SalaryPayment::findOrFail($id);
        $this->validateGymAccess($payment->gym_id);
        
        $rules = [
            'payment_status' => 'required|in:Pending,Paid,Failed,Cancelled',
            'payment_method' => 'nullable|in:Cash,Bank Transfer,Cheque,Online',
            'transaction_id' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'payment_receipt' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120', // 5MB max
        ];
        
        // If status is Paid, make payment_method and payment_date required
        if ($request->input('payment_status') === 'Paid') {
            $rules['payment_method'] = 'required|in:Cash,Bank Transfer,Cheque,Online';
            $rules['payment_date'] = 'required|date';
        }
        
        $validated = $request->validate($rules);
        
        $payment->payment_status = $validated['payment_status'];
        
        if ($validated['payment_status'] === 'Paid') {
            $payment->payment_method = $validated['payment_method'] ?? $payment->payment_method ?? 'Bank Transfer';
            $payment->payment_date = $validated['payment_date'] ?? now();
            if (isset($validated['transaction_id'])) {
                $payment->transaction_id = $validated['transaction_id'];
            }
            
            // Handle receipt upload with enhanced security
            if ($request->hasFile('payment_receipt')) {
                $file = $request->file('payment_receipt');
                
                // Enhanced file type validation for receipts (images and PDFs)
                $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'application/pdf'];
                $allowedExtensions = ['jpeg', 'png', 'jpg', 'gif', 'pdf'];
                
                if (!validate_file_type($file, $allowedMimes, $allowedExtensions)) {
                    if ($request->expectsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid file type. Only JPEG, PNG, JPG, GIF images and PDF files are allowed.'
                        ], 422);
                    }
                    return back()->withErrors(['payment_receipt' => 'Invalid file type. Only JPEG, PNG, JPG, GIF images and PDF files are allowed.'])->withInput();
                }

                // Sanitize filename and store
                $originalName = sanitize_filename($file->getClientOriginalName());
                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = 'receipt_' . $payment->id . '_' . time() . '.' . $extension;
                $path = $file->storeAs('salary-receipts', $fileName, 'public');
                $payment->payment_receipt = $path;
            }
        } elseif ($validated['payment_status'] === 'Pending') {
            $payment->payment_date = null;
        }
        
        $payment->save();
        
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Payment status updated successfully.',
                'payment' => $payment
            ]);
        }
        
        return redirect()->route('salary-payments.index')
            ->with('success', 'Payment status updated successfully.');
    }

    /**
     * Generate and download payslip.
     */
    public function payslip(Request $request, string $id)
    {
        $this->authorizePermission('salary-payments.view');

        $payment = SalaryPayment::with(['employee', 'salary', 'gym', 'salaryDeductions'])->findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        // Only allow payslip for paid payments
        if ($payment->payment_status !== 'Paid') {
            if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payslip is only available for paid payments. Current status: ' . $payment->payment_status
                ], 403);
            }
            
            return redirect()->route('salary-payments.index')
                ->with('error', 'Payslip is only available for paid payments.');
        }

        // Check if request is for modal (AJAX/JSON)
        $isModal = $request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest';
        
        if ($isModal) {
            // Return JSON with HTML for modal - use separate view without layout
            $html = view('salary-payments.payslip-modal', compact('payment'))->render();
            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        }

        // Fallback: return full page view (for direct URL access)
        return view('salary-payments.payslip', compact('payment'));
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $this->authorizePermission('salary-payments.view');

        $query = SalaryPayment::with(['employee', 'salary', 'gym']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->dateBetween($request->start_date, $request->end_date);
        }
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $payments = $this->applyGymFilter($query)->latest('payment_period_start')->get();

        return $this->apiSuccess([
            'payments' => $payments,
            'count' => $payments->count()
        ], 'Salary payments retrieved successfully');
    }

    public function apiGenerate(Request $request)
    {
        $this->authorizePermission('salary-payments.create');

        $user = Auth::user();
        $salariesQuery = Salary::with('employee')->where('status', 'active');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $salariesQuery->where('gym_id', $user->gym_id);
        }
        $salaries = $salariesQuery->get();

        return $this->apiSuccess([
            'salaries' => $salaries
        ], 'Generate form data retrieved successfully');
    }

    public function apiGetNextPeriod(Request $request, $salaryId)
    {
        $this->authorizePermission('salary-payments.create');

        $salary = Salary::with('employee')->findOrFail($salaryId);
        $user = Auth::user();
        $this->validateGymAccess($salary->gym_id);

        $lastPayment = SalaryPayment::where('salary_id', $salaryId)
            ->latest('payment_period_end')
            ->first();

        if ($lastPayment) {
            $nextStart = Carbon::parse($lastPayment->payment_period_end)->addDay();
        } else {
            $nextStart = Carbon::parse($salary->start_date)->startOfMonth();
        }

        $nextEnd = $nextStart->copy()->endOfMonth();

        return $this->apiSuccess([
            'period_start' => $nextStart->toDateString(),
            'period_end' => $nextEnd->toDateString()
        ], 'Next period retrieved successfully');
    }

    public function apiStoreGenerated(Request $request)
    {
        $this->authorizePermission('salary-payments.create');

        $validated = $request->validate([
            'salary_id' => 'required|exists:salaries,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'months' => 'required|integer|min:1|max:12',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $salary = Salary::with('employee')->findOrFail($validated['salary_id']);
        $this->validateGymAccess($salary->gym_id);

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $months = $validated['months'];

        $generatedPayments = [];
        $currentStart = $startDate->copy()->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $currentEnd = $currentStart->copy()->endOfMonth();
            
            if ($currentEnd->greaterThan($endDate)) {
                $currentEnd = $endDate;
            }

            $existingPayment = SalaryPayment::where('salary_id', $validated['salary_id'])
                ->where('payment_period_start', $currentStart->toDateString())
                ->where('payment_period_end', $currentEnd->toDateString())
                ->first();

            if ($existingPayment) {
                $currentStart = $currentStart->copy()->addMonth()->startOfMonth();
                continue;
            }

            $calculated = $salary->calculateForPeriod($currentStart, $currentEnd);
            $grossAmount = $calculated['base_amount'] + ($calculated['commission_amount'] ?? 0);
            $monthlyBonus = $salary->bonus_amount ?? 0;
            $monthlyDeductions = $salary->deductions ?? 0;
            $maritalStatus = $salary->employee->marital_status ?? 'single';
            
            $taxCalculator = app(\App\Services\NepalTaxCalculator::class);
            $taxAmount = $taxCalculator->calculateTaxForPeriod(
                $calculated['base_amount'],
                $calculated['commission_amount'] ?? 0,
                $monthlyBonus,
                $maritalStatus
            );
            
            $netAmount = $grossAmount - $taxAmount - $monthlyDeductions;

            $paymentData = [
                'salary_id' => $validated['salary_id'],
                'employee_id' => $salary->employee_id,
                'payment_period_start' => $currentStart->toDateString(),
                'payment_period_end' => $currentEnd->toDateString(),
                'base_amount' => $calculated['base_amount'],
                'commission_amount' => $calculated['commission_amount'] ?? 0,
                'bonus_amount' => round($monthlyBonus, 2),
                'deductions' => round($monthlyDeductions, 2),
                'tax_amount' => $taxAmount,
                'net_amount' => max(0, round($netAmount, 2)),
                'payment_status' => 'Pending',
                'notes' => $validated['notes'] ?? null,
                'gym_id' => $salary->gym_id,
                'created_by' => $user->id,
            ];

            $payment = SalaryPayment::create($paymentData);
            $generatedPayments[] = $payment;
            
            $currentStart = $currentStart->copy()->addMonth()->startOfMonth();
        }

        if (empty($generatedPayments)) {
            return $this->apiError('All payments for this period already exist.', null, 422);
        }

        $message = count($generatedPayments) > 1 
            ? count($generatedPayments) . ' salary payments generated successfully.' 
            : 'Salary payment generated successfully.';

        return $this->apiSuccess($generatedPayments, $message, 201);
    }

    public function apiStore(Request $request)
    {
        $this->authorizePermission('salary-payments.create');

        $validated = $request->validate([
            'salary_id' => 'required|exists:salaries,id',
            'payment_period_start' => 'required|date',
            'payment_period_end' => 'required|date|after_or_equal:payment_period_start',
            'base_amount' => 'required|numeric|min:0',
            'commission_amount' => 'nullable|numeric|min:0',
            'bonus_amount' => 'nullable|numeric|min:0',
            'deductions' => 'nullable|numeric|min:0',
            'net_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Cash,Bank Transfer,Cheque,Online',
            'payment_status' => 'required|in:Pending,Paid,Failed,Cancelled',
            'payment_date' => 'nullable|date',
            'transaction_id' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $salary = Salary::findOrFail($validated['salary_id']);
        $this->validateGymAccess($salary->gym_id);

        $paymentData = [
            'salary_id' => $validated['salary_id'],
            'employee_id' => $salary->employee_id,
            'payment_period_start' => $validated['payment_period_start'],
            'payment_period_end' => $validated['payment_period_end'],
            'base_amount' => $validated['base_amount'],
            'commission_amount' => $validated['commission_amount'] ?? 0,
            'bonus_amount' => $validated['bonus_amount'] ?? 0,
            'deductions' => $validated['deductions'] ?? 0,
            'net_amount' => $validated['net_amount'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_status'],
            'payment_date' => $validated['payment_date'] ?? null,
            'transaction_id' => $validated['transaction_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'gym_id' => $salary->gym_id,
            'created_by' => $user->id,
        ];

        $payment = SalaryPayment::create($paymentData);

        return $this->apiSuccess($payment->load(['employee', 'salary', 'gym']), 'Salary payment created successfully', 201);
    }

    public function apiShow(Request $request, string $id)
    {
        $this->authorizePermission('salary-payments.view');

        $payment = SalaryPayment::with(['employee', 'salary', 'gym', 'salaryDeductions'])->findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        return $this->apiSuccess($payment, 'Salary payment retrieved successfully');
    }

    public function apiPayslip(Request $request, string $id)
    {
        $this->authorizePermission('salary-payments.view');

        $payment = SalaryPayment::with(['employee', 'salary', 'gym', 'salaryDeductions'])->findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        if ($payment->payment_status !== 'Paid') {
            return $this->apiError('Payslip is only available for paid payments. Current status: ' . $payment->payment_status, null, 403);
        }

        return $this->apiSuccess([
            'payment' => $payment,
            'payslip_html' => view('salary-payments.payslip-modal', compact('payment'))->render()
        ], 'Payslip retrieved successfully');
    }

    public function apiMarkAsPaid(Request $request, string $id)
    {
        $this->authorizePermission('salary-payments.update');

        $payment = SalaryPayment::findOrFail($id);
        $this->validateGymAccess($payment->gym_id);

        $validated = $request->validate([
            'payment_date' => 'nullable|date',
            'payment_method' => 'required|in:Cash,Bank Transfer,Cheque,Online',
            'transaction_id' => 'nullable|string|max:255',
        ]);

        $payment->payment_status = 'Paid';
        $payment->payment_date = $validated['payment_date'] ?? now();
        $payment->payment_method = $validated['payment_method'];
        $payment->transaction_id = $validated['transaction_id'] ?? null;
        $payment->save();

        return $this->apiSuccess($payment->load(['employee', 'salary', 'gym']), 'Salary payment marked as paid');
    }

    public function apiUpdateStatus(Request $request, string $id)
    {
        $this->authorizePermission('salary-payments.update');
        
        $payment = SalaryPayment::findOrFail($id);
        $this->validateGymAccess($payment->gym_id);
        
        $rules = [
            'payment_status' => 'required|in:Pending,Paid,Failed,Cancelled',
            'payment_method' => 'nullable|in:Cash,Bank Transfer,Cheque,Online',
            'transaction_id' => 'nullable|string|max:255',
            'payment_date' => 'nullable|date',
            'payment_receipt' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:5120',
        ];
        
        if ($request->input('payment_status') === 'Paid') {
            $rules['payment_method'] = 'required|in:Cash,Bank Transfer,Cheque,Online';
            $rules['payment_date'] = 'required|date';
        }
        
        $validated = $request->validate($rules);
        
        $payment->payment_status = $validated['payment_status'];
        
        if ($validated['payment_status'] === 'Paid') {
            $payment->payment_method = $validated['payment_method'] ?? $payment->payment_method ?? 'Bank Transfer';
            $payment->payment_date = $validated['payment_date'] ?? now();
            if (isset($validated['transaction_id'])) {
                $payment->transaction_id = $validated['transaction_id'];
            }
            
            if ($request->hasFile('payment_receipt')) {
                $file = $request->file('payment_receipt');
                $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'application/pdf'];
                $allowedExtensions = ['jpeg', 'png', 'jpg', 'gif', 'pdf'];
                
                if (!validate_file_type($file, $allowedMimes, $allowedExtensions)) {
                    return $this->apiValidationError(['payment_receipt' => ['Invalid file type']], 'Invalid file type');
                }

                $originalName = sanitize_filename($file->getClientOriginalName());
                $extension = strtolower($file->getClientOriginalExtension());
                $fileName = 'receipt_' . $payment->id . '_' . time() . '.' . $extension;
                $path = $file->storeAs('salary-receipts', $fileName, 'public');
                $payment->payment_receipt = $path;
            }
        } elseif ($validated['payment_status'] === 'Pending') {
            $payment->payment_date = null;
        }
        
        $payment->save();
        
        return $this->apiSuccess($payment->load(['employee', 'salary', 'gym']), 'Payment status updated successfully');
    }
}
