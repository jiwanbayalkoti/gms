@extends('layouts.app')

@section('title', 'Staff')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Staff</h2>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#staffModal" data-action="create">
                    <i class="fas fa-plus"></i> Add New Staff
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

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('staff.index') }}" id="staffFilterForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">All Status</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label for="search">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Name, Email, Phone...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-secondary btn-block" onclick="resetStaffFilters()">
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

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="staffTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Staff Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="staffTableBody">
                                @include('staff._table-body')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Staff Modal -->
<div class="modal fade" id="staffModal" tabindex="-1" role="dialog" aria-labelledby="staffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="staffForm" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="staffModalLabel">Add New Staff</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="staffModalBody">
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

<!-- View Staff Modal -->
<div class="modal fade" id="viewStaffModal" tabindex="-1" role="dialog" aria-labelledby="viewStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewStaffModalLabel">Staff Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewStaffModalBody">
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

@include('partials.delete-confirm-modal')
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle Add/Edit Modal
    $('#staffModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var action = button.data('action');
        var staffId = button.data('staff-id');
        var modal = $(this);
        var modalBody = modal.find('#staffModalBody');
        
        if (action === 'create') {
            modal.find('#staffModalLabel').text('Add New Staff');
        } else {
            modal.find('#staffModalLabel').text('Edit Staff');
        }
        
        var url = action === 'create' 
            ? '{{ route("staff.create") }}' 
            : '{{ route("staff.edit", ":id") }}'.replace(':id', staffId);
        
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
                    ? '{{ route("staff.store") }}' 
                    : '{{ route("staff.update", ":id") }}'.replace(':id', staffId);
                
                $('#staffForm').attr('action', formAction);
                
                if (action === 'edit') {
                    $('#staffForm').find('input[name="_method"]').remove();
                    $('#staffForm').append('<input type="hidden" name="_method" value="PUT">');
                }
                
                if (!$('#staffForm').find('input[name="_token"]').length) {
                    $('#staffForm').prepend('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
                }
            },
            error: function(xhr) {
                console.error('Error loading form:', xhr);
                modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
            }
        });
    });
    
    // Handle form submission
    $(document).on('submit', '#staffForm', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = new FormData(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalBtnText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
        
        form.find('.is-invalid').removeClass('is-invalid');
        form.find('.invalid-feedback').remove();
        form.find('.alert').remove();
        
        var method = form.find('input[name="_method"]').val();
        if (method) {
            formData.append('_method', method);
        }
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#staffModal').modal('hide');
                var message = (typeof response === 'object' && response.message) ? response.message : 'Staff saved successfully.';
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
    $('#viewStaffModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var staffId = button.data('staff-id');
        var modal = $(this);
        var modalBody = modal.find('#viewStaffModalBody');
        
        modalBody.html('<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        $.ajax({
            url: '{{ route("staff.show", ":id") }}'.replace(':id', staffId),
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
                console.error('Error loading staff:', xhr);
                modalBody.html('<div class="alert alert-danger">Error loading staff details. Please try again.</div>');
            }
        });
    });
    
    function showAlert(type, message) {
        // Use JavaScript alert box for all messages
        alert(message);
    }
});

// Auto-filter for staff
(function() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    
    var $ = jQuery;
    var filterTimeout;
    
    // Auto-filter function
    window.applyStaffFilters = function() {
        var formData = {
            status: $('#status').val(),
            search: $('#search').val(),
        };
        
        // Show loading
        var $tableBody = $('#staffTableBody');
        $tableBody.html('<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        $.ajax({
            url: '{{ route("staff.index") }}',
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
        $('#status').on('change', function() {
            clearTimeout(filterTimeout);
            window.applyStaffFilters();
        });
        
        // Auto-filter on search input with debounce (500ms delay)
        $('#search').on('input', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                window.applyStaffFilters();
            }, 500);
        });
        
        // Reset filters
        window.resetStaffFilters = function() {
            $('#status').val('');
            $('#search').val('');
            window.history.pushState({}, '', '{{ route("staff.index") }}');
            window.applyStaffFilters();
        };
    });
})();
</script>
@endpush

