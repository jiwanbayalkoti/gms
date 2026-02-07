<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Booking;
use App\Models\GymClass;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * ReportController
 * 
 * Handles various reports with gym-based data isolation.
 */
class ReportController extends BaseController
{
    /**
     * Show attendance report
     */
    public function attendance(Request $request)
    {
        $this->authorizePermission('reports.view');

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $query = Attendance::with(['member', 'gymClass'])
            ->whereDate('check_in_time', '>=', $startDate)
            ->whereDate('check_in_time', '<=', $endDate);

        $attendances = $this->applyGymFilter($query)->orderBy('check_in_time', 'desc')->get();

        // Statistics
        $stats = [
            'total_checkins' => $attendances->count(),
            'unique_members' => $attendances->pluck('member_id')->unique()->count(),
            'with_classes' => $attendances->whereNotNull('class_id')->count(),
            'without_checkout' => $attendances->whereNull('check_out_time')->count(),
        ];

        // API request (mobile app) - return JSON data
        if ($this->isApiRequest($request)) {
            return $this->apiSuccess([
                'attendances' => $attendances,
                'stats' => $stats,
                'start_date' => $startDate,
                'end_date' => $endDate
            ], 'Attendance report retrieved successfully');
        }

        // Web AJAX request - return JSON with HTML partials
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'statsHtml' => view('reports._attendance-stats', compact('stats'))->render(),
                'tableHtml' => view('reports._attendance-table-body', compact('attendances'))->render()
            ]);
        }

        return view('reports.attendance', compact('attendances', 'stats', 'startDate', 'endDate'));
    }

    /**
     * Show classes report
     */
    public function classes(Request $request)
    {
        $this->authorizePermission('reports.view');

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $query = GymClass::with(['trainer', 'bookings'])
            ->whereDate('start_time', '>=', $startDate)
            ->whereDate('start_time', '<=', $endDate);

        $classes = $this->applyGymFilter($query)->orderBy('start_time')->get();

        // Statistics
        $stats = [
            'total_classes' => $classes->count(),
            'active_classes' => $classes->where('status', 'Active')->count(),
            'cancelled_classes' => $classes->where('status', 'Cancelled')->count(),
            'completed_classes' => $classes->where('status', 'Completed')->count(),
            'total_bookings' => $classes->sum(function($class) {
                return $class->bookings->count();
            }),
            'average_attendance' => $classes->count() > 0 
                ? round($classes->sum('current_bookings') / $classes->count(), 2) 
                : 0,
        ];

        // API request (mobile app) - return JSON data
        if ($this->isApiRequest($request)) {
            return $this->apiSuccess([
                'classes' => $classes,
                'stats' => $stats,
                'start_date' => $startDate,
                'end_date' => $endDate
            ], 'Classes report retrieved successfully');
        }

        // Web AJAX request - return JSON with HTML partials
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'statsHtml' => view('reports._classes-stats', compact('stats'))->render(),
                'tableHtml' => view('reports._classes-table-body', compact('classes'))->render()
            ]);
        }

        return view('reports.classes', compact('classes', 'stats', 'startDate', 'endDate'));
    }

    /**
     * Show payments report
     */
    public function payments(Request $request)
    {
        $this->authorizePermission('reports.view');

        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', now()->endOfMonth()->toDateString());

        $query = Payment::with(['member', 'membershipPlan'])
            ->whereDate('payment_date', '>=', $startDate)
            ->whereDate('payment_date', '<=', $endDate);

        $payments = $this->applyGymFilter($query)->orderBy('payment_date', 'desc')->get();

        // Statistics
        $stats = [
            'total_payments' => $payments->count(),
            'total_amount' => $payments->sum('amount'),
            'completed_payments' => $payments->where('payment_status', 'Completed')->count(),
            'failed_payments' => $payments->where('payment_status', 'Failed')->count(),
            'refunded_payments' => $payments->where('payment_status', 'Refunded')->count(),
            'completed_amount' => $payments->where('payment_status', 'Completed')->sum('amount'),
            'by_method' => $payments->groupBy('payment_method')->map(function($group) {
                return [
                    'count' => $group->count(),
                    'amount' => $group->sum('amount')
                ];
            }),
        ];

        // API request (mobile app) - return JSON data
        if ($this->isApiRequest($request)) {
            $apiStats = [
                'total_payments' => $payments->count(),
                'total_amount' => $payments->sum('amount'),
                'completed_payments' => $payments->where('payment_status', 'Completed')->count(),
                'completed_amount' => $payments->where('payment_status', 'Completed')->sum('amount'),
                'pending_payments' => $payments->where('payment_status', 'Pending')->count(),
                'failed_payments' => $payments->where('payment_status', 'Failed')->count(),
            ];
            return $this->apiSuccess([
                'payments' => $payments,
                'stats' => $apiStats,
                'start_date' => $startDate,
                'end_date' => $endDate
            ], 'Payments report retrieved successfully');
        }

        // Web AJAX request - return JSON with HTML partials
        if ($this->isWebAjaxRequest($request)) {
            return response()->json([
                'success' => true,
                'statsHtml' => view('reports._payments-stats', compact('stats'))->render(),
                'tableHtml' => view('reports._payments-table-body', compact('payments'))->render()
            ]);
        }

        return view('reports.payments', compact('payments', 'stats', 'startDate', 'endDate'));
    }

    /**
     * Show members report
     */
    public function members(Request $request)
    {
        $this->authorizePermission('reports.view');

        $query = User::where('role', 'Member');
        $members = $this->applyGymFilter($query)->withCount([
            'bookings',
            'payments',
            'attendanceRecords as attendances_count'
        ])->get();

        // Statistics
        $stats = [
            'total_members' => $members->count(),
            'active_members' => $members->where('active', true)->count(),
            'inactive_members' => $members->where('active', false)->count(),
            'members_with_bookings' => $members->where('bookings_count', '>', 0)->count(),
            'members_with_payments' => $members->where('payments_count', '>', 0)->count(),
            'members_with_attendance' => $members->where('attendances_count', '>', 0)->count(),
            'total_bookings' => $members->sum('bookings_count'),
            'total_payments' => $members->sum('payments_count'),
            'total_attendance' => $members->sum('attendances_count'),
        ];

        // API request (mobile app) - return JSON data
        if ($this->isApiRequest($request)) {
            return $this->apiSuccess([
                'members' => $members,
                'stats' => $stats
            ], 'Members report retrieved successfully');
        }

        return view('reports.members', compact('members', 'stats'));
    }

}
