{{-- Workout Plan Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">{{ $plan->name }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <h6 class="text-muted">Type</h6>
                        @if($plan->is_default)
                            <span class="badge badge-success badge-lg">Template</span>
                        @else
                            <span class="badge badge-primary badge-lg">Assigned</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Status</h6>
                        @php
                            $isActive = !$plan->end_date || $plan->end_date->isFuture();
                        @endphp
                        @if($isActive)
                            <span class="badge badge-success badge-lg">Active</span>
                        @else
                            <span class="badge badge-secondary badge-lg">Expired</span>
                        @endif
                    </div>
                </div>

                <hr>

                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>{{ $plan->id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $plan->name }}</td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td>{{ $plan->description ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Trainer:</th>
                        <td>{{ $plan->trainer->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Member:</th>
                        <td>
                            @if($plan->member)
                                {{ $plan->member->name }} ({{ $plan->member->email }})
                            @else
                                <span class="badge badge-info">Template (Not Assigned)</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Type:</th>
                        <td>
                            @if($plan->is_default)
                                <span class="badge badge-success">Template</span>
                            @else
                                <span class="badge badge-primary">Assigned Plan</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Start Date:</th>
                        <td>{{ $plan->start_date ? $plan->start_date->format('M d, Y') : 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>End Date:</th>
                        <td>
                            @if($plan->end_date)
                                {{ $plan->end_date->format('M d, Y') }}
                                @if($plan->end_date->isPast())
                                    <span class="badge badge-danger ml-2">Expired</span>
                                @endif
                            @else
                                <span class="badge badge-success">No End Date</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Notes:</th>
                        <td>{{ $plan->notes ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $plan->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $plan->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

