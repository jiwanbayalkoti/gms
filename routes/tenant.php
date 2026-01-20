<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TrainerController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\WorkoutPlanController;
use App\Http\Controllers\DietPlanController;
use App\Http\Controllers\GymClassController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PauseRequestController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\SalaryPaymentController;
use App\Http\Controllers\BulkSmsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here is where you can register tenant-specific web routes for your application.
| These routes are loaded by the TenancyServiceProvider and are automatically
| wrapped in the appropriate tenant middleware. 
|
*/

// Dashboard routes
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Profile routes
Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

// User Management (Unified view for Members, Trainers, Staff)
Route::get('user-management', [UserManagementController::class, 'index'])->name('user-management.index');

// Member routes (kept for backward compatibility and direct access)
Route::resource('members', MemberController::class);
Route::post('members/status/{member}', [MemberController::class, 'updateStatus'])->name('members.status');

// Trainer routes (kept for backward compatibility and direct access)
Route::resource('trainers', TrainerController::class);
Route::post('trainers/status/{trainer}', [TrainerController::class, 'updateStatus'])->name('trainers.status');

// Staff routes (kept for backward compatibility and direct access)
Route::resource('staff', StaffController::class);
Route::post('staff/status/{staff}', [StaffController::class, 'updateStatus'])->name('staff.status');

// Membership Plan routes
Route::resource('membership-plans', MembershipPlanController::class);
Route::post('membership-plans/status/{plan}', [MembershipPlanController::class, 'updateStatus'])->name('membership-plans.status');

// Workout Plan routes
Route::resource('workout-plans', WorkoutPlanController::class);
Route::get('workout-plans/assign/{plan}/{member?}', [WorkoutPlanController::class, 'showAssignForm'])->name('workout-plans.assign.form');
Route::post('workout-plans/assign/{plan}', [WorkoutPlanController::class, 'assign'])->name('workout-plans.assign');

// Diet Plan routes
Route::resource('diet-plans', DietPlanController::class);
Route::get('diet-plans/assign/{plan}/{member?}', [DietPlanController::class, 'showAssignForm'])->name('diet-plans.assign.form');
Route::post('diet-plans/assign/{plan}', [DietPlanController::class, 'assign'])->name('diet-plans.assign');

// Class routes
Route::resource('classes', GymClassController::class);
Route::post('classes/status/{class}', [GymClassController::class, 'updateStatus'])->name('classes.status');

// Booking routes
Route::resource('bookings', BookingController::class);
Route::post('bookings/{booking}/approve', [BookingController::class, 'approve'])->name('bookings.approve');
Route::post('bookings/{booking}/reject', [BookingController::class, 'reject'])->name('bookings.reject');
Route::post('bookings/status/{booking}', [BookingController::class, 'updateStatus'])->name('bookings.status');
Route::get('bookings/member/{member}', [BookingController::class, 'memberBookings'])->name('bookings.member');

// Attendance routes
Route::resource('attendances', AttendanceController::class);
Route::get('check-in', [AttendanceController::class, 'checkInForm'])->name('attendances.check-in.form');
Route::post('check-in', [AttendanceController::class, 'checkIn'])->name('attendances.check-in');
Route::post('check-out/{attendance}', [AttendanceController::class, 'checkOut'])->name('attendances.check-out');

// Payment routes
Route::resource('payments', PaymentController::class);
Route::get('payments/{payment}/invoice', [PaymentController::class, 'invoice'])->name('payments.invoice');
Route::get('payments/member/{member}', [PaymentController::class, 'memberPayments'])->name('payments.member');
Route::post('payments/process/stripe', [PaymentController::class, 'processStripePayment'])->name('payments.process.stripe');

// Settings routes
Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

// Report routes
Route::get('reports/attendance', [ReportController::class, 'attendance'])->name('reports.attendance');
Route::get('reports/classes', [ReportController::class, 'classes'])->name('reports.classes');
Route::get('reports/payments', [ReportController::class, 'payments'])->name('reports.payments');
Route::get('reports/members', [ReportController::class, 'members'])->name('reports.members');

// Notification routes
Route::resource('notifications', NotificationController::class);
Route::get('my-notifications', [NotificationController::class, 'myNotifications'])->name('notifications.my');
Route::get('notifications/urgent/list', [NotificationController::class, 'getUrgentNotifications'])->name('notifications.urgent');
Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
Route::post('notifications/{notification}/publish', [NotificationController::class, 'publish'])->name('notifications.publish');
Route::post('notifications/{notification}/unpublish', [NotificationController::class, 'unpublish'])->name('notifications.unpublish');
Route::get('notifications/debug', [NotificationController::class, 'debugNotifications'])->name('notifications.debug');

// Event routes
Route::resource('events', EventController::class);
Route::post('events/{event}/response', [EventController::class, 'updateResponse'])->name('events.response');
Route::post('events/{event}/publish', [EventController::class, 'publish'])->name('events.publish');

// Pause Request routes
Route::resource('pause-requests', PauseRequestController::class);
Route::post('pause-requests/{pauseRequest}/approve', [PauseRequestController::class, 'approve'])->name('pause-requests.approve');
Route::post('pause-requests/{pauseRequest}/reject', [PauseRequestController::class, 'reject'])->name('pause-requests.reject');

// Salary Management routes
Route::resource('salaries', SalaryController::class);
Route::post('salaries/{salary}/toggle-status', [SalaryController::class, 'toggleStatus'])->name('salaries.toggle-status');

// Salary Payments (Payroll) routes
Route::get('salary-payments', [SalaryPaymentController::class, 'index'])->name('salary-payments.index');
Route::get('salary-payments/generate', [SalaryPaymentController::class, 'generate'])->name('salary-payments.generate');
Route::get('salary-payments/get-next-period/{salary}', [SalaryPaymentController::class, 'getNextPeriod'])->name('salary-payments.get-next-period');
Route::post('salary-payments/generate', [SalaryPaymentController::class, 'storeGenerated'])->name('salary-payments.store-generated');
Route::get('salary-payments/create', [SalaryPaymentController::class, 'create'])->name('salary-payments.create');
Route::post('salary-payments', [SalaryPaymentController::class, 'store'])->name('salary-payments.store');
Route::get('salary-payments/{id}', [SalaryPaymentController::class, 'show'])->name('salary-payments.show');
Route::get('salary-payments/{id}/payslip', [SalaryPaymentController::class, 'payslip'])->name('salary-payments.payslip');
Route::post('salary-payments/{id}/mark-paid', [SalaryPaymentController::class, 'markAsPaid'])->name('salary-payments.mark-paid');
Route::post('salary-payments/{id}/update-status', [SalaryPaymentController::class, 'updateStatus'])->name('salary-payments.update-status');

// Bulk SMS routes
Route::get('bulk-sms', [BulkSmsController::class, 'index'])->name('bulk-sms.index');
Route::post('bulk-sms/send', [BulkSmsController::class, 'send'])->name('bulk-sms.send');
Route::get('bulk-sms/statistics', [BulkSmsController::class, 'statistics'])->name('bulk-sms.statistics');

// API routes for mobile - will be protected by Sanctum auth
Route::prefix('api')->middleware(['auth:sanctum'])->group(function () {
    Route::get('workout-plans', [WorkoutPlanController::class, 'apiIndex']);
    Route::get('diet-plans', [DietPlanController::class, 'apiIndex']);
    Route::get('classes', [GymClassController::class, 'apiIndex']);
    Route::post('bookings', [BookingController::class, 'apiStore']);
    Route::get('attendances', [AttendanceController::class, 'apiIndex']);
}); 