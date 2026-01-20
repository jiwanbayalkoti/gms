@forelse($bookings ?? [] as $booking)
    <tr id="booking-row-{{ $booking->id }}">
        <td>{{ $booking->id }}</td>
        <td>{{ $booking->member->name ?? 'N/A' }}</td>
        <td>{{ $booking->gymClass->name ?? 'N/A' }}</td>
        <td>{{ $booking->gymClass->trainer->name ?? 'N/A' }}</td>
        <td>
            @if($booking->gymClass)
                {{ $booking->gymClass->start_time->format('M d, Y h:i A') }}
            @else
                N/A
            @endif
        </td>
        <td>
            @if($booking->status === 'Pending')
                <span class="badge badge-warning">Pending</span>
            @elseif($booking->status === 'Confirmed')
                <span class="badge badge-success">Confirmed</span>
            @elseif($booking->status === 'Cancelled')
                <span class="badge badge-danger">Cancelled</span>
            @elseif($booking->status === 'Attended')
                <span class="badge badge-info">Attended</span>
            @else
                <span class="badge badge-secondary">No-Show</span>
            @endif
        </td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewBookingModal" data-booking-id="{{ $booking->id }}">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
                @if(!Auth::user()->isMember())
                    @if($booking->status === 'Pending')
                        <button type="button" class="btn btn-sm btn-success" title="Approve" onclick="approveBooking({{ $booking->id }})">
                            <i class="fas fa-check"></i> <span class="d-none d-md-inline">Approve</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" title="Reject" onclick="rejectBooking({{ $booking->id }})">
                            <i class="fas fa-times"></i> <span class="d-none d-md-inline">Reject</span>
                        </button>
                    @endif
                    <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#bookingModal" data-action="edit" data-booking-id="{{ $booking->id }}">
                        <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                        data-delete-url="{{ route('bookings.destroy', $booking->id) }}"
                        data-delete-name="Booking #{{ $booking->id }}"
                        data-delete-type="Booking"
                        data-delete-row-id="booking-row-{{ $booking->id }}">
                        <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-4">
            <i class="fas fa-calendar-check fa-3x text-muted mb-3"></i>
            <p class="text-muted">No bookings found.</p>
        </td>
    </tr>
@endforelse

