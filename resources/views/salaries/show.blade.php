{{-- Salary Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Salary Details - {{ $salary->employee->name ?? 'N/A' }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <h6 class="text-muted">Salary Type</h6>
                        <h4 class="text-primary">{{ ucfirst($salary->salary_type) }}</h4>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Status</h6>
                        @if($salary->status === 'active')
                            <span class="badge badge-success badge-lg">Active</span>
                        @elseif($salary->status === 'inactive')
                            <span class="badge badge-secondary badge-lg">Inactive</span>
                        @else
                            <span class="badge badge-danger badge-lg">Terminated</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Payment Frequency</h6>
                        <p class="mb-0">{{ ucfirst($salary->payment_frequency) }}</p>
                    </div>
                </div>

                <hr>

                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>{{ $salary->id }}</td>
                    </tr>
                    <tr>
                        <th>Employee:</th>
                        <td>{{ $salary->employee->name ?? 'N/A' }} ({{ $salary->employee->role ?? 'N/A' }})</td>
                    </tr>
                    <tr>
                        <th>Salary Type:</th>
                        <td>
                            <span class="badge badge-info">{{ ucfirst($salary->salary_type) }}</span>
                        </td>
                    </tr>
                    @if($salary->base_salary)
                        <tr>
                            <th>Base Salary:</th>
                            <td>${{ number_format($salary->base_salary, 2) }}</td>
                        </tr>
                    @endif
                    @if($salary->hourly_rate)
                        <tr>
                            <th>Hourly Rate:</th>
                            <td>${{ number_format($salary->hourly_rate, 2) }}/hour</td>
                        </tr>
                    @endif
                    @if($salary->commission_percentage)
                        <tr>
                            <th>Commission Percentage:</th>
                            <td>{{ number_format($salary->commission_percentage, 2) }}%</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Payment Frequency:</th>
                        <td>{{ ucfirst($salary->payment_frequency) }}</td>
                    </tr>
                    <tr>
                        <th>Start Date:</th>
                        <td>{{ $salary->start_date->format('M d, Y') }}</td>
                    </tr>
                    <tr>
                        <th>End Date:</th>
                        <td>
                            @if($salary->end_date)
                                {{ $salary->end_date->format('M d, Y') }}
                                @if($salary->end_date->isPast())
                                    <span class="badge badge-danger ml-2">Expired</span>
                                @else
                                    <span class="badge badge-success ml-2">Active</span>
                                @endif
                            @else
                                <span class="text-muted">Ongoing</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($salary->status === 'active')
                                <span class="badge badge-success">Active</span>
                            @elseif($salary->status === 'inactive')
                                <span class="badge badge-secondary">Inactive</span>
                            @else
                                <span class="badge badge-danger">Terminated</span>
                            @endif
                        </td>
                    </tr>
                    @if($salary->notes)
                        <tr>
                            <th>Notes:</th>
                            <td>{{ $salary->notes }}</td>
                        </tr>
                    @endif
                    @if($salary->gym)
                        <tr>
                            <th>Gym:</th>
                            <td>{{ $salary->gym->name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $salary->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $salary->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>

                @if($salary->salaryPayments && $salary->salaryPayments->count() > 0)
                    <hr>
                    <h6 class="mb-3">Salary Payment History</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Net Amount</th>
                                    <th>Status</th>
                                    <th>Payment Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salary->salaryPayments->take(5) as $payment)
                                    <tr>
                                        <td>
                                            {{ $payment->payment_period_start->format('M d') }} - 
                                            {{ $payment->payment_period_end->format('M d, Y') }}
                                        </td>
                                        <td>${{ number_format($payment->net_amount, 2) }}</td>
                                        <td>
                                            @if($payment->payment_status === 'Paid')
                                                <span class="badge badge-success">Paid</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>{{ $payment->payment_date ? $payment->payment_date->format('M d, Y') : '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($salary->salaryPayments->count() > 5)
                        <p class="text-muted text-center mt-2">
                            <small>Showing last 5 payments. Total: {{ $salary->salaryPayments->count() }} payments</small>
                        </p>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

