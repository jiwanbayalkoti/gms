@forelse($attendances ?? [] as $attendance)
    <tr id="attendance-row-{{ $attendance->id }}">
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
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewAttendanceModal" data-attendance-id="{{ $attendance->id }}">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
                <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#attendanceModal" data-action="edit" data-attendance-id="{{ $attendance->id }}">
                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                </button>
                @if(!$attendance->check_out_time)
                    <form action="{{ route('attendances.check-out', $attendance->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-success" title="Check Out">
                            <i class="fas fa-sign-out-alt"></i> <span class="d-none d-md-inline">Check Out</span>
                        </button>
                    </form>
                @endif
                <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                    data-delete-url="{{ route('attendances.destroy', $attendance->id) }}"
                    data-delete-name="Attendance #{{ $attendance->id }}"
                    data-delete-type="Attendance"
                    data-delete-row-id="attendance-row-{{ $attendance->id }}">
                    <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-4">
            <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
            <p class="text-muted">No attendance records found.</p>
        </td>
    </tr>
@endforelse


