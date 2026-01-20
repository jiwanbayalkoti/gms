@forelse($payments ?? [] as $payment)
    <tr id="payment-row-{{ $payment->id }}">
        <td>{{ $payment->id }}</td>
        <td>{{ $payment->employee->name ?? 'N/A' }}</td>
        <td>
            {{ $payment->payment_period_start->format('M d') }} - 
            {{ $payment->payment_period_end->format('M d, Y') }}
        </td>
        <td>NPR {{ number_format($payment->base_amount, 2) }}</td>
        <td>NPR {{ number_format($payment->commission_amount ?? 0, 2) }}</td>
        <td>NPR {{ number_format($payment->bonus_amount ?? 0, 2) }}</td>
        <td class="text-danger">NPR {{ number_format($payment->tax_amount ?? 0, 2) }}</td>
        <td class="text-danger">NPR {{ number_format($payment->deductions ?? 0, 2) }}</td>
        <td><strong class="text-success">NPR {{ number_format($payment->net_amount, 2) }}</strong></td>
        <td>
            <select class="form-control form-control-sm payment-status-select" 
                    data-payment-id="{{ $payment->id }}" 
                    onchange="updatePaymentStatus({{ $payment->id }}, this.value)">
                <option value="Pending" {{ $payment->payment_status === 'Pending' ? 'selected' : '' }}>Pending</option>
                <option value="Paid" {{ $payment->payment_status === 'Paid' ? 'selected' : '' }}>Paid</option>
                <option value="Failed" {{ $payment->payment_status === 'Failed' ? 'selected' : '' }}>Failed</option>
                <option value="Cancelled" {{ $payment->payment_status === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
        </td>
        <td>
            <div class="btn-group" role="group">
                @if($payment->payment_status === 'Paid')
                    <button type="button" class="btn btn-sm btn-primary" title="View Payslip & Print" data-toggle="modal" data-target="#payslipModal" data-payment-id="{{ $payment->id }}">
                        <i class="fas fa-file-invoice"></i> <span class="d-none d-md-inline">Payslip</span>
                    </button>
                    @if($payment->payment_receipt)
                        <a href="{{ asset('storage/' . $payment->payment_receipt) }}" target="_blank" class="btn btn-sm btn-info" title="View Receipt">
                            <i class="fas fa-file-image"></i> <span class="d-none d-md-inline">Receipt</span>
                        </a>
                    @endif
                @else
                    <button type="button" class="btn btn-sm btn-secondary" disabled title="Payslip available only for paid payments">
                        <i class="fas fa-file-invoice"></i> <span class="d-none d-md-inline">Payslip</span>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="11" class="text-center py-4">
            <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
            <p class="text-muted">No salary payments found.</p>
        </td>
    </tr>
@endforelse

