@forelse($events as $event)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ $event->title }}</h5>
                @if($event->status === 'Draft')
                    <span class="badge badge-secondary">Draft</span>
                @elseif($event->status === 'Published')
                    <span class="badge badge-success">Published</span>
                @else
                    <span class="badge badge-danger">Cancelled</span>
                @endif
            </div>
            <div class="card-body">
                <p class="card-text">{{ Str::limit($event->description, 100) }}</p>
                <p class="mb-1"><strong>Date:</strong> {{ $event->event_date->format('M d, Y') }}</p>
                <p class="mb-1"><strong>Time:</strong> {{ \Carbon\Carbon::parse($event->event_time)->format('h:i A') }}</p>
                @if($event->location)
                    <p class="mb-1"><strong>Location:</strong> {{ $event->location }}</p>
                @endif
                
                @if($event->status === 'Published')
                    <div class="mt-3">
                        <small class="text-muted">Responses:</small>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="badge badge-success">{{ $event->attending_count }} Attending</span>
                            <span class="badge badge-danger">{{ $event->not_attending_count }} Not Attending</span>
                            <span class="badge badge-warning">{{ $event->not_sure_count }} Not Sure</span>
                        </div>
                    </div>
                @endif
            </div>
            <div class="card-footer">
                <div class="btn-group w-100" role="group">
                    <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewEventModal" data-event-id="{{ $event->id }}">
                        <i class="fas fa-eye"></i> View
                    </button>
                    @if(!Auth::user()->isMember())
                        @if($event->status !== 'Published')
                            <button type="button" class="btn btn-sm btn-success" title="Publish" onclick="publishEvent({{ $event->id }})">
                                <i class="fas fa-check"></i> Publish
                            </button>
                        @endif
                        <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#eventModal" data-action="edit" data-event-id="{{ $event->id }}">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                            data-delete-url="{{ route('events.destroy', $event->id) }}"
                            data-delete-name="{{ $event->title }}"
                            data-delete-type="Event">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="col-md-12">
        <div class="text-center py-5">
            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
            <p class="text-muted">No events found.</p>
        </div>
    </div>
@endforelse


