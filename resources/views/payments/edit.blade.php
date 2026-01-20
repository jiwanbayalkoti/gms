{{-- Edit Payment Form - Used in Modal --}}
<form action="{{ route('payments.update', $payment->id) }}" method="POST" id="paymentForm">
    @include('payments._form', ['payment' => $payment, 'members' => $members ?? [], 'plans' => $plans ?? []])
</form>

