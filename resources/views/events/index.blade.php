@extends('layouts.app')

@section('title', 'Events')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Events</h2>
                @if(!Auth::user()->isMember())
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#eventModal" data-action="create">
                        <i class="fas fa-plus"></i> New Event
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
    @if(!Auth::user()->isMember() && !Auth::user()->isTrainer())
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('events.index') }}" id="eventsFilterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">All Status</option>
                                        <option value="Draft" {{ request('status') === 'Draft' ? 'selected' : '' }}>Draft</option>
                                        <option value="Published" {{ request('status') === 'Published' ? 'selected' : '' }}>Published</option>
                                        <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-secondary btn-block" onclick="resetEventsFilters()">
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

    <div class="row" id="eventsList">
        @include('events._events-list')
    </div>
</div>

<!-- Event Modal (Create/Edit) -->
<div class="modal fade" id="eventModal" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel">Create Event</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="eventForm" method="POST">
                @csrf
                <div class="modal-body" id="eventModalBody">
                    <!-- Form will be loaded here via AJAX -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Event
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Event Modal -->
<div class="modal fade" id="viewEventModal" tabindex="-1" role="dialog" aria-labelledby="viewEventModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewEventModalLabel">Event Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewEventModalBody">
                <!-- Event details will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    function initWhenReady() {
        $(document).ready(function() {
            // Handle Create/Edit Modal
            $('#eventModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var action = button.data('action');
                var eventId = button.data('event-id');
                var modal = $(this);
                var modalTitle = modal.find('#eventModalLabel');
                var modalBody = modal.find('#eventModalBody');
                var form = modal.find('#eventForm');
                
                if (action === 'create') {
                    modalTitle.text('Create Event');
                    form.attr('action', '{{ route("events.store") }}');
                    form.find('input[name="_method"]').remove();
                    
                    modalBody.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
                    
                    $.ajax({
                        url: '{{ route("events.create") }}',
                        type: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            modalBody.html(response);
                        },
                        error: function(xhr) {
                            console.error('Error loading form:', xhr);
                            modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                        }
                    });
                } else if (action === 'edit') {
                    modalTitle.text('Edit Event');
                    form.attr('action', '{{ route("events.update", ":id") }}'.replace(':id', eventId));
                    if (!form.find('input[name="_method"]').length) {
                        form.append('<input type="hidden" name="_method" value="PUT">');
                    }
                    
                    modalBody.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
                    
                    $.ajax({
                        url: '{{ route("events.edit", ":id") }}'.replace(':id', eventId),
                        type: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            modalBody.html(response);
                        },
                        error: function(xhr) {
                            console.error('Error loading form:', xhr);
                            modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                        }
                    });
                }
            });
            
            // Handle Form Submission
            $('#eventForm').on('submit', function(e) {
                e.preventDefault();
                var form = $(this);
                var formData = new FormData(form[0]);
                var url = form.attr('action');
                var method = form.find('input[name="_method"]').val() || 'POST';
                
                $.ajax({
                    url: url,
                    type: method === 'PUT' ? 'POST' : 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#eventModal').modal('hide');
                            location.reload();
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
                        if (errors) {
                            var errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
                            $.each(errors, function(key, value) {
                                errorHtml += '<li>' + value[0] + '</li>';
                            });
                            errorHtml += '</ul></div>';
                            form.prepend(errorHtml);
                        } else {
                            form.prepend('<div class="alert alert-danger">' + (xhr.responseJSON?.message || 'An error occurred. Please try again.') + '</div>');
                        }
                    }
                });
            });
            
            // Handle View Modal
            $('#viewEventModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var eventId = button.data('event-id');
                var modal = $(this);
                var modalBody = modal.find('#viewEventModalBody');
                
                modalBody.html('<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.ajax({
                    url: '{{ route("events.show", ":id") }}'.replace(':id', eventId),
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
                        console.error('Error loading event:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading event details. Please try again.</div>');
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

// Publish event
function publishEvent(eventId) {
    if (!confirm('Are you sure you want to publish this event? All users will be notified.')) {
        return;
    }
    
    $.ajax({
        url: '{{ route("events.publish", ":id") }}'.replace(':id', eventId),
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            }
        },
        error: function(xhr) {
            alert('Error publishing event. Please try again.');
            console.error(xhr);
        }
    });
}

// Auto-filter for events (admin only)
@if(!Auth::user()->isMember() && !Auth::user()->isTrainer())
(function() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    
    var $ = jQuery;
    var filterTimeout;
    
    // Auto-filter function
    window.applyEventsFilters = function() {
        var formData = {
            status: $('#status').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
        };
        
        // Show loading
        var $eventsList = $('#eventsList');
        $eventsList.html('<div class="col-md-12"><div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div></div>');
        
        $.ajax({
            url: '{{ route("events.index") }}',
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
                
                // Update events list
                if (response.success && response.html) {
                    $eventsList.html(response.html);
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
            window.applyEventsFilters();
        });
        
        // Auto-filter on date change with debounce (500ms delay)
        $('#start_date, #end_date').on('change', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                window.applyEventsFilters();
            }, 500);
        });
        
        // Reset filters
        window.resetEventsFilters = function() {
            $('#status').val('');
            $('#start_date').val('');
            $('#end_date').val('');
            window.history.pushState({}, '', '{{ route("events.index") }}');
            window.applyEventsFilters();
        };
    });
})();
@endif
</script>
@endpush
@endsection

