{{-- Notification Form Partial --}}
@csrf

<div class="form-group">
    <label for="title">Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $notification->title ?? '') }}" required>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="message">Message <span class="text-danger">*</span></label>
    <textarea class="form-control @error('message') is-invalid @enderror" id="message" name="message" rows="5" required>{{ old('message', $notification->message ?? '') }}</textarea>
    @error('message')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="type">Type <span class="text-danger">*</span></label>
            <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                <option value="info" {{ old('type', $notification->type ?? 'info') == 'info' ? 'selected' : '' }}>Info</option>
                <option value="success" {{ old('type', $notification->type ?? '') == 'success' ? 'selected' : '' }}>Success</option>
                <option value="warning" {{ old('type', $notification->type ?? '') == 'warning' ? 'selected' : '' }}>Warning</option>
                <option value="urgent" {{ old('type', $notification->type ?? '') == 'urgent' ? 'selected' : '' }}>Urgent</option>
            </select>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Urgent notifications will pop up on login</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="target_audience">Target Audience <span class="text-danger">*</span></label>
            <select class="form-control @error('target_audience') is-invalid @enderror" id="target_audience" name="target_audience" required>
                <option value="all" {{ old('target_audience', $notification->target_audience ?? 'all') == 'all' ? 'selected' : '' }}>All Users</option>
                <option value="members" {{ old('target_audience', $notification->target_audience ?? '') == 'members' ? 'selected' : '' }}>Members Only</option>
                <option value="trainers" {{ old('target_audience', $notification->target_audience ?? '') == 'trainers' ? 'selected' : '' }}>Trainers Only</option>
                <option value="staff" {{ old('target_audience', $notification->target_audience ?? '') == 'staff' ? 'selected' : '' }}>Staff Only</option>
                <option value="admins" {{ old('target_audience', $notification->target_audience ?? '') == 'admins' ? 'selected' : '' }}>Admins Only</option>
            </select>
            @error('target_audience')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="published_at">Published At</label>
            <input type="datetime-local" class="form-control @error('published_at') is-invalid @enderror" id="published_at" name="published_at" value="{{ old('published_at', isset($notification) && $notification->published_at ? $notification->published_at->format('Y-m-d\TH:i') : '') }}">
            @error('published_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Leave empty to publish immediately when checked</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="expires_at">Expires At</label>
            <input type="datetime-local" class="form-control @error('expires_at') is-invalid @enderror" id="expires_at" name="expires_at" value="{{ old('expires_at', isset($notification) && $notification->expires_at ? $notification->expires_at->format('Y-m-d\TH:i') : '') }}">
            @error('expires_at')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Leave empty for no expiration</small>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="is_published" name="is_published" value="1" {{ old('is_published', isset($notification) ? $notification->is_published : false) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_published">
            Publish immediately
        </label>
    </div>
    <small class="form-text text-muted">If checked, notification will be published immediately (or at the scheduled time if provided)</small>
</div>

<div class="form-group mb-0">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ isset($notification) ? 'Update' : 'Create' }} Notification
        </button>
    </div>
</div>

