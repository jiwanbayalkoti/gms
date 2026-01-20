@forelse($notifications ?? [] as $notification)
    <tr id="notification-row-{{ $notification->id }}">
        <td>{{ $notification->id }}</td>
        <td>
            <strong>{{ $notification->title }}</strong>
            @if($notification->isUrgent())
                <span class="badge badge-danger ml-2">URGENT</span>
            @endif
        </td>
        <td>
            @php
                $badgeClass = [
                    'info' => 'badge-info',
                    'success' => 'badge-success',
                    'warning' => 'badge-warning',
                    'urgent' => 'badge-danger',
                ][$notification->type] ?? 'badge-secondary';
            @endphp
            <span class="badge {{ $badgeClass }}">{{ ucfirst($notification->type) }}</span>
        </td>
        <td>
            <span class="badge badge-secondary">{{ ucfirst($notification->target_audience) }}</span>
        </td>
        <td>
            @if($notification->is_published)
                <span class="badge badge-success">Published</span>
            @else
                <span class="badge badge-secondary">Draft</span>
            @endif
        </td>
        <td>
            {{ $notification->published_at ? $notification->published_at->format('M d, Y') : 'N/A' }}
        </td>
        <td>
            @if($notification->expires_at)
                {{ $notification->expires_at->format('M d, Y') }}
                @if($notification->expires_at->isPast())
                    <span class="badge badge-danger ml-1">Expired</span>
                @endif
            @else
                <span class="badge badge-success">No Expiry</span>
            @endif
        </td>
        <td>{{ $notification->creator->name ?? 'N/A' }}</td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewNotificationModal" data-notification-id="{{ $notification->id }}">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
                <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#notificationModal" data-action="edit" data-notification-id="{{ $notification->id }}">
                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                </button>
                @if($notification->is_published)
                    <button type="button" class="btn btn-sm btn-secondary" title="Unpublish" onclick="togglePublish({{ $notification->id }}, false)">
                        <i class="fas fa-eye-slash"></i> <span class="d-none d-md-inline">Unpublish</span>
                    </button>
                @else
                    <button type="button" class="btn btn-sm btn-success" title="Publish" onclick="togglePublish({{ $notification->id }}, true)">
                        <i class="fas fa-check"></i> <span class="d-none d-md-inline">Publish</span>
                    </button>
                @endif
                <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                    data-delete-url="{{ route('notifications.destroy', $notification->id) }}"
                    data-delete-name="{{ $notification->title }}"
                    data-delete-type="Notification"
                    data-delete-row-id="notification-row-{{ $notification->id }}">
                    <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-4">
            <i class="fas fa-bell fa-3x text-muted mb-3"></i>
            <p class="text-muted">No notifications found.</p>
        </td>
    </tr>
@endforelse

