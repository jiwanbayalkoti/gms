@forelse($members ?? [] as $member)
    <tr id="member-row-{{ $member->id }}">
        <td>{{ $member->id }}</td>
        <td>
            <div class="d-flex align-items-center">
                @php
                    $photoUrl = getImageUrl($member->profile_photo);
                @endphp
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $member->name }}" class="rounded-circle mr-2" width="40" height="40" style="object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px; display: none;">
                        {{ substr($member->name, 0, 1) }}
                    </div>
                @else
                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 40px; height: 40px;">
                        {{ substr($member->name, 0, 1) }}
                    </div>
                @endif
                {{ $member->name }}
            </div>
        </td>
        <td>{{ $member->email }}</td>
        <td>{{ $member->phone ?? 'N/A' }}</td>
        <td>
            @if($member->active)
                <span class="badge badge-success">Active</span>
            @else
                <span class="badge badge-secondary">Inactive</span>
            @endif
        </td>
        <td>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-info" title="View" data-toggle="modal" data-target="#viewMemberModal" data-member-id="{{ $member->id }}">
                    <i class="fas fa-eye"></i> <span class="d-none d-md-inline">View</span>
                </button>
                <button type="button" class="btn btn-sm btn-warning" title="Edit" data-toggle="modal" data-target="#memberModal" data-action="edit" data-member-id="{{ $member->id }}">
                    <i class="fas fa-edit"></i> <span class="d-none d-md-inline">Edit</span>
                </button>
                <button type="button" class="btn btn-sm btn-danger" title="Delete" 
                    data-delete-url="{{ route('members.destroy', $member->id) }}"
                    data-delete-name="{{ $member->name }}"
                    data-delete-type="Member"
                    data-delete-row-id="member-row-{{ $member->id }}">
                    <i class="fas fa-trash"></i> <span class="d-none d-md-inline">Delete</span>
                </button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="6" class="text-center py-4">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <p class="text-muted">No members found.</p>
        </td>
    </tr>
@endforelse


