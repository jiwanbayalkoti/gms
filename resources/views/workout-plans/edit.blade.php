{{-- Edit Workout Plan Form - Used in Modal --}}
<form action="{{ route('workout-plans.update', $plan->id) }}" method="POST" id="planForm">
    @include('workout-plans._form', ['plan' => $plan, 'trainers' => $trainers ?? [], 'members' => $members ?? []])
</form>

