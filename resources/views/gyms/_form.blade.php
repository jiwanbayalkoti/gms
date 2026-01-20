{{-- Gym Form Partial - Used in Modal --}}
@csrf

@if(isset($gym))
    @method('PUT')
@endif

<div class="form-group">
    <label for="name">Gym Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $gym->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="address">Address</label>
    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2">{{ old('address', $gym->address ?? '') }}</textarea>
    @error('address')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $gym->email ?? '') }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $gym->phone ?? '') }}">
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="logo">Logo</label>
    @if(isset($gym))
        @php
            $logoUrl = getImageUrl($gym->logo);
        @endphp
        @if($logoUrl)
            <div class="mb-2">
                <img src="{{ $logoUrl }}" alt="{{ $gym->name }}" class="img-thumbnail" width="100" id="current-logo" style="object-fit: cover;" onerror="this.style.display='none';">
            </div>
        @endif
    @endif
    <input type="file" class="form-control-file @error('logo') is-invalid @enderror" id="logo" name="logo" accept="image/*">
    @error('logo')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Max size: 2MB. Allowed types: JPG, PNG, GIF</small>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="status">Status <span class="text-danger">*</span></label>
            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="active" {{ old('status', isset($gym) ? $gym->status : 'active') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', isset($gym) ? $gym->status : '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="subscription_plan">Subscription Plan</label>
            <input type="text" class="form-control @error('subscription_plan') is-invalid @enderror" id="subscription_plan" name="subscription_plan" value="{{ old('subscription_plan', $gym->subscription_plan ?? '') }}">
            @error('subscription_plan')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="subscription_ends_at">Subscription Ends At</label>
    <input type="datetime-local" class="form-control @error('subscription_ends_at') is-invalid @enderror" id="subscription_ends_at" name="subscription_ends_at" value="{{ old('subscription_ends_at', isset($gym) && $gym->subscription_ends_at ? $gym->subscription_ends_at->format('Y-m-d\TH:i') : '') }}">
    @error('subscription_ends_at')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Leave empty for unlimited subscription</small>
</div>

<div class="form-group mb-0">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ isset($gym) ? 'Update' : 'Create' }} Gym
        </button>
    </div>
</div>

