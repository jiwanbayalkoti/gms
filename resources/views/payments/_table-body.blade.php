@forelse($payments ?? [] as $payment)
    <tr id="payment-row-{{ $payment->id }}">
        <td>{{ $payment->id }}</td>
        <td>{{ $payment->member->name ?? 'N/A' }}</td>
        <td>{{ $payment->membershipPlan->name ?? 'N/A' }}</td>
        <td>${{ number_format($payment->amount, 2) }}</td>
        <td>{{ $payment->payment_method }}</td>
        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
        <td>
            @if($payment->payment_status === 'Completed')
                <span class="badge badge-success">Completed</span>
            @elseif($payment->payment_status === 'Failed')
                <span class="badge badge-danger">Failed</span>
            @else
                <span class="badge badge-warning">Refunded</span>
            @endif
        </td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewPaymentModal" data-payment-id="{{ $payment->id }}">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
                <button type="button" class="btn btn-sm btn-primary" title="Invoice" data-toggle="modal" data-target="#invoiceModal" data-payment-id="{{ $payment->id }}">
                    <i class="fas fa-file-invoice"></i> <span class="d-none d-md-inline">Invoice</span>
                </button>
                <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#paymentModal" data-action="edit" data-payment-id="{{ $payment->id }}">
                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                </button>
                <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                    data-delete-url="{{ route('payments.destroy', $payment->id) }}"
                    data-delete-name="Payment #{{ $payment->id }}"
                    data-delete-type="Payment"
                    data-delete-row-id="payment-row-{{ $payment->id }}">
                    <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-4">
            <i class="fas fa-wallet fa-3x text-muted mb-3"></i>
            <p class="text-muted">No payments found.</p>
        </td>
    </tr>
@endforelse


