<div class="col-md-3">
    <div class="card bg-primary text-white">
        <div class="card-body">
            <h5>Total Classes</h5>
            <h2>{{ $stats['total_classes'] ?? 0 }}</h2>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card bg-success text-white">
        <div class="card-body">
            <h5>Active</h5>
            <h2>{{ $stats['active_classes'] ?? 0 }}</h2>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card bg-danger text-white">
        <div class="card-body">
            <h5>Cancelled</h5>
            <h2>{{ $stats['cancelled_classes'] ?? 0 }}</h2>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card bg-info text-white">
        <div class="card-body">
            <h5>Total Bookings</h5>
            <h2>{{ $stats['total_bookings'] ?? 0 }}</h2>
        </div>
    </div>
</div>

