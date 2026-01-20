{{-- Salary Payment Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Salary Payment #{{ $payment->id }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <h6 class="text-muted">Net Amount</h6>
                        <h3 class="text-primary">${{ number_format($payment->net_amount, 2) }}</h3>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Status</h6>
                        @if($payment->payment_status === 'Paid')
                            <span class="badge badge-success badge-lg">Paid</span>
                        @elseif($payment->payment_status === 'Pending')
                            <span class="badge badge-warning badge-lg">Pending</span>
                        @elseif($payment->payment_status === 'Failed')
                            <span class="badge badge-danger badge-lg">Failed</span>
                        @else
                            <span class="badge badge-secondary badge-lg">Cancelled</span>
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
                        <th>Employee:</th>
                        <td>{{ $payment->employee->name ?? 'N/A' }} ({{ $payment->employee->role ?? 'N/A' }})</td>
                    </tr>
                    <tr>
                        <th>Payment Period:</th>
                        <td>
                            {{ $payment->payment_period_start->format('M d, Y') }} - 
                            {{ $payment->payment_period_end->format('M d, Y') }}
                        </td>
                    </tr>
                    <tr>
                        <th>Base Amount:</th>
                        <td>${{ number_format($payment->base_amount, 2) }}</td>
                    </tr>
                    @if($payment->commission_amount > 0)
                        <tr>
                            <th>Commission Amount:</th>
                            <td>${{ number_format($payment->commission_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($payment->bonus_amount > 0)
                        <tr>
                            <th>Bonus Amount:</th>
                            <td>${{ number_format($payment->bonus_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($payment->deductions > 0)
                        <tr>
                            <th>Deductions:</th>
                            <td class="text-danger">-${{ number_format($payment->deductions, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Net Amount:</th>
                        <td><strong>${{ number_format($payment->net_amount, 2) }}</strong></td>
                    </tr>
                    <tr>
                        <th>Payment Method:</th>
                        <td>{{ $payment->payment_method }}</td>
                    </tr>
                    @if($payment->transaction_id)
                        <tr>
                            <th>Transaction ID:</th>
                            <td>{{ $payment->transaction_id }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Payment Status:</th>
                        <td>
                            @if($payment->payment_status === 'Paid')
                                <span class="badge badge-success">Paid</span>
                            @elseif($payment->payment_status === 'Pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($payment->payment_status === 'Failed')
                                <span class="badge badge-danger">Failed</span>
                            @else
                                <span class="badge badge-secondary">Cancelled</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Payment Date:</th>
                        <td>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : 'Not paid yet' }}</td>
                    </tr>
                    @if($payment->notes)
                        <tr>
                            <th>Notes:</th>
                            <td>{{ $payment->notes }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $payment->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>

                @if($payment->salaryDeductions && $payment->salaryDeductions->count() > 0)
                    <hr>
                    <h6 class="mb-3">Deductions Breakdown</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->salaryDeductions as $deduction)
                                    <tr>
                                        <td>{{ $deduction->deduction_type }}</td>
                                        <td>{{ $deduction->description ?? 'N/A' }}</td>
                                        <td>${{ number_format($deduction->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

