<?php

namespace App\Http\Controllers;

use App\Models\MembershipPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * MembershipPlanController
 * 
 * Handles membership plan management with gym-based data isolation.
 * All queries are automatically filtered by gym_id.
 */
class MembershipPlanController extends BaseController
{
    /**
     * Display a listing of membership plans.
     * 
     * Returns only plans from the user's gym (unless SuperAdmin).
     */
    public function index(Request $request)
    {
        // Authorize permission
        $this->authorizePermission('membership-plans.view');

        // Get query with gym filter applied
        $query = MembershipPlan::query();
        
        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $plans = $this->applyGymFilter($query)->latest()->get();

        // If AJAX request, return JSON with plans list
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('membership-plans._gyms-list', compact('plans'))->render()
            ]);
        }

        return view('membership-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new membership plan.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('membership-plans.create');

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('membership-plans.create')->render();
        }

        return view('membership-plans.create');
    }

    /**
     * Store a newly created membership plan.
     * 
     * Automatically sets gym_id and created_by.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('membership-plans.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'allows_class_booking' => 'boolean',
            'allowed_bookings_per_week' => 'nullable|integer|min:0',
            'has_discount' => 'boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_start_date' => 'nullable|date',
            'discount_end_date' => 'nullable|date|after_or_equal:discount_start_date',
            'discount_description' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Prepare plan data
        $planData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'duration_days' => $validated['duration_days'],
            'price' => $validated['price'],
            'is_active' => $validated['is_active'] ?? true,
            'allows_class_booking' => $validated['allows_class_booking'] ?? true,
            'allowed_bookings_per_week' => $validated['allowed_bookings_per_week'] ?? ($validated['allows_class_booking'] ? 3 : null),
            'has_discount' => $validated['has_discount'] ?? false,
            'discount_percentage' => $validated['discount_percentage'] ?? null,
            'discount_amount' => $validated['discount_amount'] ?? null,
            'discount_start_date' => $validated['discount_start_date'] ?? null,
            'discount_end_date' => $validated['discount_end_date'] ?? null,
            'discount_description' => $validated['discount_description'] ?? null,
        ];

        // Set gym_id (SuperAdmin must specify, others use their gym_id)
        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $planData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $planData['gym_id'] = $user->gym_id;
        }

        $planData['created_by'] = $user->id;

        $plan = MembershipPlan::create($planData);

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Membership plan created successfully.',
                'plan' => $plan
            ]);
        }

        return redirect()->route('membership-plans.index')
            ->with('success', 'Membership plan created successfully.');
    }

    /**
     * Display the specified membership plan.
     * 
     * Validates gym access before showing.
     */
    public function show(Request $request, string $id)
    {
        $this->authorizePermission('membership-plans.view');

        $plan = MembershipPlan::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($plan->gym_id);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'plan' => $plan,
                'html' => view('membership-plans.show', compact('plan'))->render()
            ]);
        }

        return view('membership-plans.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified membership plan.
     */
    public function edit(Request $request, string $id)
    {
        $this->authorizePermission('membership-plans.update');

        $plan = MembershipPlan::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($plan->gym_id);

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('membership-plans.edit', compact('plan'))->render();
        }

        return view('membership-plans.edit', compact('plan'));
    }

    /**
     * Update the specified membership plan.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizePermission('membership-plans.update');

        $plan = MembershipPlan::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($plan->gym_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'allows_class_booking' => 'boolean',
            'allowed_bookings_per_week' => 'nullable|integer|min:0',
            'has_discount' => 'boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_start_date' => 'nullable|date',
            'discount_end_date' => 'nullable|date|after_or_equal:discount_start_date',
            'discount_description' => 'nullable|string',
        ]);

        // Update plan data
        $plan->name = $validated['name'];
        $plan->description = $validated['description'] ?? null;
        $plan->duration_days = $validated['duration_days'];
        $plan->price = $validated['price'];
        $plan->is_active = $validated['is_active'] ?? $plan->is_active;
        $plan->allows_class_booking = $validated['allows_class_booking'] ?? $plan->allows_class_booking;
        $plan->allowed_bookings_per_week = $validated['allowed_bookings_per_week'] ?? ($validated['allows_class_booking'] ? 3 : null);
        $plan->has_discount = $validated['has_discount'] ?? false;
        $plan->discount_percentage = $validated['discount_percentage'] ?? null;
        $plan->discount_amount = $validated['discount_amount'] ?? null;
        $plan->discount_start_date = $validated['discount_start_date'] ?? null;
        $plan->discount_end_date = $validated['discount_end_date'] ?? null;
        $plan->discount_description = $validated['discount_description'] ?? null;

        $plan->save();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Membership plan updated successfully.',
                'plan' => $plan
            ]);
        }

        return redirect()->route('membership-plans.index')
            ->with('success', 'Membership plan updated successfully.');
    }

    /**
     * Remove the specified membership plan.
     */
    public function destroy(Request $request, string $id)
    {
        $this->authorizePermission('membership-plans.delete');

        $plan = MembershipPlan::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($plan->gym_id);

        // Check if plan has associated payments
        if ($plan->payments()->count() > 0) {
            $message = 'Cannot delete membership plan. It has associated payments.';
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            
            return redirect()->route('membership-plans.index')
                ->with('error', $message);
        }

        $plan->delete();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Membership plan deleted successfully.'
            ]);
        }

        return redirect()->route('membership-plans.index')
            ->with('success', 'Membership plan deleted successfully.');
    }

    /**
     * Update membership plan status (active/inactive).
     */
    public function updateStatus(Request $request, $plan)
    {
        $this->authorizePermission('membership-plans.update');

        $plan = MembershipPlan::findOrFail($plan);
        
        // Validate gym access
        $this->validateGymAccess($plan->gym_id);

        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        $plan->is_active = $request->is_active;
        $plan->save();

        return redirect()->route('membership-plans.index')
            ->with('success', 'Membership plan status updated successfully.');
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $this->authorizePermission('membership-plans.view');

        $query = MembershipPlan::query();
        
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        $plans = $this->applyGymFilter($query)->latest()->get();

        return $this->apiSuccess([
            'plans' => $plans,
            'count' => $plans->count()
        ], 'Membership plans retrieved successfully');
    }

    public function apiShow(Request $request, string $id)
    {
        $this->authorizePermission('membership-plans.view');

        $plan = MembershipPlan::findOrFail($id);
        $this->validateGymAccess($plan->gym_id);

        return $this->apiSuccess($plan, 'Membership plan retrieved successfully');
    }

    public function apiStore(Request $request)
    {
        $this->authorizePermission('membership-plans.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'allows_class_booking' => 'boolean',
            'allowed_bookings_per_week' => 'nullable|integer|min:0',
            'has_discount' => 'boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_start_date' => 'nullable|date',
            'discount_end_date' => 'nullable|date|after_or_equal:discount_start_date',
            'discount_description' => 'nullable|string',
        ]);

        $user = Auth::user();
        $planData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'duration_days' => $validated['duration_days'],
            'price' => $validated['price'],
            'is_active' => $validated['is_active'] ?? true,
            'allows_class_booking' => $validated['allows_class_booking'] ?? false,
            'allowed_bookings_per_week' => $validated['allowed_bookings_per_week'] ?? ($validated['allows_class_booking'] ? 3 : null),
            'has_discount' => $validated['has_discount'] ?? false,
            'discount_percentage' => $validated['discount_percentage'] ?? null,
            'discount_amount' => $validated['discount_amount'] ?? null,
            'discount_start_date' => $validated['discount_start_date'] ?? null,
            'discount_end_date' => $validated['discount_end_date'] ?? null,
            'discount_description' => $validated['discount_description'] ?? null,
            'created_by' => $user->id,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $planData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $planData['gym_id'] = $user->gym_id;
        }

        $plan = MembershipPlan::create($planData);

        return $this->apiSuccess($plan, 'Membership plan created successfully', 201);
    }

    public function apiUpdate(Request $request, string $id)
    {
        $this->authorizePermission('membership-plans.update');

        $plan = MembershipPlan::findOrFail($id);
        $this->validateGymAccess($plan->gym_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'duration_days' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'allows_class_booking' => 'boolean',
            'allowed_bookings_per_week' => 'nullable|integer|min:0',
            'has_discount' => 'boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_start_date' => 'nullable|date',
            'discount_end_date' => 'nullable|date|after_or_equal:discount_start_date',
            'discount_description' => 'nullable|string',
        ]);

        $plan->name = $validated['name'];
        $plan->description = $validated['description'] ?? null;
        $plan->duration_days = $validated['duration_days'];
        $plan->price = $validated['price'];
        $plan->is_active = $validated['is_active'] ?? $plan->is_active;
        $plan->allows_class_booking = $validated['allows_class_booking'] ?? $plan->allows_class_booking;
        $plan->allowed_bookings_per_week = $validated['allowed_bookings_per_week'] ?? ($validated['allows_class_booking'] ? 3 : null);
        $plan->has_discount = $validated['has_discount'] ?? false;
        $plan->discount_percentage = $validated['discount_percentage'] ?? null;
        $plan->discount_amount = $validated['discount_amount'] ?? null;
        $plan->discount_start_date = $validated['discount_start_date'] ?? null;
        $plan->discount_end_date = $validated['discount_end_date'] ?? null;
        $plan->discount_description = $validated['discount_description'] ?? null;
        $plan->save();

        return $this->apiSuccess($plan, 'Membership plan updated successfully');
    }

    public function apiDestroy(Request $request, string $id)
    {
        $this->authorizePermission('membership-plans.delete');

        $plan = MembershipPlan::findOrFail($id);
        $this->validateGymAccess($plan->gym_id);

        if ($plan->payments()->count() > 0) {
            return $this->apiError('Cannot delete membership plan. It has associated payments.', null, 422);
        }

        $plan->delete();

        return $this->apiSuccess(null, 'Membership plan deleted successfully');
    }

    public function apiUpdateStatus(Request $request, $plan)
    {
        $this->authorizePermission('membership-plans.update');

        $plan = MembershipPlan::findOrFail($plan);
        $this->validateGymAccess($plan->gym_id);

        $request->validate(['is_active' => 'required|boolean']);

        $plan->is_active = $request->is_active;
        $plan->save();

        return $this->apiSuccess($plan, 'Membership plan status updated successfully');
    }
}
