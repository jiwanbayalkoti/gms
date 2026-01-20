{{-- Create Booking Form - Used in Modal (AJAX) --}}
<form action="{{ route('bookings.store') }}" method="POST" id="bookingForm">
    @include('bookings._form', ['members' => $members ?? [], 'classes' => $classes ?? []])
</form>
