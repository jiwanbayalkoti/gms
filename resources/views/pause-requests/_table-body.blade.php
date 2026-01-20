@forelse($pauseRequests as $request)
    <tr>
        @if(!Auth::user()->isMember())
            <td>{{ $request->member->name }}</td>
        @endif
        <td>
            {{ $request->pause_start_date->format('M d, Y') }} - 
            {{ $request->pause_end_date->format('M d, Y') }}
        </td>
        <td>{{ $request->getDaysCount() }} days</td>
        <td>{{ $request->reason ?? 'N/A' }}</td>
        <td>
            @if($request->status === 'Pending')
                <span class="badge badge-warning">Pending</span>
            @elseif($request->status === 'Approved')
                <span class="badge badge-success">Approved</span>
            @else
                <span class="badge badge-danger">Rejected</span>
            @endif
        </td>
        <td>{{ $request->created_at->format('M d, Y h:i A') }}</td>
        @if(!Auth::user()->isMember())
            <td>
                @if($request->reviewer)
                    {{ $request->reviewer->name }}
                    <br><small class="text-muted">{{ $request->reviewed_at->format('M d, Y') }}</small>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
            <td>
                @if($request->status === 'Pending')
                    <button type="button" class="btn btn-sm btn-success" onclick="approveRequest({{ $request->id }})">
                        <i class="fas fa-check"></i> <span class="d-none d-md-inline">Approve</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="rejectRequest({{ $request->id }})">
                        <i class="fas fa-times"></i> <span class="d-none d-md-inline">Reject</span>
                    </button>
                @endif
                <button type="button" class="btn btn-sm btn-info" onclick="viewRequest({{ $request->id }})">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
            </td>
        @else
            <td>
                <button type="button" class="btn btn-sm btn-info" onclick="viewRequest({{ $request->id }})">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
            </td>
        @endif
    </tr>
@empty
    <tr>
        <td colspan="{{ Auth::user()->isMember() ? '6' : '8' }}" class="text-center py-4">
            <i class="fas fa-pause-circle fa-3x text-muted mb-3"></i>
            <p class="text-muted">No pause requests found.</p>
        </td>
    </tr>
@endforelse

