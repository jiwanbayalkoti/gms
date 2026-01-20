@extends('layouts.app')

@section('title', 'Trainer Dashboard')
@section('page-title', 'Trainer Dashboard')

@section('content')
<div class="row">
    <!-- Stats -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow h-100 py-2 stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">My Classes</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalClasses }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dumbbell fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow h-100 py-2 stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Assigned Members</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalAssignedMembers }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow h-100 py-2 stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Today's Attendance</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayAttendance }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-0 shadow h-100 py-2 stat-card">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            <a href="{{ route('workout-plans.index') }}" class="text-warning">Workout Plans</a>
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <a href="{{ route('workout-plans.create') }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-plus"></i> Create New
                            </a>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-running fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- My Classes -->
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">My Upcoming Classes</h6>
                <a href="{{ route('classes.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Bookings</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($myClasses as $class)
                            <tr>
                                <td>{{ $class->name }}</td>
                                <td>
                                    {{ $class->start_time->format('M d, Y') }}<br>
                                    <small class="text-muted">{{ $class->start_time->format('h:i A') }} - {{ $class->end_time->format('h:i A') }}</small>
                                </td>
                                <td>{{ $class->location }}</td>
                                <td>{{ $class->current_bookings }}/{{ $class->capacity }}</td>
                                <td>
                                    <a href="{{ route('classes.show', $class) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">No upcoming classes</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="col-xl-4 col-lg-5">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Recent Bookings</h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    @forelse($recentBookings as $booking)
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h6 class="mb-1">{{ $booking->member->name }}</h6>
                            <small>{{ $booking->created_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1">
                            Booked: <strong>{{ $booking->gymClass->name }}</strong>
                        </p>
                        <small>
                            {{ $booking->gymClass->start_time->format('M d, Y h:i A') }}
                            <span class="badge badge-{{ $booking->status == 'Confirmed' ? 'success' : 'secondary' }}">
                                {{ $booking->status }}
                            </span>
                        </small>
                    </div>
                    @empty
                    <div class="list-group-item">No recent bookings</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Upcoming Classes (All) -->
    <div class="col-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">All Upcoming Classes</h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Trainer</th>
                                <th>Date & Time</th>
                                <th>Location</th>
                                <th>Bookings</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($upcomingClasses as $class)
                            <tr>
                                <td>{{ $class->name }}</td>
                                <td>{{ $class->trainer->name }}</td>
                                <td>
                                    {{ $class->start_time->format('M d, Y') }}<br>
                                    <small class="text-muted">{{ $class->start_time->format('h:i A') }} - {{ $class->end_time->format('h:i A') }}</small>
                                </td>
                                <td>{{ $class->location }}</td>
                                <td>{{ $class->current_bookings }}/{{ $class->capacity }}</td>
                                <td>
                                    <a href="{{ route('classes.show', $class) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No upcoming classes</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="{{ route('classes.index') }}" class="btn btn-primary">View All Classes</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 