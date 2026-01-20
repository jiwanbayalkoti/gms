@forelse($plans ?? [] as $plan)
    <tr id="plan-row-{{ $plan->id }}">
        <td>{{ $plan->id }}</td>
        <td>{{ $plan->name }}</td>
        <td>{{ Str::limit($plan->description, 50) }}</td>
        <td>{{ $plan->duration_days }} days</td>
        <td>${{ number_format($plan->price, 2) }}</td>
        <td>
            @if($plan->is_active)
                <span class="badge badge-success">Active</span>
            @else
                <span class="badge badge-secondary">Inactive</span>
            @endif
        </td>
        <td>
            @if($plan->allows_class_booking)
                <span class="badge badge-info">Yes</span>
            @else
                <span class="badge badge-secondary">No</span>
            @endif
        </td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewPlanModal" data-plan-id="{{ $plan->id }}">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
                <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#planModal" data-action="edit" data-plan-id="{{ $plan->id }}">
                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                </button>
                <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                    data-delete-url="{{ route('membership-plans.destroy', $plan->id) }}"
                    data-delete-name="{{ $plan->name }}"
                    data-delete-type="Membership Plan"
                    data-delete-row-id="plan-row-{{ $plan->id }}">
                    <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-4">
            <i class="fas fa-id-card fa-3x text-muted mb-3"></i>
            <p class="text-muted">No membership plans found.</p>
        </td>
    </tr>
@endforelse


