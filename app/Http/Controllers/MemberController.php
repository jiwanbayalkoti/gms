<?php

namespace App\Http\Controllers;

use App\Http\Resources\MemberResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * MemberController
 * 
 * Handles member management with gym-based data isolation.
 * All queries are automatically filtered by gym_id.
 */
class MemberController extends BaseController
{
    /**
     * Display a listing of members.
     * 
     * Returns only members from the user's gym (unless SuperAdmin).
     */
    public function index(Request $request)
    {
        // Authorize permission
        $this->authorizePermission('members.view');

        // Get query with gym filter applied
        $query = User::where('role', 'Member')->with('payments.membershipPlan');
        
        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('active', false);
            }
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
        
        // Check if request is from API (mobile app) or wants JSON
        if ($this->isApiRequest($request)) {
            // Use pagination for API requests
            $perPage = $request->get('per_page', 15);
            $members = $this->applyGymFilter($query)->latest()->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'members' => MemberResource::collection($members->items()),
                    'pagination' => [
                        'current_page' => $members->currentPage(),
                        'total' => $members->total(),
                        'per_page' => $members->perPage(),
                        'last_page' => $members->lastPage(),
                        'has_more_pages' => $members->hasMorePages(),
                        'from' => $members->firstItem(),
                        'to' => $members->lastItem(),
                    ]
                ]
            ]);
        }
        
        // For web requests, get all without pagination
        $members = $this->applyGymFilter($query)->latest()->get();

        // For web AJAX requests, return HTML
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'html' => view('members._table-body', compact('members'))->render()
            ]);
        }

        return view('members.index', compact('members'));
    }

    /**
     * Show the form for creating a new member.
     */
    public function create()
    {
        $this->authorizePermission('members.create');

        return view('members.create');
    }

    /**
     * Store a newly created member.
     * 
     * Automatically sets gym_id and created_by.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('members.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        $user = Auth::user();

        // Prepare member data
        $memberData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'Member',
            'phone' => $validated['phone'] ?? null,
            'active' => $validated['active'] ?? true,
        ];

        // Set gym_id (SuperAdmin must specify, others use their gym_id)
        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $memberData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $memberData['gym_id'] = $user->gym_id;
        }

        // Handle profile photo upload with enhanced security
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            
            // Enhanced file type validation
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            $allowedExtensions = ['jpeg', 'png', 'jpg', 'gif'];
            
            if (!validate_file_type($file, $allowedMimes, $allowedExtensions)) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid file type. Only JPEG, PNG, JPG, and GIF images are allowed.'
                    ], 422);
                }
                return back()->withErrors(['profile_photo' => 'Invalid file type. Only JPEG, PNG, JPG, and GIF images are allowed.'])->withInput();
            }

            // Sanitize filename and store
            $originalName = sanitize_filename($file->getClientOriginalName());
            $fileName = 'member_' . time() . '_' . $originalName;
            $memberData['profile_photo'] = $file->storeAs('profile-photos', $fileName, 'public');
        }

        $member = User::create($memberData);
        $member->load('payments.membershipPlan');

        // Return JSON response for AJAX requests
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Member created successfully.',
                'data' => [
                    'member' => new MemberResource($member)
                ]
            ], 201);
        }
        
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Member created successfully.',
                'member' => $member
            ]);
        }

        return redirect()->route('members.index')
            ->with('success', 'Member created successfully.');
    }

    /**
     * Display the specified member.
     * 
     * Validates gym access before showing.
     */
    public function show(Request $request, string $id)
    {
        $this->authorizePermission('members.view');

        $member = User::where('role', 'Member')->findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($member->gym_id);

        // Load relationships for API
        $member->load('payments.membershipPlan', 'workoutPlansAssigned', 'dietPlansAssigned', 'bookings', 'attendanceRecords');
        
        // Check if request is from API (mobile app) or wants JSON
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'member' => new MemberResource($member)
                ]
            ]);
        }

        // For web AJAX requests, return JSON with HTML
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'member' => $member,
                'html' => view('members.show', compact('member'))->render()
            ]);
        }

        return view('members.show', compact('member'));
    }

    /**
     * Show the form for editing the specified member.
     */
    public function edit(Request $request, string $id)
    {
        $this->authorizePermission('members.update');

        $member = User::where('role', 'Member')->findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($member->gym_id);

        // Load relationships for API
        $member->load('payments.membershipPlan');

        // Check if request is from API (mobile app) or wants JSON
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'member' => new MemberResource($member),
                    'editable_fields' => [
                        'name',
                        'email',
                        'phone',
                        'password',
                        'profile_photo',
                        'active'
                    ]
                ]
            ]);
        }

        // For web AJAX requests, return HTML form
        if ($this->isWebAjaxRequest($request)) {
            return view('members.edit', compact('member'))->render();
        }

        return view('members.edit', compact('member'));
    }

    /**
     * Update the specified member.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizePermission('members.update');

        $member = User::where('role', 'Member')->findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($member->gym_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        // Update member data
        $member->name = $validated['name'];
        $member->email = $validated['email'];
        $member->phone = $validated['phone'] ?? null;
        $member->active = $validated['active'] ?? $member->active;

        // Update password if provided
        if (!empty($validated['password'])) {
            $member->password = Hash::make($validated['password']);
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($member->profile_photo && Storage::exists('public/' . $member->profile_photo)) {
                Storage::delete('public/' . $member->profile_photo);
            }
            $member->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $member->save();
        $member->load('payments.membershipPlan');

        // Return JSON response for AJAX requests
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'message' => 'Member updated successfully.',
                'data' => [
                    'member' => new MemberResource($member->fresh())
                ]
            ]);
        }
        
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Member updated successfully.',
                'member' => $member
            ]);
        }

        return redirect()->route('members.index')
            ->with('success', 'Member updated successfully.');
    }

    /**
     * Remove the specified member.
     */
    public function destroy(Request $request, string $id)
    {
        $this->authorizePermission('members.delete');

        $member = User::where('role', 'Member')->findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($member->gym_id);

        // Delete profile photo if exists
        if ($member->profile_photo && Storage::exists('public/' . $member->profile_photo)) {
            Storage::delete('public/' . $member->profile_photo);
        }

        $member->delete();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Member deleted successfully.'
            ]);
        }

        return redirect()->route('members.index')
            ->with('success', 'Member deleted successfully.');
    }

    /**
     * Update member status (active/inactive).
     */
    public function updateStatus(Request $request, $member)
    {
        $this->authorizePermission('members.update');

        $member = User::where('role', 'Member')->findOrFail($member);
        
        // Validate gym access
        $this->validateGymAccess($member->gym_id);

        $request->validate([
            'active' => 'required|boolean',
        ]);

        $member->active = $request->active;
        $member->save();

        return redirect()->route('members.index')
            ->with('success', 'Member status updated successfully.');
    }

    // ==================== API METHODS ====================

    /**
     * API: List all members
     */
    public function apiIndex(Request $request)
    {
        $this->authorizePermission('members.view');

        $query = User::where('role', 'Member');
        
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
        
        // Use pagination for API requests
        $perPage = $request->get('per_page', 15);
        $members = $this->applyGymFilter($query)->with('payments.membershipPlan')->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Members retrieved successfully',
            'data' => [
                'members' => MemberResource::collection($members->items()),
                'pagination' => [
                    'current_page' => $members->currentPage(),
                    'total' => $members->total(),
                    'per_page' => $members->perPage(),
                    'last_page' => $members->lastPage(),
                    'has_more_pages' => $members->hasMorePages(),
                    'from' => $members->firstItem(),
                    'to' => $members->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * API: Show a specific member
     */
    public function apiShow(Request $request, string $id)
    {
        $this->authorizePermission('members.view');

        $member = User::where('role', 'Member')
            ->with('payments.membershipPlan', 'workoutPlansAssigned', 'dietPlansAssigned', 'bookings', 'attendanceRecords')
            ->findOrFail($id);
        $this->validateGymAccess($member->gym_id);

        return response()->json([
            'success' => true,
            'message' => 'Member retrieved successfully',
            'data' => [
                'member' => new MemberResource($member)
            ]
        ]);
    }

    /**
     * API: Store a new member
     */
    public function apiStore(Request $request)
    {
        $this->authorizePermission('members.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        $user = Auth::user();
        $memberData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'Member',
            'phone' => $validated['phone'] ?? null,
            'active' => $validated['active'] ?? true,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $memberData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $memberData['gym_id'] = $user->gym_id;
        }

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif'];
            $allowedExtensions = ['jpeg', 'png', 'jpg', 'gif'];
            
            if (!validate_file_type($file, $allowedMimes, $allowedExtensions)) {
                return $this->apiValidationError(['profile_photo' => ['Invalid file type']], 'Invalid file type');
            }

            $originalName = sanitize_filename($file->getClientOriginalName());
            $fileName = 'member_' . time() . '_' . $originalName;
            $memberData['profile_photo'] = $file->storeAs('profile-photos', $fileName, 'public');
        }

        $member = User::create($memberData);
        $member->load('payments.membershipPlan');

        return response()->json([
            'success' => true,
            'message' => 'Member created successfully',
            'data' => [
                'member' => new MemberResource($member)
            ]
        ], 201);
    }

    /**
     * API: Update a member
     */
    public function apiUpdate(Request $request, string $id)
    {
        $this->authorizePermission('members.update');

        $member = User::where('role', 'Member')->findOrFail($id);
        $this->validateGymAccess($member->gym_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        $member->name = $validated['name'];
        $member->email = $validated['email'];
        $member->phone = $validated['phone'] ?? null;
        $member->active = $validated['active'] ?? $member->active;

        if (!empty($validated['password'])) {
            $member->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_photo')) {
            if ($member->profile_photo && Storage::exists('public/' . $member->profile_photo)) {
                Storage::delete('public/' . $member->profile_photo);
            }
            $member->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $member->save();
        $member->load('payments.membershipPlan');

        return response()->json([
            'success' => true,
            'message' => 'Member updated successfully',
            'data' => [
                'member' => new MemberResource($member->fresh())
            ]
        ]);
    }

    /**
     * API: Delete a member
     */
    public function apiDestroy(Request $request, string $id)
    {
        $this->authorizePermission('members.delete');

        $member = User::where('role', 'Member')->findOrFail($id);
        $this->validateGymAccess($member->gym_id);

        if ($member->profile_photo && Storage::exists('public/' . $member->profile_photo)) {
            Storage::delete('public/' . $member->profile_photo);
        }

        $member->delete();

        return $this->apiSuccess(null, 'Member deleted successfully');
    }

    /**
     * API: Update member status
     */
    public function apiUpdateStatus(Request $request, $member)
    {
        $this->authorizePermission('members.update');

        $member = User::where('role', 'Member')->findOrFail($member);
        $this->validateGymAccess($member->gym_id);

        $request->validate(['active' => 'required|boolean']);

        $member->active = $request->active;
        $member->save();
        $member->load('payments.membershipPlan');

        return response()->json([
            'success' => true,
            'message' => 'Member status updated successfully',
            'data' => [
                'member' => new MemberResource($member->fresh())
            ]
        ]);
    }
}
