{{-- Create Diet Plan Form - Used in Modal --}}
<form action="{{ route('diet-plans.store') }}" method="POST" id="planForm">
    @include('diet-plans._form', ['trainers' => $trainers ?? [], 'members' => $members ?? []])
</form>

