{{-- Class Form Partial - Used in Modal --}}
@csrf

@if(isset($class))
    @method('PUT')
@endif

<div class="form-group">
    <label for="name">Class Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $class->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $class->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="trainer_id">Trainer <span class="text-danger">*</span></label>
            <select class="form-control @error('trainer_id') is-invalid @enderror" id="trainer_id" name="trainer_id" required>
                <option value="">Select Trainer</option>
                @foreach($trainers ?? [] as $trainer)
                    <option value="{{ $trainer->id }}" {{ old('trainer_id', $class->trainer_id ?? '') == $trainer->id ? 'selected' : '' }}>
                        {{ $trainer->name }}
                    </option>
                @endforeach
            </select>
            @error('trainer_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="capacity">Capacity <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('capacity') is-invalid @enderror" id="capacity" name="capacity" value="{{ old('capacity', $class->capacity ?? '') }}" min="1" required>
            @error('capacity')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="start_time">Start Time <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control @error('start_time') is-invalid @enderror" id="start_time" name="start_time" value="{{ old('start_time', isset($class) ? $class->start_time->format('Y-m-d\TH:i') : '') }}" required>
            @error('start_time')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="end_time">End Time <span class="text-danger">*</span></label>
            <input type="datetime-local" class="form-control @error('end_time') is-invalid @enderror" id="end_time" name="end_time" value="{{ old('end_time', isset($class) ? $class->end_time->format('Y-m-d\TH:i') : '') }}" required>
            @error('end_time')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="location">Location</label>
    <input type="text" class="form-control @error('location') is-invalid @enderror" id="location" name="location" value="{{ old('location', $class->location ?? '') }}">
    @error('location')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="status">Status <span class="text-danger">*</span></label>
    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
        <option value="Active" {{ old('status', $class->status ?? 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
        <option value="Cancelled" {{ old('status', $class->status ?? '') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
        <option value="Completed" {{ old('status', $class->status ?? '') === 'Completed' ? 'selected' : '' }}>Completed</option>
    </select>
    @error('status')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="recurring" name="recurring" value="1" {{ old('recurring', isset($class) ? $class->recurring : false) ? 'checked' : '' }}>
        <label class="form-check-label" for="recurring">
            Recurring Class
        </label>
    </div>
</div>

<div class="row" id="recurring_options" style="{{ old('recurring', isset($class) ? $class->recurring : false) ? '' : 'display: none;' }}">
    <div class="col-md-6">
        <div class="form-group">
            <label for="recurring_pattern">Recurring Pattern</label>
            <select class="form-control @error('recurring_pattern') is-invalid @enderror" id="recurring_pattern" name="recurring_pattern">
                <option value="">Select Pattern</option>
                <option value="Daily" {{ old('recurring_pattern', $class->recurring_pattern ?? '') === 'Daily' ? 'selected' : '' }}>Daily</option>
                <option value="Weekly" {{ old('recurring_pattern', $class->recurring_pattern ?? '') === 'Weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="Monthly" {{ old('recurring_pattern', $class->recurring_pattern ?? '') === 'Monthly' ? 'selected' : '' }}>Monthly</option>
            </select>
            @error('recurring_pattern')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="recurring_end_date">Recurring End Date</label>
            <input type="date" class="form-control @error('recurring_end_date') is-invalid @enderror" id="recurring_end_date" name="recurring_end_date" value="{{ old('recurring_end_date', isset($class) && $class->recurring_end_date ? $class->recurring_end_date->format('Y-m-d') : '') }}">
            @error('recurring_end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group mb-0">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ isset($class) ? 'Update' : 'Create' }} Class
        </button>
    </div>
</div>

<script>
(function() {
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function($) {
            // Show/hide recurring options based on recurring checkbox
            $('#recurring').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#recurring_options').slideDown();
                } else {
                    $('#recurring_options').slideUp();
                }
            });
        });
    }
})();
</script>

