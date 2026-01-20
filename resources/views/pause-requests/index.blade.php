@extends('layouts.app')

@section('title', 'Pause Requests')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Pause Requests</h2>
                @if(Auth::user()->isMember())
                    @php
                        $settings = \App\Models\Setting::current();
                    @endphp
                    @if($settings->enable_pause_feature)
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#pauseRequestModal" data-action="create">
                            <i class="fas fa-plus"></i> Request Pause
                        </button>
                    @endif
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

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    @if(!Auth::user()->isMember())
                                        <th>Member</th>
                                    @endif
                                    <th>Pause Period</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Requested On</th>
                                    @if(!Auth::user()->isMember())
                                        <th>Reviewed By</th>
                                        <th>Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody id="pauseRequestsTableBody">
                                @include('pause-requests._table-body')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pause Request Modal -->
@if(Auth::user()->isMember())
    <div class="modal fade" id="pauseRequestModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="pauseRequestModalLabel">Request Membership Pause</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="pauseRequestModalBody">
                    <!-- Form will be loaded here via AJAX -->
                </div>
            </div>
        </div>
    </div>
@endif

<!-- View Request Modal -->
<div class="modal fade" id="viewRequestModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Pause Request Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewRequestModalBody">
                <!-- Content will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>

<!-- Approve/Reject Modal -->
@if(!Auth::user()->isMember())
    <div class="modal fade" id="actionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" id="actionModalHeader">
                    <h5 class="modal-title" id="actionModalTitle">Action</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="actionForm">
                    <div class="modal-body">
                        <input type="hidden" id="actionRequestId" name="request_id">
                        <input type="hidden" id="actionType" name="action_type">
                        <div class="form-group">
                            <label for="admin_notes">Admin Notes (Optional)</label>
                            <textarea class="form-control" id="admin_notes" name="admin_notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn" id="actionSubmitBtn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

<script>
(function() {
    // Wait for jQuery to be loaded
    function initPauseRequests() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initPauseRequests, 100);
            return;
        }
        
        var $ = jQuery;
        
        $(document).ready(function() {
            @if(Auth::user()->isMember())
                // Handle create pause request modal
                $('#pauseRequestModal').on('show.bs.modal', function(event) {
                    var button = $(event.relatedTarget);
                    var action = button.data('action');
                    var modal = $(this);
                    var modalBody = modal.find('#pauseRequestModalBody');
                    
                    modalBody.html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>');
                    
                    $.ajax({
                        url: '{{ route("pause-requests.create") }}',
                        type: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        },
                        success: function(response) {
                            modalBody.html(response);
                        },
                        error: function(xhr) {
                            modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                        }
                    });
                });
            @endif

            // Handle view request
            window.viewRequest = function(requestId) {
                $.ajax({
                    url: '{{ route("pause-requests.show", ":id") }}'.replace(':id', requestId),
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        $('#viewRequestModalBody').html(response.html);
                        $('#viewRequestModal').modal('show');
                    },
                    error: function(xhr) {
                        alert('Error loading request details.');
                    }
                });
            };

            @if(!Auth::user()->isMember())
                // Handle approve/reject with alert box confirmation
                window.approveRequest = function(requestId) {
                    if (!confirm('Are you sure you want to approve this pause request?')) {
                        return;
                    }
                    
                    var adminNotes = prompt('Enter admin notes (optional):', '');
                    if (adminNotes === null) {
                        return; // User cancelled
                    }
                    
                    $.ajax({
                        url: '{{ route("pause-requests.approve", ":id") }}'.replace(':id', requestId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            admin_notes: adminNotes || ''
                        },
                        success: function(response) {
                            alert('Pause request approved successfully.');
                            location.reload();
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON?.message || 'Error approving request. Please try again.');
                        }
                    });
                };

                window.rejectRequest = function(requestId) {
                    if (!confirm('Are you sure you want to reject this pause request?')) {
                        return;
                    }
                    
                    var adminNotes = prompt('Enter rejection reason (optional):', '');
                    if (adminNotes === null) {
                        return; // User cancelled
                    }
                    
                    $.ajax({
                        url: '{{ route("pause-requests.reject", ":id") }}'.replace(':id', requestId),
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            admin_notes: adminNotes || ''
                        },
                        success: function(response) {
                            alert('Pause request rejected successfully.');
                            location.reload();
                        },
                        error: function(xhr) {
                            alert(xhr.responseJSON?.message || 'Error rejecting request. Please try again.');
                        }
                    });
                };
            @endif
        });
    }
    
    // Start initialization
    initPauseRequests();
    
    // Auto-filter for pause requests
    (function() {
        if (typeof jQuery === 'undefined') {
            return;
        }
        
        var $ = jQuery;
        var filterTimeout;
        
        // Auto-filter function
        window.applyPauseRequestsFilters = function() {
            var formData = {
                status: $('#status').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
            };
            
            // Show loading
            var $tableBody = $('#pauseRequestsTableBody');
            $tableBody.html('<tr><td colspan="{{ Auth::user()->isMember() ? "6" : "8" }}" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
            
            $.ajax({
                url: '{{ route("pause-requests.index") }}',
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
                window.applyPauseRequestsFilters();
            });
            
            // Auto-filter on date change with debounce (500ms delay)
            $('#start_date, #end_date').on('change', function() {
                clearTimeout(filterTimeout);
                filterTimeout = setTimeout(function() {
                    window.applyPauseRequestsFilters();
                }, 500);
            });
            
            // Reset filters
            window.resetPauseRequestsFilters = function() {
                $('#status').val('');
                $('#start_date').val('');
                $('#end_date').val('');
                window.history.pushState({}, '', '{{ route("pause-requests.index") }}');
                window.applyPauseRequestsFilters();
            };
        });
    })();
})();
</script>
@endsection

