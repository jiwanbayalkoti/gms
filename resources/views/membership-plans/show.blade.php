{{-- Membership Plan Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">{{ $plan->name }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <h3 class="text-primary">${{ number_format($plan->price, 2) }}</h3>
                        <p class="text-muted mb-0">Price</p>
                    </div>
                    <div class="col-md-6">
                        <h3 class="text-success">{{ $plan->duration_days }} Days</h3>
                        <p class="text-muted mb-0">Duration</p>
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
                        <th>Duration:</th>
                        <td>{{ $plan->duration_days }} days</td>
                    </tr>
                    <tr>
                        <th>Price:</th>
                        <td>${{ number_format($plan->price, 2) }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($plan->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Allows Class Booking:</th>
                        <td>
                            @if($plan->allows_class_booking)
                                <span class="badge badge-success">Yes</span>
                            @else
                                <span class="badge badge-danger">No</span>
                            @endif
                        </td>
                    </tr>
                    @if($plan->allows_class_booking)
                        <tr>
                            <th>Bookings Per Week:</th>
                            <td>{{ $plan->allowed_bookings_per_week ?? 'Unlimited' }}</td>
                        </tr>
                    @endif
                    @if($plan->gym)
                        <tr>
                            <th>Gym:</th>
                            <td>{{ $plan->gym->name }}</td>
                        </tr>
                    @endif
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
