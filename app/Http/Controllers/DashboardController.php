<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Booking;
use App\Models\GymClass;
use App\Models\Payment;
use App\Models\SalaryPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the dashboard based on user role
     */
    public function index()
    {
        // Common data for all dashboards
        $data = [
            'upcomingClasses' => GymClass::upcoming()->limit(5)->get(),
        ];
        
        // Role-specific dashboard data
        if (Auth::user()->isGymAdmin()) {
            return $this->adminDashboard($data);
        } elseif (Auth::user()->isTrainer()) {
            return $this->trainerDashboard($data);
        } else {
            return $this->memberDashboard($data);
        }
    }
    
    /**
     * Admin dashboard with stats and reports
     */
    private function adminDashboard($data)
    {
        $user = Auth::user();
        
        // Add admin-specific data
        $data['totalMembers'] = User::role('Member')->count();
        $data['totalTrainers'] = User::role('Trainer')->count();
        $data['activeClasses'] = GymClass::active()->count();
        
        // Apply gym filter for financial data
        $paymentQuery = Payment::where('payment_status', 'Completed');
        $salaryPaymentQuery = SalaryPayment::where('payment_status', 'Paid');
        
        // Apply gym filter if not SuperAdmin
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $paymentQuery->where('gym_id', $user->gym_id);
            $salaryPaymentQuery->where('gym_id', $user->gym_id);
        }
        
        // Monthly financial data
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        // Monthly Income (Completed Payments)
        $data['monthlyIncome'] = (clone $paymentQuery)
            ->whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->sum('amount');
        
        // Monthly Expenses (Paid Salary Payments)
        $data['monthlyExpenses'] = (clone $salaryPaymentQuery)
            ->whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->sum('net_amount');
        
        // Net Profit/Loss
        $data['monthlyNetProfit'] = $data['monthlyIncome'] - $data['monthlyExpenses'];
        
        // Yearly totals
        $data['yearlyIncome'] = (clone $paymentQuery)
            ->whereYear('payment_date', $currentYear)
            ->sum('amount');
        
        $data['yearlyExpenses'] = (clone $salaryPaymentQuery)
            ->whereYear('payment_date', $currentYear)
            ->sum('net_amount');
        
        $data['yearlyNetProfit'] = $data['yearlyIncome'] - $data['yearlyExpenses'];
        
        // Last 7 days financial data for chart
        $last7Days = [];
        $incomeData = [];
        $expenseData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last7Days[] = $date->format('D');
            
            $dayIncome = (clone $paymentQuery)
                ->whereDate('payment_date', $date->toDateString())
                ->sum('amount');
            $incomeData[] = $dayIncome;
            
            $dayExpense = (clone $salaryPaymentQuery)
                ->whereDate('payment_date', $date->toDateString())
                ->sum('net_amount');
            $expenseData[] = $dayExpense;
        }
        
        $data['financialChartLabels'] = $last7Days;
        $data['financialChartIncome'] = $incomeData;
        $data['financialChartExpenses'] = $expenseData;
        
        // Last 12 months data for monthly trend
        $monthlyLabels = [];
        $monthlyIncome = [];
        $monthlyExpenses = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthlyLabels[] = $date->format('M Y');
            
            $monthIncome = (clone $paymentQuery)
                ->whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->sum('amount');
            $monthlyIncome[] = $monthIncome;
            
            $monthExpense = (clone $salaryPaymentQuery)
                ->whereMonth('payment_date', $date->month)
                ->whereYear('payment_date', $date->year)
                ->sum('net_amount');
            $monthlyExpenses[] = $monthExpense;
        }
        
        $data['monthlyTrendLabels'] = $monthlyLabels;
        $data['monthlyTrendIncome'] = $monthlyIncome;
        $data['monthlyTrendExpenses'] = $monthlyExpenses;
        
        // Recent payments
        $recentPaymentsQuery = Payment::with('member')
            ->orderBy('payment_date', 'desc')
            ->limit(5);
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $recentPaymentsQuery->where('gym_id', $user->gym_id);
        }
        $data['recentPayments'] = $recentPaymentsQuery->get();
        
        // Recent salary payments
        $recentSalaryPaymentsQuery = SalaryPayment::with('employee')
            ->orderBy('payment_date', 'desc')
            ->limit(5);
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $recentSalaryPaymentsQuery->where('gym_id', $user->gym_id);
        }
        $data['recentSalaryPayments'] = $recentSalaryPaymentsQuery->get();
            
        // Members attendance chart data (last 7 days)
        $last7Days = [];
        $attendanceData = [];
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last7Days[] = $date->format('D');
            
            $attendanceData[] = Attendance::whereDate('check_in_time', $date->toDateString())->count();
        }
        
        $data['attendanceChartLabels'] = $last7Days;
        $data['attendanceChartData'] = $attendanceData;
        
        return view('dashboard.admin', $data);
    }
    
    /**
     * Trainer dashboard focusing on classes and members
     */
    private function trainerDashboard($data)
    {
        $trainer = Auth::user();
        
        // Add trainer-specific data
        $data['myClasses'] = GymClass::where('trainer_id', $trainer->id)
            ->upcoming()
            ->limit(5)
            ->get();
            
        $data['totalClasses'] = GymClass::where('trainer_id', $trainer->id)->count();
        
        $data['totalAssignedMembers'] = User::whereHas('workoutPlansAssigned', function($q) use ($trainer) {
                $q->where('trainer_id', $trainer->id);
            })
            ->orWhereHas('dietPlansAssigned', function($q) use ($trainer) {
                $q->where('trainer_id', $trainer->id);
            })
            ->distinct()
            ->count();
            
        $data['todayAttendance'] = Attendance::whereDate('check_in_time', Carbon::today())
            ->whereHas('gymClass', function($q) use ($trainer) {
                $q->where('trainer_id', $trainer->id);
            })
            ->count();
            
        // Recent class bookings
        $data['recentBookings'] = Booking::with(['member', 'gymClass'])
            ->whereHas('gymClass', function($q) use ($trainer) {
                $q->where('trainer_id', $trainer->id);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        return view('dashboard.trainer', $data);
    }
    
    /**
     * Member dashboard focusing on their bookings and plans
     */
    private function memberDashboard($data)
    {
        $member = Auth::user();
        
        // Add member-specific data
        $data['myBookings'] = Booking::with('gymClass')
            ->where('member_id', $member->id)
            ->whereHas('gymClass', function($q) {
                $q->upcoming();
            })
            ->limit(5)
            ->get();
            
        $data['workoutPlan'] = $member->workoutPlansAssigned()
            ->with('trainer')
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->latest()
            ->first();
            
        $data['dietPlan'] = $member->dietPlansAssigned()
            ->with('trainer')
            ->where(function($q) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', now());
            })
            ->latest()
            ->first();
            
        // Recent attendance
        $data['recentAttendance'] = $member->attendanceRecords()
            ->orderBy('check_in_time', 'desc')
            ->limit(5)
            ->get();
            
        // Payment information
        $latestPayment = $member->payments()
            ->where('payment_status', 'Completed')
            ->latest('payment_date')
            ->first();
        
        $data['paymentStatus'] = $latestPayment && !$latestPayment->hasExpired() ? 'active' : 'expired';
        $data['latestPayment'] = $latestPayment;
        
        // Calculate days left until renewal
        $data['daysLeft'] = null;
        $data['renewalDate'] = null;
        if ($latestPayment && $latestPayment->expiry_date) {
            $data['renewalDate'] = $latestPayment->expiry_date;
            $daysLeft = now()->diffInDays($latestPayment->expiry_date, false);
            $data['daysLeft'] = $daysLeft >= 0 ? $daysLeft : 0;
        }
        
        // Get all payments for the member
        $data['myPayments'] = $member->payments()
            ->with('membershipPlan')
            ->orderBy('payment_date', 'desc')
            ->limit(10)
            ->get();
        
        return view('dashboard.member', $data);
    }

    /**
     * API endpoint for dashboard data
     */
    public function apiIndex(Request $request)
    {
        // Common data for all dashboards
        $data = [
            'upcomingClasses' => GymClass::upcoming()->limit(5)->get(),
        ];
        
        // Role-specific dashboard data
        if (Auth::user()->isGymAdmin()) {
            return $this->apiAdminDashboard($data);
        } elseif (Auth::user()->isTrainer()) {
            return $this->apiTrainerDashboard($data);
        } else {
            return $this->apiMemberDashboard($data);
        }
    }
    
    /**
     * API: Admin dashboard data
     */
    private function apiAdminDashboard($data)
    {
        $user = Auth::user();
        
        // Add admin-specific data
        $data['totalMembers'] = User::role('Member')->count();
        $data['totalTrainers'] = User::role('Trainer')->count();
        $data['activeClasses'] = GymClass::active()->count();
        
        // Apply gym filter for financial data
        $paymentQuery = Payment::where('payment_status', 'Completed');
        $salaryPaymentQuery = SalaryPayment::where('payment_status', 'Paid');
        
        if (!$user->isSuperAdmin() && $user->gym_id) {
            $paymentQuery->where('gym_id', $user->gym_id);
            $salaryPaymentQuery->where('gym_id', $user->gym_id);
        }
        
        // Monthly financial data
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;
        
        $data['monthlyIncome'] = (clone $paymentQuery)
            ->whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->sum('amount');
        
        $data['monthlyExpenses'] = (clone $salaryPaymentQuery)
            ->whereMonth('payment_date', $currentMonth)
            ->whereYear('payment_date', $currentYear)
            ->sum('net_amount');
        
        $data['monthlyNetProfit'] = $data['monthlyIncome'] - $data['monthlyExpenses'];
        
        // Yearly totals
        $data['yearlyIncome'] = (clone $paymentQuery)->whereYear('payment_date', $currentYear)->sum('amount');
        $data['yearlyExpenses'] = (clone $salaryPaymentQuery)->whereYear('payment_date', $currentYear)->sum('net_amount');
        $data['yearlyNetProfit'] = $data['yearlyIncome'] - $data['yearlyExpenses'];
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    
    /**
     * API: Trainer dashboard data
     */
    private function apiTrainerDashboard($data)
    {
        $trainer = Auth::user();
        
        $data['myClasses'] = GymClass::where('trainer_id', $trainer->id)->upcoming()->limit(5)->get();
        $data['totalClasses'] = GymClass::where('trainer_id', $trainer->id)->count();
        $data['todayAttendance'] = Attendance::whereDate('check_in_time', Carbon::today())
            ->whereHas('gymClass', function($q) use ($trainer) {
                $q->where('trainer_id', $trainer->id);
            })->count();
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
    
    /**
     * API: Member dashboard data
     */
    private function apiMemberDashboard($data)
    {
        $member = Auth::user();
        
        $data['myBookings'] = Booking::with('gymClass')
            ->where('member_id', $member->id)
            ->whereHas('gymClass', function($q) {
                $q->upcoming();
            })->limit(5)->get();
        
        $data['workoutPlan'] = $member->workoutPlansAssigned()
            ->with('trainer')
            ->where(function($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })->latest()->first();
        
        $data['dietPlan'] = $member->dietPlansAssigned()
            ->with('trainer')
            ->where(function($q) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', now());
            })->latest()->first();
        
        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}
