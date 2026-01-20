{{-- Create Workout Plan Form - Used in Modal --}}
<form action="{{ route('workout-plans.store') }}" method="POST" id="planForm">
    @include('workout-plans._form', ['trainers' => $trainers ?? [], 'members' => $members ?? []])
</form>

