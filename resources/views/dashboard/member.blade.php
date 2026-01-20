@extends('layouts.app')

@section('title', 'Member Dashboard')

@section('content')
<div class="row">
    <!-- Membership Status Card -->
    <div class="col-lg-3 col-6">
        <div class="small-box {{ isset($paymentStatus) && $paymentStatus === 'active' ? 'bg-success' : 'bg-danger' }}">
            <div class="inner">
                <h3>{{ isset($paymentStatus) && $paymentStatus === 'active' ? 'Active' : 'Expired' }}</h3>
                <p>Membership Status</p>
                @if(isset($daysLeft) && $daysLeft !== null)
                    <small>{{ $daysLeft }} days left</small>
                @endif
            </div>
            <div class="icon">
                <i class="fas fa-id-card"></i>
            </div>
            <a href="{{ route('profile.edit') }}" class="small-box-footer">
                More info <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Upcoming Classes Card -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $upcomingClasses->count() ?? 0 }}</h3>
                <p>Upcoming Classes</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <a href="{{ route('classes.index') }}" class="small-box-footer">
                View All <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Workout Plans Card -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ Auth::user()->workoutPlansAssigned()->count() ?? 0 }}</h3>
                <p>Workout Plans</p>
            </div>
            <div class="icon">
                <i class="fas fa-dumbbell"></i>
            </div>
            <a href="{{ route('workout-plans.index') }}" class="small-box-footer">
                View Plans <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>

    <!-- Diet Plans Card -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ Auth::user()->dietPlansAssigned()->count() ?? 0 }}</h3>
                <p>Diet Plans</p>
            </div>
            <div class="icon">
                <i class="fas fa-apple-alt"></i>
            </div>
            <a href="{{ route('diet-plans.index') }}" class="small-box-footer">
                View Plans <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

<div class="row">
    <!-- Upcoming Classes -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt mr-1"></i>
                    Upcoming Classes
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($upcomingClasses ?? [] as $class)
                            <tr>
                                <td>{{ $class->name }}</td>
                                <td>{{ $class->start_time->format('M d, Y') }}</td>
                                <td>{{ $class->start_time->format('h:i A') }}</td>
                                <td>
                                    @if($class->status === 'Active')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $class->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No upcoming classes</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Attendance -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-check-circle mr-1"></i>
                    Recent Attendance
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Class</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(Auth::user()->attendanceRecords()->latest()->limit(5)->get() ?? [] as $attendance)
                            <tr>
                                <td>{{ $attendance->check_in_time->format('M d, Y') }}</td>
                                <td>{{ $attendance->gymClass->name ?? 'N/A' }}</td>
                                <td>{{ $attendance->check_in_time->format('h:i A') }}</td>
                                <td>{{ $attendance->check_out_time ? $attendance->check_out_time->format('h:i A') : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No attendance records</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Payment Information -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-credit-card mr-1"></i>
                    My Payments
                </h3>
            </div>
            <div class="card-body">
                @if(isset($latestPayment) && $latestPayment)
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-calendar-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Renewal Date</span>
                                    <span class="info-box-number">
                                        @if($latestPayment->expiry_date)
                                            {{ $latestPayment->expiry_date->format('M d, Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box {{ isset($daysLeft) && $daysLeft !== null && $daysLeft <= 7 ? 'bg-danger' : 'bg-success' }}">
                                <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Days Left</span>
                                    <span class="info-box-number">
                                        @if(isset($daysLeft) && $daysLeft !== null)
                                            {{ $daysLeft }} days
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Last Payment</span>
                                    <span class="info-box-number">
                                        ${{ number_format($latestPayment->amount, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Payment Date</th>
                                <th>Membership Plan</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Expiry Date</th>
                                <th>Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myPayments ?? [] as $payment)
                                @php
                                    $paymentDaysLeft = null;
                                    if ($payment->expiry_date) {
                                        $days = now()->diffInDays($payment->expiry_date, false);
                                        $paymentDaysLeft = $days >= 0 ? $days : 0;
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                    <td>{{ $payment->membershipPlan->name ?? 'N/A' }}</td>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->payment_method }}</td>
                                    <td>
                                        @if($payment->payment_status === 'Completed')
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($payment->payment_status === 'Failed')
                                            <span class="badge badge-danger">Failed</span>
                                        @else
                                            <span class="badge badge-warning">Refunded</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($payment->expiry_date)
                                            {{ $payment->expiry_date->format('M d, Y') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($paymentDaysLeft !== null)
                                            @if($paymentDaysLeft <= 7 && $paymentDaysLeft > 0)
                                                <span class="badge badge-warning">{{ $paymentDaysLeft }} days</span>
                                            @elseif($paymentDaysLeft <= 0)
                                                <span class="badge badge-danger">Expired</span>
                                            @else
                                                <span class="badge badge-success">{{ $paymentDaysLeft }} days</span>
                                            @endif
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No payment records found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
