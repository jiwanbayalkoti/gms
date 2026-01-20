<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * TrainerController
 * 
 * Handles trainer management with gym-based data isolation.
 * All queries are automatically filtered by gym_id.
 */
class TrainerController extends BaseController
{
    /**
     * Display a listing of trainers.
     * 
     * Returns only trainers from the user's gym (unless SuperAdmin).
     */
    public function index(Request $request)
    {
        // Authorize permission
        $this->authorizePermission('trainers.view');

        // Get query with gym filter applied
        $query = User::where('role', 'Trainer');
        
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
        
        $trainers = $this->applyGymFilter($query)->latest()->get();

        // If AJAX request, return JSON with table body
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('trainers._table-body', compact('trainers'))->render()
            ]);
        }

        return view('trainers.index', compact('trainers'));
    }

    /**
     * Show the form for creating a new trainer.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('trainers.create');

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('trainers.create')->render();
        }

        return view('trainers.create');
    }

    /**
     * Store a newly created trainer.
     * 
     * Automatically sets gym_id and created_by.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('trainers.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        $user = Auth::user();

        // Prepare trainer data
        $trainerData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'Trainer',
            'phone' => $validated['phone'] ?? null,
            'active' => $validated['active'] ?? true,
        ];

        // Set gym_id (SuperAdmin must specify, others use their gym_id)
        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $trainerData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $trainerData['gym_id'] = $user->gym_id;
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            $trainerData['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $trainer = User::create($trainerData);

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Trainer created successfully.',
                'trainer' => $trainer
            ]);
        }

        return redirect()->route('trainers.index')
            ->with('success', 'Trainer created successfully.');
    }

    /**
     * Display the specified trainer.
     * 
     * Validates gym access before showing.
     */
    public function show(Request $request, string $id)
    {
        $this->authorizePermission('trainers.view');

        $trainer = User::where('role', 'Trainer')->findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($trainer->gym_id);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'trainer' => $trainer,
                'html' => view('trainers.show', compact('trainer'))->render()
            ]);
        }

        return view('trainers.show', compact('trainer'));
    }

    /**
     * Show the form for editing the specified trainer.
     */
    public function edit(Request $request, string $id)
    {
        $this->authorizePermission('trainers.update');

        $trainer = User::where('role', 'Trainer')->findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($trainer->gym_id);

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('trainers.edit', compact('trainer'))->render();
        }

        return view('trainers.edit', compact('trainer'));
    }

    /**
     * Update the specified trainer.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizePermission('trainers.update');

        $trainer = User::where('role', 'Trainer')->findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($trainer->gym_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        // Update trainer data
        $trainer->name = $validated['name'];
        $trainer->email = $validated['email'];
        $trainer->phone = $validated['phone'] ?? null;
        $trainer->active = $validated['active'] ?? $trainer->active;

        // Update password if provided
        if (!empty($validated['password'])) {
            $trainer->password = Hash::make($validated['password']);
        }

        // Handle profile photo upload
        if ($request->hasFile('profile_photo')) {
            // Delete old photo
            if ($trainer->profile_photo && Storage::exists('public/' . $trainer->profile_photo)) {
                Storage::delete('public/' . $trainer->profile_photo);
            }
            $trainer->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $trainer->save();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Trainer updated successfully.',
                'trainer' => $trainer
            ]);
        }

        return redirect()->route('trainers.index')
            ->with('success', 'Trainer updated successfully.');
    }

    /**
     * Remove the specified trainer.
     */
    public function destroy(Request $request, string $id)
    {
        $this->authorizePermission('trainers.delete');

        $trainer = User::where('role', 'Trainer')->findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($trainer->gym_id);

        // Delete profile photo if exists
        if ($trainer->profile_photo && Storage::exists('public/' . $trainer->profile_photo)) {
            Storage::delete('public/' . $trainer->profile_photo);
        }

        $trainer->delete();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Trainer deleted successfully.'
            ]);
        }

        return redirect()->route('trainers.index')
            ->with('success', 'Trainer deleted successfully.');
    }

    /**
     * Update trainer status (active/inactive).
     */
    public function updateStatus(Request $request, $trainer)
    {
        $this->authorizePermission('trainers.update');

        $trainer = User::where('role', 'Trainer')->findOrFail($trainer);
        
        // Validate gym access
        $this->validateGymAccess($trainer->gym_id);

        $request->validate([
            'active' => 'required|boolean',
        ]);

        $trainer->active = $request->active;
        $trainer->save();

        return redirect()->route('trainers.index')
            ->with('success', 'Trainer status updated successfully.');
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $this->authorizePermission('trainers.view');

        $query = User::where('role', 'Trainer');
        
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
        
        $trainers = $this->applyGymFilter($query)->latest()->get();

        return $this->apiSuccess([
            'trainers' => $trainers,
            'count' => $trainers->count()
        ], 'Trainers retrieved successfully');
    }

    public function apiShow(Request $request, string $id)
    {
        $this->authorizePermission('trainers.view');

        $trainer = User::where('role', 'Trainer')->findOrFail($id);
        $this->validateGymAccess($trainer->gym_id);

        return $this->apiSuccess($trainer, 'Trainer retrieved successfully');
    }

    public function apiStore(Request $request)
    {
        $this->authorizePermission('trainers.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        $user = Auth::user();
        $trainerData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'Trainer',
            'phone' => $validated['phone'] ?? null,
            'active' => $validated['active'] ?? true,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $trainerData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $trainerData['gym_id'] = $user->gym_id;
        }

        if ($request->hasFile('profile_photo')) {
            $trainerData['profile_photo'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $trainer = User::create($trainerData);

        return $this->apiSuccess($trainer, 'Trainer created successfully', 201);
    }

    public function apiUpdate(Request $request, string $id)
    {
        $this->authorizePermission('trainers.update');

        $trainer = User::where('role', 'Trainer')->findOrFail($id);
        $this->validateGymAccess($trainer->gym_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'active' => 'boolean',
        ]);

        $trainer->name = $validated['name'];
        $trainer->email = $validated['email'];
        $trainer->phone = $validated['phone'] ?? null;
        $trainer->active = $validated['active'] ?? $trainer->active;

        if (!empty($validated['password'])) {
            $trainer->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('profile_photo')) {
            if ($trainer->profile_photo && Storage::exists('public/' . $trainer->profile_photo)) {
                Storage::delete('public/' . $trainer->profile_photo);
            }
            $trainer->profile_photo = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $trainer->save();

        return $this->apiSuccess($trainer, 'Trainer updated successfully');
    }

    public function apiDestroy(Request $request, string $id)
    {
        $this->authorizePermission('trainers.delete');

        $trainer = User::where('role', 'Trainer')->findOrFail($id);
        $this->validateGymAccess($trainer->gym_id);

        if ($trainer->profile_photo && Storage::exists('public/' . $trainer->profile_photo)) {
            Storage::delete('public/' . $trainer->profile_photo);
        }

        $trainer->delete();

        return $this->apiSuccess(null, 'Trainer deleted successfully');
    }

    public function apiUpdateStatus(Request $request, $trainer)
    {
        $this->authorizePermission('trainers.update');

        $trainer = User::where('role', 'Trainer')->findOrFail($trainer);
        $this->validateGymAccess($trainer->gym_id);

        $request->validate(['active' => 'required|boolean']);

        $trainer->active = $request->active;
        $trainer->save();

        return $this->apiSuccess($trainer, 'Trainer status updated successfully');
    }
}
