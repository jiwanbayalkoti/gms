{{-- Edit Membership Plan Form - Used in Modal --}}
<form action="{{ route('membership-plans.update', $plan->id) }}" method="POST" id="planForm">
    @include('membership-plans._form', ['plan' => $plan])
</form>
