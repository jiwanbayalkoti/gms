{{-- Attendance Form Partial --}}
@csrf

@if(isset($attendance))
    @method('PUT')
@endif

<div class="form-group">
    <label for="member_id">Member <span class="text-danger">*</span></label>
    <select class="form-control @error('member_id') is-invalid @enderror" id="member_id" name="member_id" required>
        <option value="">Select Member</option>
        @foreach($members ?? [] as $member)
            <option value="{{ $member->id }}" {{ old('member_id', $attendance->member_id ?? '') == $member->id ? 'selected' : '' }}>
                {{ $member->name }} ({{ $member->email }})
            </option>
        @endforeach
    </select>
    @error('member_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="check_in_time">Check In Time <span class="text-danger">*</span></label>
    <input type="datetime-local" class="form-control @error('check_in_time') is-invalid @enderror" id="check_in_time" name="check_in_time" value="{{ old('check_in_time', isset($attendance) ? $attendance->check_in_time->format('Y-m-d\TH:i') : '') }}" required>
    @error('check_in_time')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="check_out_time">Check Out Time</label>
    <input type="datetime-local" class="form-control @error('check_out_time') is-invalid @enderror" id="check_out_time" name="check_out_time" value="{{ old('check_out_time', isset($attendance) && $attendance->check_out_time ? $attendance->check_out_time->format('Y-m-d\TH:i') : '') }}">
    @error('check_out_time')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Leave empty if member hasn't checked out yet</small>
</div>

<div class="form-group">
    <label for="class_id">Class (Optional)</label>
    <select class="form-control @error('class_id') is-invalid @enderror" id="class_id" name="class_id">
        <option value="">No Class</option>
        @foreach($classes ?? [] as $class)
            <option value="{{ $class->id }}" {{ old('class_id', $attendance->class_id ?? '') == $class->id ? 'selected' : '' }}>
                {{ $class->name }} - {{ $class->start_time->format('M d, Y h:i A') }}
            </option>
        @endforeach
    </select>
    @error('class_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="notes">Notes</label>
    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $attendance->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group mb-0">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ isset($attendance) ? 'Update' : 'Create' }} Record
        </button>
    </div>
</div>

