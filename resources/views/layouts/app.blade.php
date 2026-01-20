<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} - @yield('title', 'Dashboard')</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
    
    <!-- AdminLTE 3 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    
    <!-- Dynamic Theme Colors from Settings -->
    @php
        try {
        $settings = App\Models\Setting::current();
        } catch (\Exception $e) {
            $settings = null;
        }
    @endphp
    
    <style>
        :root {
            --primary-color: {{ $settings->primary_color ?? '#007bff' }};
            --secondary-color: {{ $settings->secondary_color ?? '#6c757d' }};
        }
        
        /* Override AdminLTE primary color if settings exist */
        @if($settings && $settings->primary_color)
        .btn-primary, .bg-primary, .text-primary, .nav-link.active {
            background-color: {{ $settings->primary_color }} !important;
            border-color: {{ $settings->primary_color }} !important;
            color: #fff !important;
        }
        @endif
    </style>
    
    @stack('styles')
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
        <!-- Left navbar links -->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
            </li>
        </ul>

        <!-- Right navbar links -->
        <ul class="navbar-nav ml-auto">
            <!-- Notifications Dropdown Menu -->
            <li class="nav-item dropdown" id="notificationDropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-bell"></i>
                    <span class="badge badge-warning navbar-badge" id="notificationBadge">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" id="notificationMenu" style="width: 350px;">
                    <span class="dropdown-item dropdown-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span id="notificationHeader">Notifications</span>
                            <button type="button" class="btn btn-sm btn-link p-0" onclick="markAllNotificationsAsRead()" id="markAllReadBtn" style="display: none;">
                                <small>Mark all read</small>
                            </button>
                        </div>
                    </span>
                    <div class="dropdown-divider"></div>
                    
                    <!-- Tabs for Read/Unread -->
                    <div class="px-2 pb-2">
                        <ul class="nav nav-tabs nav-justified" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="unread-tab" href="#unread-notifications" role="tab" aria-controls="unread-notifications" aria-selected="true" onclick="switchNotificationTab('unread', event); return false;">
                                    Unread <span class="badge badge-danger" id="unreadCount">0</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="read-tab" href="#read-notifications" role="tab" aria-controls="read-notifications" aria-selected="false" onclick="switchNotificationTab('read', event); return false;">
                                    Read <span class="badge badge-secondary" id="readCount">0</span>
                                </a>
                            </li>
                        </ul>
            </div>
            
                    <!-- Tab Content -->
                    <div class="tab-content" style="max-height: 400px; overflow-y: auto;">
                        <div class="tab-pane fade show active" id="unread-notifications" role="tabpanel" aria-labelledby="unread-tab">
                            <div id="unreadNotificationList">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="read-notifications" role="tabpanel" aria-labelledby="read-tab">
                            <div id="readNotificationList">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-secondary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="dropdown-divider"></div>
                    @if(Route::has('notifications.my') && (Auth::user()->isGymAdmin() || Auth::user()->isSuperAdmin()))
                        <a href="{{ route('notifications.my') }}" class="dropdown-item dropdown-footer text-center">View All Notifications</a>
                    @endif
                </div>
            </li>
                    
            <!-- User Dropdown Menu -->
                            <li class="nav-item dropdown">
                <a class="nav-link" data-toggle="dropdown" href="#">
                    <i class="far fa-user"></i>
                    <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'User' }}</span>
                                </a>
                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                    <a href="{{ route('profile.edit') }}" class="dropdown-item">
                                        <i class="fas fa-user-circle mr-2"></i> Profile
                                    </a>
                    @if(Auth::check() && Auth::user()->isGymAdmin())
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('settings.index') }}" class="dropdown-item">
                                            <i class="fas fa-cog mr-2"></i> Settings
                                        </a>
                                    @endif
                                    <div class="dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                        <button type="submit" class="dropdown-item dropdown-footer">
                                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                                        </button>
                                    </form>
                                </div>
                            </li>
                        </ul>
                </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
        <!-- Brand Logo -->
        <a href="{{ route('dashboard') }}" class="brand-link">
            @php
                $logoExists = false;
                if ($settings && $settings->logo) {
                    $logoPath = storage_path('app/public/' . $settings->logo);
                    $logoExists = file_exists($logoPath);
                }
            @endphp
            @if($logoExists)
                <img src="{{ asset('storage/' . $settings->logo) }}" alt="{{ $settings->gym_name ?? config('app.name') }}" class="brand-image img-circle elevation-3" style="opacity: .8" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <span class="brand-image img-circle elevation-3 bg-white d-flex align-items-center justify-content-center" style="width: 33px; height: 33px; display: none;">
                    <i class="fas fa-dumbbell text-primary"></i>
                </span>
            @else
                <span class="brand-image img-circle elevation-3 bg-white d-flex align-items-center justify-content-center" style="width: 33px; height: 33px;">
                    <i class="fas fa-dumbbell text-primary"></i>
                </span>
            @endif
            <span class="brand-text font-weight-light">{{ $settings->gym_name ?? config('app.name') }}</span>
        </a>

        <!-- Sidebar -->
        <div class="sidebar">
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    @php
                        $userPhotoUrl = Auth::check() ? getImageUrl(Auth::user()->profile_photo) : null;
                    @endphp
                    @if($userPhotoUrl)
                        <img src="{{ $userPhotoUrl }}" class="img-circle elevation-2" alt="User Image" style="width: 40px; height: 40px; object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="img-circle elevation-2 bg-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; display: none;">
                            <span class="text-white">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                        </div>
                    @else
                        <div class="img-circle elevation-2 bg-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <span class="text-white">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                            </div>
                        @endif
                    </div>
                <div class="info">
                    <a href="{{ route('profile.edit') }}" class="d-block">{{ Auth::user()->name ?? 'User' }}</a>
                    <small class="text-muted">{{ Auth::user()->role ?? 'Member' }}</small>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                    @include('layouts.sidebar')
                </ul>
            </nav>
            <!-- /.sidebar-menu -->
        </div>
        <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">@yield('title', 'Dashboard')</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                            @hasSection('breadcrumb')
                                @yield('breadcrumb')
                            @else
                                <li class="breadcrumb-item active">@yield('title', 'Dashboard')</li>
                            @endif
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif
                    
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif
                    
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                    @yield('content')
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    <!-- Footer -->
    <footer class="main-footer">
        <strong>Copyright &copy; {{ date('Y') }} <a href="#">{{ $settings->gym_name ?? config('app.name') }}</a>.</strong>
        All rights reserved.
        <div class="float-right d-none d-sm-inline-block">
            <b>Version</b> 1.0.0
        </div>
    </footer>

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery Validation Plugin -->
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- API Helper (must be loaded before other scripts that use API) -->
<script src="{{ asset('assets/js/api-helper.js') }}"></script>
<!-- Custom JS -->
<script src="{{ asset('assets/js/custom.js') }}"></script>
<!-- Delete Confirmation Modal JS -->
<script src="{{ asset('assets/js/delete-confirm.js') }}"></script>
<!-- Form Validation -->
<script src="{{ asset('assets/js/form-validation.js') }}"></script>

@stack('scripts')

<!-- Notification System Script -->
<script>
(function() {
    'use strict';
    
    // Make formatDate globally accessible first
    window.formatDate = function(dateString) {
        var date = new Date(dateString);
        var now = new Date();
        var diff = Math.floor((now - date) / 1000);
        
        if (diff < 60) return 'Just now';
        if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
        if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
        if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
        
        return date.toLocaleDateString();
    };
    
    // Switch notification tabs without closing dropdown
    window.switchNotificationTab = function(tab, event) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }
        
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded');
            return false;
        }
        
        var $ = jQuery;
        
        // Remove active class from all tabs
        $('#unread-tab, #read-tab').removeClass('active').attr('aria-selected', 'false');
        
        // Hide all tab panes
        $('#unread-notifications, #read-notifications').removeClass('show active');
        
        // Show selected tab and pane
        if (tab === 'unread') {
            $('#unread-tab').addClass('active').attr('aria-selected', 'true');
            $('#unread-notifications').addClass('show active');
        } else if (tab === 'read') {
            $('#read-tab').addClass('active').attr('aria-selected', 'true');
            $('#read-notifications').addClass('show active');
        }
        
        // Keep dropdown open
        $('#notificationDropdown .dropdown-menu').addClass('show');
        
        return false;
    };
    
    // Make functions globally accessible
    window.viewNotification = function(notificationId) {
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded');
            return;
        }
        
        var $ = jQuery;
        
        // Close dropdown first
        $('#notificationDropdown .dropdown-menu').removeClass('show');
        
        // Show loading in modal
        $('#viewNotificationModalBody').html('<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');
        $('#viewNotificationModal').modal('show');
        
        // Load notification details
        $.ajax({
            url: '{{ route("notifications.show", ":id") }}'.replace(':id', notificationId),
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                var content = '';
                if (typeof response === 'object' && response.notification) {
                    var n = response.notification;
                    var typeClass = {
                        'info': 'text-info',
                        'success': 'text-success',
                        'warning': 'text-warning',
                        'urgent': 'text-danger'
                    }[n.type] || 'text-secondary';
                    
                    var typeBadge = {
                        'info': 'badge-info',
                        'success': 'badge-success',
                        'warning': 'badge-warning',
                        'urgent': 'badge-danger'
                    }[n.type] || 'badge-secondary';
                    
                    content = '<div class="p-4">';
                    content += '<div class="d-flex align-items-center mb-3">';
                    content += '<i class="fas fa-bell ' + typeClass + ' fa-2x mr-3"></i>';
                    content += '<div class="flex-grow-1">';
                    content += '<h4 class="mb-1">' + n.title + '</h4>';
                    content += '<span class="badge ' + typeBadge + '">' + n.type.toUpperCase() + '</span>';
                    if (n.type === 'urgent') {
                        content += ' <span class="badge badge-danger">URGENT</span>';
                    }
                    content += '</div>';
                    content += '</div>';
                    content += '<hr>';
                    content += '<div class="mb-3">';
                    content += '<p class="mb-0" style="white-space: pre-wrap; line-height: 1.6;">' + (n.message || '') + '</p>';
                    content += '</div>';
                    content += '<hr>';
                    content += '<div class="d-flex justify-content-between align-items-center">';
                    content += '<small class="text-muted">';
                    content += '<i class="fas fa-clock"></i> ' + window.formatDate(n.created_at);
                    if (n.expires_at) {
                        content += ' | <i class="fas fa-calendar-times"></i> Expires: ' + window.formatDate(n.expires_at);
                    }
                    content += '</small>';
                    content += '</div>';
                    content += '</div>';
                } else if (typeof response === 'object' && response.html) {
                    content = response.html;
                } else {
                    content = response;
                }
                
                $('#viewNotificationModalBody').html(content);
                
                // Mark as read after showing modal (so it moves to read tab)
                window.markNotificationAsRead(notificationId);
            },
            error: function(xhr) {
                console.error('Error loading notification:', xhr);
                $('#viewNotificationModalBody').html('<div class="alert alert-danger">Error loading notification. Please try again.</div>');
                // Still mark as read even if loading fails
                window.markNotificationAsRead(notificationId);
            }
        });
    };
    
    window.markNotificationAsRead = function(notificationId, event) {
        if (event) {
            event.stopPropagation();
        }
        
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded');
            return;
        }
        
        var $ = jQuery;
        
        // Optimistically update UI - move notification from unread to read immediately
        var $notificationItem = $('#unreadNotificationList').find('a[onclick*="' + notificationId + '"]').closest('a');
        if ($notificationItem.length > 0) {
            // Get notification data from the item
            var notificationTitle = $notificationItem.find('.font-weight-bold').text().trim();
            var notificationMessage = $notificationItem.find('.text-sm').text().trim();
            var notificationType = 'info'; // Default, can be extracted from classes if needed
            var notificationCreatedAt = $notificationItem.find('small').text().trim();
            
            // Remove from unread list
            $notificationItem.remove();
            
            // Add to read list
            var readHtml = window.buildNotificationItem({
                id: notificationId,
                title: notificationTitle,
                message: notificationMessage,
                type: notificationType,
                created_at: notificationCreatedAt
            }, true);
            
            var $readList = $('#readNotificationList');
            if ($readList.find('.dropdown-item').length === 1 && $readList.find('.dropdown-item').hasClass('text-center')) {
                // Replace "No read notifications" message
                $readList.html(readHtml);
            } else {
                // Prepend to read list
                $readList.prepend(readHtml);
            }
            
            // Update counts
            var unreadCount = $('#unreadNotificationList .dropdown-item:not(.text-center)').length;
            var readCount = $('#readNotificationList .dropdown-item:not(.text-center)').length;
            
            $('#notificationBadge').text(unreadCount);
            $('#unreadCount').text(unreadCount);
            $('#readCount').text(readCount);
            
            if (unreadCount === 0) {
                $('#unreadNotificationList').html('<div class="dropdown-item text-center text-muted py-3"><i class="fas fa-bell-slash mr-2"></i> No unread notifications</div>');
                $('#notificationBadge').hide();
                $('#markAllReadBtn').hide();
            }
        }
        
        // Mark as read on server
        $.ajax({
            url: '{{ route("notifications.read", ":id") }}'.replace(':id', notificationId),
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                // Reload notifications to ensure sync with server
                if (typeof window.loadAllNotifications === 'function') {
                    window.loadAllNotifications();
                }
            },
            error: function(xhr) {
                console.error('Error marking as read:', xhr);
                // Reload on error to sync state
                if (typeof window.loadAllNotifications === 'function') {
                    window.loadAllNotifications();
                }
            }
        });
    };
    
    window.markAllNotificationsAsRead = function() {
        if (!confirm('Mark all notifications as read?')) {
            return;
        }
        
        if (typeof jQuery === 'undefined') {
            console.error('jQuery not loaded');
            return;
        }
        
        var $ = jQuery;
        
        // Optimistically update UI
        var $unreadList = $('#unreadNotificationList');
        var $readList = $('#readNotificationList');
        var unreadItems = $unreadList.find('.dropdown-item:not(.text-center)');
        
        if (unreadItems.length > 0) {
            // Move all unread items to read list
            unreadItems.each(function() {
                var $item = $(this);
                var notificationId = $item.attr('onclick') ? $item.attr('onclick').match(/\d+/)[0] : null;
                if (notificationId) {
                    var notificationTitle = $item.find('.font-weight-bold').text().trim().replace(/\s*URGENT\s*/g, '').trim();
                    var notificationMessage = $item.find('.text-sm').text().trim();
                    var iconClass = $item.find('i.fas.fa-bell').attr('class') || '';
                    var notificationType = 'info';
                    if (iconClass.includes('text-danger')) notificationType = 'urgent';
                    else if (iconClass.includes('text-warning')) notificationType = 'warning';
                    else if (iconClass.includes('text-success')) notificationType = 'success';
                    else if (iconClass.includes('text-info')) notificationType = 'info';
                    var notificationCreatedAt = $item.find('small').text().trim();
                    
                    var readHtml = window.buildNotificationItem({
                        id: notificationId,
                        title: notificationTitle,
                        message: notificationMessage,
                        type: notificationType,
                        created_at: notificationCreatedAt
                    }, true);
                    
                    $readList.prepend(readHtml);
                }
            });
            
            // Clear unread list
            $unreadList.html('<div class="dropdown-item text-center text-muted py-3"><i class="fas fa-bell-slash mr-2"></i> No unread notifications</div>');
            
            // Update counts
            var readCount = $('#readNotificationList .dropdown-item:not(.text-center)').length;
            $('#notificationBadge').text(0).hide();
            $('#unreadCount').text(0);
            $('#readCount').text(readCount);
            $('#markAllReadBtn').hide();
        }
        
        // Mark all as read on server
        $.ajax({
            url: '{{ route("notifications.read-all") }}',
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function() {
                // Reload to ensure sync
                if (typeof window.loadAllNotifications === 'function') {
                    window.loadAllNotifications();
                }
            },
            error: function(xhr) {
                console.error('Error marking all as read:', xhr);
                alert('Error marking all as read.');
                // Reload on error to sync state
                if (typeof window.loadAllNotifications === 'function') {
                    window.loadAllNotifications();
                }
            }
        });
    };
    
    function initWhenReady() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initWhenReady, 100);
            return;
        }
        
        var $ = jQuery;
        
        $(document).ready(function() {
            // Load notifications for navbar dropdown
            loadNotifications();
            
            // Check for urgent notifications on page load
            checkUrgentNotifications();
            
            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);
            
            // Prevent dropdown from closing when clicking inside
            $('#notificationMenu').on('click', function(e) {
                e.stopPropagation();
            });
            
            // Prevent tab clicks from closing dropdown
            $('#unread-tab, #read-tab').on('click', function(e) {
                e.stopPropagation();
            });
        });
        
        function loadNotifications() {
            loadAllNotifications();
        }
        
        function loadAllNotifications() {
            $.ajax({
                url: '{{ route("notifications.my") }}',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        updateNotificationUI(response.unread || [], response.read || []);
                    }
                },
                error: function(xhr) {
                    // Fallback: try to get from urgent endpoint
                    $.ajax({
                        url: '{{ route("notifications.urgent") }}',
                        type: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response2) {
                            if (response2.success) {
                                var allNotifications = response2.notifications || [];
                                var unreadNotifications = allNotifications.filter(function(n) {
                                    return !n.read_at;
                                });
                                updateNotificationUI(unreadNotifications, []);
                            }
                        }
                    });
                }
            });
        }
        
        // Make updateNotificationUI globally accessible
        window.updateNotificationUI = function(unreadNotifications, readNotifications) {
            if (typeof jQuery === 'undefined') {
                console.error('jQuery not loaded');
                return;
            }
            
            var $ = jQuery;
            
            // Update badge
            var unreadCount = unreadNotifications.length;
            $('#notificationBadge').text(unreadCount);
            $('#unreadCount').text(unreadCount);
            $('#readCount').text(readNotifications.length);
            
            if (unreadCount > 0) {
                $('#notificationBadge').show();
                $('#markAllReadBtn').show();
            } else {
                $('#notificationBadge').hide();
                $('#markAllReadBtn').hide();
            }
            
            // Update unread list
            var unreadHtml = '';
            if (unreadNotifications.length > 0) {
                unreadNotifications.slice(0, 10).forEach(function(notification) {
                    unreadHtml += window.buildNotificationItem(notification, false);
                });
            } else {
                unreadHtml = '<div class="dropdown-item text-center text-muted py-3"><i class="fas fa-bell-slash mr-2"></i> No unread notifications</div>';
            }
            $('#unreadNotificationList').html(unreadHtml);
            
            // Update read list
            var readHtml = '';
            if (readNotifications.length > 0) {
                readNotifications.slice(0, 10).forEach(function(notification) {
                    readHtml += window.buildNotificationItem(notification, true);
                });
            } else {
                readHtml = '<div class="dropdown-item text-center text-muted py-3"><i class="fas fa-bell-slash mr-2"></i> No read notifications</div>';
            }
            $('#readNotificationList').html(readHtml);
        };
        
        // Make buildNotificationItem globally accessible
        window.buildNotificationItem = function(notification, isRead) {
            var typeClass = {
                'info': 'text-info',
                'success': 'text-success',
                'warning': 'text-warning',
                'urgent': 'text-danger'
            }[notification.type] || 'text-secondary';
            
            var itemClass = isRead ? 'dropdown-item text-muted' : 'dropdown-item';
            var html = '<a href="#" class="' + itemClass + '" onclick="window.viewNotification(' + notification.id + '); return false;">';
            html += '<div class="d-flex align-items-start">';
            html += '<i class="fas fa-bell ' + typeClass + ' mr-2 mt-1"></i>';
            html += '<div class="flex-grow-1">';
            html += '<div class="font-weight-bold">' + notification.title;
            if (notification.type === 'urgent') {
                html += ' <span class="badge badge-danger badge-sm">URGENT</span>';
            }
            html += '</div>';
            html += '<div class="text-sm text-muted">' + (notification.message || '').substring(0, 50) + (notification.message && notification.message.length > 50 ? '...' : '') + '</div>';
            html += '<small class="text-muted">' + window.formatDate(notification.created_at) + '</small>';
            html += '</div>';
            if (!isRead) {
                html += '<button type="button" class="btn btn-sm btn-link p-0 ml-2" onclick="window.markNotificationAsRead(' + notification.id + ', event); return false;" title="Mark as read">';
                html += '<i class="fas fa-check text-success"></i>';
                html += '</button>';
            }
            html += '</div>';
            html += '</a>';
            return html;
        };
        
        
        function checkUrgentNotifications() {
            $.ajax({
                url: '{{ route("notifications.urgent") }}',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success && response.urgent && response.urgent.length > 0) {
                        showUrgentNotifications(response.urgent);
                    }
                },
                error: function(xhr) {
                    console.error('Error checking urgent notifications:', xhr);
                }
            });
        }
        
        function showUrgentNotifications(notifications) {
            var modalBody = $('#urgentNotificationsBody');
            var html = '<div class="list-group">';
            
            notifications.forEach(function(notification) {
                html += '<div class="list-group-item border-danger mb-2">';
                html += '<h6 class="mb-2"><i class="fas fa-exclamation-triangle text-danger"></i> ' + notification.title + '</h6>';
                html += '<p class="mb-2">' + notification.message + '</p>';
                html += '<small class="text-muted">' + formatDate(notification.created_at) + '</small>';
                html += '</div>';
            });
            
            html += '</div>';
            modalBody.html(html);
            
            $('#urgentNotificationsModal').modal('show');
            
            // Mark as read when modal is shown
            notifications.forEach(function(notification) {
                markNotificationAsReadFromPopup(notification.id);
            });
        }
        
        function markNotificationAsReadFromPopup(notificationId) {
            $.ajax({
                url: '{{ route("notifications.read", ":id") }}'.replace(':id', notificationId),
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function() {
                    loadAllNotifications();
                }
            });
        }
        
        function formatDate(dateString) {
            var date = new Date(dateString);
            var now = new Date();
            var diff = Math.floor((now - date) / 1000);
            
            if (diff < 60) return 'Just now';
            if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
            if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
            if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
            
            return date.toLocaleDateString();
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWhenReady);
    } else {
        initWhenReady();
    }
})();
</script>
    
    {{-- Delete Confirmation Modal (Global) --}}
    @include('partials.delete-confirm-modal')
    
    <!-- Urgent Notifications Popup Modal -->
    <div class="modal fade" id="urgentNotificationsModal" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content border-danger">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-exclamation-triangle"></i> Urgent Notifications
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="urgentNotificationsBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-danger" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- View Notification Modal -->
    <div class="modal fade" id="viewNotificationModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-bell"></i> Notification Details
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="viewNotificationModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-info" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 
