{{-- Create Notification Form - Used in Modal --}}
<form action="{{ route('notifications.store') }}" method="POST" id="notificationForm">
    @include('notifications._form')
</form>

