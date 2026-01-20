@extends('layouts.app')

@section('title', 'Gyms Management')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Gyms Management</h2>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#gymModal" data-action="create">
                    <i class="fas fa-plus"></i> Add New Gym
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
                        <table class="table table-striped table-hover" id="gymsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Logo</th>
                                    <th>Name</th>
                                    <th>Address</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Users</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="gymsTableBody">
                                @include('gyms._gyms-list')
                                    <tr id="gym-row-{{ $gym->id }}">
                                        <td>{{ $gym->id }}</td>
                                        <td>
                                            @php
                                                $logoUrl = getImageUrl($gym->logo);
                                            @endphp
                                            @if($logoUrl)
                                                <img src="{{ $logoUrl }}" alt="{{ $gym->name }}" class="img-circle" width="40" height="40" style="object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; display: none;">
                                                    {{ substr($gym->name, 0, 1) }}
                                                </div>
                                            @else
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    {{ substr($gym->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </td>
                                        <td><strong>{{ $gym->name }}</strong></td>
                                        <td>{{ $gym->address ?? 'N/A' }}</td>
                                        <td>{{ $gym->email ?? 'N/A' }}</td>
                                        <td>{{ $gym->phone ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $gym->users_count ?? 0 }} Users</span>
                                            <small class="text-muted d-block">{{ $gym->members_count ?? 0 }} Members</small>
                                            <small class="text-muted d-block">{{ $gym->trainers_count ?? 0 }} Trainers</small>
                                        </td>
                                        <td>
                                            @if($gym->status === 'active')
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewGymModal" data-gym-id="{{ $gym->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#gymModal" data-action="edit" data-gym-id="{{ $gym->id }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-success" title="Create Admin" data-toggle="modal" data-target="#createAdminModal" data-gym-id="{{ $gym->id }}" data-gym-name="{{ $gym->name }}">
                                                    <i class="fas fa-user-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                                                    data-delete-url="{{ route('gyms.destroy', $gym->id) }}"
                                                    data-delete-name="{{ $gym->name }}"
                                                    data-delete-type="Gym"
                                                    data-delete-row-id="gym-row-{{ $gym->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No gyms found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(isset($gyms) && $gyms->hasPages())
                        <div class="mt-3">
                            {{ $gyms->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Gym Modal -->
<div class="modal fade" id="gymModal" tabindex="-1" role="dialog" aria-labelledby="gymModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="gymForm" method="POST" enctype="multipart/form-data">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="gymModalLabel">Add New Gym</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="gymModalBody">
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

<!-- View Gym Modal -->
<div class="modal fade" id="viewGymModal" tabindex="-1" role="dialog" aria-labelledby="viewGymModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewGymModalLabel">Gym Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewGymModalBody">
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

<!-- Create Gym Admin Modal -->
<div class="modal fade" id="createAdminModal" tabindex="-1" role="dialog" aria-labelledby="createAdminModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="createAdminModalLabel">Create Gym Admin</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="createAdminModalBody">
                <!-- Form will be loaded here via AJAX -->
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
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
            $('#gymModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var action = button.data('action');
                var gymId = button.data('gym-id');
                var modal = $(this);
                var modalBody = modal.find('#gymModalBody');
                
                if (action === 'create') {
                    modal.find('#gymModalLabel').text('Add New Gym');
                } else {
                    modal.find('#gymModalLabel').text('Edit Gym');
                }
                
                var url = action === 'create' 
                    ? '{{ route("gyms.create") }}' 
                    : '{{ route("gyms.edit", ":id") }}'.replace(':id', gymId);
                
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
                            ? '{{ route("gyms.store") }}' 
                            : '{{ route("gyms.update", ":id") }}'.replace(':id', gymId);
                        
                        $('#gymForm').attr('action', formAction);
                        
                        if (action === 'edit') {
                            // Remove existing _method if any
                            $('#gymForm').find('input[name="_method"]').remove();
                            // Add PUT method
                            var methodInput = $('<input>').attr({
                                type: 'hidden',
                                name: '_method',
                                value: 'PUT'
                            });
                            $('#gymForm').append(methodInput);
                        } else {
                            // Remove _method for create
                            $('#gymForm').find('input[name="_method"]').remove();
                        }
                        
                        if (!$('#gymForm').find('input[name="_token"]').length) {
                            $('#gymForm').prepend('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading form:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                    }
                });
            });
            
            // Handle form submission
            $(document).on('submit', '#gymForm', function(e) {
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
                var ajaxType = 'POST'; // Always use POST for FormData, Laravel handles method spoofing
                
                // Ensure _method is in FormData if it exists
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
                        $('#gymModal').modal('hide');
                        var message = (typeof response === 'object' && response.message) ? response.message : 'Gym saved successfully.';
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
            $('#viewGymModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var gymId = button.data('gym-id');
                var modal = $(this);
                var modalBody = modal.find('#viewGymModalBody');
                
                modalBody.html('<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.ajax({
                    url: '{{ route("gyms.show", ":id") }}'.replace(':id', gymId),
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
                        console.error('Error loading gym:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading gym details. Please try again.</div>');
                    }
                });
            });
            
            // Handle Create Admin Modal
            $('#createAdminModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var gymId = button.data('gym-id');
                var gymName = button.data('gym-name');
                var modal = $(this);
                var modalBody = modal.find('#createAdminModalBody');
                
                modal.find('#createAdminModalLabel').text('Create Admin for: ' + gymName);
                
                modalBody.html('<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.ajax({
                    url: '{{ route("gyms.create-admin", ":id") }}'.replace(':id', gymId),
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    success: function(response) {
                        modalBody.html(response);
                        
                        // Add CSRF token if not present
                        if (!$('#gymAdminForm').find('input[name="_token"]').length) {
                            $('#gymAdminForm').prepend('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading form:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                    }
                });
            });
            
            // Handle Admin Form Submission
            $(document).on('submit', '#gymAdminForm', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var formData = new FormData(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalBtnText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
                
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                form.find('.alert-danger').remove();
                
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
                        $('#createAdminModal').modal('hide');
                        var message = (typeof response === 'object' && response.message) ? response.message : 'Gym admin created successfully.';
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
            
            // Delete is now handled by delete-confirm.js
            
            function showAlert(type, message) {
                // Use JavaScript alert box for all messages
                alert(message);
            }
            
            // Delete is now handled by delete-confirm.js (global handler)
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWhenReady);
    } else {
        initWhenReady();
    }
})();

// Auto-filter for gyms
(function() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    
    var $ = jQuery;
    var filterTimeout;
    
    // Auto-filter function
    window.applyGymsFilters = function() {
        var formData = {
            is_active: $('#is_active').val(),
            search: $('#search').val(),
        };
        
        // Show loading
        var $tableBody = $('#gymsTableBody');
        $tableBody.html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        $.ajax({
            url: '{{ route("gyms.index") }}',
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
        $('#is_active').on('change', function() {
            clearTimeout(filterTimeout);
            window.applyGymsFilters();
        });
        
        // Auto-filter on search input with debounce (500ms delay)
        $('#search').on('input', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                window.applyGymsFilters();
            }, 500);
        });
        
        // Reset filters
        window.resetGymsFilters = function() {
            $('#is_active').val('');
            $('#search').val('');
            window.history.pushState({}, '', '{{ route("gyms.index") }}');
            window.applyGymsFilters();
        };
    });
})();
</script>
@endpush

