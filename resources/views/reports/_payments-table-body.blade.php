@forelse($payments ?? [] as $payment)
    <tr>
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
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center">No payments found for the selected period.</td>
    </tr>
@endforelse

