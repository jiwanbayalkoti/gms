{{-- Edit Diet Plan Form - Used in Modal --}}
<form action="{{ route('diet-plans.update', $plan->id) }}" method="POST" id="planForm">
    @include('diet-plans._form', ['plan' => $plan, 'trainers' => $trainers ?? [], 'members' => $members ?? []])
</form>

