@forelse($attendances ?? [] as $attendance)
    <tr>
        <td>{{ $attendance->id }}</td>
        <td>{{ $attendance->member->name ?? 'N/A' }}</td>
        <td>{{ $attendance->gymClass->name ?? 'N/A' }}</td>
        <td>{{ $attendance->check_in_time->format('M d, Y h:i A') }}</td>
        <td>
            @if($attendance->check_out_time)
                {{ $attendance->check_out_time->format('M d, Y h:i A') }}
            @else
                <span class="badge badge-warning">Not checked out</span>
            @endif
        </td>
        <td>
            @if($attendance->check_out_time)
                {{ $attendance->check_in_time->diffForHumans($attendance->check_out_time, true) }}
            @else
                <span class="badge badge-info">In Progress</span>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center">No attendance records found for the selected period.</td>
    </tr>
@endforelse

