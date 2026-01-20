@forelse($staff ?? [] as $staffMember)
    <tr id="staff-row-{{ $staffMember->id }}">
        <td>{{ $staffMember->id }}</td>
        <td>
            <div class="d-flex align-items-center">
                @php
                    $photoUrl = getImageUrl($staffMember->profile_photo);
                @endphp
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $staffMember->name }}" class="rounded-circle mr-2" width="40" height="40" style="object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px; display: none;">
                        {{ substr($staffMember->name, 0, 1) }}
                    </div>
                @else
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px;">
                        {{ substr($staffMember->name, 0, 1) }}
                    </div>
                @endif
                {{ $staffMember->name }}
            </div>
        </td>
        <td>{{ $staffMember->email }}</td>
        <td>{{ $staffMember->phone ?? 'N/A' }}</td>
        <td>
            @if($staffMember->staff_type)
                <span class="badge badge-info">{{ $staffMember->staff_type }}</span>
            @else
                <span class="text-muted">-</span>
            @endif
        </td>
        <td>
            @if($staffMember->active)
                <span class="badge badge-success">Active</span>
            @else
                <span class="badge badge-secondary">Inactive</span>
            @endif
        </td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewStaffModal" data-staff-id="{{ $staffMember->id }}">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
                <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#staffModal" data-action="edit" data-staff-id="{{ $staffMember->id }}">
                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                </button>
                <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                    data-delete-url="{{ route('staff.destroy', $staffMember->id) }}"
                    data-delete-name="{{ $staffMember->name }}"
                    data-delete-type="Staff"
                    data-delete-row-id="staff-row-{{ $staffMember->id }}">
                    <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center py-4">
            <i class="fas fa-users-cog fa-3x text-muted mb-3"></i>
            <p class="text-muted">No staff found.</p>
        </td>
    </tr>
@endforelse


