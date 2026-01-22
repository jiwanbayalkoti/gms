<?php

/*
|--------------------------------------------------------------------------
| Tenant API Routes (v1)
|--------------------------------------------------------------------------
|
| These are API routes for tenant-specific functionality.
| These routes are used by both web (via AJAX) and mobile app.
| All routes support both session auth (web) and Sanctum token auth (app).
|
*/

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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// API Authentication routes (login is defined in web.php to allow access without tenancy)
// Logout and user routes require authentication
Route::post('/logout', [App\Http\Controllers\Auth\LoginController::class, 'logout'])->middleware('auth:sanctum')->name('api.logout');
Route::get('/user', function (Request $request) {
    return response()->json([
        'success' => true,
        'data' => $request->user()
    ]);
})->middleware('auth:sanctum')->name('api.user');

// All other API routes require authentication (session or token)
// Middleware supports both 'auth' (web session) and 'auth:sanctum' (API token)
// Note: No prefix needed here as we're already in /api/v1 prefix from web.php
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'apiIndex'])->name('api.dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'apiShow'])->name('api.profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'apiEdit'])->name('api.profile.edit');
    Route::put('/profile', [ProfileController::class, 'apiUpdate'])->name('api.profile.update');
    
    // User Management (Unified view)
    Route::get('/user-management', [UserManagementController::class, 'apiIndex'])->name('api.user-management.index');
    
    // Members
    Route::apiResource('members', MemberController::class)->names([
        'index' => 'api.members.index',
        'show' => 'api.members.show',
        'store' => 'api.members.store',
        'update' => 'api.members.update',
        'destroy' => 'api.members.destroy',
    ]);
    Route::post('/members/{member}/status', [MemberController::class, 'apiUpdateStatus'])->name('api.members.status');
    
    // Trainers
    Route::apiResource('trainers', TrainerController::class)->names([
        'index' => 'api.trainers.index',
        'show' => 'api.trainers.show',
        'store' => 'api.trainers.store',
        'update' => 'api.trainers.update',
        'destroy' => 'api.trainers.destroy',
    ]);
    Route::post('/trainers/{trainer}/status', [TrainerController::class, 'apiUpdateStatus'])->name('api.trainers.status');
    
    // Staff
    Route::apiResource('staff', StaffController::class)->names([
        'index' => 'api.staff.index',
        'show' => 'api.staff.show',
        'store' => 'api.staff.store',
        'update' => 'api.staff.update',
        'destroy' => 'api.staff.destroy',
    ]);
    Route::post('/staff/{staff}/status', [StaffController::class, 'apiUpdateStatus'])->name('api.staff.status');
    
    // Membership Plans
    Route::apiResource('membership-plans', MembershipPlanController::class)->names([
        'index' => 'api.membership-plans.index',
        'show' => 'api.membership-plans.show',
        'store' => 'api.membership-plans.store',
        'update' => 'api.membership-plans.update',
        'destroy' => 'api.membership-plans.destroy',
    ]);
    Route::post('/membership-plans/{plan}/status', [MembershipPlanController::class, 'apiUpdateStatus'])->name('api.membership-plans.status');
    
    // Workout Plans
    Route::apiResource('workout-plans', WorkoutPlanController::class)->names([
        'index' => 'api.workout-plans.index',
        'show' => 'api.workout-plans.show',
        'store' => 'api.workout-plans.store',
        'update' => 'api.workout-plans.update',
        'destroy' => 'api.workout-plans.destroy',
    ]);
    Route::get('/workout-plans/{plan}/assign/{member?}', [WorkoutPlanController::class, 'apiShowAssignForm'])->name('api.workout-plans.assign.form');
    Route::post('/workout-plans/{plan}/assign', [WorkoutPlanController::class, 'apiAssign'])->name('api.workout-plans.assign');
    
    // Diet Plans
    Route::apiResource('diet-plans', DietPlanController::class)->names([
        'index' => 'api.diet-plans.index',
        'show' => 'api.diet-plans.show',
        'store' => 'api.diet-plans.store',
        'update' => 'api.diet-plans.update',
        'destroy' => 'api.diet-plans.destroy',
    ]);
    Route::get('/diet-plans/{plan}/assign/{member?}', [DietPlanController::class, 'apiShowAssignForm'])->name('api.diet-plans.assign.form');
    Route::post('/diet-plans/{plan}/assign', [DietPlanController::class, 'apiAssign'])->name('api.diet-plans.assign');
    
    // Classes
    Route::apiResource('classes', GymClassController::class)->names([
        'index' => 'api.classes.index',
        'show' => 'api.classes.show',
        'store' => 'api.classes.store',
        'update' => 'api.classes.update',
        'destroy' => 'api.classes.destroy',
    ]);
    Route::post('/classes/{class}/status', [GymClassController::class, 'apiUpdateStatus'])->name('api.classes.status');
    
    // Bookings
    Route::apiResource('bookings', BookingController::class)->names([
        'index' => 'api.bookings.index',
        'show' => 'api.bookings.show',
        'store' => 'api.bookings.store',
        'update' => 'api.bookings.update',
        'destroy' => 'api.bookings.destroy',
    ]);
    Route::post('/bookings/{booking}/approve', [BookingController::class, 'apiApprove'])->name('api.bookings.approve');
    Route::post('/bookings/{booking}/reject', [BookingController::class, 'apiReject'])->name('api.bookings.reject');
    Route::post('/bookings/{booking}/status', [BookingController::class, 'apiUpdateStatus'])->name('api.bookings.status');
    Route::get('/bookings/member/{member}', [BookingController::class, 'apiMemberBookings'])->name('api.bookings.member');
    
    // Attendances
    Route::apiResource('attendances', AttendanceController::class)->names([
        'index' => 'api.attendances.index',
        'show' => 'api.attendances.show',
        'store' => 'api.attendances.store',
        'update' => 'api.attendances.update',
        'destroy' => 'api.attendances.destroy',
    ]);
    Route::get('/check-in', [AttendanceController::class, 'apiCheckInForm'])->name('api.attendances.check-in.form');
    Route::post('/check-in', [AttendanceController::class, 'apiCheckIn'])->name('api.attendances.check-in');
    Route::post('/check-out/{attendance}', [AttendanceController::class, 'apiCheckOut'])->name('api.attendances.check-out');
    
    // Payments
    Route::apiResource('payments', PaymentController::class)->names([
        'index' => 'api.payments.index',
        'show' => 'api.payments.show',
        'store' => 'api.payments.store',
        'update' => 'api.payments.update',
        'destroy' => 'api.payments.destroy',
    ]);
    Route::get('/payments/{payment}/invoice', [PaymentController::class, 'apiInvoice'])->name('api.payments.invoice');
    Route::get('/payments/member/{member}', [PaymentController::class, 'apiMemberPayments'])->name('api.payments.member');
    Route::post('/payments/process/stripe', [PaymentController::class, 'apiProcessStripePayment'])->name('api.payments.process.stripe');
    
    // Settings
    Route::get('/settings', [SettingController::class, 'apiIndex'])->name('api.settings.index');
    Route::put('/settings', [SettingController::class, 'apiUpdate'])->name('api.settings.update');
    
    // Reports
    Route::get('/reports/attendance', [ReportController::class, 'apiAttendance'])->name('api.reports.attendance');
    Route::get('/reports/classes', [ReportController::class, 'apiClasses'])->name('api.reports.classes');
    Route::get('/reports/payments', [ReportController::class, 'apiPayments'])->name('api.reports.payments');
    Route::get('/reports/members', [ReportController::class, 'apiMembers'])->name('api.reports.members');
    
    // Notifications
    Route::apiResource('notifications', NotificationController::class)->names([
        'index' => 'api.notifications.index',
        'show' => 'api.notifications.show',
        'store' => 'api.notifications.store',
        'update' => 'api.notifications.update',
        'destroy' => 'api.notifications.destroy',
    ]);
    Route::get('/my-notifications', [NotificationController::class, 'apiMyNotifications'])->name('api.notifications.my');
    Route::get('/notifications/urgent/list', [NotificationController::class, 'apiGetUrgentNotifications'])->name('api.notifications.urgent');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'apiMarkAsRead'])->name('api.notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'apiMarkAllAsRead'])->name('api.notifications.read-all');
    Route::post('/notifications/{notification}/publish', [NotificationController::class, 'apiPublish'])->name('api.notifications.publish');
    Route::post('/notifications/{notification}/unpublish', [NotificationController::class, 'apiUnpublish'])->name('api.notifications.unpublish');
    
    // Events
    Route::apiResource('events', EventController::class)->names([
        'index' => 'api.events.index',
        'show' => 'api.events.show',
        'store' => 'api.events.store',
        'update' => 'api.events.update',
        'destroy' => 'api.events.destroy',
    ]);
    Route::post('/events/{event}/response', [EventController::class, 'apiUpdateResponse'])->name('api.events.response');
    Route::post('/events/{event}/publish', [EventController::class, 'apiPublish'])->name('api.events.publish');
    
    // Pause Requests
    Route::apiResource('pause-requests', PauseRequestController::class)->names([
        'index' => 'api.pause-requests.index',
        'show' => 'api.pause-requests.show',
        'store' => 'api.pause-requests.store',
        'update' => 'api.pause-requests.update',
        'destroy' => 'api.pause-requests.destroy',
    ]);
    Route::post('/pause-requests/{pauseRequest}/approve', [PauseRequestController::class, 'apiApprove'])->name('api.pause-requests.approve');
    Route::post('/pause-requests/{pauseRequest}/reject', [PauseRequestController::class, 'apiReject'])->name('api.pause-requests.reject');
    
    // Salaries
    Route::apiResource('salaries', SalaryController::class)->names([
        'index' => 'api.salaries.index',
        'show' => 'api.salaries.show',
        'store' => 'api.salaries.store',
        'update' => 'api.salaries.update',
        'destroy' => 'api.salaries.destroy',
    ]);
    Route::post('/salaries/{salary}/toggle-status', [SalaryController::class, 'apiToggleStatus'])->name('api.salaries.toggle-status');
    
    // Salary Payments (Payroll)
    Route::get('/salary-payments', [SalaryPaymentController::class, 'apiIndex'])->name('api.salary-payments.index');
    Route::get('/salary-payments/generate', [SalaryPaymentController::class, 'apiGenerate'])->name('api.salary-payments.generate');
    Route::get('/salary-payments/get-next-period/{salary}', [SalaryPaymentController::class, 'apiGetNextPeriod'])->name('api.salary-payments.get-next-period');
    Route::post('/salary-payments/generate', [SalaryPaymentController::class, 'apiStoreGenerated'])->name('api.salary-payments.store-generated');
    Route::post('/salary-payments', [SalaryPaymentController::class, 'apiStore'])->name('api.salary-payments.store');
    Route::get('/salary-payments/{id}', [SalaryPaymentController::class, 'apiShow'])->name('api.salary-payments.show');
    Route::get('/salary-payments/{id}/payslip', [SalaryPaymentController::class, 'apiPayslip'])->name('api.salary-payments.payslip');
    Route::post('/salary-payments/{id}/mark-paid', [SalaryPaymentController::class, 'apiMarkAsPaid'])->name('api.salary-payments.mark-paid');
    Route::post('/salary-payments/{id}/update-status', [SalaryPaymentController::class, 'apiUpdateStatus'])->name('api.salary-payments.update-status');
    
    // Bulk SMS
    Route::get('/bulk-sms', [BulkSmsController::class, 'apiIndex'])->name('api.bulk-sms.index');
    Route::post('/bulk-sms/send', [BulkSmsController::class, 'apiSend'])->name('api.bulk-sms.send');
    Route::get('/bulk-sms/statistics', [BulkSmsController::class, 'apiStatistics'])->name('api.bulk-sms.statistics');
});
