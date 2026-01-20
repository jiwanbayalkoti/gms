{{-- Notification Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">{{ $notification->title }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <h6 class="text-muted">Type</h6>
                        @php
                            $badgeClass = [
                                'info' => 'badge-info',
                                'success' => 'badge-success',
                                'warning' => 'badge-warning',
                                'urgent' => 'badge-danger',
                            ][$notification->type] ?? 'badge-secondary';
                        @endphp
                        <span class="badge {{ $badgeClass }} badge-lg">{{ ucfirst($notification->type) }}</span>
                        @if($notification->isUrgent())
                            <span class="badge badge-danger ml-2">URGENT</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Status</h6>
                        @if($notification->is_published)
                            <span class="badge badge-success badge-lg">Published</span>
                        @else
                            <span class="badge badge-secondary badge-lg">Draft</span>
                        @endif
                    </div>
                    <div class="col-md-4">
                        <h6 class="text-muted">Target Audience</h6>
                        <span class="badge badge-secondary badge-lg">{{ ucfirst($notification->target_audience) }}</span>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <h6>Message:</h6>
                    <p class="text-muted">{{ $notification->message }}</p>
                </div>

                <table class="table table-borderless">
                    <tr>
                        <th width="200">Published At:</th>
                        <td>{{ $notification->published_at ? $notification->published_at->format('M d, Y h:i A') : 'Not Published' }}</td>
                    </tr>
                    <tr>
                        <th>Expires At:</th>
                        <td>
                            @if($notification->expires_at)
                                {{ $notification->expires_at->format('M d, Y h:i A') }}
                                @if($notification->expires_at->isPast())
                                    <span class="badge badge-danger ml-2">Expired</span>
                                @endif
                            @else
                                <span class="badge badge-success">No Expiration</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created By:</th>
                        <td>{{ $notification->creator->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $notification->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $notification->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

