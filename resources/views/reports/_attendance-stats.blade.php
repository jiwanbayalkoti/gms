<div class="col-md-3">
    <div class="card bg-primary text-white">
        <div class="card-body">
            <h5>Total Check-ins</h5>
            <h2>{{ $stats['total_checkins'] ?? 0 }}</h2>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card bg-success text-white">
        <div class="card-body">
            <h5>Unique Members</h5>
            <h2>{{ $stats['unique_members'] ?? 0 }}</h2>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card bg-info text-white">
        <div class="card-body">
            <h5>With Classes</h5>
            <h2>{{ $stats['with_classes'] ?? 0 }}</h2>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card bg-warning text-white">
        <div class="card-body">
            <h5>Pending Check-out</h5>
            <h2>{{ $stats['without_checkout'] ?? 0 }}</h2>
        </div>
    </div>
</div>

