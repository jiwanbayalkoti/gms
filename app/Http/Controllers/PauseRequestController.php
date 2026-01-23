<?php

namespace App\Http\Controllers;

use App\Models\PauseRequest;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PauseRequestController extends BaseController
{
    /**
     * Display a listing of pause requests.
     * For members: show their own requests
     * For admins: show all requests
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        if ($user->isMember()) {
            // Members see only their own requests
            $query = PauseRequest::with(['payment', 'reviewer'])
                ->where('member_id', $user->id);
            
            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            $pauseRequests = $query->orderBy('created_at', 'desc')->get();
        } else {
            // Admins see all requests
            if (!$user->isSuperAdmin()) {
                $this->authorizePermission('payments.view'); // Using payments permission for now
            }
            $query = PauseRequest::with(['member', 'payment', 'reviewer']);
            
            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            // Filter by date range
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            
            $pauseRequests = $this->applyGymFilter($query)->orderBy('created_at', 'desc')->get();
        }

        // Check if request is from API (mobile app) or wants JSON
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'pause_requests' => $pauseRequests
                ]
            ]);
        }

        // For web AJAX requests, return HTML
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'html' => view('pause-requests._table-body', compact('pauseRequests'))->render()
            ]);
        }

        return view('pause-requests.index', compact('pauseRequests'));
    }

    /**
     * Show the form for creating a new pause request.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Check if pause feature is enabled
        $settings = Setting::current();
        if (!$settings->enable_pause_feature) {
            return redirect()->back()->with('error', 'Pause feature is not enabled.');
        }

        // Get member's active payment
        $activePayment = Payment::where('member_id', $user->id)
            ->where('payment_status', 'Completed')
            ->where(function($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            })
            ->latest('payment_date')
            ->first();

        if (!$activePayment) {
            return redirect()->back()->with('error', 'No active membership found.');
        }

        // Return only form partial for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return view('pause-requests.create', compact('activePayment', 'settings'))->render();
        }

        return view('pause-requests.create-page', compact('activePayment', 'settings'));
    }

    /**
     * Store a newly created pause request.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Check if pause feature is enabled
        $settings = Setting::current();
        if (!$settings->enable_pause_feature) {
            return response()->json([
                'success' => false,
                'message' => 'Pause feature is not enabled.'
            ], 422);
        }

        // Get member's active payment
        $activePayment = Payment::where('member_id', $user->id)
            ->where('payment_status', 'Completed')
            ->where(function($q) {
                $q->whereNull('expiry_date')
                  ->orWhere('expiry_date', '>=', now());
            })
            ->latest('payment_date')
            ->first();

        if (!$activePayment) {
            return response()->json([
                'success' => false,
                'message' => 'No active membership found.'
            ], 422);
        }

        $validated = $request->validate([
            'pause_start_date' => 'required|date|after_or_equal:today',
            'pause_end_date' => 'required|date|after:pause_start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        // Validate minimum pause days
        $startDate = Carbon::parse($validated['pause_start_date']);
        $endDate = Carbon::parse($validated['pause_end_date']);
        $pauseDays = $startDate->diffInDays($endDate) + 1;

        if ($pauseDays < $settings->minimum_pause_days) {
            return response()->json([
                'success' => false,
                'message' => "Minimum pause period is {$settings->minimum_pause_days} days."
            ], 422);
        }

        // Validate that pause dates are within payment validity period
        if ($activePayment->expiry_date) {
            if ($startDate->greaterThan($activePayment->expiry_date)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pause start date cannot be after membership expiry date.'
                ], 422);
            }
        }

        // Check for overlapping pause requests
        $overlapping = PauseRequest::where('member_id', $user->id)
            ->where('status', '!=', 'Rejected')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('pause_start_date', [$startDate, $endDate])
                  ->orWhereBetween('pause_end_date', [$startDate, $endDate])
                  ->orWhere(function($q2) use ($startDate, $endDate) {
                      $q2->where('pause_start_date', '<=', $startDate)
                         ->where('pause_end_date', '>=', $endDate);
                  });
            })
            ->exists();

        if ($overlapping) {
            return response()->json([
                'success' => false,
                'message' => 'You already have a pause request for this period.'
            ], 422);
        }

        // Create pause request
        $pauseRequest = PauseRequest::create([
            'member_id' => $user->id,
            'payment_id' => $activePayment->id,
            'pause_start_date' => $validated['pause_start_date'],
            'pause_end_date' => $validated['pause_end_date'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'Pending',
            'gym_id' => $user->gym_id,
        ]);

        // Return JSON response for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pause request submitted successfully.',
                'pauseRequest' => $pauseRequest
            ]);
        }

        return redirect()->route('pause-requests.index')
            ->with('success', 'Pause request submitted successfully.');
    }

    /**
     * Approve a pause request.
     */
    public function approve(Request $request, string $id)
    {
        $user = Auth::user();
        
        // SuperAdmin and GymAdmin can approve pause requests
        if (!$user->isSuperAdmin() && !$user->isGymAdmin()) {
            abort(403, 'You do not have permission to approve pause requests.');
        }

        $pauseRequest = PauseRequest::with('payment')->findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($pauseRequest->gym_id);

        if ($pauseRequest->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been processed.'
            ], 422);
        }

        DB::transaction(function() use ($pauseRequest, $user) {
            // Update pause request status
            $pauseRequest->status = 'Approved';
            $pauseRequest->reviewed_by = $user->id;
            $pauseRequest->reviewed_at = now();
            $pauseRequest->admin_notes = $request->admin_notes ?? null;
            $pauseRequest->save();

            // Extend the payment expiry date
            if ($pauseRequest->payment && $pauseRequest->payment->expiry_date) {
                $pauseDays = $pauseRequest->pause_start_date->diffInDays($pauseRequest->pause_end_date) + 1;
                $newExpiryDate = Carbon::parse($pauseRequest->payment->expiry_date)->addDays($pauseDays);
                $pauseRequest->payment->expiry_date = $newExpiryDate;
                $pauseRequest->payment->save();
            }
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pause request approved successfully.',
                'pauseRequest' => $pauseRequest->fresh()
            ]);
        }

        return redirect()->route('pause-requests.index')
            ->with('success', 'Pause request approved successfully.');
    }

    /**
     * Reject a pause request.
     */
    public function reject(Request $request, string $id)
    {
        $user = Auth::user();
        
        // SuperAdmin and GymAdmin can reject pause requests
        if (!$user->isSuperAdmin() && !$user->isGymAdmin()) {
            abort(403, 'You do not have permission to reject pause requests.');
        }

        $pauseRequest = PauseRequest::findOrFail($id);
        
        // Validate gym access
        $this->validateGymAccess($pauseRequest->gym_id);

        if ($pauseRequest->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'This request has already been processed.'
            ], 422);
        }

        $pauseRequest->status = 'Rejected';
        $pauseRequest->reviewed_by = $user->id;
        $pauseRequest->reviewed_at = now();
        $pauseRequest->admin_notes = $request->admin_notes ?? null;
        $pauseRequest->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pause request rejected.',
                'pauseRequest' => $pauseRequest->fresh()
            ]);
        }

        return redirect()->route('pause-requests.index')
            ->with('success', 'Pause request rejected.');
    }

    /**
     * Display the specified pause request.
     */
    public function show(Request $request, string $id)
    {
        $user = Auth::user();
        $pauseRequest = PauseRequest::with(['member', 'payment', 'reviewer'])->findOrFail($id);

        // Members can only view their own requests
        if ($user->isMember() && $pauseRequest->member_id !== $user->id) {
            abort(403, 'You do not have access to this request.');
        }

        // Admins need permission
        if (!$user->isMember() && !$user->isSuperAdmin()) {
            $this->authorizePermission('payments.view');
            $this->validateGymAccess($pauseRequest->gym_id);
        }

        // Check if request is from API (mobile app) or wants JSON
        if ($this->isApiRequest($request)) {
            return response()->json([
                'success' => true,
                'data' => [
                    'pause_request' => $pauseRequest
                ]
            ]);
        }

        // For web AJAX requests, return JSON with HTML
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'pauseRequest' => $pauseRequest,
                'html' => view('pause-requests.show', compact('pauseRequest'))->render()
            ]);
        }

        return view('pause-requests.show', compact('pauseRequest'));
    }

    // ==================== API METHODS ====================

    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        
        if ($user->isMember()) {
            $query = PauseRequest::with(['payment', 'reviewer'])
                ->where('member_id', $user->id);
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            
            $pauseRequests = $query->orderBy('created_at', 'desc')->get();
        } else {
            if (!$user->isSuperAdmin()) {
                $this->authorizePermission('payments.view');
            }
            $query = PauseRequest::with(['member', 'payment', 'reviewer']);
            
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }
            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }
            
            $pauseRequests = $this->applyGymFilter($query)->orderBy('created_at', 'desc')->get();
        }

        return $this->apiSuccess([
            'pauseRequests' => $pauseRequests,
            'count' => $pauseRequests->count()
        ], 'Pause requests retrieved successfully');
    }

    public function apiShow(Request $request, string $id)
    {
        $user = Auth::user();
        $pauseRequest = PauseRequest::with(['member', 'payment', 'reviewer'])->findOrFail($id);

        if ($user->isMember() && $pauseRequest->member_id !== $user->id) {
            return $this->apiForbidden('You do not have access to this request.');
        }

        if (!$user->isMember() && !$user->isSuperAdmin()) {
            $this->authorizePermission('payments.view');
            $this->validateGymAccess($pauseRequest->gym_id);
        }

        return $this->apiSuccess($pauseRequest, 'Pause request retrieved successfully');
    }

    public function apiStore(Request $request)
    {
        $user = Auth::user();
        $settings = Setting::current();
        
        if (!$settings->enable_pause_feature) {
            return $this->apiError('Pause feature is not enabled.', null, 422);
        }

        $activePayment = Payment::where('member_id', $user->id)
            ->where('payment_status', 'Completed')
            ->where(function($q) {
                $q->whereNull('expiry_date')->orWhere('expiry_date', '>=', now());
            })
            ->latest('payment_date')
            ->first();

        if (!$activePayment) {
            return $this->apiError('No active membership found.', null, 422);
        }

        $validated = $request->validate([
            'pause_start_date' => 'required|date|after_or_equal:today',
            'pause_end_date' => 'required|date|after:pause_start_date',
            'reason' => 'nullable|string|max:1000',
        ]);

        $startDate = Carbon::parse($validated['pause_start_date']);
        $endDate = Carbon::parse($validated['pause_end_date']);
        $pauseDays = $startDate->diffInDays($endDate) + 1;

        if ($pauseDays < $settings->minimum_pause_days) {
            return $this->apiError("Minimum pause period is {$settings->minimum_pause_days} days.", null, 422);
        }

        if ($activePayment->expiry_date && $startDate->greaterThan($activePayment->expiry_date)) {
            return $this->apiError('Pause start date cannot be after membership expiry date.', null, 422);
        }

        $overlapping = PauseRequest::where('member_id', $user->id)
            ->where('status', '!=', 'Rejected')
            ->where(function($q) use ($startDate, $endDate) {
                $q->whereBetween('pause_start_date', [$startDate, $endDate])
                  ->orWhereBetween('pause_end_date', [$startDate, $endDate])
                  ->orWhere(function($q2) use ($startDate, $endDate) {
                      $q2->where('pause_start_date', '<=', $startDate)
                         ->where('pause_end_date', '>=', $endDate);
                  });
            })
            ->exists();

        if ($overlapping) {
            return $this->apiError('You already have a pause request for this period.', null, 422);
        }

        $pauseRequest = PauseRequest::create([
            'member_id' => $user->id,
            'payment_id' => $activePayment->id,
            'pause_start_date' => $validated['pause_start_date'],
            'pause_end_date' => $validated['pause_end_date'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'Pending',
            'gym_id' => $user->gym_id,
        ]);

        return $this->apiSuccess($pauseRequest->load(['payment', 'member']), 'Pause request submitted successfully', 201);
    }

    public function apiApprove(Request $request, string $id)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin() && !$user->isGymAdmin()) {
            return $this->apiForbidden('You do not have permission to approve pause requests.');
        }

        $pauseRequest = PauseRequest::with('payment')->findOrFail($id);
        $this->validateGymAccess($pauseRequest->gym_id);

        if ($pauseRequest->status !== 'Pending') {
            return $this->apiError('This request has already been processed.', null, 422);
        }

        DB::transaction(function() use ($pauseRequest, $user, $request) {
            $pauseRequest->status = 'Approved';
            $pauseRequest->reviewed_by = $user->id;
            $pauseRequest->reviewed_at = now();
            $pauseRequest->admin_notes = $request->admin_notes ?? null;
            $pauseRequest->save();

            if ($pauseRequest->payment && $pauseRequest->payment->expiry_date) {
                $pauseDays = $pauseRequest->pause_start_date->diffInDays($pauseRequest->pause_end_date) + 1;
                $newExpiryDate = Carbon::parse($pauseRequest->payment->expiry_date)->addDays($pauseDays);
                $pauseRequest->payment->expiry_date = $newExpiryDate;
                $pauseRequest->payment->save();
            }
        });

        return $this->apiSuccess($pauseRequest->fresh()->load(['member', 'payment', 'reviewer']), 'Pause request approved successfully');
    }

    public function apiReject(Request $request, string $id)
    {
        $user = Auth::user();
        
        if (!$user->isSuperAdmin() && !$user->isGymAdmin()) {
            return $this->apiForbidden('You do not have permission to reject pause requests.');
        }

        $pauseRequest = PauseRequest::findOrFail($id);
        $this->validateGymAccess($pauseRequest->gym_id);

        if ($pauseRequest->status !== 'Pending') {
            return $this->apiError('This request has already been processed.', null, 422);
        }

        $pauseRequest->status = 'Rejected';
        $pauseRequest->reviewed_by = $user->id;
        $pauseRequest->reviewed_at = now();
        $pauseRequest->admin_notes = $request->admin_notes ?? null;
        $pauseRequest->save();

        return $this->apiSuccess($pauseRequest->fresh()->load(['member', 'payment', 'reviewer']), 'Pause request rejected');
    }
}
