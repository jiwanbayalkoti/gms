{{-- Booking Form Partial - Used in Modal --}}
@csrf

@if(isset($booking))
    @method('PUT')
@endif

@if(!isset($user) || !$user->isMember())
    <div class="form-group">
        <label for="member_id">Member <span class="text-danger">*</span></label>
        <select class="form-control @error('member_id') is-invalid @enderror" id="member_id" name="member_id" required>
            <option value="">Select Member</option>
            @foreach($members ?? [] as $member)
                <option value="{{ $member->id }}" {{ old('member_id', $booking->member_id ?? '') == $member->id ? 'selected' : '' }}>
                    {{ $member->name }} ({{ $member->email }})
                </option>
            @endforeach
        </select>
        @error('member_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@else
    {{-- For members, hide member selection (they can only book for themselves) --}}
    <input type="hidden" name="member_id" value="{{ $user->id }}">
@endif

<div class="form-group">
    <label for="class_id">Class <span class="text-danger">*</span></label>
    <select class="form-control @error('class_id') is-invalid @enderror" id="class_id" name="class_id" required>
        <option value="">Select Class</option>
        @foreach($classes ?? [] as $class)
            <option value="{{ $class->id }}" 
                data-capacity="{{ $class->capacity }}"
                data-bookings="{{ $class->current_bookings }}"
                data-start-time="{{ $class->start_time->format('M d, Y h:i A') }}"
                {{ old('class_id', $booking->class_id ?? '') == $class->id ? 'selected' : '' }}>
                {{ $class->name }} - {{ $class->start_time->format('M d, Y h:i A') }} 
                ({{ $class->current_bookings }}/{{ $class->capacity }})
                @if($class->isFull())
                    - FULL
                @endif
            </option>
        @endforeach
    </select>
    @error('class_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Only available classes are shown</small>
</div>

@if(!isset($user) || !$user->isMember())
    <div class="form-group">
        <label for="status">Status <span class="text-danger">*</span></label>
        <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
            <option value="Pending" {{ old('status', $booking->status ?? 'Pending') === 'Pending' ? 'selected' : '' }}>Pending</option>
            <option value="Confirmed" {{ old('status', $booking->status ?? '') === 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
            <option value="Cancelled" {{ old('status', $booking->status ?? '') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
            <option value="Attended" {{ old('status', $booking->status ?? '') === 'Attended' ? 'selected' : '' }}>Attended</option>
            <option value="No-Show" {{ old('status', $booking->status ?? '') === 'No-Show' ? 'selected' : '' }}>No-Show</option>
        </select>
        @error('status')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@else
    {{-- For members, status is automatically set to Pending --}}
    <input type="hidden" name="status" value="Pending">
@endif

<div class="form-group">
    <label for="notes">Notes</label>
    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $booking->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group mb-0">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ isset($booking) ? 'Update' : 'Create' }} Booking
        </button>
    </div>
</div>

