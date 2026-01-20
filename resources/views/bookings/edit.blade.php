{{-- Edit Booking Form - Used in Modal --}}
<form action="{{ route('bookings.update', $booking->id) }}" method="POST" id="bookingForm">
    @include('bookings._form', ['booking' => $booking, 'members' => $members, 'classes' => $classes])
</form>

