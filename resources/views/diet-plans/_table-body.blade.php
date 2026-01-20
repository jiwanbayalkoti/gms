@forelse($plans ?? [] as $plan)
    <tr id="plan-row-{{ $plan->id }}">
        <td>{{ $plan->id }}</td>
        <td>{{ $plan->name }}</td>
        <td>{{ $plan->trainer->name ?? 'N/A' }}</td>
        <td>
            @if($plan->member)
                {{ $plan->member->name }}
            @else
                <span class="badge badge-info">Default Template</span>
            @endif
        </td>
        <td>
            @if($plan->is_default)
                <span class="badge badge-success">Template</span>
            @else
                <span class="badge badge-primary">Assigned</span>
            @endif
        </td>
        <td>{{ $plan->start_date ? $plan->start_date->format('M d, Y') : 'N/A' }}</td>
        <td>{{ $plan->end_date ? $plan->end_date->format('M d, Y') : 'N/A' }}</td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewPlanModal" data-plan-id="{{ $plan->id }}">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
                @if(!Auth::user()->isMember())
                    <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#planModal" data-action="edit" data-plan-id="{{ $plan->id }}">
                        <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                    </button>
                    @if($plan->is_default)
                        <a href="{{ route('diet-plans.assign.form', $plan->id) }}" class="btn btn-sm btn-success" title="Assign">
                            <i class="fas fa-user-plus"></i> <span class="d-none d-md-inline">Assign</span>
                        </a>
                    @endif
                    <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                        data-delete-url="{{ route('diet-plans.destroy', $plan->id) }}"
                        data-delete-name="{{ $plan->name }}"
                        data-delete-type="Diet Plan"
                        data-delete-row-id="plan-row-{{ $plan->id }}">
                        <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center py-4">
            <i class="fas fa-utensils fa-3x text-muted mb-3"></i>
            <p class="text-muted">No diet plans found.</p>
        </td>
    </tr>
@endforelse

