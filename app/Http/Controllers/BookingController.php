<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\GymClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * BookingController
 * 
 * Handles booking management with gym-based data isolation.
 * All queries are automatically filtered by gym_id.
 */
class BookingController extends BaseController
{
    /**
     * Display a listing of bookings.
     * 
     * Returns only bookings from the user's gym (unless SuperAdmin).
     * For Members, returns only their own bookings.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // For members, check for bookings.view_own permission
        if ($user->isMember()) {
            $this->authorizePermission('bookings.view_own');
            $query = Booking::with(['member', 'gymClass.trainer'])
                ->where('member_id', $user->id);
        } else {
            // For Admin/SuperAdmin, check for bookings.view permission
            $this->authorizePermission('bookings.view');
            $query = Booking::with(['member', 'gymClass.trainer']);
            $query = $this->applyGymFilter($query);
        }
        
        // Filter by member (for admin)
        if ($request->filled('member_id') && !$user->isMember()) {
            $query->where('member_id', $request->member_id);
        }
        
        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereHas('gymClass', function($q) use ($request) {
                $q->whereDate('start_time', '>=', $request->start_date);
            });
        }
        if ($request->filled('end_date')) {
            $query->whereHas('gymClass', function($q) use ($request) {
                $q->whereDate('start_time', '<=', $request->end_date);
            });
        }
        
        $bookings = $query->latest()->get();
        
        // Get members and classes for filter dropdowns (for admin)
        $members = collect();
        $classes = collect();
        if (!$user->isMember()) {
            $membersQuery = User::where('role', 'Member');
            if (!$user->isSuperAdmin() && $user->gym_id) {
                $membersQuery->where('gym_id', $user->gym_id);
            }
            $members = $membersQuery->get();
            
            $classesQuery = GymClass::query();
            if (!$user->isSuperAdmin() && $user->gym_id) {
                $classesQuery->where('gym_id', $user->gym_id);
            }
            $classes = $classesQuery->get();
        }

        // If AJAX request, return JSON with table body
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('bookings._table-body', compact('bookings'))->render()
            ]);
        }

        return view('bookings.index', compact('bookings', 'members', 'classes'));
    }

    /**
     * Show the form for creating a new booking.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // For members, allow booking creation
        if ($user->isMember()) {
            // Members can create bookings (they already have bookings.create permission)
        } else {
            // For Admin/SuperAdmin, check permission
            $this->authorizePermission('bookings.create');
        }

        // Get members for the gym (for admin to select member)
        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        // Get available classes (active, upcoming, not full)
        $classesQuery = GymClass::where('status', 'Active')
            ->where('start_time', '>', now());
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $classesQuery->where('gym_id', $user->gym_id);
        }
        // Use whereColumn instead of whereRaw for better security
        $classes = $classesQuery->whereColumn('current_bookings', '<', 'capacity')
            ->orderBy('start_time')
            ->get();

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('bookings.create', compact('members', 'classes', 'user'))->render();
        }

        // Return full page for direct access
        return view('bookings.create-page', compact('members', 'classes', 'user'));
    }

    /**
     * Store a newly created booking.
     * 
     * Automatically sets gym_id and created_by.
     * Updates class current_bookings count.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // For members, allow booking creation (they have bookings.create permission)
        if (!$user->isMember()) {
            // For Admin/SuperAdmin, check permission
            $this->authorizePermission('bookings.create');
        }

        // Validation rules
        $rules = [
            'class_id' => 'required|exists:classes,id',
            'notes' => 'nullable|string',
        ];
        
        // For members, member_id is automatically set to current user and status is Pending
        // For admin, member_id and status are required
        if ($user->isMember()) {
            // Members can only book for themselves
            $rules['member_id'] = 'nullable'; // Will be set automatically
            // Status will be set to Pending automatically
        } else {
            $rules['member_id'] = 'required|exists:users,id';
            $rules['status'] = 'required|in:Pending,Confirmed,Cancelled,Attended,No-Show';
        }

        $validated = $request->validate($rules);

        // For members, automatically set member_id to current user and status to Pending
        if ($user->isMember()) {
            $validated['member_id'] = $user->id;
            $validated['status'] = 'Pending';
        }

        // Get member and class
        $member = User::findOrFail($validated['member_id']);
        $gymClass = GymClass::findOrFail($validated['class_id']);

        // Validate member and class belong to same gym
        if (!$user->isSuperAdmin()) {
            if ($member->gym_id !== $user->gym_id || $gymClass->gym_id !== $user->gym_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member and class must belong to the same gym.'
                ], 422);
            }
        }

        // Check if class is full
        if ($gymClass->isFull()) {
            return response()->json([
                'success' => false,
                'message' => 'Class is full. Cannot create booking.'
            ], 422);
        }

        // Check if member already has a booking for this class (only check Confirmed or Pending bookings)
        $existingBooking = Booking::where('member_id', $validated['member_id'])
            ->where('class_id', $validated['class_id'])
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->first();

        if ($existingBooking) {
            return response()->json([
                'success' => false,
                'message' => 'Member already has a booking for this class.'
            ], 422);
        }

        // Prepare booking data
        $bookingData = [
            'member_id' => $validated['member_id'],
            'class_id' => $validated['class_id'],
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'reminder_sent' => false,
        ];

        // Set gym_id
        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $bookingData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $bookingData['gym_id'] = $user->gym_id;
        }

        $bookingData['created_by'] = $user->id;

        // Create booking and update class bookings count in transaction
        DB::transaction(function () use ($bookingData, $gymClass) {
            $booking = Booking::create($bookingData);
            
            // Increment class current_bookings if status is Confirmed
            if ($bookingData['status'] === 'Confirmed') {
                $gymClass->increment('current_bookings');
            }
            
            return $booking;
        });

        $booking = Booking::where('member_id', $validated['member_id'])
            ->where('class_id', $validated['class_id'])
            ->first();

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully.',
                'booking' => $booking
            ]);
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    /**
     * Display the specified booking.
     * 
     * Validates gym access before showing.
     * Members can only view their own bookings.
     */
    public function show(Request $request, string $id)
    {
        $user = Auth::user();
        $booking = Booking::with(['member', 'gymClass.trainer'])->findOrFail($id);
        
        // For members, check if they own the booking
        if ($user->isMember()) {
            $this->authorizePermission('bookings.view_own');
            
            // Ensure member can only view their own booking
            if ($booking->member_id !== $user->id) {
                abort(403, 'You can only view your own bookings.');
            }
        } else {
            // For Admin/SuperAdmin, check for bookings.view permission
            $this->authorizePermission('bookings.view');
            
            // Validate gym access
            $this->validateGymAccess($booking->gym_id);
        }

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'booking' => $booking,
                'html' => view('bookings.show', compact('booking'))->render()
            ]);
        }

        return view('bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified booking.
     */
    public function edit(Request $request, string $id)
    {
        $this->authorizePermission('bookings.update');

        $booking = Booking::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($booking->gym_id);

        $user = Auth::user();

        // Get members for the gym
        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin()) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        // Get available classes
        $classesQuery = GymClass::where('status', 'Active');
        if (!$user->isSuperAdmin()) {
            $classesQuery->where('gym_id', $user->gym_id);
        }
        $classes = $classesQuery->orderBy('start_time')->get();

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('bookings.edit', compact('booking', 'members', 'classes'))->render();
        }

        return view('bookings.edit', compact('booking', 'members', 'classes'));
    }

    /**
     * Update the specified booking.
     * 
     * Updates class current_bookings count based on status changes.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizePermission('bookings.update');

        $booking = Booking::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($booking->gym_id);

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:classes,id',
            'status' => 'required|in:Pending,Confirmed,Cancelled,Attended,No-Show',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Get member and class
        $member = User::findOrFail($validated['member_id']);
        $gymClass = GymClass::findOrFail($validated['class_id']);

        // Validate member and class belong to same gym
        if (!$user->isSuperAdmin()) {
            if ($member->gym_id !== $user->gym_id || $gymClass->gym_id !== $user->gym_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Member and class must belong to the same gym.'
                ], 422);
            }
        }

        $oldStatus = $booking->status;
        $oldClassId = $booking->class_id;

        // Update booking
        $booking->member_id = $validated['member_id'];
        $booking->class_id = $validated['class_id'];
        $booking->status = $validated['status'];
        $booking->notes = $validated['notes'] ?? null;

        // Update class bookings count in transaction
        DB::transaction(function () use ($booking, $oldStatus, $oldClassId, $gymClass) {
            $booking->save();

            // Handle old class bookings count
            if ($oldClassId != $booking->class_id) {
                $oldClass = GymClass::find($oldClassId);
                if ($oldClass && $oldStatus === 'Confirmed') {
                    $oldClass->decrement('current_bookings');
                }
            }

            // Handle new class bookings count
            if ($oldStatus !== 'Confirmed' && $booking->status === 'Confirmed') {
                // Status changed to Confirmed
                if ($oldClassId == $booking->class_id) {
                    $gymClass->increment('current_bookings');
                } else {
                    // Class changed and new status is Confirmed
                    if (!$gymClass->isFull()) {
                        $gymClass->increment('current_bookings');
                    }
                }
            } elseif ($oldStatus === 'Confirmed' && $booking->status !== 'Confirmed') {
                // Status changed from Confirmed
                if ($oldClassId == $booking->class_id) {
                    $gymClass->decrement('current_bookings');
                }
            } elseif ($oldClassId != $booking->class_id && $booking->status === 'Confirmed') {
                // Class changed and status is Confirmed
                if (!$gymClass->isFull()) {
                    $gymClass->increment('current_bookings');
                }
            }
        });

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking updated successfully.',
                'booking' => $booking
            ]);
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Remove the specified booking.
     * 
     * Decrements class current_bookings count.
     */
    public function destroy(Request $request, string $id)
    {
        $this->authorizePermission('bookings.delete');

        $booking = Booking::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($booking->gym_id);

        $gymClass = $booking->gymClass;

        // Delete booking and update class bookings count in transaction
        DB::transaction(function () use ($booking, $gymClass) {
            // Decrement class current_bookings if status was Confirmed
            if ($booking->status === 'Confirmed' && $gymClass) {
                $gymClass->decrement('current_bookings');
            }
            
            $booking->delete();
        });

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking deleted successfully.'
            ]);
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Booking deleted successfully.');
    }

    /**
     * Update booking status (Confirmed/Cancelled/Attended/No-Show).
     */
    public function updateStatus(Request $request, $booking)
    {
        $this->authorizePermission('bookings.update');

        $booking = Booking::findOrFail($booking);
        
        // Validate gym access
        $this->validateGymAccess($booking->gym_id);

        $request->validate([
            'status' => 'required|in:Pending,Confirmed,Cancelled,Attended,No-Show',
        ]);

        $oldStatus = $booking->status;
        $gymClass = $booking->gymClass;

        // Update status and class bookings count in transaction
        DB::transaction(function () use ($booking, $oldStatus, $gymClass, $request) {
            $booking->status = $request->status;
            $booking->save();

            // Update class current_bookings based on status change
            // Only Confirmed bookings count towards capacity
            if ($oldStatus === 'Confirmed' && !in_array($request->status, ['Confirmed'])) {
                // Was confirmed, now not confirmed - decrement
                $gymClass->decrement('current_bookings');
            } elseif ($oldStatus !== 'Confirmed' && $request->status === 'Confirmed') {
                // Was not confirmed, now confirmed - increment
                if (!$gymClass->isFull()) {
                    $gymClass->increment('current_bookings');
                }
            }
        });

        return redirect()->route('bookings.index')
            ->with('success', 'Booking status updated successfully.');
    }

    /**
     * Approve a pending booking (change status to Confirmed).
     */
    public function approve(Request $request, $booking)
    {
        $this->authorizePermission('bookings.update');

        $booking = Booking::findOrFail($booking);
        
        // Validate gym access
        $this->validateGymAccess($booking->gym_id);

        if ($booking->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be approved.'
            ], 422);
        }

        $gymClass = $booking->gymClass;

        // Update status to Confirmed and increment class bookings count
        DB::transaction(function () use ($booking, $gymClass) {
            $booking->status = 'Confirmed';
            $booking->save();

            // Increment class bookings count if not full
            if (!$gymClass->isFull()) {
                $gymClass->increment('current_bookings');
            }
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking approved successfully.',
                'booking' => $booking->fresh()
            ]);
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Booking approved successfully.');
    }

    /**
     * Reject a pending booking (change status to Cancelled).
     */
    public function reject(Request $request, $booking)
    {
        $this->authorizePermission('bookings.update');

        $booking = Booking::findOrFail($booking);
        
        // Validate gym access
        $this->validateGymAccess($booking->gym_id);

        if ($booking->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending bookings can be rejected.'
            ], 422);
        }

        // Update status to Cancelled (no need to change class bookings count as it was never Confirmed)
        $booking->status = 'Cancelled';
        $booking->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Booking rejected successfully.',
                'booking' => $booking->fresh()
            ]);
        }

        return redirect()->route('bookings.index')
            ->with('success', 'Booking rejected successfully.');
    }

    /**
     * Get bookings for a specific member
     */
    public function memberBookings($member)
    {
        $this->authorizePermission('bookings.view');

        $member = User::where('role', 'Member')->findOrFail($member);
        
        // Validate gym access
        $this->validateGymAccess($member->gym_id);

        $bookings = Booking::with('gymClass.trainer')
            ->where('member_id', $member->id)
            ->latest()
            ->get();

        return view('bookings.member', compact('bookings', 'member'));
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        
        if ($user->isMember()) {
            $this->authorizePermission('bookings.view_own');
            $query = Booking::with(['member', 'gymClass.trainer'])->where('member_id', $user->id);
        } else {
            $this->authorizePermission('bookings.view');
            $query = Booking::with(['member', 'gymClass.trainer']);
            $query = $this->applyGymFilter($query);
        }
        
        if ($request->filled('member_id') && !$user->isMember()) {
            $query->where('member_id', $request->member_id);
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereHas('gymClass', function($q) use ($request) {
                $q->whereDate('start_time', '>=', $request->start_date);
            });
        }
        
        $bookings = $query->latest()->get();

        return $this->apiSuccess([
            'bookings' => $bookings,
            'count' => $bookings->count()
        ], 'Bookings retrieved successfully');
    }

    public function apiShow(Request $request, string $id)
    {
        $user = Auth::user();
        
        if ($user->isMember()) {
            $this->authorizePermission('bookings.view_own');
            $booking = Booking::with(['member', 'gymClass.trainer'])->where('member_id', $user->id)->findOrFail($id);
        } else {
            $this->authorizePermission('bookings.view');
            $booking = Booking::with(['member', 'gymClass.trainer'])->findOrFail($id);
            $this->validateGymAccess($booking->gym_id);
        }

        return $this->apiSuccess($booking, 'Booking retrieved successfully');
    }

    public function apiStore(Request $request)
    {
        $user = Auth::user();
        
        if ($user->isMember()) {
            // Members can create bookings
        } else {
            $this->authorizePermission('bookings.create');
        }

        $validated = $request->validate([
            'class_id' => 'required|exists:gym_classes,id',
            'member_id' => $user->isMember() ? 'nullable' : 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $memberId = $user->isMember() ? $user->id : $validated['member_id'];
        $member = User::findOrFail($memberId);
        $gymClass = GymClass::findOrFail($validated['class_id']);

        $this->validateGymAccess($gymClass->gym_id);
        $this->validateGymAccess($member->gym_id);

        if ($gymClass->isFull()) {
            return $this->apiError('Class is full. Cannot create booking.', null, 422);
        }

        $existingBooking = Booking::where('member_id', $memberId)
            ->where('class_id', $validated['class_id'])
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->first();

        if ($existingBooking) {
            return $this->apiError('You already have a booking for this class.', null, 422);
        }

        $booking = Booking::create([
            'member_id' => $memberId,
            'class_id' => $validated['class_id'],
            'gym_id' => $gymClass->gym_id,
            'status' => $user->isMember() ? 'Pending' : 'Confirmed',
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($booking->status === 'Confirmed' && !$gymClass->isFull()) {
            $gymClass->increment('current_bookings');
        }

        return $this->apiSuccess($booking->load(['member', 'gymClass.trainer']), 'Booking created successfully', 201);
    }

    public function apiUpdate(Request $request, string $id)
    {
        $this->authorizePermission('bookings.update');

        $booking = Booking::findOrFail($id);
        $this->validateGymAccess($booking->gym_id);

        $validated = $request->validate([
            'class_id' => 'required|exists:gym_classes,id',
            'member_id' => 'required|exists:users,id',
            'status' => 'required|in:Pending,Confirmed,Cancelled,Attended,No-Show',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldStatus = $booking->status;
        $oldClassId = $booking->class_id;
        $oldGymClass = GymClass::find($oldClassId);

        $member = User::findOrFail($validated['member_id']);
        $gymClass = GymClass::findOrFail($validated['class_id']);

        $this->validateGymAccess($gymClass->gym_id);
        $this->validateGymAccess($member->gym_id);

        $booking->member_id = $validated['member_id'];
        $booking->class_id = $validated['class_id'];
        $booking->status = $validated['status'];
        $booking->notes = $validated['notes'] ?? null;

        DB::transaction(function () use ($booking, $oldStatus, $oldClassId, $oldGymClass, $gymClass) {
            $booking->save();

            if ($oldGymClass && $oldClassId != $booking->class_id) {
                if ($oldStatus === 'Confirmed') {
                    $oldGymClass->decrement('current_bookings');
                }
            }

            if ($oldStatus !== 'Confirmed' && $booking->status === 'Confirmed') {
                if (!$gymClass->isFull()) {
                    $gymClass->increment('current_bookings');
                }
            } elseif ($oldStatus === 'Confirmed' && $booking->status !== 'Confirmed') {
                if ($oldClassId == $booking->class_id) {
                    $gymClass->decrement('current_bookings');
                }
            } elseif ($oldClassId != $booking->class_id && $booking->status === 'Confirmed') {
                if (!$gymClass->isFull()) {
                    $gymClass->increment('current_bookings');
                }
            }
        });

        return $this->apiSuccess($booking->load(['member', 'gymClass.trainer']), 'Booking updated successfully');
    }

    public function apiDestroy(Request $request, string $id)
    {
        $this->authorizePermission('bookings.delete');

        $booking = Booking::findOrFail($id);
        $this->validateGymAccess($booking->gym_id);

        $gymClass = $booking->gymClass;

        DB::transaction(function () use ($booking, $gymClass) {
            if ($booking->status === 'Confirmed') {
                $gymClass->decrement('current_bookings');
            }
            $booking->delete();
        });

        return $this->apiSuccess(null, 'Booking deleted successfully');
    }

    public function apiApprove(Request $request, $booking)
    {
        $this->authorizePermission('bookings.update');

        $booking = Booking::findOrFail($booking);
        $this->validateGymAccess($booking->gym_id);

        if ($booking->status !== 'Pending') {
            return $this->apiError('Only pending bookings can be approved.', null, 422);
        }

        $gymClass = $booking->gymClass;

        DB::transaction(function () use ($booking, $gymClass) {
            $booking->status = 'Confirmed';
            $booking->save();

            if (!$gymClass->isFull()) {
                $gymClass->increment('current_bookings');
            }
        });

        return $this->apiSuccess($booking->fresh()->load(['member', 'gymClass.trainer']), 'Booking approved successfully');
    }

    public function apiReject(Request $request, $booking)
    {
        $this->authorizePermission('bookings.update');

        $booking = Booking::findOrFail($booking);
        $this->validateGymAccess($booking->gym_id);

        if ($booking->status !== 'Pending') {
            return $this->apiError('Only pending bookings can be rejected.', null, 422);
        }

        $booking->status = 'Cancelled';
        $booking->save();

        return $this->apiSuccess($booking->fresh()->load(['member', 'gymClass.trainer']), 'Booking rejected successfully');
    }

    public function apiUpdateStatus(Request $request, $booking)
    {
        $this->authorizePermission('bookings.update');

        $booking = Booking::findOrFail($booking);
        $this->validateGymAccess($booking->gym_id);

        $request->validate([
            'status' => 'required|in:Pending,Confirmed,Cancelled,Attended,No-Show',
        ]);

        $oldStatus = $booking->status;
        $gymClass = $booking->gymClass;

        DB::transaction(function () use ($booking, $oldStatus, $gymClass, $request) {
            $booking->status = $request->status;
            $booking->save();

            if ($oldStatus === 'Confirmed' && !in_array($request->status, ['Confirmed'])) {
                $gymClass->decrement('current_bookings');
            } elseif ($oldStatus !== 'Confirmed' && $request->status === 'Confirmed') {
                if (!$gymClass->isFull()) {
                    $gymClass->increment('current_bookings');
                }
            }
        });

        return $this->apiSuccess($booking->fresh()->load(['member', 'gymClass.trainer']), 'Booking status updated successfully');
    }

    public function apiMemberBookings(Request $request, $member)
    {
        $this->authorizePermission('bookings.view');

        $member = User::where('role', 'Member')->findOrFail($member);
        $this->validateGymAccess($member->gym_id);

        $bookings = Booking::with('gymClass.trainer')
            ->where('member_id', $member->id)
            ->latest()
            ->get();

        return $this->apiSuccess([
            'member' => $member,
            'bookings' => $bookings,
            'count' => $bookings->count()
        ], 'Member bookings retrieved successfully');
    }
}
