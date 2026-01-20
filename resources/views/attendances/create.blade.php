{{-- Create Attendance Form - Used in Modal --}}
<form action="{{ route('attendances.store') }}" method="POST" id="attendanceForm">
    @include('attendances._form', ['members' => $members ?? [], 'classes' => $classes ?? []])
</form>

