<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * StaffController
 * 
 * Handles staff management with gym-based data isolation.
 * All queries are automatically filtered by gym_id.
 */
class StaffController extends BaseController
{
    /**
     * Display a listing of staff.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('staff.view');

        $query = User::where('role', 'Staff');
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('active', $request->status);
        }
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $staff = $this->applyGymFilter($query)->latest()->get();

        // If AJAX request, return JSON with table body
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('staff._table-body', compact('staff'))->render()
            ]);
        }

        return view('staff.index', compact('staff'));
    }

    /**
     * Show the form for creating a new staff.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('staff.create');

        if ($request->expectsJson() || $request->ajax()) {
            return view('staff.create')->render();
        }

        return view('staff.create');
    }

    /**
     * Store a newly created staff.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('staff.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'staff_type' => 'nullable|string|max:255',
            'marital_status' => 'nullable|in:single,married',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        $user = Auth::user();

        $staffData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'Staff',
            'phone' => $validated['phone'] ?? null,
            'staff_type' => $validated['staff_type'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'active' => $validated['active'] ?? true,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $staffData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $staffData['gym_id'] = $user->gym_id;
        }

        if ($request->hasFile('profile_photo')) {
            $staffData['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $staff = User::create($staffData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Staff created successfully.',
                'staff' => $staff
            ]);
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff created successfully.');
    }

    /**
     * Display the specified staff.
     */
    public function show(Request $request, string $id)
    {
        $this->authorizePermission('staff.view');

        $staff = User::where('role', 'Staff')->findOrFail($id);
        $this->validateGymAccess($staff->gym_id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'staff' => $staff,
                'html' => view('staff.show', compact('staff'))->render()
            ]);
        }

        return view('staff.show', compact('staff'));
    }

    /**
     * Show the form for editing the specified staff.
     */
    public function edit(Request $request, string $id)
    {
        $this->authorizePermission('staff.update');

        $staff = User::where('role', 'Staff')->findOrFail($id);
        $this->validateGymAccess($staff->gym_id);

        if ($request->expectsJson() || $request->ajax()) {
            return view('staff.edit', compact('staff'))->render();
        }

        return view('staff.edit', compact('staff'));
    }

    /**
     * Update the specified staff.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizePermission('staff.update');

        $staff = User::where('role', 'Staff')->findOrFail($id);
        $this->validateGymAccess($staff->gym_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'staff_type' => 'nullable|string|max:255',
            'marital_status' => 'nullable|in:single,married',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        $staff->name = $validated['name'];
        $staff->email = $validated['email'];
        $staff->phone = $validated['phone'] ?? null;
        $staff->staff_type = $validated['staff_type'] ?? null;
        $staff->marital_status = $validated['marital_status'] ?? null;
        $staff->active = $validated['active'] ?? $staff->active;

        if (!empty($validated['password'])) {
            $staff->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_photo')) {
            if ($staff->profile_photo && Storage::exists('public/' . $staff->profile_photo)) {
                Storage::delete('public/' . $staff->profile_photo);
            }
            $staff->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $staff->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Staff updated successfully.',
                'staff' => $staff
            ]);
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff updated successfully.');
    }

    /**
     * Remove the specified staff.
     */
    public function destroy(Request $request, string $id)
    {
        $this->authorizePermission('staff.delete');

        $staff = User::where('role', 'Staff')->findOrFail($id);
        $this->validateGymAccess($staff->gym_id);

        if ($staff->profile_photo && Storage::exists('public/' . $staff->profile_photo)) {
            Storage::delete('public/' . $staff->profile_photo);
        }

        $staff->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Staff deleted successfully.'
            ]);
        }

        return redirect()->route('staff.index')
            ->with('success', 'Staff deleted successfully.');
    }

    /**
     * Update staff status (active/inactive).
     */
    public function updateStatus(Request $request, $staff)
    {
        $this->authorizePermission('staff.update');

        $staff = User::where('role', 'Staff')->findOrFail($staff);
        $this->validateGymAccess($staff->gym_id);

        $request->validate([
            'active' => 'required|boolean',
        ]);

        $staff->active = $request->active;
        $staff->save();

        return redirect()->route('staff.index')
            ->with('success', 'Staff status updated successfully.');
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $this->authorizePermission('staff.view');

        $query = User::where('role', 'Staff');
        
        if ($request->filled('status')) {
            $query->where('active', $request->status);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $staff = $this->applyGymFilter($query)->latest()->get();

        return $this->apiSuccess([
            'staff' => $staff,
            'count' => $staff->count()
        ], 'Staff retrieved successfully');
    }

    public function apiShow(Request $request, string $id)
    {
        $this->authorizePermission('staff.view');

        $staff = User::where('role', 'Staff')->findOrFail($id);
        $this->validateGymAccess($staff->gym_id);

        return $this->apiSuccess($staff, 'Staff retrieved successfully');
    }

    public function apiStore(Request $request)
    {
        $this->authorizePermission('staff.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'staff_type' => 'nullable|string|max:255',
            'marital_status' => 'nullable|in:single,married',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        $user = Auth::user();
        $staffData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'Staff',
            'phone' => $validated['phone'] ?? null,
            'staff_type' => $validated['staff_type'] ?? null,
            'marital_status' => $validated['marital_status'] ?? null,
            'active' => $validated['active'] ?? true,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $staffData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $staffData['gym_id'] = $user->gym_id;
        }

        if ($request->hasFile('profile_photo')) {
            $staffData['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $staff = User::create($staffData);

        return $this->apiSuccess($staff, 'Staff created successfully', 201);
    }

    public function apiUpdate(Request $request, string $id)
    {
        $this->authorizePermission('staff.update');

        $staff = User::where('role', 'Staff')->findOrFail($id);
        $this->validateGymAccess($staff->gym_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'staff_type' => 'nullable|string|max:255',
            'marital_status' => 'nullable|in:single,married',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        $staff->name = $validated['name'];
        $staff->email = $validated['email'];
        $staff->phone = $validated['phone'] ?? null;
        $staff->staff_type = $validated['staff_type'] ?? null;
        $staff->marital_status = $validated['marital_status'] ?? null;
        $staff->active = $validated['active'] ?? $staff->active;

        if (!empty($validated['password'])) {
            $staff->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_photo')) {
            if ($staff->profile_photo && Storage::exists('public/' . $staff->profile_photo)) {
                Storage::delete('public/' . $staff->profile_photo);
            }
            $staff->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $staff->save();

        return $this->apiSuccess($staff, 'Staff updated successfully');
    }

    public function apiDestroy(Request $request, string $id)
    {
        $this->authorizePermission('staff.delete');

        $staff = User::where('role', 'Staff')->findOrFail($id);
        $this->validateGymAccess($staff->gym_id);

        if ($staff->profile_photo && Storage::exists('public/' . $staff->profile_photo)) {
            Storage::delete('public/' . $staff->profile_photo);
        }

        $staff->delete();

        return $this->apiSuccess(null, 'Staff deleted successfully');
    }

    public function apiUpdateStatus(Request $request, $staff)
    {
        $this->authorizePermission('staff.update');

        $staff = User::where('role', 'Staff')->findOrFail($staff);
        $this->validateGymAccess($staff->gym_id);

        $request->validate(['active' => 'required|boolean']);

        $staff->active = $request->active;
        $staff->save();

        return $this->apiSuccess($staff, 'Staff status updated successfully');
    }
}
