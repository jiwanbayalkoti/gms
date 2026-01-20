{{-- Event Form Partial - Used in Modal --}}
@csrf

@if(isset($event))
    @method('PUT')
@endif

<div class="form-group">
    <label for="title">Event Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', isset($event) ? $event->title : '') }}" required>
    @error('title')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', isset($event) ? $event->description : '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
<div class="form-group">
    <label for="event_date">Event Date <span class="text-danger">*</span></label>
    <input type="date" class="form-control @error('event_date') is-invalid @enderror" id="event_date" name="event_date" value="{{ old('event_date', isset($event) && $event->event_date ? $event->event_date->format('Y-m-d') : '') }}" required>
    @error('event_date')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
</div>
<div class="col-md-6">
    <div class="form-group">
        <label for="event_time">Event Time <span class="text-danger">*</span></label>
        <input type="time" class="form-control @error('event_time') is-invalid @enderror" id="event_time" name="event_time" value="{{ old('event_time', isset($event) && $event->event_time ? \Carbon\Carbon::parse($event->event_time)->format('H:i') : '') }}" required>
            @error('event_time')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="location">Location</label>
    <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location', isset($event) ? $event->location : '') }}" placeholder="e.g., Main Hall, Gym Floor">
    @error('location')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="status">Status <span class="text-danger">*</span></label>
    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
        <option value="Draft" {{ old('status', isset($event) ? $event->status : 'Draft') === 'Draft' ? 'selected' : '' }}>Draft</option>
        <option value="Published" {{ old('status', isset($event) ? $event->status : '') === 'Published' ? 'selected' : '' }}>Published</option>
        <option value="Cancelled" {{ old('status', isset($event) ? $event->status : '') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
    </select>
    @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Selecting "Published" will send notifications to all users.</small>
</div>

