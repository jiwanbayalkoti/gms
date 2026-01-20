@extends('layouts.app')

@section('title', 'My Notifications')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">My Notifications</h2>
                <button type="button" class="btn btn-primary" onclick="markAllAsRead()">
                    <i class="fas fa-check-double"></i> Mark All as Read
                </button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            @forelse($notifications ?? [] as $notification)
                @php
                    $isRead = $notification->isReadBy(Auth::id());
                    $badgeClass = [
                        'info' => 'badge-info',
                        'success' => 'badge-success',
                        'warning' => 'badge-warning',
                        'urgent' => 'badge-danger',
                    ][$notification->type] ?? 'badge-secondary';
                @endphp
                <div class="card mb-3 {{ !$isRead ? 'border-left-primary' : '' }}" id="notification-card-{{ $notification->id }}">
                    <div class="card-header {{ !$isRead ? 'bg-light' : '' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">
                                    {{ $notification->title }}
                                    @if(!$isRead)
                                        <span class="badge badge-primary ml-2">New</span>
                                    @endif
                                    <span class="badge {{ $badgeClass }} ml-2">{{ ucfirst($notification->type) }}</span>
                                    @if($notification->isUrgent())
                                        <span class="badge badge-danger ml-2">URGENT</span>
                                    @endif
                                </h5>
                            </div>
                            <div>
                                @if(!$isRead)
                                    <button type="button" class="btn btn-sm btn-success" onclick="markAsRead({{ $notification->id }})">
                                        <i class="fas fa-check"></i> Mark as Read
                                    </button>
                                @endif
                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#viewNotificationModal" data-notification-id="{{ $notification->id }}">
                                    <i class="fas fa-eye"></i> View
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">{{ $notification->message }}</p>
                        <small class="text-muted">
                            <i class="fas fa-clock"></i> {{ $notification->created_at->format('M d, Y h:i A') }}
                            @if($notification->expires_at)
                                | <i class="fas fa-calendar-times"></i> Expires: {{ $notification->expires_at->format('M d, Y') }}
                            @endif
                        </small>
                    </div>
                </div>
            @empty
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No notifications found.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- View Notification Modal -->
<div class="modal fade" id="viewNotificationModal" tabindex="-1" role="dialog" aria-labelledby="viewNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewNotificationModalLabel">Notification Details</h5>
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
@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    
    function initWhenReady() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initWhenReady, 100);
            return;
        }
        
        var $ = jQuery;
        
        $(document).ready(function() {
            // Handle View Modal
            $('#viewNotificationModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var notificationId = button.data('notification-id');
                var modal = $(this);
                var modalBody = modal.find('#viewNotificationModalBody');
                
                modalBody.html('<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.ajax({
                    url: '{{ route("notifications.show", ":id") }}'.replace(':id', notificationId),
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (typeof response === 'object' && response.html) {
                            modalBody.html(response.html);
                        } else {
                            var $response = $('<div>').html(response);
                            var content = $response.find('.row').html() || $response.find('.container-fluid').html() || response;
                            modalBody.html(content);
                        }
                        
                        // Mark as read when viewed
                        markAsRead(notificationId);
                    },
                    error: function(xhr) {
                        console.error('Error loading notification:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading notification details. Please try again.</div>');
                    }
                });
            });
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWhenReady);
    } else {
        initWhenReady();
    }
})();

function markAsRead(notificationId) {
    $.ajax({
        url: '{{ route("notifications.read", ":id") }}'.replace(':id', notificationId),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#notification-card-' + notificationId).removeClass('border-left-primary');
            $('#notification-card-' + notificationId + ' .card-header').removeClass('bg-light');
            $('#notification-card-' + notificationId + ' .badge-primary').remove();
            $('#notification-card-' + notificationId + ' .btn-success').remove();
        },
        error: function(xhr) {
            console.error('Error marking as read:', xhr);
        }
    });
}

function markAllAsRead() {
    if (!confirm('Mark all notifications as read?')) {
        return;
    }
    
    $.ajax({
        url: '{{ route("notifications.read-all") }}',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            location.reload();
        },
        error: function(xhr) {
            alert('Error marking all as read.');
        }
    });
}
</script>
@endpush

