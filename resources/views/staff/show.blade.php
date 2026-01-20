{{-- Staff Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">{{ $staff->name }}</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4 text-center">
                        @php
                            $photoUrl = getImageUrl($staff->profile_photo);
                        @endphp
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ $staff->name }}" class="img-thumbnail rounded-circle" width="120" height="120" style="object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; display: none; font-size: 48px;">
                                {{ substr($staff->name, 0, 1) }}
                            </div>
                        @else
                            <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; font-size: 48px;">
                                {{ substr($staff->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-8">
                        <h4>{{ $staff->name }}</h4>
                        <p class="text-muted mb-2">{{ $staff->email }}</p>
                        @if($staff->staff_type)
                            <p><span class="badge badge-info badge-lg">{{ $staff->staff_type }}</span></p>
                        @endif
                        @if($staff->active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </div>
                </div>

                <hr>

                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>{{ $staff->id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $staff->name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $staff->email }}</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td>{{ $staff->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Staff Type:</th>
                        <td>
                            @if($staff->staff_type)
                                <span class="badge badge-info">{{ $staff->staff_type }}</span>
                            @else
                                <span class="text-muted">Not specified</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td><span class="badge badge-secondary">{{ $staff->role }}</span></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($staff->active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    @if($staff->gym)
                        <tr>
                            <th>Gym:</th>
                            <td>{{ $staff->gym->name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $staff->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $staff->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

