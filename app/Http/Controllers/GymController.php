<?php

namespace App\Http\Controllers;

use App\Models\Gym;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * GymController
 * 
 * Handles gym management (CRUD operations).
 * Only SuperAdmin can access these routes.
 */
class GymController extends Controller
{
    /**
     * Display a listing of gyms.
     * Only SuperAdmin can access.
     */
    public function index(Request $request)
    {
        // Only SuperAdmin can access
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can access gym management.');
        }

        $query = Gym::withCount(['users', 'members', 'trainers']);
        
        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        // Filter by search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $gyms = $query->latest()->paginate(15);

        // If AJAX request, return JSON with rendered HTML
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('gyms._gyms-list', compact('gyms'))->render()
            ]);
        }

        return view('gyms.index', compact('gyms'));
    }

    /**
     * Show the form for creating a new gym.
     */
    public function create()
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can create gyms.');
        }

        return view('gyms.create');
    }

    /**
     * Store a newly created gym.
     */
    public function store(Request $request)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can create gyms.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
            'subscription_plan' => 'nullable|string|max:255',
            'subscription_ends_at' => 'nullable|date',
        ]);

        // Handle logo upload with enhanced security
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            
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
                return back()->withErrors(['logo' => 'Invalid file type. Only JPEG, PNG, JPG, and GIF images are allowed.'])->withInput();
            }

            // Sanitize filename and store
            $originalName = sanitize_filename($file->getClientOriginalName());
            $fileName = 'gym_logo_' . time() . '_' . $originalName;
            $validated['logo'] = $file->storeAs('gym-logos', $fileName, 'public');
        }

        $gym = Gym::create($validated);

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gym created successfully.',
                'gym' => $gym
            ]);
        }

        return redirect()->route('gyms.index')
            ->with('success', 'Gym created successfully.');
    }

    /**
     * Display the specified gym.
     */
    public function show(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can view gym details.');
        }

        $gym = Gym::with(['users', 'members', 'trainers', 'admin'])->findOrFail($id);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'gym' => $gym,
                'html' => view('gyms.show', compact('gym'))->render()
            ]);
        }

        return view('gyms.show', compact('gym'));
    }

    /**
     * Show the form for editing the specified gym.
     */
    public function edit(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can edit gyms.');
        }

        $gym = Gym::findOrFail($id);

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('gyms.edit', compact('gym'))->render();
        }

        return view('gyms.edit', compact('gym'));
    }

    /**
     * Update the specified gym.
     */
    public function update(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can update gyms.');
        }

        $gym = Gym::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:active,inactive',
            'subscription_plan' => 'nullable|string|max:255',
            'subscription_ends_at' => 'nullable|date',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($gym->logo && Storage::exists('public/' . $gym->logo)) {
                Storage::delete('public/' . $gym->logo);
            }
            $validated['logo'] = $request->file('logo')->store('gym-logos', 'public');
        }

        $gym->update($validated);

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gym updated successfully.',
                'gym' => $gym
            ]);
        }

        return redirect()->route('gyms.index')
            ->with('success', 'Gym updated successfully.');
    }

    /**
     * Remove the specified gym.
     */
    public function destroy(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can delete gyms.');
        }

        $gym = Gym::findOrFail($id);

        // Delete logo if exists
        if ($gym->logo && Storage::exists('public/' . $gym->logo)) {
            Storage::delete('public/' . $gym->logo);
        }

        $gym->delete();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gym deleted successfully.'
            ]);
        }

        return redirect()->route('gyms.index')
            ->with('success', 'Gym deleted successfully.');
    }

    /**
     * Update gym status.
     */
    public function updateStatus(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can update gym status.');
        }

        $gym = Gym::findOrFail($id);

        $request->validate([
            'status' => 'required|in:active,inactive',
        ]);

        $gym->status = $request->status;
        $gym->save();

        return redirect()->route('gyms.index')
            ->with('success', 'Gym status updated successfully.');
    }

    /**
     * Show the form for creating a gym admin.
     */
    public function createAdmin(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can create gym admins.');
        }

        $gym = Gym::findOrFail($id);

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('gyms.create-admin', compact('gym'))->render();
        }

        return view('gyms.create-admin', compact('gym'));
    }

    /**
     * Store a newly created gym admin.
     */
    public function storeAdmin(Request $request, $id)
    {
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            abort(403, 'Only SuperAdmin can create gym admins.');
        }

        $gym = Gym::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        // Create gym admin user
        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'GymAdmin',
            'gym_id' => $gym->id,
            'phone' => $validated['phone'] ?? null,
            'active' => true,
        ]);

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gym admin created successfully.',
                'admin' => $admin
            ]);
        }

        return redirect()->route('gyms.show', $gym->id)
            ->with('success', 'Gym admin created successfully.');
    }
}

