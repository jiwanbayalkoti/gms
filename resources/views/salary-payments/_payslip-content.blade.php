{{-- Payslip Content Partial - Used in both modal and full page --}}
<div class="payslip-content">
    @php
        $settings = \App\Models\Setting::current();
        $gym = $payment->gym ?? null;
    @endphp

    <!-- Payslip Header -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h5 class="mb-2">{{ $settings->gym_name ?? 'Gym Management System' }}</h5>
            @if($settings->address)
                <p class="text-muted mb-1" style="font-size: 14px;">{{ $settings->address }}</p>
            @endif
            @if($settings->phone)
                <p class="text-muted mb-1" style="font-size: 14px;">Phone: {{ $settings->phone }}</p>
            @endif
            @if($settings->email)
                <p class="text-muted mb-0" style="font-size: 14px;">Email: {{ $settings->email }}</p>
            @endif
        </div>
        <div class="col-md-6 text-right">
            <h5 class="mb-2">Payslip #{{ $payment->id }}</h5>
            <p class="text-muted mb-1" style="font-size: 14px;">
                <strong>Payment Period:</strong><br>
                {{ $payment->payment_period_start->format('M d, Y') }} - 
                {{ $payment->payment_period_end->format('M d, Y') }}
            </p>
            <p class="text-muted mb-0" style="font-size: 14px;">
                <strong>Generated:</strong> {{ $payment->created_at->format('M d, Y') }}
            </p>
        </div>
    </div>

    <hr>

    <!-- Employee Information -->
    <div class="row mb-4">
        <div class="col-md-6">
            <h6 class="text-muted mb-3">Employee Information</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <th width="120" style="font-weight: 600;">Name:</th>
                    <td>{{ $payment->employee->name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Employee ID:</th>
                    <td>#{{ $payment->employee->id ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Role:</th>
                    <td>{{ $payment->employee->role ?? 'N/A' }}</td>
                </tr>
                @if($payment->employee && $payment->employee->phone)
                    <tr>
                        <th>Phone:</th>
                        <td>{{ $payment->employee->phone }}</td>
                    </tr>
                @endif
                @if($payment->employee && $payment->employee->email)
                    <tr>
                        <th>Email:</th>
                        <td>{{ $payment->employee->email }}</td>
                    </tr>
                @endif
            </table>
        </div>
        <div class="col-md-6">
            <h6 class="text-muted mb-3">Payment Information</h6>
            <table class="table table-sm table-borderless">
                <tr>
                    <th width="120" style="font-weight: 600;">Status:</th>
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
                    <th>Payment Method:</th>
                    <td>{{ $payment->payment_method ?? 'N/A' }}</td>
                </tr>
                @if($payment->payment_date)
                    <tr>
                        <th>Payment Date:</th>
                        <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                    </tr>
                @endif
                                @if($payment->transaction_id)
                                    <tr>
                                        <th>Transaction ID:</th>
                                        <td>{{ $payment->transaction_id }}</td>
                                    </tr>
                                @endif
                                @if($payment->payment_receipt)
                                    <tr>
                                        <th>Payment Receipt:</th>
                                        <td>
                                            <a href="{{ asset('storage/' . $payment->payment_receipt) }}" target="_blank" class="btn btn-sm btn-info">
                                                <i class="fas fa-file-image"></i> View Receipt
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

    <hr>

    <!-- Salary Breakdown -->
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h6 class="text-muted mb-3">Salary Breakdown</h6>
            <table class="table table-bordered">
                <tbody>
                    <tr>
                        <th width="60%">Base Salary</th>
                        <td class="text-right">NPR {{ number_format($payment->base_amount, 2) }}</td>
                    </tr>
                    @if($payment->commission_amount > 0)
                        <tr>
                            <th>Commission</th>
                            <td class="text-right text-success">+ NPR {{ number_format($payment->commission_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($payment->bonus_amount > 0)
                        <tr>
                            <th>Bonus</th>
                            <td class="text-right text-success">+ NPR {{ number_format($payment->bonus_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th><strong>Gross Salary</strong></th>
                        <td class="text-right">
                            <strong>NPR {{ number_format($payment->base_amount + ($payment->commission_amount ?? 0) + ($payment->bonus_amount ?? 0), 2) }}</strong>
                        </td>
                    </tr>
                    <tr class="bg-light">
                        <th colspan="2" class="text-center"><strong>Deductions</strong></th>
                    </tr>
                    @if($payment->tax_amount > 0)
                        <tr>
                            <th>Income Tax</th>
                            <td class="text-right text-danger">- NPR {{ number_format($payment->tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if($payment->deductions > 0)
                        <tr>
                            <th>Other Deductions</th>
                            <td class="text-right text-danger">- NPR {{ number_format($payment->deductions, 2) }}</td>
                        </tr>
                    @endif
                    @if($payment->salaryDeductions && $payment->salaryDeductions->count() > 0)
                        @foreach($payment->salaryDeductions as $deduction)
                            <tr>
                                <th style="padding-left: 20px;">{{ $deduction->deduction_type }}</th>
                                <td class="text-right text-danger">- NPR {{ number_format($deduction->amount, 2) }}</td>
                            </tr>
                        @endforeach
                    @endif
                    <tr class="bg-primary text-white">
                        <th><strong>Net Salary</strong></th>
                        <td class="text-right">
                            <strong style="font-size: 18px;">NPR {{ number_format($payment->net_amount, 2) }}</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    @if($payment->notes)
        <hr>
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h6 class="text-muted mb-2">Notes</h6>
                <p class="mb-0">{{ $payment->notes }}</p>
            </div>
        </div>
    @endif

    <hr>

    <!-- Footer -->
    <div class="row mt-4">
        <div class="col-md-12 text-center">
            <p class="text-muted mb-0" style="font-size: 12px;">
                This is a computer-generated payslip. No signature required.
            </p>
            <p class="text-muted mb-0" style="font-size: 12px;">
                Generated on {{ now()->format('M d, Y h:i A') }}
            </p>
        </div>
    </div>
</div>

