@extends('layouts.app')

@section('title', 'Notifications Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Notifications Management</h2>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#notificationModal" data-action="create">
                    <i class="fas fa-plus"></i> Create Notification
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
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Target</th>
                                    <th>Status</th>
                                    <th>Published</th>
                                    <th>Expires</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="notificationsTableBody">
                                @include('notifications._table-body')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Notification Modal -->
<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="notificationForm" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="notificationModalLabel">Create Notification</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="notificationModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </form>
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

<!-- Publish Notification Confirmation Modal -->
<div class="modal fade" id="publishNotificationModal" tabindex="-1" role="dialog" aria-labelledby="publishNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="publishNotificationModalLabel">
                    <i class="fas fa-check-circle mr-2"></i>Confirm Publish
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-bullhorn fa-4x text-success mb-3"></i>
                </div>
                <h5 class="text-center mb-3">Are you sure you want to publish this notification?</h5>
                <p class="text-center text-muted mb-0" id="publishNotificationMessage">
                    This notification will be visible to all users.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="publishNotificationButton">
                    <i class="fas fa-check mr-1"></i> Publish
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Unpublish Notification Confirmation Modal -->
<div class="modal fade" id="unpublishNotificationModal" tabindex="-1" role="dialog" aria-labelledby="unpublishNotificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="unpublishNotificationModalLabel">
                    <i class="fas fa-eye-slash mr-2"></i>Confirm Unpublish
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-ban fa-4x text-warning mb-3"></i>
                </div>
                <h5 class="text-center mb-3">Are you sure you want to unpublish this notification?</h5>
                <p class="text-center text-muted mb-0" id="unpublishNotificationMessage">
                    This notification will no longer be visible to users.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-warning" id="unpublishNotificationButton">
                    <i class="fas fa-eye-slash mr-1"></i> Unpublish
                </button>
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
            // Handle Add/Edit Modal
            $('#notificationModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var action = button.data('action');
                var notificationId = button.data('notification-id');
                var modal = $(this);
                var modalBody = modal.find('#notificationModalBody');
                
                if (action === 'create') {
                    modal.find('#notificationModalLabel').text('Create Notification');
                } else {
                    modal.find('#notificationModalLabel').text('Edit Notification');
                }
                
                var url = action === 'create' 
                    ? '{{ route("notifications.create") }}' 
                    : '{{ route("notifications.edit", ":id") }}'.replace(':id', notificationId);
                
                modalBody.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    success: function(response) {
                        var formHtml = response.trim();
                        if (formHtml.includes('<form')) {
                            var $temp = $('<div>').html(response);
                            formHtml = $temp.find('form').html() || response;
                        }
                        
                        modalBody.html(formHtml);
                        
                        var formAction = action === 'create' 
                            ? '{{ route("notifications.store") }}' 
                            : '{{ route("notifications.update", ":id") }}'.replace(':id', notificationId);
                        
                        $('#notificationForm').attr('action', formAction);
                        
                        // Remove any existing _method field first
                        $('#notificationForm').find('input[name="_method"]').remove();
                        
                        // Add _method only for edit action
                        if (action === 'edit') {
                            $('#notificationForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        
                        // Ensure CSRF token exists
                        if (!$('#notificationForm').find('input[name="_token"]').length) {
                            $('#notificationForm').prepend('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading form:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                    }
                });
            });
            
            // Handle form submission
            $(document).on('submit', '#notificationForm', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var formData = new FormData(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalBtnText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                form.find('.alert').remove();
                
                // Get method from form, but always use POST for AJAX
                var method = form.find('input[name="_method"]').val();
                var ajaxType = 'POST';
                
                // Only append _method if it exists (for PUT/PATCH/DELETE)
                if (method) {
                    formData.append('_method', method);
                } else {
                    // Ensure _method is not in FormData for create action
                    formData.delete('_method');
                }
                
                $.ajax({
                    url: form.attr('action'),
                    type: ajaxType,
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#notificationModal').modal('hide');
                        var message = (typeof response === 'object' && response.message) ? response.message : 'Notification saved successfully.';
                        alert(message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                        
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
                            
                            $.each(errors, function(field, messages) {
                                $.each(messages, function(i, message) {
                                    errorHtml += '<li>' + message + '</li>';
                                    var input = form.find('[name="' + field + '"]');
                                    input.addClass('is-invalid');
                                    input.after('<div class="invalid-feedback">' + message + '</div>');
                                });
                            });
                            
                            errorHtml += '</ul></div>';
                            form.prepend(errorHtml);
                        } else {
                            form.prepend('<div class="alert alert-danger">' + (xhr.responseJSON.message || 'An error occurred. Please try again.') + '</div>');
                        }
                    }
                });
            });
            
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
                    },
                    error: function(xhr) {
                        console.error('Error loading notification:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading notification details. Please try again.</div>');
                    }
                });
            });
            
            function showAlert(type, message) {
                // Use JavaScript alert box for all messages
                alert(message);
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWhenReady);
    } else {
        initWhenReady();
    }
})();

// Store notification context for publish/unpublish
var notificationContext = {
    id: null,
    publish: null
};

function togglePublish(id, publish) {
    notificationContext.id = id;
    notificationContext.publish = publish;
    
    if (publish) {
        $('#publishNotificationModal').modal('show');
    } else {
        $('#unpublishNotificationModal').modal('show');
    }
}

// Handle publish button click
$(document).on('click', '#publishNotificationButton', function() {
    var $btn = $(this);
    var originalHtml = $btn.html();
    
    // Disable button and show loading
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Publishing...');
    
    $.ajax({
        url: '{{ route("notifications.publish", ":id") }}'.replace(':id', notificationContext.id),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#publishNotificationModal').modal('hide');
            $btn.prop('disabled', false).html(originalHtml);
            
            alert('Notification published successfully.');
            location.reload();
        },
        error: function(xhr) {
            $btn.prop('disabled', false).html(originalHtml);
            alert('Error updating notification status. Please try again.');
        }
    });
});

// Handle unpublish button click
$(document).on('click', '#unpublishNotificationButton', function() {
    var $btn = $(this);
    var originalHtml = $btn.html();
    
    // Disable button and show loading
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Unpublishing...');
    
    $.ajax({
        url: '{{ route("notifications.unpublish", ":id") }}'.replace(':id', notificationContext.id),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#unpublishNotificationModal').modal('hide');
            $btn.prop('disabled', false).html(originalHtml);
            
            alert('Notification unpublished successfully.');
            location.reload();
        },
        error: function(xhr) {
            $btn.prop('disabled', false).html(originalHtml);
            alert('Error updating notification status. Please try again.');
        }
    });
});

// Reset context when modals are hidden
$('#publishNotificationModal, #unpublishNotificationModal').on('hidden.bs.modal', function() {
    notificationContext.id = null;
    notificationContext.publish = null;
});

// Auto-filter for notifications
(function() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    
    var $ = jQuery;
    var filterTimeout;
    
    // Auto-filter function
    window.applyNotificationsFilters = function() {
        var formData = {
            is_published: $('#is_published').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
        };
        
        // Show loading
        var $tableBody = $('#notificationsTableBody');
        $tableBody.html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        $.ajax({
            url: '{{ route("notifications.index") }}',
            type: 'GET',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                // Update URL
                var url = new URL(window.location.href);
                Object.keys(formData).forEach(function(key) {
                    if (formData[key]) {
                        url.searchParams.set(key, formData[key]);
                    } else {
                        url.searchParams.delete(key);
                    }
                });
                window.history.pushState({}, '', url);
                
                // Update table body
                if (response.success && response.html) {
                    $tableBody.html(response.html);
                } else {
                    window.location.reload();
                }
            },
            error: function() {
                window.location.reload();
            }
        });
    };
    
    $(document).ready(function() {
        // Auto-filter on select change (immediate)
        $('#is_published').on('change', function() {
            clearTimeout(filterTimeout);
            window.applyNotificationsFilters();
        });
        
        // Auto-filter on date change with debounce (500ms delay)
        $('#start_date, #end_date').on('change', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                window.applyNotificationsFilters();
            }, 500);
        });
        
        // Reset filters
        window.resetNotificationsFilters = function() {
            $('#is_published').val('');
            $('#start_date').val('');
            $('#end_date').val('');
            window.history.pushState({}, '', '{{ route("notifications.index") }}');
            window.applyNotificationsFilters();
        };
    });
})();
</script>
@endpush

