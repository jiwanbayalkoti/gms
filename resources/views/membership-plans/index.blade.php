@extends('layouts.app')

@section('title', 'Membership Plans')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Membership Plans</h2>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#planModal" data-action="create">
                    <i class="fas fa-plus"></i> Add New Plan
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
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('membership-plans.index') }}" id="membershipPlansFilterForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label for="is_active">Status</label>
                                    <select class="form-control" id="is_active" name="is_active">
                                        <option value="">All Status</option>
                                        <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label for="search">Search</label>
                                    <input type="text" class="form-control" id="search" name="search" value="{{ request('search') }}" placeholder="Name, Description...">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-secondary btn-block" onclick="resetMembershipPlansFilters()">
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

    <div class="row" id="membershipPlansList">
        @include('membership-plans._gyms-list')
    </div>
</div>

<!-- Add/Edit Plan Modal -->
<div class="modal fade" id="planModal" tabindex="-1" role="dialog" aria-labelledby="planModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="planForm" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="planModalLabel">Add New Membership Plan</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="planModalBody">
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

<!-- View Plan Modal -->
<div class="modal fade" id="viewPlanModal" tabindex="-1" role="dialog" aria-labelledby="viewPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewPlanModalLabel">Membership Plan Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewPlanModalBody">
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
            $('#planModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var action = button.data('action');
                var planId = button.data('plan-id');
                var modal = $(this);
                var modalBody = modal.find('#planModalBody');
                
                if (action === 'create') {
                    modal.find('#planModalLabel').text('Add New Membership Plan');
                } else {
                    modal.find('#planModalLabel').text('Edit Membership Plan');
                }
                
                var url = action === 'create' 
                    ? '{{ route("membership-plans.create") }}' 
                    : '{{ route("membership-plans.edit", ":id") }}'.replace(':id', planId);
                
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
                            ? '{{ route("membership-plans.store") }}' 
                            : '{{ route("membership-plans.update", ":id") }}'.replace(':id', planId);
                        
                        $('#planForm').attr('action', formAction);
                        
                        if (action === 'edit') {
                            $('#planForm').find('input[name="_method"]').remove();
                            $('#planForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        
                        if (!$('#planForm').find('input[name="_token"]').length) {
                            $('#planForm').prepend('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading form:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                    }
                });
            });
            
            // Handle form submission
            $(document).on('submit', '#planForm', function(e) {
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
                        $('#planModal').modal('hide');
                        var message = (typeof response === 'object' && response.message) ? response.message : 'Membership plan saved successfully.';
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
            $('#viewPlanModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var planId = button.data('plan-id');
                var modal = $(this);
                var modalBody = modal.find('#viewPlanModalBody');
                
                modalBody.html('<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.ajax({
                    url: '{{ route("membership-plans.show", ":id") }}'.replace(':id', planId),
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
                        console.error('Error loading plan:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading plan details. Please try again.</div>');
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

// Auto-filter for membership plans
(function() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    
    var $ = jQuery;
    var filterTimeout;
    
    // Auto-filter function
    window.applyMembershipPlansFilters = function() {
        var formData = {
            is_active: $('#is_active').val(),
            search: $('#search').val(),
        };
        
        // Show loading
        var $plansList = $('#membershipPlansList');
        $plansList.html('<div class="col-md-12 text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        $.ajax({
            url: '{{ route("membership-plans.index") }}',
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
                
                // Update plans list
                if (response.success && response.html) {
                    $plansList.html(response.html);
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
            window.applyMembershipPlansFilters();
        });
        
        // Auto-filter on search input with debounce (500ms delay)
        $('#search').on('input', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                window.applyMembershipPlansFilters();
            }, 500);
        });
        
        // Reset filters
        window.resetMembershipPlansFilters = function() {
            $('#is_active').val('');
            $('#search').val('');
            window.history.pushState({}, '', '{{ route("membership-plans.index") }}');
            window.applyMembershipPlansFilters();
        };
    });
})();
</script>
@endpush
