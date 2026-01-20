@forelse($trainers ?? [] as $trainer)
    <tr id="trainer-row-{{ $trainer->id }}">
        <td>{{ $trainer->id }}</td>
        <td>
            <div class="d-flex align-items-center">
                @php
                    $photoUrl = getImageUrl($trainer->profile_photo);
                @endphp
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $trainer->name }}" class="rounded-circle mr-2" width="40" height="40" style="object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px; display: none;">
                        {{ substr($trainer->name, 0, 1) }}
                    </div>
                @else
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px;">
                        {{ substr($trainer->name, 0, 1) }}
                    </div>
                @endif
                {{ $trainer->name }}
            </div>
        </td>
        <td>{{ $trainer->email }}</td>
        <td>{{ $trainer->phone ?? 'N/A' }}</td>
        <td>
            @if($trainer->active)
                <span class="badge badge-success">Active</span>
            @else
                <span class="badge badge-secondary">Inactive</span>
            @endif
        </td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewTrainerModal" data-trainer-id="{{ $trainer->id }}">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
                <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#trainerModal" data-action="edit" data-trainer-id="{{ $trainer->id }}">
                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                </button>
                <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                    data-delete-url="{{ route('trainers.destroy', $trainer->id) }}"
                    data-delete-name="{{ $trainer->name }}"
                    data-delete-type="Trainer"
                    data-delete-row-id="trainer-row-{{ $trainer->id }}">
                    <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-4">
            <i class="fas fa-user-tie fa-3x text-muted mb-3"></i>
            <p class="text-muted">No trainers found.</p>
        </td>
    </tr>
@endforelse


