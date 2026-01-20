{{-- Booking Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Booking #{{ $booking->id }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <h6 class="text-muted">Status</h6>
                        @if($booking->status === 'Confirmed')
                            <span class="badge badge-success badge-lg">Confirmed</span>
                        @elseif($booking->status === 'Cancelled')
                            <span class="badge badge-danger badge-lg">Cancelled</span>
                        @elseif($booking->status === 'Attended')
                            <span class="badge badge-info badge-lg">Attended</span>
                        @else
                            <span class="badge badge-warning badge-lg">No-Show</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Reminder Sent</h6>
                        <p class="mb-0">
                            @if($booking->reminder_sent)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-secondary">No</span>
                            @endif
                        </p>
                    </div>
                </div>

                <hr>

                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>{{ $booking->id }}</td>
                    </tr>
                    <tr>
                        <th>Member:</th>
                        <td>
                            @if($booking->member)
                                {{ $booking->member->name }} ({{ $booking->member->email }})
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Class:</th>
                        <td>
                            @if($booking->gymClass)
                                {{ $booking->gymClass->name }}
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    @if($booking->gymClass)
                        <tr>
                            <th>Trainer:</th>
                            <td>{{ $booking->gymClass->trainer->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Class Date & Time:</th>
                            <td>{{ $booking->gymClass->start_time->format('M d, Y h:i A') }} - {{ $booking->gymClass->end_time->format('h:i A') }}</td>
                        </tr>
                        <tr>
                            <th>Location:</th>
                            <td>{{ $booking->gymClass->location ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Class Capacity:</th>
                            <td>
                                <span class="badge {{ $booking->gymClass->isFull() ? 'badge-danger' : 'badge-info' }}">
                                    {{ $booking->gymClass->current_bookings }} / {{ $booking->gymClass->capacity }}
                                </span>
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($booking->status === 'Confirmed')
                                <span class="badge badge-success">Confirmed</span>
                            @elseif($booking->status === 'Cancelled')
                                <span class="badge badge-danger">Cancelled</span>
                            @elseif($booking->status === 'Attended')
                                <span class="badge badge-info">Attended</span>
                            @else
                                <span class="badge badge-warning">No-Show</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Notes:</th>
                        <td>{{ $booking->notes ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Reminder Sent:</th>
                        <td>
                            @if($booking->reminder_sent)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-secondary">No</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $booking->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $booking->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

