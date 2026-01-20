@forelse($classes ?? [] as $class)
    <tr id="class-row-{{ $class->id }}">
        <td>{{ $class->id }}</td>
        <td>{{ $class->name }}</td>
        <td>{{ $class->trainer->name ?? 'N/A' }}</td>
        <td>{{ $class->start_time->format('M d, Y h:i A') }}</td>
        <td>{{ $class->end_time->format('M d, Y h:i A') }}</td>
        <td>{{ $class->location ?? 'N/A' }}</td>
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
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewClassModal" data-class-id="{{ $class->id }}">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
                @if(Auth::user()->isMember())
                    @php
                        $user = Auth::user();
                        $existingBooking = \App\Models\Booking::where('member_id', $user->id)
                            ->where('class_id', $class->id)
                            ->whereIn('status', ['Pending', 'Confirmed'])
                            ->first();
                        $canBook = $class->status === 'Active' 
                            && $class->start_time > now() 
                            && !$class->isFull() 
                            && !$existingBooking;
                    @endphp
                    @if($canBook)
                        <button type="button" class="btn btn-sm btn-success book-class-btn" 
                                title="Book This Class" 
                                data-class-id="{{ $class->id }}"
                                data-class-name="{{ $class->name }}">
                            <i class="fas fa-calendar-check"></i> <span class="d-none d-md-inline">Book</span>
                        </button>
                    @elseif($existingBooking)
                        <span class="badge badge-info">Booked</span>
                    @elseif($class->isFull())
                        <span class="badge badge-danger">Full</span>
                    @elseif($class->status !== 'Active')
                        <span class="badge badge-secondary">{{ $class->status }}</span>
                    @else
                        <span class="badge badge-warning">Past</span>
                    @endif
                @else
                    <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#classModal" data-action="edit" data-class-id="{{ $class->id }}">
                        <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                        data-delete-url="{{ route('classes.destroy', $class->id) }}"
                        data-delete-name="{{ $class->name }}"
                        data-delete-type="Class"
                        data-delete-row-id="class-row-{{ $class->id }}">
                        <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="10" class="text-center py-4">
            <i class="fas fa-dumbbell fa-3x text-muted mb-3"></i>
            <p class="text-muted">No classes found.</p>
        </td>
    </tr>
@endforelse


