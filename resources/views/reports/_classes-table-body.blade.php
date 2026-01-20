@forelse($classes ?? [] as $class)
    <tr>
        <td>{{ $class->id }}</td>
        <td>{{ $class->name }}</td>
        <td>{{ $class->trainer->name ?? 'N/A' }}</td>
        <td>{{ $class->start_time->format('M d, Y h:i A') }}</td>
        <td>{{ $class->capacity }}</td>
        <td>
            <span class="badge {{ $class->isFull() ? 'badge-danger' : 'badge-info' }}">
                {{ $class->current_bookings }} / {{ $class->capacity }}
            </span>
        </td>
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
@empty
    <tr>
        <td colspan="7" class="text-center">No classes found for the selected period.</td>
    </tr>
@endforelse

