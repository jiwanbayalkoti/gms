{{-- Gym Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @php
                    $logoUrl = getImageUrl($gym->logo);
                @endphp
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $gym->name }}" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px; font-size: 48px; display: none;">
                        {{ substr($gym->name, 0, 1) }}
                    </div>
                @else
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px; font-size: 48px;">
                        {{ substr($gym->name, 0, 1) }}
                    </div>
                @endif
                <h4>{{ $gym->name }}</h4>
                @if($gym->status === 'active')
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
                <h5 class="mb-0">Gym Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>{{ $gym->id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $gym->name }}</td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td>{{ $gym->address ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $gym->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td>{{ $gym->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($gym->status === 'active')
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Subscription Plan:</th>
                        <td>{{ $gym->subscription_plan ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Subscription Ends:</th>
                        <td>{{ $gym->subscription_ends_at ? $gym->subscription_ends_at->format('M d, Y h:i A') : 'Unlimited' }}</td>
                    </tr>
                    <tr>
                        <th>Total Users:</th>
                        <td><span class="badge badge-info">{{ $gym->users_count ?? 0 }}</span></td>
                    </tr>
                    <tr>
                        <th>Members:</th>
                        <td><span class="badge badge-primary">{{ $gym->members_count ?? 0 }}</span></td>
                    </tr>
                    <tr>
                        <th>Trainers:</th>
                        <td><span class="badge badge-warning">{{ $gym->trainers_count ?? 0 }}</span></td>
                    </tr>
                    <tr>
                        <th>Gym Admin:</th>
                        <td>
                            @php
                                $gymAdmin = $gym->admin;
                            @endphp
                            @if($gymAdmin)
                                <div class="d-flex align-items-center">
                                    @php
                                        $adminPhotoUrl = getImageUrl($gymAdmin->profile_photo);
                                    @endphp
                                    @if($adminPhotoUrl)
                                        <img src="{{ $adminPhotoUrl }}" alt="{{ $gymAdmin->name }}" class="rounded-circle mr-2" width="30" height="30" style="object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 30px; height: 30px; display: none; font-size: 12px;">
                                            {{ substr($gymAdmin->name, 0, 1) }}
                                        </div>
                                    @else
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2" style="width: 30px; height: 30px; font-size: 12px;">
                                            {{ substr($gymAdmin->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $gymAdmin->name }}</strong><br>
                                        <small class="text-muted">{{ $gymAdmin->email }}</small>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted">No admin assigned</span>
                                <button type="button" class="btn btn-sm btn-primary ml-2" data-toggle="modal" data-target="#createAdminModal" data-gym-id="{{ $gym->id }}">
                                    <i class="fas fa-user-plus"></i> Create Admin
                                </button>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $gym->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $gym->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

