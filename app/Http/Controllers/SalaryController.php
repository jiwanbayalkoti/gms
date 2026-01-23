<?php

namespace App\Http\Controllers;

use App\Models\Salary;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SalaryController
 * 
 * Handles salary management with gym-based data isolation.
 */
class SalaryController extends BaseController
{
    /**
     * Display a listing of salaries.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('salaries.view');

        $query = Salary::with(['employee', 'gym']);
        
        // Filter by employee
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $salaries = $this->applyGymFilter($query)->latest()->get();
        
        // Get employees for filter dropdown
        $user = Auth::user();
        $employeesQuery = User::whereIn('role', ['Trainer', 'Staff']);
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $employeesQuery->where('gym_id', $user->gym_id);
        }
        $employees = $employeesQuery->get();

        // Check if request is from API (mobile app) or wants JSON
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'salaries' => $salaries
                ]
            ]);
        }

        // For web AJAX requests, return HTML
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'html' => view('salaries._table-body', compact('salaries'))->render()
            ]);
        }

        return view('salaries.index', compact('salaries', 'employees'));
    }

    /**
     * Show the form for creating a new salary.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('salaries.create');

        $user = Auth::user();

        // Get employees (Trainers and Staff)
        $employeesQuery = User::whereIn('role', ['Trainer', 'Staff']);
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $employeesQuery->where('gym_id', $user->gym_id);
        }
        $employees = $employeesQuery->get();

        if ($request->expectsJson() || $request->ajax()) {
            return view('salaries.create', compact('employees'))->render();
        }

        return view('salaries.create-page', compact('employees'));
    }

    /**
     * Store a newly created salary.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('salaries.create');

        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'salary_type' => 'required|in:fixed,hourly,commission,hybrid',
            'base_salary' => 'nullable|numeric|min:0|required_if:salary_type,fixed,hybrid',
            'hourly_rate' => 'nullable|numeric|min:0|required_if:salary_type,hourly',
            'commission_percentage' => 'nullable|numeric|min:0|max:100|required_if:salary_type,commission,hybrid',
            'payment_frequency' => 'required|in:monthly,weekly,bi-weekly,daily',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:active,inactive,terminated',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $employee = User::findOrFail($validated['employee_id']);
        if (!$user->isSuperAdmin() && $employee->gym_id !== $user->gym_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid employee selected.'
            ], 422);
        }

        // Check if employee already has an active salary
        $existingSalary = Salary::where('employee_id', $validated['employee_id'])
            ->where('status', 'active')
            ->first();

        if ($existingSalary && $validated['status'] === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Employee already has an active salary. Please deactivate it first.'
            ], 422);
        }

        $salaryData = [
            'employee_id' => $validated['employee_id'],
            'salary_type' => $validated['salary_type'],
            'base_salary' => $validated['base_salary'] ?? null,
            'hourly_rate' => $validated['hourly_rate'] ?? null,
            'commission_percentage' => $validated['commission_percentage'] ?? null,
            'payment_frequency' => $validated['payment_frequency'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $salaryData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $salaryData['gym_id'] = $user->gym_id;
        }

        $salaryData['created_by'] = $user->id;

        $salary = Salary::create($salaryData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Salary created successfully.',
                'salary' => $salary
            ]);
        }

        return redirect()->route('salaries.index')
            ->with('success', 'Salary created successfully.');
    }

    /**
     * Display the specified salary.
     */
    public function show(Request $request, string $id)
    {
        $this->authorizePermission('salaries.view');

        $salary = Salary::with(['employee', 'gym', 'salaryPayments'])->findOrFail($id);
        $this->validateGymAccess($salary->gym_id);

        // Check if request is from API (mobile app) or wants JSON
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'salary' => $salary
                ]
            ]);
        }

        // For web AJAX requests, return JSON with HTML
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'salary' => $salary,
                'html' => view('salaries.show', compact('salary'))->render()
            ]);
        }

        return view('salaries.show', compact('salary'));
    }

    /**
     * Show the form for editing the specified salary.
     */
    public function edit(Request $request, string $id)
    {
        $this->authorizePermission('salaries.update');

        $salary = Salary::findOrFail($id);
        $this->validateGymAccess($salary->gym_id);

        $user = Auth::user();

        // Get employees
        $employeesQuery = User::whereIn('role', ['Trainer', 'Staff']);
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $employeesQuery->where('gym_id', $user->gym_id);
        }
        $employees = $employeesQuery->get();

        if ($request->expectsJson() || $request->ajax()) {
            return view('salaries.edit', compact('salary', 'employees'))->render();
        }

        return view('salaries.edit-page', compact('salary', 'employees'));
    }

    /**
     * Update the specified salary.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizePermission('salaries.update');

        $salary = Salary::findOrFail($id);
        $this->validateGymAccess($salary->gym_id);

        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'salary_type' => 'required|in:fixed,hourly,commission,hybrid',
            'base_salary' => 'nullable|numeric|min:0|required_if:salary_type,fixed,hybrid',
            'hourly_rate' => 'nullable|numeric|min:0|required_if:salary_type,hourly',
            'commission_percentage' => 'nullable|numeric|min:0|max:100|required_if:salary_type,commission,hybrid',
            'payment_frequency' => 'required|in:monthly,weekly,bi-weekly,daily',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:active,inactive,terminated',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $employee = User::findOrFail($validated['employee_id']);
        if (!$user->isSuperAdmin() && $employee->gym_id !== $user->gym_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid employee selected.'
            ], 422);
        }

        // Check if another employee has active salary if status is active
        if ($validated['status'] === 'active' && $validated['employee_id'] != $salary->employee_id) {
            $existingSalary = Salary::where('employee_id', $validated['employee_id'])
                ->where('status', 'active')
                ->where('id', '!=', $id)
                ->first();

            if ($existingSalary) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee already has an active salary.'
                ], 422);
            }
        }

        $salary->employee_id = $validated['employee_id'];
        $salary->salary_type = $validated['salary_type'];
        $salary->base_salary = $validated['base_salary'] ?? null;
        $salary->hourly_rate = $validated['hourly_rate'] ?? null;
        $salary->commission_percentage = $validated['commission_percentage'] ?? null;
        $salary->payment_frequency = $validated['payment_frequency'];
        $salary->start_date = $validated['start_date'];
        $salary->end_date = $validated['end_date'] ?? null;
        $salary->status = $validated['status'];
        $salary->notes = $validated['notes'] ?? null;
        $salary->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Salary updated successfully.',
                'salary' => $salary
            ]);
        }

        return redirect()->route('salaries.index')
            ->with('success', 'Salary updated successfully.');
    }

    /**
     * Remove the specified salary.
     */
    public function destroy(Request $request, string $id)
    {
        $this->authorizePermission('salaries.delete');

        $salary = Salary::findOrFail($id);
        $this->validateGymAccess($salary->gym_id);

        // Check if salary has payments
        if ($salary->salaryPayments()->count() > 0) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete salary. It has associated salary payments.'
                ], 422);
            }
            return redirect()->route('salaries.index')
                ->withErrors(['error' => 'Cannot delete salary. It has associated salary payments.']);
        }

        $salary->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Salary deleted successfully.'
            ]);
        }

        return redirect()->route('salaries.index')
            ->with('success', 'Salary deleted successfully.');
    }

    /**
     * Toggle salary status (active/inactive).
     */
    public function toggleStatus(Request $request, string $id)
    {
        $this->authorizePermission('salaries.update');

        $salary = Salary::findOrFail($id);
        $this->validateGymAccess($salary->gym_id);

        $salary->status = $salary->status === 'active' ? 'inactive' : 'active';
        $salary->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Salary status updated successfully.',
                'salary' => $salary
            ]);
        }

        return redirect()->route('salaries.index')
            ->with('success', 'Salary status updated successfully.');
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $this->authorizePermission('salaries.view');

        $query = Salary::with(['employee', 'gym']);
        
        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $salaries = $this->applyGymFilter($query)->latest()->get();

        return $this->apiSuccess([
            'salaries' => $salaries,
            'count' => $salaries->count()
        ], 'Salaries retrieved successfully');
    }

    public function apiShow(Request $request, string $id)
    {
        $this->authorizePermission('salaries.view');

        $salary = Salary::with(['employee', 'gym'])->findOrFail($id);
        $this->validateGymAccess($salary->gym_id);

        return $this->apiSuccess($salary, 'Salary retrieved successfully');
    }

    public function apiStore(Request $request)
    {
        $this->authorizePermission('salaries.create');

        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'salary_type' => 'required|in:fixed,hourly,commission,hybrid',
            'base_salary' => 'nullable|numeric|min:0|required_if:salary_type,fixed,hybrid',
            'hourly_rate' => 'nullable|numeric|min:0|required_if:salary_type,hourly',
            'commission_percentage' => 'nullable|numeric|min:0|max:100|required_if:salary_type,commission,hybrid',
            'payment_frequency' => 'required|in:monthly,weekly,bi-weekly,daily',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:active,inactive,terminated',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $employee = User::findOrFail($validated['employee_id']);
        
        if (!$user->isSuperAdmin() && $employee->gym_id !== $user->gym_id) {
            return $this->apiError('Invalid employee selected.', null, 422);
        }

        if ($validated['status'] === 'active') {
            $existingSalary = Salary::where('employee_id', $validated['employee_id'])
                ->where('status', 'active')
                ->first();

            if ($existingSalary) {
                return $this->apiError('Employee already has an active salary.', null, 422);
            }
        }

        $salaryData = [
            'employee_id' => $validated['employee_id'],
            'salary_type' => $validated['salary_type'],
            'base_salary' => $validated['base_salary'] ?? null,
            'hourly_rate' => $validated['hourly_rate'] ?? null,
            'commission_percentage' => $validated['commission_percentage'] ?? null,
            'payment_frequency' => $validated['payment_frequency'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'] ?? null,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $salaryData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $salaryData['gym_id'] = $user->gym_id;
        }

        $salary = Salary::create($salaryData);

        return $this->apiSuccess($salary->load(['employee', 'gym']), 'Salary created successfully', 201);
    }

    public function apiUpdate(Request $request, string $id)
    {
        $this->authorizePermission('salaries.update');

        $salary = Salary::findOrFail($id);
        $this->validateGymAccess($salary->gym_id);

        $validated = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'salary_type' => 'required|in:fixed,hourly,commission,hybrid',
            'base_salary' => 'nullable|numeric|min:0|required_if:salary_type,fixed,hybrid',
            'hourly_rate' => 'nullable|numeric|min:0|required_if:salary_type,hourly',
            'commission_percentage' => 'nullable|numeric|min:0|max:100|required_if:salary_type,commission,hybrid',
            'payment_frequency' => 'required|in:monthly,weekly,bi-weekly,daily',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:active,inactive,terminated',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();
        $employee = User::findOrFail($validated['employee_id']);
        
        if (!$user->isSuperAdmin() && $employee->gym_id !== $user->gym_id) {
            return $this->apiError('Invalid employee selected.', null, 422);
        }

        if ($validated['status'] === 'active' && $validated['employee_id'] != $salary->employee_id) {
            $existingSalary = Salary::where('employee_id', $validated['employee_id'])
                ->where('status', 'active')
                ->where('id', '!=', $id)
                ->first();

            if ($existingSalary) {
                return $this->apiError('Employee already has an active salary.', null, 422);
            }
        }

        $salary->employee_id = $validated['employee_id'];
        $salary->salary_type = $validated['salary_type'];
        $salary->base_salary = $validated['base_salary'] ?? null;
        $salary->hourly_rate = $validated['hourly_rate'] ?? null;
        $salary->commission_percentage = $validated['commission_percentage'] ?? null;
        $salary->payment_frequency = $validated['payment_frequency'];
        $salary->start_date = $validated['start_date'];
        $salary->end_date = $validated['end_date'] ?? null;
        $salary->status = $validated['status'];
        $salary->notes = $validated['notes'] ?? null;
        $salary->save();

        return $this->apiSuccess($salary->load(['employee', 'gym']), 'Salary updated successfully');
    }

    public function apiDestroy(Request $request, string $id)
    {
        $this->authorizePermission('salaries.delete');

        $salary = Salary::findOrFail($id);
        $this->validateGymAccess($salary->gym_id);

        if ($salary->salaryPayments()->count() > 0) {
            return $this->apiError('Cannot delete salary. It has associated salary payments.', null, 422);
        }

        $salary->delete();

        return $this->apiSuccess(null, 'Salary deleted successfully');
    }

    public function apiToggleStatus(Request $request, string $id)
    {
        $this->authorizePermission('salaries.update');

        $salary = Salary::findOrFail($id);
        $this->validateGymAccess($salary->gym_id);

        $salary->status = $salary->status === 'active' ? 'inactive' : 'active';
        $salary->save();

        return $this->apiSuccess($salary, 'Salary status updated successfully');
    }
}
