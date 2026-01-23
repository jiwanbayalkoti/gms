<?php

namespace App\Http\Controllers;

use App\Models\GymClass;
use App\Models\User;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * GymClassController
 * 
 * Handles gym class management with gym-based data isolation.
 * All queries are automatically filtered by gym_id.
 */
class GymClassController extends BaseController
{
    /**
     * Display a listing of gym classes.
     * 
     * Returns only classes from the user's gym (unless SuperAdmin).
     * For Members, returns only classes they have booked.
     */
    public function index(Request $request)
    {
        // Authorize permission
        $this->authorizePermission('classes.view');

        $user = Auth::user();
        
        // For members, show all active, upcoming classes from their gym (so they can book)
        if ($user->isMember()) {
            $query = GymClass::with('trainer')
                ->where('status', 'Active')
                ->where('start_time', '>', now());
            
            // Apply gym filter for members
            if (!$user->isSuperAdmin() && $user->gym_id) {
                $query->where('gym_id', $user->gym_id);
            }
            
            $classes = $query->orderBy('start_time', 'asc')->get();
        } else {
            // For Admin/SuperAdmin, show all classes from their gym
            $query = GymClass::with('trainer');
            
            // Filter by trainer
            if ($request->filled('trainer_id')) {
                $query->where('trainer_id', $request->trainer_id);
            }
            
            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by date range
            if ($request->filled('start_date')) {
                $query->whereDate('start_time', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('start_time', '<=', $request->end_date);
            }
            
            $classes = $this->applyGymFilter($query)->latest('start_time')->get();
        }
        
        // Get trainers for filter dropdown (admin only)
        $trainers = collect();
        if (!$user->isMember()) {
            $trainersQuery = User::where('role', 'Trainer');
            if (!$user->isSuperAdmin() && $user->gym_id) {
                $trainersQuery->where('gym_id', $user->gym_id);
            }
            $trainers = $trainersQuery->get();
        }

        // Check if request is from API (mobile app) or wants JSON
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'classes' => $classes
                ]
            ]);
        }

        // For web AJAX requests, return HTML
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'html' => view('classes._table-body', compact('classes'))->render()
            ]);
        }

        return view('classes.index', compact('classes', 'trainers'));
    }

    /**
     * Show the form for creating a new gym class.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('classes.create');

        // Get trainers for the gym
        $user = Auth::user();
        $trainersQuery = User::where('role', 'Trainer');
        
        if (!$user->isSuperAdmin()) {
            $trainersQuery->where('gym_id', $user->gym_id);
        }
        
        $trainers = $trainersQuery->get();

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('classes.create', compact('trainers'))->render();
        }

        return view('classes.create', compact('trainers'));
    }

    /**
     * Store a newly created gym class.
     * 
     * Automatically sets gym_id and created_by.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('classes.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trainer_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1',
            'start_time' => 'required|date|after_or_equal:now',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Cancelled,Completed',
            'recurring' => 'boolean',
            'recurring_pattern' => 'nullable|in:Daily,Weekly,Monthly',
            'recurring_end_date' => 'nullable|date|after:start_time',
        ]);

        $user = Auth::user();

        // Validate trainer belongs to the same gym
        $trainer = User::findOrFail($validated['trainer_id']);
        if (!$user->isSuperAdmin() && $trainer->gym_id !== $user->gym_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid trainer selected.'
            ], 422);
        }

        // Prepare class data
        $classData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trainer_id' => $validated['trainer_id'],
            'capacity' => $validated['capacity'],
            'current_bookings' => 0,
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'] ?? null,
            'status' => $validated['status'],
            'recurring' => $validated['recurring'] ?? false,
            'recurring_pattern' => $validated['recurring_pattern'] ?? null,
            'recurring_end_date' => $validated['recurring_end_date'] ?? null,
        ];

        // Set gym_id (SuperAdmin must specify, others use their gym_id)
        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $classData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $classData['gym_id'] = $user->gym_id;
        }

        $classData['created_by'] = $user->id;

        $class = GymClass::create($classData);

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gym class created successfully.',
                'class' => $class
            ]);
        }

        return redirect()->route('classes.index')
            ->with('success', 'Gym class created successfully.');
    }

    /**
     * Display the specified gym class.
     * 
     * Validates gym access before showing.
     */
    public function show(Request $request, string $id)
    {
        $this->authorizePermission('classes.view');

        $class = GymClass::with('trainer')->findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($class->gym_id);

        // Check if request is from API (mobile app) or wants JSON
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'class' => $class
                ]
            ]);
        }

        // For web AJAX requests, return JSON with HTML
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'class' => $class,
                'html' => view('classes.show', compact('class'))->render()
            ]);
        }

        return view('classes.show', compact('class'));
    }

    /**
     * Show the form for editing the specified gym class.
     */
    public function edit(Request $request, string $id)
    {
        $this->authorizePermission('classes.update');

        $class = GymClass::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($class->gym_id);

        // Get trainers for the gym
        $user = Auth::user();
        $trainersQuery = User::where('role', 'Trainer');
        
        if (!$user->isSuperAdmin()) {
            $trainersQuery->where('gym_id', $user->gym_id);
        }
        
        $trainers = $trainersQuery->get();

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('classes.edit', compact('class', 'trainers'))->render();
        }

        return view('classes.edit', compact('class', 'trainers'));
    }

    /**
     * Update the specified gym class.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizePermission('classes.update');

        $class = GymClass::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($class->gym_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trainer_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Cancelled,Completed',
            'recurring' => 'boolean',
            'recurring_pattern' => 'nullable|in:Daily,Weekly,Monthly',
            'recurring_end_date' => 'nullable|date|after:start_time',
        ]);

        $user = Auth::user();

        // Validate trainer belongs to the same gym
        $trainer = User::findOrFail($validated['trainer_id']);
        if (!$user->isSuperAdmin() && $trainer->gym_id !== $user->gym_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid trainer selected.'
            ], 422);
        }

        // Update class data
        $class->name = $validated['name'];
        $class->description = $validated['description'] ?? null;
        $class->trainer_id = $validated['trainer_id'];
        $class->capacity = $validated['capacity'];
        $class->start_time = $validated['start_time'];
        $class->end_time = $validated['end_time'];
        $class->location = $validated['location'] ?? null;
        $class->status = $validated['status'];
        $class->recurring = $validated['recurring'] ?? false;
        $class->recurring_pattern = $validated['recurring_pattern'] ?? null;
        $class->recurring_end_date = $validated['recurring_end_date'] ?? null;

        // Ensure current_bookings doesn't exceed capacity
        if ($class->current_bookings > $class->capacity) {
            $class->current_bookings = $class->capacity;
        }

        $class->save();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gym class updated successfully.',
                'class' => $class
            ]);
        }

        return redirect()->route('classes.index')
            ->with('success', 'Gym class updated successfully.');
    }

    /**
     * Remove the specified gym class.
     */
    public function destroy(Request $request, string $id)
    {
        $this->authorizePermission('classes.delete');

        $class = GymClass::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($class->gym_id);

        // Check if class has bookings
        if ($class->bookings()->count() > 0) {
            $message = 'Cannot delete class. It has associated bookings.';
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 422);
            }
            
            return redirect()->route('classes.index')
                ->with('error', $message);
        }

        $class->delete();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Gym class deleted successfully.'
            ]);
        }

        return redirect()->route('classes.index')
            ->with('success', 'Gym class deleted successfully.');
    }

    /**
     * Update class status (Active/Cancelled/Completed).
     */
    public function updateStatus(Request $request, $class)
    {
        $this->authorizePermission('classes.update');

        $class = GymClass::findOrFail($class);
        
        // Validate gym access
        $this->validateGymAccess($class->gym_id);

        $request->validate([
            'status' => 'required|in:Active,Cancelled,Completed',
        ]);

        $class->status = $request->status;
        $class->save();

        return redirect()->route('classes.index')
            ->with('success', 'Class status updated successfully.');
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $this->authorizePermission('classes.view');

        $user = Auth::user();
        
        if ($user->isMember()) {
            $query = GymClass::with('trainer')
                ->where('status', 'Active')
                ->where('start_time', '>', now());
            
            if (!$user->isSuperAdmin() && $user->gym_id) {
                $query->where('gym_id', $user->gym_id);
            }
            
            $classes = $query->orderBy('start_time', 'asc')->get();
        } else {
            $query = GymClass::with('trainer');
            
            if ($request->filled('trainer_id')) {
                $query->where('trainer_id', $request->trainer_id);
            }
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('start_date')) {
                $query->whereDate('start_time', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('start_time', '<=', $request->end_date);
            }
            
            $classes = $this->applyGymFilter($query)->latest('start_time')->get();
        }

        return $this->apiSuccess([
            'classes' => $classes,
            'count' => $classes->count()
        ], 'Classes retrieved successfully');
    }

    public function apiShow(Request $request, string $id)
    {
        $this->authorizePermission('classes.view');

        $class = GymClass::with('trainer')->findOrFail($id);
        $this->validateGymAccess($class->gym_id);

        return $this->apiSuccess($class, 'Class retrieved successfully');
    }

    public function apiStore(Request $request)
    {
        $this->authorizePermission('classes.create');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trainer_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Cancelled,Completed',
            'recurring' => 'boolean',
            'recurring_pattern' => 'nullable|in:Daily,Weekly,Monthly',
            'recurring_end_date' => 'nullable|date|after:start_time',
        ]);

        $user = Auth::user();
        $trainer = User::findOrFail($validated['trainer_id']);
        
        if (!$user->isSuperAdmin() && $trainer->gym_id !== $user->gym_id) {
            return $this->apiError('Invalid trainer selected.', null, 422);
        }

        $classData = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'trainer_id' => $validated['trainer_id'],
            'capacity' => $validated['capacity'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'location' => $validated['location'] ?? null,
            'status' => $validated['status'],
            'recurring' => $validated['recurring'] ?? false,
            'recurring_pattern' => $validated['recurring_pattern'] ?? null,
            'recurring_end_date' => $validated['recurring_end_date'] ?? null,
            'current_bookings' => 0,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $classData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $classData['gym_id'] = $user->gym_id;
        }

        $class = GymClass::create($classData);

        return $this->apiSuccess($class->load('trainer'), 'Class created successfully', 201);
    }

    public function apiUpdate(Request $request, string $id)
    {
        $this->authorizePermission('classes.update');

        $class = GymClass::findOrFail($id);
        $this->validateGymAccess($class->gym_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'trainer_id' => 'required|exists:users,id',
            'capacity' => 'required|integer|min:1',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'location' => 'nullable|string|max:255',
            'status' => 'required|in:Active,Cancelled,Completed',
            'recurring' => 'boolean',
            'recurring_pattern' => 'nullable|in:Daily,Weekly,Monthly',
            'recurring_end_date' => 'nullable|date|after:start_time',
        ]);

        $user = Auth::user();
        $trainer = User::findOrFail($validated['trainer_id']);
        
        if (!$user->isSuperAdmin() && $trainer->gym_id !== $user->gym_id) {
            return $this->apiError('Invalid trainer selected.', null, 422);
        }

        $class->name = $validated['name'];
        $class->description = $validated['description'] ?? null;
        $class->trainer_id = $validated['trainer_id'];
        $class->capacity = $validated['capacity'];
        $class->start_time = $validated['start_time'];
        $class->end_time = $validated['end_time'];
        $class->location = $validated['location'] ?? null;
        $class->status = $validated['status'];
        $class->recurring = $validated['recurring'] ?? false;
        $class->recurring_pattern = $validated['recurring_pattern'] ?? null;
        $class->recurring_end_date = $validated['recurring_end_date'] ?? null;

        if ($class->current_bookings > $class->capacity) {
            $class->current_bookings = $class->capacity;
        }

        $class->save();

        return $this->apiSuccess($class->load('trainer'), 'Class updated successfully');
    }

    public function apiDestroy(Request $request, string $id)
    {
        $this->authorizePermission('classes.delete');

        $class = GymClass::findOrFail($id);
        $this->validateGymAccess($class->gym_id);

        if ($class->bookings()->count() > 0) {
            return $this->apiError('Cannot delete class. It has associated bookings.', null, 422);
        }

        $class->delete();

        return $this->apiSuccess(null, 'Class deleted successfully');
    }

    public function apiUpdateStatus(Request $request, $class)
    {
        $this->authorizePermission('classes.update');

        $class = GymClass::findOrFail($class);
        $this->validateGymAccess($class->gym_id);

        $request->validate(['status' => 'required|in:Active,Cancelled,Completed']);

        $class->status = $request->status;
        $class->save();

        return $this->apiSuccess($class, 'Class status updated successfully');
    }
}
