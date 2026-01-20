@forelse($users ?? [] as $user)
    <tr id="user-row-{{ $user->id }}">
        <td>{{ $user->id }}</td>
        <td>
            <div class="d-flex align-items-center">
                @php
                    $photoUrl = getImageUrl($user->profile_photo);
                @endphp
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $user->name }}" class="rounded-circle mr-2" width="40" height="40" style="object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px; display: none;">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                @else
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px;">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                @endif
                {{ $user->name }}
            </div>
        </td>
        <td>{{ $user->email }}</td>
        <td>{{ $user->phone ?? 'N/A' }}</td>
        @if($category === 'staff')
            <td>
                @if($user->staff_type)
                    <span class="badge badge-info">{{ $user->staff_type }}</span>
                @else
                    <span class="text-muted">-</span>
                @endif
            </td>
        @endif
        <td>
            @if($user->active)
                <span class="badge badge-success">Active</span>
            @else
                <span class="badge badge-secondary">Inactive</span>
            @endif
        </td>
        <td>
            <div class="btn-group" role="group">
                @if($category === 'members')
                    <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewUserModal" data-user-id="{{ $user->id }}" data-route="{{ route('members.show', $user->id) }}">
                        <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#userModal" data-action="edit" data-user-id="{{ $user->id }}" data-route="{{ route('members.edit', $user->id) }}" data-role="member">
                        <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                        data-delete-url="{{ route('members.destroy', $user->id) }}"
                        data-delete-name="{{ $user->name }}"
                        data-delete-type="Member"
                        data-delete-row-id="user-row-{{ $user->id }}">
                        <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                    </button>
                @elseif($category === 'trainers')
                    <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewUserModal" data-user-id="{{ $user->id }}" data-route="{{ route('trainers.show', $user->id) }}">
                        <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#userModal" data-action="edit" data-user-id="{{ $user->id }}" data-route="{{ route('trainers.edit', $user->id) }}" data-role="trainer">
                        <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                        data-delete-url="{{ route('trainers.destroy', $user->id) }}"
                        data-delete-name="{{ $user->name }}"
                        data-delete-type="Trainer"
                        data-delete-row-id="user-row-{{ $user->id }}">
                        <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                    </button>
                @elseif($category === 'staff')
                    <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewUserModal" data-user-id="{{ $user->id }}" data-route="{{ route('staff.show', $user->id) }}">
                        <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#userModal" data-action="edit" data-user-id="{{ $user->id }}" data-route="{{ route('staff.edit', $user->id) }}" data-role="staff">
                        <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                    </button>
                    <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                        data-delete-url="{{ route('staff.destroy', $user->id) }}"
                        data-delete-name="{{ $user->name }}"
                        data-delete-type="Staff"
                        data-delete-row-id="user-row-{{ $user->id }}">
                        <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                    </button>
                @endif
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="{{ $category === 'staff' ? '7' : '6' }}" class="text-center py-4">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <p class="text-muted">No users found.</p>
        </td>
    </tr>
@endforelse


