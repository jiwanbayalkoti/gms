<div class="row">
    <div class="col-md-12">
        <h4>{{ $event->title }}</h4>
        <hr>
        
        <div class="mb-3">
            <strong>Description:</strong>
            <p>{{ $event->description ?? 'No description provided.' }}</p>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Date:</strong> {{ $event->event_date->format('M d, Y') }}
            </div>
            <div class="col-md-6">
                <strong>Time:</strong> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}
            </div>
        </div>
        
        @if($event->location)
            <div class="mb-3">
                <strong>Location:</strong> {{ $event->location }}
            </div>
        @endif
        
        <div class="mb-3">
            <strong>Status:</strong>
            @if($event->status === 'Draft')
                <span class="badge badge-secondary">Draft</span>
            @elseif($event->status === 'Published')
                <span class="badge badge-success">Published</span>
            @else
                <span class="badge badge-danger">Cancelled</span>
            @endif
        </div>
        
        @if($event->status === 'Published')
            <hr>
            <h5>Response Counts</h5>
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-success">{{ $attendingCount ?? 0 }}</h3>
                            <p class="mb-0">Attending</p>
                            @if(!Auth::user()->isMember() && isset($attendingUsers) && $attendingUsers->count() > 0)
                                <hr class="my-2">
                                <div class="text-left">
                                    <small class="text-muted"><strong>Users:</strong></small>
                                    <ul class="list-unstyled mb-0 mt-1">
                                        @foreach($attendingUsers as $user)
                                            <li><small>{{ $user->name }} ({{ $user->email }})</small></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-danger">{{ $notAttendingCount ?? 0 }}</h3>
                            <p class="mb-0">Not Attending</p>
                            @if(!Auth::user()->isMember() && isset($notAttendingUsers) && $notAttendingUsers->count() > 0)
                                <hr class="my-2">
                                <div class="text-left">
                                    <small class="text-muted"><strong>Users:</strong></small>
                                    <ul class="list-unstyled mb-0 mt-1">
                                        @foreach($notAttendingUsers as $user)
                                            <li><small>{{ $user->name }} ({{ $user->email }})</small></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <h3 class="text-warning">{{ $notSureCount ?? 0 }}</h3>
                            <p class="mb-0">Not Sure</p>
                            @if(!Auth::user()->isMember() && isset($notSureUsers) && $notSureUsers->count() > 0)
                                <hr class="my-2">
                                <div class="text-left">
                                    <small class="text-muted"><strong>Users:</strong></small>
                                    <ul class="list-unstyled mb-0 mt-1">
                                        @foreach($notSureUsers as $user)
                                            <li><small>{{ $user->name }} ({{ $user->email }})</small></li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            @if(!Auth::user()->isMember() || true)
                <hr>
                <h5>Your Response</h5>
                <div class="btn-group" role="group" id="responseButtons">
                    <button type="button" class="btn btn-success response-btn {{ $response === 'Attending' ? 'active' : '' }}" data-response="Attending">
                        <i class="fas fa-check"></i> Attending
                    </button>
                    <button type="button" class="btn btn-danger response-btn {{ $response === 'Not Attending' ? 'active' : '' }}" data-response="Not Attending">
                        <i class="fas fa-times"></i> Not Attending
                    </button>
                    <button type="button" class="btn btn-warning response-btn {{ $response === 'Not Sure' ? 'active' : '' }}" data-response="Not Sure">
                        <i class="fas fa-question"></i> Not Sure
                    </button>
                </div>
                <div id="responseMessage" class="mt-2"></div>
            @endif
        @endif
    </div>
</div>

@if($event->status === 'Published')
<script>
$(document).ready(function() {
    $('.response-btn').on('click', function() {
        var button = $(this);
        var response = button.data('response');
        var eventId = {{ $event->id }};
        
        // Remove active class from all buttons
        $('.response-btn').removeClass('active');
        // Add active class to clicked button
        button.addClass('active');
        
        $.ajax({
            url: '{{ route("events.response", ":id") }}'.replace(':id', eventId),
            type: 'POST',
            data: {
                response: response,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                if (data.success) {
                    $('#responseMessage').html('<div class="alert alert-success">Response updated successfully!</div>');
                    setTimeout(function() {
                        $('#responseMessage').html('');
                    }, 3000);
                    
                    // Update counts if provided
                    if (data.counts) {
                        // Reload the modal to show updated counts
                        $('#viewEventModal').modal('hide');
                        setTimeout(function() {
                            $('#viewEventModal').modal('show');
                            $('[data-event-id="' + eventId + '"]').click();
                        }, 100);
                    }
                }
            },
            error: function(xhr) {
                $('#responseMessage').html('<div class="alert alert-danger">Error updating response. Please try again.</div>');
                console.error(xhr);
            }
        });
    });
});
</script>
@endif

