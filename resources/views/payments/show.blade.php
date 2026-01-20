{{-- Payment Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Payment #{{ $payment->id }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <h6 class="text-muted">Amount</h6>
                        <h3 class="text-primary">${{ number_format($payment->amount, 2) }}</h3>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Status</h6>
                        @if($payment->payment_status === 'Completed')
                            <span class="badge badge-success badge-lg">Completed</span>
                        @elseif($payment->payment_status === 'Failed')
                            <span class="badge badge-danger badge-lg">Failed</span>
                        @else
                            <span class="badge badge-warning badge-lg">Refunded</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Payment Method</h6>
                        <p class="mb-0">{{ $payment->payment_method }}</p>
                    </div>
                </div>

                <hr>

                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>{{ $payment->id }}</td>
                    </tr>
                    <tr>
                        <th>Member:</th>
                        <td>{{ $payment->member->name ?? 'N/A' }} ({{ $payment->member->email ?? 'N/A' }})</td>
                    </tr>
                    <tr>
                        <th>Membership Plan:</th>
                        <td>{{ $payment->membershipPlan->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Amount:</th>
                        <td>${{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Payment Method:</th>
                        <td>{{ $payment->payment_method }}</td>
                    </tr>
                    <tr>
                        <th>Transaction ID:</th>
                        <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Payment Status:</th>
                        <td>
                            @if($payment->payment_status === 'Completed')
                                <span class="badge badge-success">Completed</span>
                            @elseif($payment->payment_status === 'Failed')
                                <span class="badge badge-danger">Failed</span>
                            @else
                                <span class="badge badge-warning">Refunded</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Payment Date:</th>
                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>Expiry Date:</th>
                        <td>
                            @if($payment->expiry_date)
                                {{ $payment->expiry_date->format('M d, Y') }}
                                @if($payment->hasExpired())
                                    <span class="badge badge-danger ml-2">Expired</span>
                                @else
                                    <span class="badge badge-success ml-2">Active</span>
                                @endif
                            @else
                                N/A
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Notes:</th>
                        <td>{{ $payment->notes ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

