{{-- Workout Plan Form Partial --}}
@csrf

@if(isset($plan))
    @method('PUT')
@endif

<div class="form-group">
    <label for="name">Plan Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $plan->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="description">Description</label>
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="4">{{ old('description', $plan->description ?? '') }}</textarea>
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
                    <option value="{{ $trainer->id }}" {{ old('trainer_id', $plan->trainer_id ?? '') == $trainer->id ? 'selected' : '' }}>
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
            <label for="member_id">Member (Optional)</label>
            <select class="form-control @error('member_id') is-invalid @enderror" id="member_id" name="member_id">
                <option value="">No Member (Template)</option>
                @foreach($members ?? [] as $member)
                    <option value="{{ $member->id }}" {{ old('member_id', $plan->member_id ?? '') == $member->id ? 'selected' : '' }}>
                        {{ $member->name }} ({{ $member->email }})
                    </option>
                @endforeach
            </select>
            @error('member_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Leave empty to create a template plan</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="start_date">Start Date</label>
            <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', isset($plan) && $plan->start_date ? $plan->start_date->format('Y-m-d') : '') }}">
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', isset($plan) && $plan->end_date ? $plan->end_date->format('Y-m-d') : '') }}">
            @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="notes">Notes</label>
    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $plan->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="is_default" name="is_default" value="1" {{ old('is_default', isset($plan) ? $plan->is_default : false) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_default">
            Default Template (can be assigned to multiple members)
        </label>
    </div>
    <small class="form-text text-muted">Template plans can be assigned to multiple members</small>
</div>

<div class="form-group mb-0">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ isset($plan) ? 'Update' : 'Create' }} Plan
        </button>
    </div>
</div>

