{{-- Edit Attendance Form - Used in Modal --}}
<form action="{{ route('attendances.update', $attendance->id) }}" method="POST" id="attendanceForm">
    @include('attendances._form', ['attendance' => $attendance, 'members' => $members ?? [], 'classes' => $classes ?? []])
</form>

