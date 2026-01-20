<div class="row">
    <div class="col-md-6">
        <h6 class="text-muted">Member Information</h6>
        <p><strong>Name:</strong> {{ $pauseRequest->member->name }}</p>
        <p><strong>Email:</strong> {{ $pauseRequest->member->email }}</p>
    </div>
    <div class="col-md-6">
        <h6 class="text-muted">Request Status</h6>
        <p>
            <strong>Status:</strong> 
            @if($pauseRequest->status === 'Pending')
                <span class="badge badge-warning">Pending</span>
            @elseif($pauseRequest->status === 'Approved')
                <span class="badge badge-success">Approved</span>
            @else
                <span class="badge badge-danger">Rejected</span>
            @endif
        </p>
        <p><strong>Requested On:</strong> {{ $pauseRequest->created_at->format('M d, Y h:i A') }}</p>
        @if($pauseRequest->reviewed_at)
            <p><strong>Reviewed On:</strong> {{ $pauseRequest->reviewed_at->format('M d, Y h:i A') }}</p>
            <p><strong>Reviewed By:</strong> {{ $pauseRequest->reviewer->name ?? 'N/A' }}</p>
        @endif
    </div>
</div>

<hr>

<div class="row">
    <div class="col-md-6">
        <h6 class="text-muted">Pause Period</h6>
        <p><strong>Start Date:</strong> {{ $pauseRequest->pause_start_date->format('M d, Y') }}</p>
        <p><strong>End Date:</strong> {{ $pauseRequest->pause_end_date->format('M d, Y') }}</p>
        <p><strong>Duration:</strong> {{ $pauseRequest->getDaysCount() }} days</p>
    </div>
    <div class="col-md-6">
        <h6 class="text-muted">Payment Information</h6>
        @if($pauseRequest->payment)
            <p><strong>Plan:</strong> {{ $pauseRequest->payment->membershipPlan->name ?? 'N/A' }}</p>
            <p><strong>Amount:</strong> ${{ number_format($pauseRequest->payment->amount, 2) }}</p>
            <p><strong>Original Expiry:</strong> 
                @if($pauseRequest->payment->expiry_date)
                    {{ $pauseRequest->payment->expiry_date->format('M d, Y') }}
                @else
                    N/A
                @endif
            </p>
        @else
            <p class="text-muted">No payment information available</p>
        @endif
    </div>
</div>

@if($pauseRequest->reason)
    <hr>
    <div>
        <h6 class="text-muted">Reason</h6>
        <p>{{ $pauseRequest->reason }}</p>
    </div>
@endif

@if($pauseRequest->admin_notes)
    <hr>
    <div>
        <h6 class="text-muted">Admin Notes</h6>
        <p>{{ $pauseRequest->admin_notes }}</p>
    </div>
@endif

