{{-- Dashboard --}}
<li class="nav-item">
    <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>

{{-- Gyms Management (SuperAdmin only) --}}
@if(Auth::check() && Auth::user()->isSuperAdmin() && Route::has('gyms.index'))
<li class="nav-item">
    <a href="{{ route('gyms.index') }}" class="nav-link {{ request()->routeIs('gyms.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-building"></i>
        <p>Gyms</p>
    </a>
</li>
@endif

{{-- Users Management (Members, Trainers, Staff) --}}
@if(Route::has('user-management.index') && 
    ((Auth::user()->hasPermission('members.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin()) ||
     (Auth::user()->hasPermission('trainers.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin()) ||
     (Auth::user()->hasPermission('staff.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin())))
<li class="nav-item">
    <a href="{{ route('user-management.index', ['category' => 'members']) }}" class="nav-link {{ request()->routeIs('user-management.*') || request()->routeIs('members.*') || request()->routeIs('trainers.*') || request()->routeIs('staff.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users"></i>
        <p>Users</p>
    </a>
</li>
@endif

{{-- Membership Plans --}}
@if(Route::has('membership-plans.index') && (Auth::user()->hasPermission('membership-plans.view') || Auth::user()->isGymAdmin()))
<li class="nav-item">
    <a href="{{ route('membership-plans.index') }}" class="nav-link {{ request()->routeIs('membership-plans.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-id-card"></i>
        <p>Membership Plans</p>
    </a>
</li>
@endif

{{-- Classes --}}
@if(Route::has('classes.index') && (Auth::user()->hasPermission('classes.view') || Auth::user()->isGymAdmin() || Auth::user()->isTrainer()))
<li class="nav-item">
    <a href="{{ route('classes.index') }}" class="nav-link {{ request()->routeIs('classes.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-alt"></i>
        <p>Classes</p>
    </a>
</li>
@endif

{{-- Bookings --}}
@if(Route::has('bookings.index') && (Auth::user()->hasPermission('bookings.view') || Auth::user()->isGymAdmin() || Auth::user()->isStaff() || Auth::user()->isMember()))
<li class="nav-item">
    <a href="{{ route('bookings.index') }}" class="nav-link {{ request()->routeIs('bookings.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-book"></i>
        <p>Bookings</p>
    </a>
</li>
@endif

{{-- Attendance --}}
@if(Route::has('attendances.index') && (Auth::user()->hasPermission('attendance.view') || Auth::user()->isGymAdmin() || Auth::user()->isStaff()))
<li class="nav-item">
    <a href="{{ route('attendances.index') }}" class="nav-link {{ request()->routeIs('attendances.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-check-circle"></i>
        <p>Attendance</p>
    </a>
</li>
@endif

{{-- Workout Plans --}}
@if(Route::has('workout-plans.index') && (Auth::user()->hasPermission('workout-plans.view') || Auth::user()->isTrainer()))
<li class="nav-item">
    <a href="{{ route('workout-plans.index') }}" class="nav-link {{ request()->routeIs('workout-plans.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-dumbbell"></i>
        <p>Workout Plans</p>
    </a>
</li>
@endif

{{-- Diet Plans --}}
@if(Route::has('diet-plans.index') && (Auth::user()->hasPermission('diet-plans.view') || Auth::user()->isTrainer()))
<li class="nav-item">
    <a href="{{ route('diet-plans.index') }}" class="nav-link {{ request()->routeIs('diet-plans.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-apple-alt"></i>
        <p>Diet Plans</p>
    </a>
</li>
@endif

{{-- Payments --}}
@if(Route::has('payments.index') && (Auth::user()->hasPermission('payments.view') || Auth::user()->isGymAdmin()))
<li class="nav-item">
    <a href="{{ route('payments.index') }}" class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-credit-card"></i>
        <p>Payments</p>
    </a>
</li>
@endif

{{-- Salaries --}}
@if(Route::has('salaries.index') && (Auth::user()->hasPermission('salaries.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin()))
<li class="nav-item">
    <a href="{{ route('salaries.index') }}" class="nav-link {{ request()->routeIs('salaries.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-money-bill-wave"></i>
        <p>Salaries</p>
    </a>
</li>
@endif

{{-- Salary Payments (Payroll) --}}
@if(Route::has('salary-payments.index') && (Auth::user()->hasPermission('salary-payments.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin()))
<li class="nav-item">
    <a href="{{ route('salary-payments.index') }}" class="nav-link {{ request()->routeIs('salary-payments.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-wallet"></i>
        <p>Payroll</p>
    </a>
</li>
@endif

{{-- Pause Requests --}}
@if(Route::has('pause-requests.index'))
    @php
        $settings = \App\Models\Setting::current();
    @endphp
    @if(Auth::user()->isMember() && $settings->enable_pause_feature)
        <li class="nav-item">
            <a href="{{ route('pause-requests.index') }}" class="nav-link {{ request()->routeIs('pause-requests.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-pause-circle"></i>
                <p>Pause Requests</p>
            </a>
        </li>
    @elseif((Auth::user()->hasPermission('payments.view') || Auth::user()->isGymAdmin()) && $settings->enable_pause_feature)
        <li class="nav-item">
            <a href="{{ route('pause-requests.index') }}" class="nav-link {{ request()->routeIs('pause-requests.*') ? 'active' : '' }}">
                <i class="nav-icon fas fa-pause-circle"></i>
                <p>Pause Requests</p>
            </a>
        </li>
    @endif
@endif

{{-- Social Media - Removed for now, will be added later --}}

{{-- Reports --}}
@if(Route::has('reports.attendance') && (Auth::user()->hasPermission('reports.view') || Auth::user()->isGymAdmin()))
<li class="nav-item has-treeview {{ request()->routeIs('reports.*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-chart-bar"></i>
        <p>
            Reports
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('reports.attendance') }}" class="nav-link {{ request()->routeIs('reports.attendance') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Attendance</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.classes') }}" class="nav-link {{ request()->routeIs('reports.classes') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Classes</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.payments') }}" class="nav-link {{ request()->routeIs('reports.payments') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Payments</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('reports.members') }}" class="nav-link {{ request()->routeIs('reports.members') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Members</p>
            </a>
        </li>
    </ul>
</li>
@endif

{{-- Notifications Management (Admin only) --}}
@if(Route::has('notifications.index') && (Auth::user()->hasPermission('notifications.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin()))
<li class="nav-item">
    <a href="{{ route('notifications.index') }}" class="nav-link {{ request()->routeIs('notifications.index') || request()->routeIs('notifications.create') || request()->routeIs('notifications.edit') ? 'active' : '' }}">
        <i class="nav-icon fas fa-bell"></i>
        <p>Notifications</p>
    </a>
</li>
@endif

{{-- My Notifications (Only for Admin/SuperAdmin - Members use navbar dropdown) --}}
@if(Route::has('notifications.my') && (Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin()))
<li class="nav-item">
    <a href="{{ route('notifications.my') }}" class="nav-link {{ request()->routeIs('notifications.my') ? 'active' : '' }}">
        <i class="nav-icon far fa-bell"></i>
        <p>My Notifications</p>
    </a>
</li>
@endif

{{-- Bulk SMS --}}
@if(Route::has('bulk-sms.index') && (Auth::user()->hasPermission('notifications.create') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin()))
<li class="nav-item">
    <a href="{{ route('bulk-sms.index') }}" class="nav-link {{ request()->routeIs('bulk-sms.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-sms"></i>
        <p>Bulk SMS</p>
    </a>
</li>
@endif

{{-- Events --}}
@if(Route::has('events.index') && (Auth::user()->hasPermission('events.view') || Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin() || Auth::user()->isMember()))
<li class="nav-item">
    <a href="{{ route('events.index') }}" class="nav-link {{ request()->routeIs('events.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-calendar-alt"></i>
        <p>Events</p>
    </a>
</li>
@endif

{{-- Settings (GymAdmin only) --}}
@if(Route::has('settings.index') && Auth::check() && Auth::user()->isGymAdmin())
<li class="nav-item">
    <a href="{{ route('settings.index') }}" class="nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-cog"></i>
        <p>Settings</p>
    </a>
</li>
@endif

{{-- Profile --}}
@if(Route::has('profile.edit'))
<li class="nav-item">
    <a href="{{ route('profile.edit') }}" class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-user"></i>
        <p>Profile</p>
    </a>
</li>
@endif

{{-- Divider --}}
<li class="nav-header">ACCOUNT</li>

{{-- Logout --}}
@if(Route::has('logout'))
<li class="nav-item">
    <form method="POST" action="{{ route('logout') }}" class="d-inline">
        @csrf
        <button type="submit" class="nav-link btn btn-link text-left w-100" style="border: none; background: none; color: #c2c7d0;">
            <i class="nav-icon fas fa-sign-out-alt"></i>
            <p>Logout</p>
        </button>
    </form>
</li>
@endif
