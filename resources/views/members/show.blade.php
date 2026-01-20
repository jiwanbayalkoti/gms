{{-- Member Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @php
                    $photoUrl = getImageUrl($member->profile_photo);
                @endphp
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $member->name }}" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px; font-size: 48px; display: none;">
                        {{ substr($member->name, 0, 1) }}
                    </div>
                @else
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px; font-size: 48px;">
                        {{ substr($member->name, 0, 1) }}
                    </div>
                @endif
                <h4>{{ $member->name }}</h4>
                <p class="text-muted">{{ $member->email }}</p>
                @if($member->active)
                    <span class="badge badge-success">Active</span>
                @else
                    <span class="badge badge-secondary">Inactive</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Member Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>{{ $member->id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $member->name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $member->email }}</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td>{{ $member->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td><span class="badge badge-info">{{ $member->role }}</span></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($member->active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $member->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $member->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

