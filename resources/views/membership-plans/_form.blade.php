{{-- Membership Plan Form Partial - Used in Modal --}}
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
    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $plan->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="duration_days">Duration (Days) <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('duration_days') is-invalid @enderror" id="duration_days" name="duration_days" value="{{ old('duration_days', $plan->duration_days ?? '') }}" min="1" required>
            @error('duration_days')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Number of days the membership is valid</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="price">Price ($) <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price', $plan->price ?? '') }}" step="0.01" min="0" required>
            @error('price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="allows_class_booking" name="allows_class_booking" value="1" {{ old('allows_class_booking', isset($plan) ? $plan->allows_class_booking : true) ? 'checked' : '' }}>
        <label class="form-check-label" for="allows_class_booking">
            Allows Class Booking
        </label>
    </div>
</div>

<div class="form-group" id="bookings_per_week_group" style="{{ old('allows_class_booking', isset($plan) ? $plan->allows_class_booking : true) ? '' : 'display: none;' }}">
    <label for="allowed_bookings_per_week">Allowed Bookings Per Week</label>
    <input type="number" class="form-control @error('allowed_bookings_per_week') is-invalid @enderror" id="allowed_bookings_per_week" name="allowed_bookings_per_week" value="{{ old('allowed_bookings_per_week', $plan->allowed_bookings_per_week ?? '3') }}" min="0">
    @error('allowed_bookings_per_week')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Leave empty or set to 0 for unlimited</small>
</div>

<div class="form-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', isset($plan) ? $plan->is_active : true) ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">
            Active
        </label>
    </div>
</div>

<hr class="my-4">
<h5 class="mb-3"><i class="fas fa-tag text-warning"></i> Discount Offer (Optional)</h5>

<div class="form-group">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="has_discount" name="has_discount" value="1" {{ old('has_discount', isset($plan) ? $plan->has_discount : false) ? 'checked' : '' }}>
        <label class="form-check-label" for="has_discount">
            <strong>Enable Discount Offer</strong>
        </label>
    </div>
    <small class="form-text text-muted">Check this to create a special discount offer for this plan</small>
</div>

<div id="discount_fields" style="{{ old('has_discount', isset($plan) ? $plan->has_discount : false) ? '' : 'display: none;' }}">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="discount_percentage">Discount Percentage (%)</label>
                <input type="number" class="form-control @error('discount_percentage') is-invalid @enderror" id="discount_percentage" name="discount_percentage" value="{{ old('discount_percentage', $plan->discount_percentage ?? '') }}" step="0.01" min="0" max="100">
                @error('discount_percentage')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">e.g., 10 for 10% discount</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="discount_amount">Discount Amount ($)</label>
                <input type="number" class="form-control @error('discount_amount') is-invalid @enderror" id="discount_amount" name="discount_amount" value="{{ old('discount_amount', $plan->discount_amount ?? '') }}" step="0.01" min="0">
                @error('discount_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Fixed discount amount (optional if percentage is set)</small>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="discount_start_date">Discount Start Date</label>
                <input type="date" class="form-control @error('discount_start_date') is-invalid @enderror" id="discount_start_date" name="discount_start_date" value="{{ old('discount_start_date', isset($plan) && $plan->discount_start_date ? $plan->discount_start_date->format('Y-m-d') : '') }}">
                @error('discount_start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Leave empty to start immediately</small>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="discount_end_date">Discount End Date</label>
                <input type="date" class="form-control @error('discount_end_date') is-invalid @enderror" id="discount_end_date" name="discount_end_date" value="{{ old('discount_end_date', isset($plan) && $plan->discount_end_date ? $plan->discount_end_date->format('Y-m-d') : '') }}">
                @error('discount_end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="form-text text-muted">Leave empty for no expiry</small>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <label for="discount_description">Discount Description</label>
        <textarea class="form-control @error('discount_description') is-invalid @enderror" id="discount_description" name="discount_description" rows="2">{{ old('discount_description', $plan->discount_description ?? '') }}</textarea>
        @error('discount_description')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">Optional: Description of the discount offer (e.g., "New Year Special", "Limited Time Offer")</small>
    </div>
</div>

<div class="form-group mb-0">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ isset($plan) ? 'Update' : 'Create' }} Plan
        </button>
    </div>
</div>

<script>
(function() {
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function($) {
            // Show/hide bookings per week based on allows_class_booking checkbox
            $('#allows_class_booking').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#bookings_per_week_group').slideDown();
                } else {
                    $('#bookings_per_week_group').slideUp();
                }
            });
            
            // Show/hide discount fields based on has_discount checkbox
            $('#has_discount').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#discount_fields').slideDown();
                } else {
                    $('#discount_fields').slideUp();
                }
            });
        });
    }
})();
</script>

