@extends('layouts.app')

@section('title', 'Classes')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Gym Classes</h2>
                @if(!Auth::user()->isMember())
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#classModal" data-action="create">
                        <i class="fas fa-plus"></i> Add New Class
                    </button>
                @endif
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
                    <form method="GET" action="{{ route('classes.index') }}" id="classesFilterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="trainer_id">Trainer</label>
                                    <select class="form-control" id="trainer_id" name="trainer_id">
                                        <option value="">All Trainers</option>
                                        @foreach($trainers ?? [] as $trainer)
                                            <option value="{{ $trainer->id }}" {{ request('trainer_id') == $trainer->id ? 'selected' : '' }}>
                                                {{ $trainer->name }}
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
                                        <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        <option value="Completed" {{ request('status') === 'Completed' ? 'selected' : '' }}>Completed</option>
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
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-secondary btn-block" onclick="resetClassesFilters()">
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
                        <table class="table table-striped table-hover" id="classesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Trainer</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Location</th>
                                    <th>Capacity</th>
                                    <th>Bookings</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="classesTableBody">
                                @include('classes._table-body')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Class Modal -->
<div class="modal fade" id="classModal" tabindex="-1" role="dialog" aria-labelledby="classModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="classForm" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="classModalLabel">Add New Class</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="classModalBody">
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

<!-- View Class Modal -->
<div class="modal fade" id="viewClassModal" tabindex="-1" role="dialog" aria-labelledby="viewClassModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewClassModalLabel">Class Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewClassModalBody">
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
            $('#classModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var action = button.data('action');
                var classId = button.data('class-id');
                var modal = $(this);
                var modalBody = modal.find('#classModalBody');
                
                if (action === 'create') {
                    modal.find('#classModalLabel').text('Add New Class');
                } else {
                    modal.find('#classModalLabel').text('Edit Class');
                }
                
                var url = action === 'create' 
                    ? '{{ route("classes.create") }}' 
                    : '{{ route("classes.edit", ":id") }}'.replace(':id', classId);
                
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
                            ? '{{ route("classes.store") }}' 
                            : '{{ route("classes.update", ":id") }}'.replace(':id', classId);
                        
                        $('#classForm').attr('action', formAction);
                        
                        if (action === 'edit') {
                            $('#classForm').find('input[name="_method"]').remove();
                            $('#classForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        
                        if (!$('#classForm').find('input[name="_token"]').length) {
                            $('#classForm').prepend('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading form:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                    }
                });
            });
            
            // Handle form submission
            $(document).on('submit', '#classForm', function(e) {
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
                        $('#classModal').modal('hide');
                        var message = (typeof response === 'object' && response.message) ? response.message : 'Class saved successfully.';
                        showAlert('success', message);
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
            $('#viewClassModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var classId = button.data('class-id');
                var modal = $(this);
                var modalBody = modal.find('#viewClassModalBody');
                
                modalBody.html('<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.ajax({
                    url: '{{ route("classes.show", ":id") }}'.replace(':id', classId),
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
                        console.error('Error loading class:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading class details. Please try again.</div>');
                    }
                });
            });
            
            // Delete is now handled by delete-confirm.js
            
            // Handle Book Class button for members
            $(document).on('click', '.book-class-btn', function() {
                var button = $(this);
                var classId = button.data('class-id');
                var className = button.data('class-name');
                var originalHtml = button.html();
                
                if (!confirm('Are you sure you want to book "' + className + '"?')) {
                    return;
                }
                
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Booking...');
                
                $.ajax({
                    url: '{{ route("bookings.store") }}',
                    type: 'POST',
                    data: {
                        class_id: classId,
                        _token: '{{ csrf_token() }}'
                    },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        alert('Class booked successfully! Your booking is pending approval.');
                        // Update the button to show "Booked" status
                        button.replaceWith('<span class="badge badge-info">Booked</span>');
                        // Reload page after 1 second to update booking count
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        button.prop('disabled', false).html(originalHtml);
                        
                        var errorMessage = 'Failed to book class. Please try again.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            var firstError = Object.values(errors)[0];
                            if (firstError && firstError.length > 0) {
                                errorMessage = firstError[0];
                            }
                        }
                        
                        alert(errorMessage);
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

// Auto-filter for classes (admin only)
@if(!Auth::user()->isMember())
(function() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    
    var $ = jQuery;
    var filterTimeout;
    
    // Auto-filter function
    window.applyClassesFilters = function() {
        var formData = {
            trainer_id: $('#trainer_id').val(),
            status: $('#status').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
        };
        
        // Show loading
        var $tableBody = $('#classesTableBody');
        $tableBody.html('<tr><td colspan="10" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        $.ajax({
            url: '{{ route("classes.index") }}',
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
        $('#trainer_id, #status').on('change', function() {
            clearTimeout(filterTimeout);
            window.applyClassesFilters();
        });
        
        // Auto-filter on date change with debounce (500ms delay)
        $('#start_date, #end_date').on('change', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                window.applyClassesFilters();
            }, 500);
        });
        
        // Reset filters
        window.resetClassesFilters = function() {
            $('#trainer_id').val('');
            $('#status').val('');
            $('#start_date').val('');
            $('#end_date').val('');
            window.history.pushState({}, '', '{{ route("classes.index") }}');
            window.applyClassesFilters();
        };
    });
})();
@endif
</script>
@endpush
