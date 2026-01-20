{{-- Attendance Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Attendance Record #{{ $attendance->id }}</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>{{ $attendance->id }}</td>
                    </tr>
                    <tr>
                        <th>Member:</th>
                        <td>{{ $attendance->member->name ?? 'N/A' }} ({{ $attendance->member->email ?? 'N/A' }})</td>
                    </tr>
                    <tr>
                        <th>Class:</th>
                        <td>{{ $attendance->gymClass->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Check In Time:</th>
                        <td>{{ $attendance->check_in_time->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Check Out Time:</th>
                        <td>
                            @if($attendance->check_out_time)
                                {{ $attendance->check_out_time->format('M d, Y h:i A') }}
                            @else
                                <span class="badge badge-warning">Not checked out</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Duration:</th>
                        <td>
                            @if($attendance->check_out_time)
                                {{ $attendance->check_in_time->diffForHumans($attendance->check_out_time, true) }}
                            @else
                                <span class="badge badge-info">In Progress</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Notes:</th>
                        <td>{{ $attendance->notes ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $attendance->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

