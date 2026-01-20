{{-- Trainer Form Partial - Used in Modal --}}
@csrf

@if(isset($trainer))
    @method('PUT')
@endif

<div class="form-group">
    <label for="name">Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $trainer->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="email">Email <span class="text-danger">*</span></label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $trainer->email ?? '') }}" required>
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="phone">Phone</label>
    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $trainer->phone ?? '') }}">
    @error('phone')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

@if(!isset($trainer))
    <div class="form-group">
        <label for="password">Password <span class="text-danger">*</span></label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirm Password <span class="text-danger">*</span></label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
    </div>
@else
    <div class="form-group">
        <label for="password">New Password (leave blank to keep current)</label>
        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirm New Password</label>
        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
    </div>
@endif

<div class="form-group">
    <label for="profile_photo">Profile Photo</label>
    @if(isset($trainer))
        @php
            $photoUrl = getImageUrl($trainer->profile_photo);
        @endphp
        @if($photoUrl)
            <div class="mb-2">
                <img src="{{ $photoUrl }}" alt="{{ $trainer->name }}" class="img-thumbnail" width="100" id="current-photo" style="object-fit: cover;" onerror="this.style.display='none';">
            </div>
        @endif
    @endif
    <input type="file" class="form-control-file @error('profile_photo') is-invalid @enderror" id="profile_photo" name="profile_photo" accept="image/*">
    @error('profile_photo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Max size: 2MB. Allowed types: JPG, PNG, GIF</small>
</div>

<div class="form-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="active" name="active" value="1" {{ old('active', isset($trainer) ? $trainer->active : true) ? 'checked' : '' }}>
        <label class="form-check-label" for="active">
            Active
        </label>
    </div>
</div>

<div class="form-group mb-0">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ isset($trainer) ? 'Update' : 'Create' }} Trainer
        </button>
    </div>
</div>

