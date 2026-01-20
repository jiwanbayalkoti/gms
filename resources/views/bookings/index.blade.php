@extends('layouts.app')

@section('title', 'Bookings')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Bookings</h2>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#bookingModal" data-action="create">
                    <i class="fas fa-plus"></i> New Booking
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filters -->
    @if(!Auth::user()->isMember())
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('bookings.index') }}" id="bookingsFilterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="member_id">Member</label>
                                    <select class="form-control" id="member_id" name="member_id">
                                        <option value="">All Members</option>
                                        @foreach($members ?? [] as $member)
                                            <option value="{{ $member->id }}" {{ request('member_id') == $member->id ? 'selected' : '' }}>
                                                {{ $member->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="class_id">Class</label>
                                    <select class="form-control" id="class_id" name="class_id">
                                        <option value="">All Classes</option>
                                        @foreach($classes ?? [] as $class)
                                            <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                                                {{ $class->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">All Status</option>
                                        <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Confirmed" {{ request('status') === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                                        <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="Attended" {{ request('status') === 'Attended' ? 'selected' : '' }}>Attended</option>
                                        <option value="No-Show" {{ request('status') === 'No-Show' ? 'selected' : '' }}>No-Show</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-secondary btn-block" onclick="resetBookingsFilters()">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="bookingsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Member</th>
                                    <th>Class</th>
                                    <th>Trainer</th>
                                    <th>Date & Time</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="bookingsTableBody">
                                @include('bookings._table-body')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" role="dialog" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="bookingForm" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="bookingModalLabel">New Booking</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="bookingModalBody">
                    <!-- Form will be loaded here via AJAX -->
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

<!-- View Booking Modal -->
<div class="modal fade" id="viewBookingModal" tabindex="-1" role="dialog" aria-labelledby="viewBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewBookingModalLabel">Booking Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewBookingModalBody">
                <!-- Details will be loaded here via AJAX -->
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

<!-- Approve Booking Confirmation Modal -->
<div class="modal fade" id="approveBookingModal" tabindex="-1" role="dialog" aria-labelledby="approveBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveBookingModalLabel">
                    <i class="fas fa-check-circle mr-2"></i>Confirm Approval
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                </div>
                <h5 class="text-center mb-3">Are you sure you want to approve this booking?</h5>
                <p class="text-center text-muted mb-0" id="approveBookingMessage">
                    This booking will be confirmed and the member will be notified.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-success" id="approveBookingButton">
                    <i class="fas fa-check mr-1"></i> Approve
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Booking Confirmation Modal -->
<div class="modal fade" id="rejectBookingModal" tabindex="-1" role="dialog" aria-labelledby="rejectBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectBookingModalLabel">
                    <i class="fas fa-times-circle mr-2"></i>Confirm Rejection
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                </div>
                <h5 class="text-center mb-3">Are you sure you want to reject this booking?</h5>
                <p class="text-center text-muted mb-0" id="rejectBookingMessage">
                    This booking will be cancelled and the member will be notified.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="rejectBookingButton">
                    <i class="fas fa-times mr-1"></i> Reject
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
            $('#bookingModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var action = button.data('action');
                var bookingId = button.data('booking-id');
                var modal = $(this);
                var modalBody = modal.find('#bookingModalBody');
                
                if (action === 'create') {
                    modal.find('#bookingModalLabel').text('New Booking');
                } else {
                    modal.find('#bookingModalLabel').text('Edit Booking');
                }
                
                var url = action === 'create' 
                    ? '{{ route("bookings.create") }}' 
                    : '{{ route("bookings.edit", ":id") }}'.replace(':id', bookingId);
                
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
                            ? '{{ route("bookings.store") }}' 
                            : '{{ route("bookings.update", ":id") }}'.replace(':id', bookingId);
                        
                        $('#bookingForm').attr('action', formAction);
                        
                        if (action === 'edit') {
                            $('#bookingForm').find('input[name="_method"]').remove();
                            $('#bookingForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        
                        if (!$('#bookingForm').find('input[name="_token"]').length) {
                            $('#bookingForm').prepend('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading form:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                    }
                });
            });
            
            // Handle form submission
            $(document).on('submit', '#bookingForm', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var formData = new FormData(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalBtnText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                form.find('.alert').remove();
                
                // Get method from form - Laravel uses POST with _method spoofing
                var method = form.find('input[name="_method"]').val();
                var ajaxType = 'POST';
                
                if (method) {
                    formData.append('_method', method);
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
                        $('#bookingModal').modal('hide');
                        var message = (typeof response === 'object' && response.message) ? response.message : 'Booking saved successfully.';
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
            $('#viewBookingModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var bookingId = button.data('booking-id');
                var modal = $(this);
                var modalBody = modal.find('#viewBookingModalBody');
                
                modalBody.html('<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.ajax({
                    url: '{{ route("bookings.show", ":id") }}'.replace(':id', bookingId),
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
                        console.error('Error loading booking:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading booking details. Please try again.</div>');
                    }
                });
            });
            
            // Delete is now handled by delete-confirm.js
            
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

// Store booking context for approve/reject
var bookingContext = {
    id: null
};

// Approve booking
function approveBooking(bookingId) {
    bookingContext.id = bookingId;
    $('#approveBookingModal').modal('show');
}

// Reject booking
function rejectBooking(bookingId) {
    bookingContext.id = bookingId;
    $('#rejectBookingModal').modal('show');
}

// Handle approve button click
$(document).on('click', '#approveBookingButton', function() {
    var $btn = $(this);
    var originalHtml = $btn.html();
    
    // Disable button and show loading
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Approving...');
    
    $.ajax({
        url: '{{ route("bookings.approve", ":id") }}'.replace(':id', bookingContext.id),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#approveBookingModal').modal('hide');
            $btn.prop('disabled', false).html(originalHtml);
            
            if (response.success) {
                alert('Booking approved successfully.');
                location.reload();
            }
        },
        error: function(xhr) {
            $btn.prop('disabled', false).html(originalHtml);
            alert('Error approving booking. Please try again.');
            console.error(xhr);
        }
    });
});

// Handle reject button click
$(document).on('click', '#rejectBookingButton', function() {
    var $btn = $(this);
    var originalHtml = $btn.html();
    
    // Disable button and show loading
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Rejecting...');
    
    $.ajax({
        url: '{{ route("bookings.reject", ":id") }}'.replace(':id', bookingContext.id),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            $('#rejectBookingModal').modal('hide');
            $btn.prop('disabled', false).html(originalHtml);
            
            if (response.success) {
                alert('Booking rejected successfully.');
                location.reload();
            }
        },
        error: function(xhr) {
            $btn.prop('disabled', false).html(originalHtml);
            alert('Error rejecting booking. Please try again.');
            console.error(xhr);
        }
    });
});

// Reset context when modals are hidden
$('#approveBookingModal, #rejectBookingModal').on('hidden.bs.modal', function() {
    bookingContext.id = null;
});

// Auto-filter for bookings (admin only)
@if(!Auth::user()->isMember())
(function() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    
    var $ = jQuery;
    var filterTimeout;
    
    // Auto-filter function
    window.applyBookingsFilters = function() {
        var formData = {
            member_id: $('#member_id').val(),
            class_id: $('#class_id').val(),
            status: $('#status').val(),
            start_date: $('#start_date').val(),
        };
        
        // Show loading
        var $tableBody = $('#bookingsTableBody');
        $tableBody.html('<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        $.ajax({
            url: '{{ route("bookings.index") }}',
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
        $('#member_id, #class_id, #status').on('change', function() {
            clearTimeout(filterTimeout);
            window.applyBookingsFilters();
        });
        
        // Auto-filter on date change with debounce (500ms delay)
        $('#start_date').on('change', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                window.applyBookingsFilters();
            }, 500);
        });
        
        // Reset filters
        window.resetBookingsFilters = function() {
            $('#member_id').val('');
            $('#class_id').val('');
            $('#status').val('');
            $('#start_date').val('');
            window.history.pushState({}, '', '{{ route("bookings.index") }}');
            window.applyBookingsFilters();
        };
    });
})();
@endif
</script>
@endpush
