{{-- Class Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">{{ $class->name }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <h6 class="text-muted">Status</h6>
                        @if($class->status === 'Active')
                            <span class="badge badge-success badge-lg">Active</span>
                        @elseif($class->status === 'Cancelled')
                            <span class="badge badge-danger badge-lg">Cancelled</span>
                        @else
                            <span class="badge badge-secondary badge-lg">Completed</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Capacity</h6>
                        <p class="mb-0">
                            <span class="badge {{ $class->isFull() ? 'badge-danger' : 'badge-info' }}">
                                {{ $class->current_bookings }} / {{ $class->capacity }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Trainer</h6>
                        <p class="mb-0">{{ $class->trainer->name ?? 'N/A' }}</p>
                    </div>
                </div>

                <hr>

                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>{{ $class->id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $class->name }}</td>
                    </tr>
                    <tr>
                        <th>Description:</th>
                        <td>{{ $class->description ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Trainer:</th>
                        <td>{{ $class->trainer->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Start Time:</th>
                        <td>{{ $class->start_time->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>End Time:</th>
                        <td>{{ $class->end_time->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Duration:</th>
                        <td>{{ $class->start_time->diffForHumans($class->end_time, true) }}</td>
                    </tr>
                    <tr>
                        <th>Location:</th>
                        <td>{{ $class->location ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Capacity:</th>
                        <td>{{ $class->capacity }}</td>
                    </tr>
                    <tr>
                        <th>Current Bookings:</th>
                        <td>
                            <span class="badge {{ $class->isFull() ? 'badge-danger' : 'badge-info' }}">
                                {{ $class->current_bookings }} / {{ $class->capacity }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($class->status === 'Active')
                                <span class="badge badge-success">Active</span>
                            @elseif($class->status === 'Cancelled')
                                <span class="badge badge-danger">Cancelled</span>
                            @else
                                <span class="badge badge-secondary">Completed</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Recurring:</th>
                        <td>{{ $class->recurring ? 'Yes' : 'No' }}</td>
                    </tr>
                    @if($class->recurring)
                        <tr>
                            <th>Recurring Pattern:</th>
                            <td>{{ $class->recurring_pattern ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th>Recurring End Date:</th>
                            <td>{{ $class->recurring_end_date ? $class->recurring_end_date->format('M d, Y') : 'N/A' }}</td>
                        </tr>
                    @endif
                    @if($class->gym)
                        <tr>
                            <th>Gym:</th>
                            <td>{{ $class->gym->name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $class->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $class->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
