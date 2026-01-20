{{-- Payment Form Partial --}}
@csrf

@if(isset($payment))
    @method('PUT')
@endif

<div class="form-group">
    <label for="member_id">Member <span class="text-danger">*</span></label>
    <select class="form-control @error('member_id') is-invalid @enderror" id="member_id" name="member_id" required>
        <option value="">Select Member</option>
        @foreach($members ?? [] as $member)
            <option value="{{ $member->id }}" {{ old('member_id', $payment->member_id ?? '') == $member->id ? 'selected' : '' }}>
                {{ $member->name }} ({{ $member->email }})
            </option>
        @endforeach
    </select>
    @error('member_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="membership_plan_id">Membership Plan (Optional)</label>
    <select class="form-control @error('membership_plan_id') is-invalid @enderror" id="membership_plan_id" name="membership_plan_id">
        <option value="">No Plan</option>
        @php
            // Separate plans with active discounts
            $discountPlans = collect($plans ?? [])->filter(function($plan) {
                return $plan->isDiscountActive();
            });
            $regularPlans = collect($plans ?? [])->filter(function($plan) {
                return !$plan->isDiscountActive();
            });
        @endphp
        
        @if($discountPlans->count() > 0)
            <optgroup label="🔥 Special Offers (Discount Available)">
                @foreach($discountPlans as $plan)
                    @php
                        $discountedPrice = $plan->getDiscountedPrice();
                        $discountAmount = $plan->getDiscountAmount();
                        $discountPercent = $plan->discount_percentage > 0 ? $plan->discount_percentage : (($discountAmount / $plan->price) * 100);
                    @endphp
                    <option value="{{ $plan->id }}" 
                        data-duration="{{ $plan->duration_days }}"
                        data-price="{{ $discountedPrice }}"
                        data-original-price="{{ $plan->price }}"
                        data-discount="{{ $discountAmount }}"
                        {{ old('membership_plan_id', $payment->membership_plan_id ?? '') == $plan->id ? 'selected' : '' }}>
                        🔥 {{ $plan->name }} - ${{ number_format($discountedPrice, 2) }} 
                        <del style="color: #999;">${{ number_format($plan->price, 2) }}</del> 
                        ({{ number_format($discountPercent, 0) }}% OFF - {{ $plan->duration_days }} days)
                    </option>
                @endforeach
            </optgroup>
        @endif
        
        @if($regularPlans->count() > 0)
            <optgroup label="Regular Plans">
                @foreach($regularPlans as $plan)
                    <option value="{{ $plan->id }}" 
                        data-duration="{{ $plan->duration_days }}"
                        data-price="{{ $plan->price }}"
                        data-original-price="{{ $plan->price }}"
                        data-discount="0"
                        {{ old('membership_plan_id', $payment->membership_plan_id ?? '') == $plan->id ? 'selected' : '' }}>
                        {{ $plan->name }} - ${{ number_format($plan->price, 2) }} ({{ $plan->duration_days }} days)
                    </option>
                @endforeach
            </optgroup>
        @endif
    </select>
    @error('membership_plan_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Selecting a plan will auto-fill amount and expiry date</small>
    
    @if($discountPlans->count() > 0)
        <div class="alert alert-info mt-2 mb-0" style="font-size: 0.875rem;">
            <i class="fas fa-tag"></i> <strong>Special Offers Available!</strong> 
            {{ $discountPlans->count() }} plan(s) with active discounts. Discounted prices will be automatically applied.
        </div>
    @endif
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="amount">Amount ($) <span class="text-danger">*</span></label>
            <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $payment->amount ?? '') }}" step="0.01" min="0" required>
            @error('amount')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="payment_method">Payment Method <span class="text-danger">*</span></label>
            <select class="form-control @error('payment_method') is-invalid @enderror" id="payment_method" name="payment_method" required>
                <option value="Cash" {{ old('payment_method', $payment->payment_method ?? '') === 'Cash' ? 'selected' : '' }}>Cash</option>
                <option value="Card" {{ old('payment_method', $payment->payment_method ?? '') === 'Card' ? 'selected' : '' }}>Card</option>
                <option value="Stripe" {{ old('payment_method', $payment->payment_method ?? '') === 'Stripe' ? 'selected' : '' }}>Stripe</option>
                <option value="PayPal" {{ old('payment_method', $payment->payment_method ?? '') === 'PayPal' ? 'selected' : '' }}>PayPal</option>
                <option value="Bank Transfer" {{ old('payment_method', $payment->payment_method ?? '') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
            </select>
            @error('payment_method')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="transaction_id">Transaction ID</label>
    <input type="text" class="form-control @error('transaction_id') is-invalid @enderror" id="transaction_id" name="transaction_id" value="{{ old('transaction_id', $payment->transaction_id ?? '') }}">
    @error('transaction_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">For online payments (Stripe, PayPal, etc.)</small>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="payment_status">Payment Status <span class="text-danger">*</span></label>
            <select class="form-control @error('payment_status') is-invalid @enderror" id="payment_status" name="payment_status" required>
                <option value="Completed" {{ old('payment_status', $payment->payment_status ?? 'Completed') === 'Completed' ? 'selected' : '' }}>Completed</option>
                <option value="Failed" {{ old('payment_status', $payment->payment_status ?? '') === 'Failed' ? 'selected' : '' }}>Failed</option>
                <option value="Refunded" {{ old('payment_status', $payment->payment_status ?? '') === 'Refunded' ? 'selected' : '' }}>Refunded</option>
            </select>
            @error('payment_status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="payment_date">Payment Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control @error('payment_date') is-invalid @enderror" id="payment_date" name="payment_date" value="{{ old('payment_date', isset($payment) ? $payment->payment_date->format('Y-m-d') : now()->toDateString()) }}" required>
            @error('payment_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label for="expiry_date">Expiry Date</label>
    <input type="date" class="form-control @error('expiry_date') is-invalid @enderror" id="expiry_date" name="expiry_date" value="{{ old('expiry_date', isset($payment) && $payment->expiry_date ? $payment->expiry_date->format('Y-m-d') : '') }}">
    @error('expiry_date')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Auto-filled if membership plan is selected</small>
</div>

<div class="form-group">
    <label for="notes">Notes</label>
    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $payment->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group mb-0">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ isset($payment) ? 'Update' : 'Create' }} Payment
        </button>
    </div>
</div>

<script>
(function() {
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function($) {
            // Auto-fill amount and expiry date when plan is selected
            $('#membership_plan_id').on('change', function() {
                var selectedOption = $(this).find('option:selected');
                var price = selectedOption.data('price');
                var originalPrice = selectedOption.data('original-price');
                var discount = selectedOption.data('discount');
                var duration = selectedOption.data('duration');
                
                if (price) {
                    // Always use discounted price if available
                    $('#amount').val(price);
                    
                    // Show discount info if applicable
                    if (discount > 0 && originalPrice) {
                        var discountInfo = 'Original: $' + parseFloat(originalPrice).toFixed(2) + 
                                          ' | Discount: $' + parseFloat(discount).toFixed(2) + 
                                          ' | Final: $' + parseFloat(price).toFixed(2);
                        // You can display this in a tooltip or info box
                        console.log('Discount Applied: ' + discountInfo);
                    }
                }
                
                if (duration && $('#expiry_date').val() === '') {
                    var paymentDate = $('#payment_date').val() || new Date().toISOString().split('T')[0];
                    var expiryDate = new Date(paymentDate);
                    expiryDate.setDate(expiryDate.getDate() + parseInt(duration));
                    $('#expiry_date').val(expiryDate.toISOString().split('T')[0]);
                }
            });
        });
    }
})();
</script>

