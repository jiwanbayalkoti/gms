@forelse($plans ?? [] as $plan)
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $plan->name }}</h5>
                @if($plan->is_active)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-secondary">Inactive</span>
                @endif
            </div>
            <div class="card-body">
                <p class="text-muted">{{ $plan->description ?? 'No description' }}</p>
                <div class="mb-3">
                    <h3 class="text-primary">${{ number_format($plan->price, 2) }}</h3>
                    <small class="text-muted">Duration: {{ $plan->duration_days }} days</small>
                </div>
                <ul class="list-unstyled">
                    <li><i class="fas {{ $plan->allows_class_booking ? 'fa-check text-success' : 'fa-times text-danger' }}"></i> Class Booking: {{ $plan->allows_class_booking ? 'Yes' : 'No' }}</li>
                    @if($plan->allows_class_booking)
                        <li><i class="fas fa-check text-success"></i> Bookings per week: {{ $plan->allowed_bookings_per_week ?? 'Unlimited' }}</li>
                    @endif
                </ul>
            </div>
            <div class="card-footer">
                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewPlanModal" data-plan-id="{{ $plan->id }}">
                        <i class="fas fa-eye"></i> View
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#planModal" data-action="edit" data-plan-id="{{ $plan->id }}">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                        data-delete-url="{{ route('membership-plans.destroy', $plan->id) }}"
                        data-delete-name="{{ $plan->name }}"
                        data-delete-type="Membership Plan"
                        data-delete-row-id="plan-card-{{ $plan->id }}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-md-12">
        <div class="alert alert-info text-center">
            <i class="fas fa-id-card fa-3x mb-3"></i>
            <p>No membership plans found.</p>
        </div>
    </div>
@endforelse


