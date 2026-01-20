{{-- Edit Notification Form - Used in Modal --}}
<form action="{{ route('notifications.update', $notification->id) }}" method="POST" id="notificationForm">
    @method('PUT')
    @include('notifications._form', ['notification' => $notification])
</form>

