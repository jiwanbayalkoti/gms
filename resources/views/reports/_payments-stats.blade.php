<div class="col-md-3">
    <div class="card bg-primary text-white">
        <div class="card-body">
            <h5>Total Payments</h5>
            <h2>{{ $stats['total_payments'] ?? 0 }}</h2>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card bg-success text-white">
        <div class="card-body">
            <h5>Total Amount</h5>
            <h2>${{ number_format($stats['total_amount'] ?? 0, 2) }}</h2>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card bg-info text-white">
        <div class="card-body">
            <h5>Completed</h5>
            <h2>{{ $stats['completed_payments'] ?? 0 }}</h2>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card bg-warning text-white">
        <div class="card-body">
            <h5>Completed Amount</h5>
            <h2>${{ number_format($stats['completed_amount'] ?? 0, 2) }}</h2>
        </div>
    </div>
</div>

