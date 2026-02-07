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

        // API request (mobile app) - same data structure as web
        if ($request->routeIs('api.reports.*') || $request->is('api/*')) {
            return $this->apiSuccess([
                'attendances' => $attendances->map(function ($a) {
                    $duration = null;
                    if ($a->check_out_time && $a->check_in_time) {
                        $duration = $a->check_in_time->diffForHumans($a->check_out_time, true);
                    }
                    return [
                        'id' => $a->id,
                        'member_id' => $a->member_id,
                        'member_name' => $a->member?->name ?? 'N/A',
                        'class_id' => $a->class_id,
                        'class_name' => $a->gymClass?->name ?? 'N/A',
                        'check_in_time' => $a->check_in_time?->format('M d, Y h:i A'),
                        'check_out_time' => $a->check_out_time?->format('M d, Y h:i A'),
                        'check_out_time_raw' => $a->check_out_time?->toIso8601String(),
                        'duration' => $duration,
                        'gym_id' => $a->gym_id,
                    ];
                })->values(),
                'stats' => $stats,
                'start_date' => $startDate,
                'end_date' => $endDate
            ], 'Attendance report retrieved successfully');
        }

        // Web AJAX request (filter/auto-load) - return JSON with HTML partials
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
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

        // API request (mobile app) - same data structure as web
        if ($request->routeIs('api.reports.*') || $request->is('api/*')) {
            return $this->apiSuccess([
                'classes' => $classes->map(function ($c) {
                    return [
                        'id' => $c->id,
                        'name' => $c->name,
                        'description' => $c->description,
                        'trainer_id' => $c->trainer_id,
                        'trainer_name' => $c->trainer?->name ?? 'N/A',
                        'start_time' => $c->start_time?->format('M d, Y h:i A'),
                        'start_time_raw' => $c->start_time?->toIso8601String(),
                        'end_time' => $c->end_time?->toIso8601String(),
                        'capacity' => $c->capacity,
                        'current_bookings' => $c->current_bookings,
                        'bookings_display' => $c->current_bookings . ' / ' . $c->capacity,
                        'is_full' => $c->isFull(),
                        'status' => $c->status,
                        'gym_id' => $c->gym_id,
                    ];
                })->values(),
                'stats' => $stats,
                'start_date' => $startDate,
                'end_date' => $endDate
            ], 'Classes report retrieved successfully');
        }

        // Web AJAX request (filter/auto-load) - return JSON with HTML partials
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
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

        // API request (mobile app) - same data structure as web
        if ($request->routeIs('api.reports.*') || $request->is('api/*')) {
            $byMethod = $stats['by_method']->mapWithKeys(function ($group, $method) {
                return [$method => ['count' => $group['count'], 'amount' => (float) $group['amount']]];
            })->toArray();
            $apiStats = [
                'total_payments' => $stats['total_payments'],
                'total_amount' => (float) $stats['total_amount'],
                'completed_payments' => $stats['completed_payments'],
                'completed_amount' => (float) $stats['completed_amount'],
                'failed_payments' => $stats['failed_payments'],
                'refunded_payments' => $stats['refunded_payments'],
                'by_method' => $byMethod,
            ];
            return $this->apiSuccess([
                'payments' => $payments->map(function ($p) {
                    return [
                        'id' => $p->id,
                        'member_id' => $p->member_id,
                        'member_name' => $p->member?->name ?? 'N/A',
                        'membership_plan_id' => $p->membership_plan_id,
                        'membership_plan_name' => $p->membershipPlan?->name ?? 'N/A',
                        'amount' => (float) $p->amount,
                        'amount_formatted' => '$' . number_format($p->amount, 2),
                        'payment_method' => $p->payment_method,
                        'payment_status' => $p->payment_status,
                        'payment_date' => $p->payment_date?->format('M d, Y'),
                        'payment_date_raw' => $p->payment_date?->format('Y-m-d'),
                        'expiry_date' => $p->expiry_date?->format('Y-m-d'),
                        'gym_id' => $p->gym_id,
                    ];
                })->values(),
                'stats' => $apiStats,
                'start_date' => $startDate,
                'end_date' => $endDate
            ], 'Payments report retrieved successfully');
        }

        // Web AJAX request (filter/auto-load) - return JSON with HTML partials
        if ($request->ajax() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
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

        // API request (mobile app) - same data structure as web
        if ($request->routeIs('api.reports.*') || $request->is('api/*')) {
            return $this->apiSuccess([
                'members' => $members->map(function ($m) {
                    return [
                        'id' => $m->id,
                        'name' => $m->name,
                        'email' => $m->email,
                        'phone' => $m->phone,
                        'active' => (bool) $m->active,
                        'status_display' => $m->active ? 'Active' : 'Inactive',
                        'bookings_count' => (int) ($m->bookings_count ?? 0),
                        'payments_count' => (int) ($m->payments_count ?? 0),
                        'attendances_count' => (int) ($m->attendances_count ?? 0),
                        'gym_id' => $m->gym_id,
                    ];
                })->values(),
                'stats' => [
                    'total_members' => $stats['total_members'],
                    'active_members' => $stats['active_members'],
                    'inactive_members' => $stats['inactive_members'],
                    'members_with_bookings' => $stats['members_with_bookings'],
                    'members_with_payments' => $stats['members_with_payments'],
                    'members_with_attendance' => $stats['members_with_attendance'],
                    'total_bookings' => $stats['total_bookings'],
                    'total_payments' => $stats['total_payments'],
                    'total_attendance' => $stats['total_attendance'],
                ]
            ], 'Members report retrieved successfully');
        }

        return view('reports.members', compact('members', 'stats'));
    }

}
