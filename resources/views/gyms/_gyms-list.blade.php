@forelse($gyms ?? [] as $gym)
    <tr id="gym-row-{{ $gym->id }}">
        <td>{{ $gym->id }}</td>
        <td>
            @php
                $logoUrl = getImageUrl($gym->logo);
            @endphp
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="{{ $gym->name }}" class="img-circle" width="40" height="40" style="object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; display: none;">
                    {{ substr($gym->name, 0, 1) }}
                </div>
            @else
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                    {{ substr($gym->name, 0, 1) }}
                </div>
            @endif
        </td>
        <td><strong>{{ $gym->name }}</strong></td>
        <td>{{ $gym->address ?? 'N/A' }}</td>
        <td>{{ $gym->email ?? 'N/A' }}</td>
        <td>{{ $gym->phone ?? 'N/A' }}</td>
        <td>
            <span class="badge badge-info">{{ $gym->users_count ?? 0 }} Users</span>
            <small class="text-muted d-block">{{ $gym->members_count ?? 0 }} Members</small>
            <small class="text-muted d-block">{{ $gym->trainers_count ?? 0 }} Trainers</small>
        </td>
        <td>
            @if($gym->is_active)
                <span class="badge badge-success">Active</span>
            @else
                <span class="badge badge-secondary">Inactive</span>
            @endif
        </td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewGymModal" data-gym-id="{{ $gym->id }}">
                    <i class="fas fa-eye"></i>
                </button>
                <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#gymModal" data-action="edit" data-gym-id="{{ $gym->id }}">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-success" title="Create Admin" data-toggle="modal" data-target="#createAdminModal" data-gym-id="{{ $gym->id }}" data-gym-name="{{ $gym->name }}">
                    <i class="fas fa-user-plus"></i>
                </button>
                <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                    data-delete-url="{{ route('gyms.destroy', $gym->id) }}"
                    data-delete-name="{{ $gym->name }}"
                    data-delete-type="Gym"
                    data-delete-row-id="gym-row-{{ $gym->id }}">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="text-center py-4">
            <i class="fas fa-dumbbell fa-3x text-muted mb-3"></i>
            <p class="text-muted">No gyms found.</p>
        </td>
    </tr>
@endforelse


