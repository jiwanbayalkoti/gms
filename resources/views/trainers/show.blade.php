{{-- Trainer Details View - Used in Modal --}}
<div class="row">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                @php
                    $photoUrl = getImageUrl($trainer->profile_photo);
                @endphp
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $trainer->name }}" class="img-fluid rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;" onerror="this.onerror=null; this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px; font-size: 48px; display: none;">
                        {{ substr($trainer->name, 0, 1) }}
                    </div>
                @else
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 150px; height: 150px; font-size: 48px;">
                        {{ substr($trainer->name, 0, 1) }}
                    </div>
                @endif
                <h4>{{ $trainer->name }}</h4>
                <p class="text-muted">{{ $trainer->email }}</p>
                @if($trainer->active)
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
                <h5 class="mb-0">Trainer Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">ID:</th>
                        <td>{{ $trainer->id }}</td>
                    </tr>
                    <tr>
                        <th>Name:</th>
                        <td>{{ $trainer->name }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $trainer->email }}</td>
                    </tr>
                    <tr>
                        <th>Phone:</th>
                        <td>{{ $trainer->phone ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Role:</th>
                        <td><span class="badge badge-info">{{ $trainer->role }}</span></td>
                    </tr>
                    <tr>
                        <th>Status:</th>
                        <td>
                            @if($trainer->active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-secondary">Inactive</span>
                            @endif
                        </td>
                    </tr>
                    @if($trainer->gym)
                        <tr>
                            <th>Gym:</th>
                            <td>{{ $trainer->gym->name }}</td>
                        </tr>
                    @endif
                    <tr>
                        <th>Created At:</th>
                        <td>{{ $trainer->created_at->format('M d, Y h:i A') }}</td>
                    </tr>
                    <tr>
                        <th>Updated At:</th>
                        <td>{{ $trainer->updated_at->format('M d, Y h:i A') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
