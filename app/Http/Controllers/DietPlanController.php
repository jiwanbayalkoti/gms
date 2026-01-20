<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DietPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * DietPlanController
 * 
 * Handles diet plan management with gym-based data isolation.
 */
class DietPlanController extends BaseController
{
    /**
     * Display a listing of diet plans.
     * 
     * For Members, returns only plans assigned to them.
     * For Admin/SuperAdmin, returns all plans from their gym.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('diet-plans.view');

        $user = Auth::user();
        
        // For members, show only plans assigned to them
        if ($user->isMember()) {
            $query = DietPlan::with(['trainer', 'member'])
                ->where('member_id', $user->id);
            $plans = $query->latest()->get();
        } else {
            // For Admin/SuperAdmin, show all plans from their gym
            $query = DietPlan::with(['trainer', 'member']);
            
            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by search
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            $plans = $this->applyGymFilter($query)->latest()->get();
        }

        // If AJAX request, return JSON with table body
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('diet-plans._table-body', compact('plans'))->render()
            ]);
        }

        return view('diet-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new diet plan.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('diet-plans.create');

        $user = Auth::user();

        // Get trainers for the gym
        $trainersQuery = User::where('role', 'Trainer');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $trainersQuery->where('gym_id', $user->gym_id);
        }
        $trainers = $trainersQuery->get();

        // Get members for the gym (optional - for direct assignment)
        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        if ($request->expectsJson() || $request->ajax()) {
            return view('diet-plans.create', compact('trainers', 'members'))->render();
        }

        return view('diet-plans.create-page', compact('trainers', 'members'));
    }

    /**
     * Store a newly created diet plan.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('diet-plans.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trainer_id' => 'required|exists:users,id',
            'member_id' => 'nullable|exists:users,id',
            'is_default' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'breakfast' => 'nullable|string',
            'lunch' => 'nullable|string',
            'dinner' => 'nullable|string',
            'snacks' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Validate trainer belongs to same gym
        $trainer = User::findOrFail($validated['trainer_id']);
        if (!$user->isSuperAdmin() && $trainer->gym_id !== $user->gym_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid trainer selected.'
            ], 422);
        }

        // Validate member if provided
        if ($validated['member_id']) {
            $member = User::findOrFail($validated['member_id']);
            if (!$user->isSuperAdmin() && $member->gym_id !== $user->gym_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid member selected.'
                ], 422);
            }
        }

        $planData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trainer_id' => $validated['trainer_id'],
            'member_id' => $validated['member_id'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'breakfast' => $validated['breakfast'] ?? null,
            'lunch' => $validated['lunch'] ?? null,
            'dinner' => $validated['dinner'] ?? null,
            'snacks' => $validated['snacks'] ?? null,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $planData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $planData['gym_id'] = $user->gym_id;
        }

        $planData['created_by'] = $user->id;

        $plan = DietPlan::create($planData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Diet plan created successfully.',
                'plan' => $plan
            ]);
        }

        return redirect()->route('diet-plans.index')
            ->with('success', 'Diet plan created successfully.');
    }

    /**
     * Display the specified diet plan.
     */
    public function show(Request $request, string $id)
    {
        $this->authorizePermission('diet-plans.view');

        $plan = DietPlan::with(['trainer', 'member'])->findOrFail($id);
        $this->validateGymAccess($plan->gym_id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'plan' => $plan,
                'html' => view('diet-plans.show', compact('plan'))->render()
            ]);
        }

        return view('diet-plans.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified diet plan.
     */
    public function edit(Request $request, string $id)
    {
        $this->authorizePermission('diet-plans.update');

        $plan = DietPlan::findOrFail($id);
        $this->validateGymAccess($plan->gym_id);

        $user = Auth::user();

        $trainersQuery = User::where('role', 'Trainer');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $trainersQuery->where('gym_id', $user->gym_id);
        }
        $trainers = $trainersQuery->get();

        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        if ($request->expectsJson() || $request->ajax()) {
            return view('diet-plans.edit', compact('plan', 'trainers', 'members'))->render();
        }

        return view('diet-plans.edit-page', compact('plan', 'trainers', 'members'));
    }

    /**
     * Update the specified diet plan.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizePermission('diet-plans.update');

        $plan = DietPlan::findOrFail($id);
        $this->validateGymAccess($plan->gym_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trainer_id' => 'required|exists:users,id',
            'member_id' => 'nullable|exists:users,id',
            'is_default' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'breakfast' => 'nullable|string',
            'lunch' => 'nullable|string',
            'dinner' => 'nullable|string',
            'snacks' => 'nullable|string',
        ]);

        $user = Auth::user();

        $trainer = User::findOrFail($validated['trainer_id']);
        if (!$user->isSuperAdmin() && $trainer->gym_id !== $user->gym_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid trainer selected.'
            ], 422);
        }

        if ($validated['member_id']) {
            $member = User::findOrFail($validated['member_id']);
            if (!$user->isSuperAdmin() && $member->gym_id !== $user->gym_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid member selected.'
                ], 422);
            }
        }

        $plan->name = $validated['name'];
        $plan->description = $validated['description'] ?? null;
        $plan->trainer_id = $validated['trainer_id'];
        $plan->member_id = $validated['member_id'] ?? null;
        $plan->is_default = $validated['is_default'] ?? false;
        $plan->start_date = $validated['start_date'] ?? null;
        $plan->end_date = $validated['end_date'] ?? null;
        $plan->notes = $validated['notes'] ?? null;
        $plan->breakfast = $validated['breakfast'] ?? null;
        $plan->lunch = $validated['lunch'] ?? null;
        $plan->dinner = $validated['dinner'] ?? null;
        $plan->snacks = $validated['snacks'] ?? null;
        $plan->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Diet plan updated successfully.',
                'plan' => $plan
            ]);
        }

        return redirect()->route('diet-plans.index')
            ->with('success', 'Diet plan updated successfully.');
    }

    /**
     * Remove the specified diet plan.
     */
    public function destroy(Request $request, string $id)
    {
        $this->authorizePermission('diet-plans.delete');

        $plan = DietPlan::findOrFail($id);
        $this->validateGymAccess($plan->gym_id);

        $plan->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Diet plan deleted successfully.'
            ]);
        }

        return redirect()->route('diet-plans.index')
            ->with('success', 'Diet plan deleted successfully.');
    }

    /**
     * Show the form for assigning a diet plan to a member
     */
    public function showAssignForm($plan, $member = null)
    {
        $this->authorizePermission('diet-plans.update');

        $plan = DietPlan::findOrFail($plan);
        $this->validateGymAccess($plan->gym_id);

        $user = Auth::user();

        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        $selectedMember = $member ? User::findOrFail($member) : null;

        return view('diet-plans.assign', compact('plan', 'members', 'selectedMember'));
    }

    /**
     * Assign a diet plan to a member
     */
    public function assign(Request $request, $plan)
    {
        $this->authorizePermission('diet-plans.update');

        $plan = DietPlan::findOrFail($plan);
        $this->validateGymAccess($plan->gym_id);

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = Auth::user();

        $member = User::findOrFail($validated['member_id']);
        if (!$user->isSuperAdmin() && $member->gym_id !== $user->gym_id) {
            return back()->withErrors(['member_id' => 'Invalid member selected.']);
        }

        // Create a new diet plan instance for the member (copy from template)
        $newPlan = $plan->replicate();
        $newPlan->member_id = $validated['member_id'];
        $newPlan->is_default = false;
        $newPlan->start_date = $validated['start_date'] ?? now()->toDateString();
        $newPlan->end_date = $validated['end_date'] ?? null;
        $newPlan->created_by = $user->id;
        $newPlan->save();

        return redirect()->route('diet-plans.index')
            ->with('success', 'Diet plan assigned to member successfully.');
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $this->authorizePermission('diet-plans.view');

        $user = Auth::user();
        
        if ($user->isMember()) {
            $query = DietPlan::with(['trainer', 'member'])
                ->where('member_id', $user->id);
            $plans = $query->latest()->get();
        } else {
            $query = DietPlan::with(['trainer', 'member']);
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }
            
            $plans = $this->applyGymFilter($query)->latest()->get();
        }

        return $this->apiSuccess([
            'plans' => $plans,
            'count' => $plans->count()
        ], 'Diet plans retrieved successfully');
    }

    public function apiShow(Request $request, string $id)
    {
        $this->authorizePermission('diet-plans.view');

        $plan = DietPlan::with(['trainer', 'member'])->findOrFail($id);
        $this->validateGymAccess($plan->gym_id);

        return $this->apiSuccess($plan, 'Diet plan retrieved successfully');
    }

    public function apiStore(Request $request)
    {
        $this->authorizePermission('diet-plans.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trainer_id' => 'required|exists:users,id',
            'member_id' => 'nullable|exists:users,id',
            'is_default' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'breakfast' => 'nullable|string',
            'lunch' => 'nullable|string',
            'dinner' => 'nullable|string',
            'snacks' => 'nullable|string',
        ]);

        $user = Auth::user();
        $trainer = User::findOrFail($validated['trainer_id']);
        
        if (!$user->isSuperAdmin() && $trainer->gym_id !== $user->gym_id) {
            return $this->apiError('Invalid trainer selected.', null, 422);
        }

        $planData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trainer_id' => $validated['trainer_id'],
            'member_id' => $validated['member_id'] ?? null,
            'is_default' => $validated['is_default'] ?? false,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'breakfast' => $validated['breakfast'] ?? null,
            'lunch' => $validated['lunch'] ?? null,
            'dinner' => $validated['dinner'] ?? null,
            'snacks' => $validated['snacks'] ?? null,
            'created_by' => $user->id,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $planData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $planData['gym_id'] = $user->gym_id;
        }

        $plan = DietPlan::create($planData);

        return $this->apiSuccess($plan->load(['trainer', 'member']), 'Diet plan created successfully', 201);
    }

    public function apiUpdate(Request $request, string $id)
    {
        $this->authorizePermission('diet-plans.update');

        $plan = DietPlan::findOrFail($id);
        $this->validateGymAccess($plan->gym_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trainer_id' => 'required|exists:users,id',
            'member_id' => 'nullable|exists:users,id',
            'is_default' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'notes' => 'nullable|string',
            'breakfast' => 'nullable|string',
            'lunch' => 'nullable|string',
            'dinner' => 'nullable|string',
            'snacks' => 'nullable|string',
        ]);

        $user = Auth::user();
        $trainer = User::findOrFail($validated['trainer_id']);
        
        if (!$user->isSuperAdmin() && $trainer->gym_id !== $user->gym_id) {
            return $this->apiError('Invalid trainer selected.', null, 422);
        }

        $plan->name = $validated['name'];
        $plan->description = $validated['description'] ?? null;
        $plan->trainer_id = $validated['trainer_id'];
        $plan->member_id = $validated['member_id'] ?? null;
        $plan->is_default = $validated['is_default'] ?? false;
        $plan->start_date = $validated['start_date'] ?? null;
        $plan->end_date = $validated['end_date'] ?? null;
        $plan->notes = $validated['notes'] ?? null;
        $plan->breakfast = $validated['breakfast'] ?? null;
        $plan->lunch = $validated['lunch'] ?? null;
        $plan->dinner = $validated['dinner'] ?? null;
        $plan->snacks = $validated['snacks'] ?? null;
        $plan->save();

        return $this->apiSuccess($plan->load(['trainer', 'member']), 'Diet plan updated successfully');
    }

    public function apiDestroy(Request $request, string $id)
    {
        $this->authorizePermission('diet-plans.delete');

        $plan = DietPlan::findOrFail($id);
        $this->validateGymAccess($plan->gym_id);

        $plan->delete();

        return $this->apiSuccess(null, 'Diet plan deleted successfully');
    }

    public function apiShowAssignForm(Request $request, $plan, $member = null)
    {
        $this->authorizePermission('diet-plans.create');

        $plan = DietPlan::findOrFail($plan);
        $this->validateGymAccess($plan->gym_id);

        $user = Auth::user();
        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        return $this->apiSuccess([
            'plan' => $plan,
            'members' => $members,
            'selected_member' => $member ? User::find($member) : null
        ], 'Assign form data retrieved successfully');
    }

    public function apiAssign(Request $request, $plan)
    {
        $this->authorizePermission('diet-plans.create');

        $plan = DietPlan::findOrFail($plan);
        $this->validateGymAccess($plan->gym_id);

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $user = Auth::user();
        $member = User::findOrFail($validated['member_id']);
        
        if (!$user->isSuperAdmin() && $member->gym_id !== $user->gym_id) {
            return $this->apiError('Invalid member selected.', null, 422);
        }

        $newPlan = $plan->replicate();
        $newPlan->member_id = $validated['member_id'];
        $newPlan->is_default = false;
        $newPlan->start_date = $validated['start_date'] ?? now()->toDateString();
        $newPlan->end_date = $validated['end_date'] ?? null;
        $newPlan->created_by = $user->id;
        $newPlan->save();

        return $this->apiSuccess($newPlan->load(['trainer', 'member']), 'Diet plan assigned to member successfully', 201);
    }
}
