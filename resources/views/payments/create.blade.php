{{-- Create Payment Form - Used in Modal --}}
<form action="{{ route('payments.store') }}" method="POST" id="paymentForm">
    @include('payments._form', ['members' => $members ?? [], 'plans' => $plans ?? []])
</form>

