<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\GymClass;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AttendanceController
 * 
 * Handles attendance management with gym-based data isolation.
 */
class AttendanceController extends BaseController
{
    /**
     * Display a listing of attendance records.
     */
    public function index(Request $request)
    {
        $this->authorizePermission('attendance.view');

        $query = Attendance::with(['member', 'gymClass']);
        
        // Filter by member
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        
        // Filter by class
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('check_in_time', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('check_in_time', '<=', $request->end_date);
        }
        
        $attendances = $this->applyGymFilter($query)->latest('check_in_time')->get();
        
        // Get members and classes for filter dropdowns
        $user = Auth::user();
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

        // If AJAX request, return JSON with table body
        if ($request->expectsJson() || $request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->json([
                'success' => true,
                'html' => view('attendances._table-body', compact('attendances'))->render()
            ]);
        }

        return view('attendances.index', compact('attendances', 'members', 'classes'));
    }

    /**
     * Show the form for creating a new attendance record.
     */
    public function create(Request $request)
    {
        $this->authorizePermission('attendance.create');

        $user = Auth::user();

        // Get members for the gym
        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        // Get active classes
        $classesQuery = GymClass::where('status', 'Active');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $classesQuery->where('gym_id', $user->gym_id);
        }
        $classes = $classesQuery->orderBy('start_time')->get();

        if ($request->expectsJson() || $request->ajax()) {
            return view('attendances.create', compact('members', 'classes'))->render();
        }

        return view('attendances.create-page', compact('members', 'classes'));
    }

    /**
     * Store a newly created attendance record.
     */
    public function store(Request $request)
    {
        $this->authorizePermission('attendance.create');

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'check_in_time' => 'required|date',
            'class_id' => 'nullable|exists:classes,id',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Validate member belongs to same gym
        $member = User::findOrFail($validated['member_id']);
        if (!$user->isSuperAdmin() && $member->gym_id !== $user->gym_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid member selected.'
            ], 422);
        }

        $attendanceData = [
            'member_id' => $validated['member_id'],
            'check_in_time' => $validated['check_in_time'],
            'class_id' => $validated['class_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($user->isSuperAdmin()) {
            $request->validate(['gym_id' => 'required|exists:gyms,id']);
            $attendanceData['gym_id'] = $request->gym_id;
            $this->validateGymAccess($request->gym_id);
        } else {
            $attendanceData['gym_id'] = $user->gym_id;
        }

        $attendanceData['created_by'] = $user->id;

        $attendance = Attendance::create($attendanceData);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance record created successfully.',
                'attendance' => $attendance
            ]);
        }

        return redirect()->route('attendances.index')
            ->with('success', 'Attendance record created successfully.');
    }

    /**
     * Display the specified attendance record.
     */
    public function show(Request $request, string $id)
    {
        $this->authorizePermission('attendance.view');

        $attendance = Attendance::with(['member', 'gymClass'])->findOrFail($id);
        $this->validateGymAccess($attendance->gym_id);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'attendance' => $attendance,
                'html' => view('attendances.show', compact('attendance'))->render()
            ]);
        }

        return view('attendances.show', compact('attendance'));
    }

    /**
     * Show the form for editing the specified attendance record.
     */
    public function edit(Request $request, string $id)
    {
        $this->authorizePermission('attendance.update');

        $attendance = Attendance::findOrFail($id);
        $this->validateGymAccess($attendance->gym_id);

        $user = Auth::user();

        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        $classesQuery = GymClass::where('status', 'Active');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $classesQuery->where('gym_id', $user->gym_id);
        }
        $classes = $classesQuery->orderBy('start_time')->get();

        if ($request->expectsJson() || $request->ajax()) {
            return view('attendances.edit', compact('attendance', 'members', 'classes'))->render();
        }

        return view('attendances.edit-page', compact('attendance', 'members', 'classes'));
    }

    /**
     * Update the specified attendance record.
     */
    public function update(Request $request, string $id)
    {
        $this->authorizePermission('attendance.update');

        $attendance = Attendance::findOrFail($id);
        $this->validateGymAccess($attendance->gym_id);

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'check_in_time' => 'required|date',
            'check_out_time' => 'nullable|date|after:check_in_time',
            'class_id' => 'nullable|exists:classes,id',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $member = User::findOrFail($validated['member_id']);
        if (!$user->isSuperAdmin() && $member->gym_id !== $user->gym_id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid member selected.'
            ], 422);
        }

        $attendance->member_id = $validated['member_id'];
        $attendance->check_in_time = $validated['check_in_time'];
        $attendance->check_out_time = $validated['check_out_time'] ?? null;
        $attendance->class_id = $validated['class_id'] ?? null;
        $attendance->notes = $validated['notes'] ?? null;
        $attendance->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance record updated successfully.',
                'attendance' => $attendance
            ]);
        }

        return redirect()->route('attendances.index')
            ->with('success', 'Attendance record updated successfully.');
    }

    /**
     * Remove the specified attendance record.
     */
    public function destroy(Request $request, string $id)
    {
        $this->authorizePermission('attendance.delete');

        $attendance = Attendance::findOrFail($id);
        $this->validateGymAccess($attendance->gym_id);

        $attendance->delete();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance record deleted successfully.'
            ]);
        }

        return redirect()->route('attendances.index')
            ->with('success', 'Attendance record deleted successfully.');
    }

    /**
     * Show check-in form
     */
    public function checkInForm()
    {
        $this->authorizePermission('attendance.create');

        $user = Auth::user();

        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        $classesQuery = GymClass::where('status', 'Active')
            ->where('start_time', '<=', now())
            ->where('end_time', '>=', now());
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $classesQuery->where('gym_id', $user->gym_id);
        }
        $classes = $classesQuery->get();

        return view('attendances.check-in', compact('members', 'classes'));
    }

    /**
     * Process check-in
     */
    public function checkIn(Request $request)
    {
        $this->authorizePermission('attendance.create');

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'class_id' => 'nullable|exists:classes,id',
            'notes' => 'nullable|string',
        ]);

        $user = Auth::user();

        $member = User::findOrFail($validated['member_id']);
        if (!$user->isSuperAdmin() && $member->gym_id !== $user->gym_id) {
            return back()->withErrors(['member_id' => 'Invalid member selected.']);
        }

        // Check if member already checked in today
        $existing = Attendance::where('member_id', $validated['member_id'])
            ->whereNull('check_out_time')
            ->whereDate('check_in_time', now()->toDateString())
            ->first();

        if ($existing) {
            return back()->withErrors(['member_id' => 'Member already checked in today.']);
        }

        $attendanceData = [
            'member_id' => $validated['member_id'],
            'check_in_time' => now(),
            'class_id' => $validated['class_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ];

        if ($user->isSuperAdmin()) {
            $attendanceData['gym_id'] = $member->gym_id;
        } else {
            $attendanceData['gym_id'] = $user->gym_id;
        }

        $attendanceData['created_by'] = $user->id;

        Attendance::create($attendanceData);

        return redirect()->route('attendances.check-in.form')
            ->with('success', 'Check-in successful.');
    }

    /**
     * Process check-out
     */
    public function checkOut($attendance)
    {
        $this->authorizePermission('attendance.update');

        $attendance = Attendance::findOrFail($attendance);
        $this->validateGymAccess($attendance->gym_id);

        if ($attendance->check_out_time) {
            return back()->withErrors(['error' => 'Member already checked out.']);
        }

        $attendance->check_out_time = now();
        $attendance->save();

        return redirect()->route('attendances.index')
            ->with('success', 'Check-out successful.');
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $this->authorizePermission('attendance.view');

        $query = Attendance::with(['member', 'gymClass']);
        
        if ($request->filled('member_id')) {
            $query->where('member_id', $request->member_id);
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('check_in_time', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('check_in_time', '<=', $request->end_date);
        }
        
        $attendances = $this->applyGymFilter($query)->latest('check_in_time')->get();

        return $this->apiSuccess([
            'attendances' => $attendances,
            'count' => $attendances->count()
        ], 'Attendances retrieved successfully');
    }

    public function apiShow(Request $request, string $id)
    {
        $this->authorizePermission('attendance.view');

        $attendance = Attendance::with(['member', 'gymClass'])->findOrFail($id);
        $this->validateGymAccess($attendance->gym_id);

        return $this->apiSuccess($attendance, 'Attendance retrieved successfully');
    }

    public function apiStore(Request $request)
    {
        $this->authorizePermission('attendance.create');

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:gym_classes,id',
            'check_in_time' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $member = User::findOrFail($validated['member_id']);
        $gymClass = GymClass::findOrFail($validated['class_id']);

        $this->validateGymAccess($member->gym_id);
        $this->validateGymAccess($gymClass->gym_id);

        $attendanceData = [
            'member_id' => $validated['member_id'],
            'class_id' => $validated['class_id'],
            'gym_id' => $gymClass->gym_id,
            'check_in_time' => $validated['check_in_time'] ?? now(),
            'notes' => $validated['notes'] ?? null,
        ];

        $attendance = Attendance::create($attendanceData);

        return $this->apiSuccess($attendance->load(['member', 'gymClass']), 'Attendance created successfully', 201);
    }

    public function apiUpdate(Request $request, string $id)
    {
        $this->authorizePermission('attendance.update');

        $attendance = Attendance::findOrFail($id);
        $this->validateGymAccess($attendance->gym_id);

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:gym_classes,id',
            'check_in_time' => 'nullable|date',
            'check_out_time' => 'nullable|date|after:check_in_time',
            'notes' => 'nullable|string|max:1000',
        ]);

        $member = User::findOrFail($validated['member_id']);
        $gymClass = GymClass::findOrFail($validated['class_id']);

        $this->validateGymAccess($member->gym_id);
        $this->validateGymAccess($gymClass->gym_id);

        $attendance->member_id = $validated['member_id'];
        $attendance->class_id = $validated['class_id'];
        $attendance->check_in_time = $validated['check_in_time'] ?? $attendance->check_in_time;
        $attendance->check_out_time = $validated['check_out_time'] ?? $attendance->check_out_time;
        $attendance->notes = $validated['notes'] ?? null;
        $attendance->save();

        return $this->apiSuccess($attendance->load(['member', 'gymClass']), 'Attendance updated successfully');
    }

    public function apiDestroy(Request $request, string $id)
    {
        $this->authorizePermission('attendance.delete');

        $attendance = Attendance::findOrFail($id);
        $this->validateGymAccess($attendance->gym_id);

        $attendance->delete();

        return $this->apiSuccess(null, 'Attendance deleted successfully');
    }

    public function apiCheckInForm(Request $request)
    {
        $this->authorizePermission('attendance.create');

        $user = Auth::user();
        $membersQuery = User::where('role', 'Member');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $membersQuery->where('gym_id', $user->gym_id);
        }
        $members = $membersQuery->get();

        $classesQuery = GymClass::where('status', 'Active');
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $classesQuery->where('gym_id', $user->gym_id);
        }
        $classes = $classesQuery->orderBy('start_time')->get();

        return $this->apiSuccess([
            'members' => $members,
            'classes' => $classes
        ], 'Check-in form data retrieved successfully');
    }

    public function apiCheckIn(Request $request)
    {
        $this->authorizePermission('attendance.create');

        $validated = $request->validate([
            'member_id' => 'required|exists:users,id',
            'class_id' => 'required|exists:gym_classes,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $member = User::findOrFail($validated['member_id']);
        $gymClass = GymClass::findOrFail($validated['class_id']);

        $this->validateGymAccess($member->gym_id);
        $this->validateGymAccess($gymClass->gym_id);

        $attendanceData = [
            'member_id' => $validated['member_id'],
            'class_id' => $validated['class_id'],
            'gym_id' => $gymClass->gym_id,
            'check_in_time' => now(),
            'notes' => $validated['notes'] ?? null,
        ];

        $attendance = Attendance::create($attendanceData);

        return $this->apiSuccess($attendance->load(['member', 'gymClass']), 'Check-in successful', 201);
    }

    public function apiCheckOut(Request $request, $attendance)
    {
        $this->authorizePermission('attendance.update');

        $attendance = Attendance::findOrFail($attendance);
        $this->validateGymAccess($attendance->gym_id);

        if ($attendance->check_out_time) {
            return $this->apiError('Member already checked out.', null, 422);
        }

        $attendance->check_out_time = now();
        $attendance->save();

        return $this->apiSuccess($attendance->load(['member', 'gymClass']), 'Check-out successful');
    }
}
