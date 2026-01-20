{{-- Manual Payment Form - For Modal --}}
<form id="manualPaymentForm" method="POST">
    @csrf
    
    <div class="form-group">
        <label for="manual_salary_id">Employee Salary <span class="text-danger">*</span></label>
        <select class="form-control @error('salary_id') is-invalid @enderror" id="manual_salary_id" name="salary_id" required>
            <option value="">Select Employee</option>
            @foreach($salaries ?? [] as $salary)
                <option value="{{ $salary->id }}" 
                    data-employee="{{ $salary->employee->name }}"
                    {{ old('salary_id') == $salary->id ? 'selected' : '' }}>
                    {{ $salary->employee->name }} - {{ ucfirst($salary->salary_type) }}
                </option>
            @endforeach
        </select>
        @error('salary_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="manual_payment_period_start">Payment Period Start <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('payment_period_start') is-invalid @enderror" id="manual_payment_period_start" name="payment_period_start" value="{{ old('payment_period_start') }}" required>
                @error('payment_period_start')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="manual_payment_period_end">Payment Period End <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('payment_period_end') is-invalid @enderror" id="manual_payment_period_end" name="payment_period_end" value="{{ old('payment_period_end') }}" required>
                @error('payment_period_end')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="manual_base_amount">Base Amount (NPR) <span class="text-danger">*</span></label>
                <input type="number" class="form-control @error('base_amount') is-invalid @enderror" id="manual_base_amount" name="base_amount" value="{{ old('base_amount', 0) }}" step="0.01" min="0" required>
                @error('base_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="manual_commission_amount">Commission Amount (NPR)</label>
                <input type="number" class="form-control @error('commission_amount') is-invalid @enderror" id="manual_commission_amount" name="commission_amount" value="{{ old('commission_amount', 0) }}" step="0.01" min="0">
                @error('commission_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="manual_bonus_amount">Bonus Amount (NPR)</label>
                <input type="number" class="form-control @error('bonus_amount') is-invalid @enderror" id="manual_bonus_amount" name="bonus_amount" value="{{ old('bonus_amount', 0) }}" step="0.01" min="0">
                @error('bonus_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="manual_deductions">Deductions (NPR)</label>
                <input type="number" class="form-control @error('deductions') is-invalid @enderror" id="manual_deductions" name="deductions" value="{{ old('deductions', 0) }}" step="0.01" min="0">
                @error('deductions')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="manual_net_amount">Net Amount (NPR) <span class="text-danger">*</span></label>
        <input type="number" class="form-control @error('net_amount') is-invalid @enderror" id="manual_net_amount" name="net_amount" value="{{ old('net_amount', 0) }}" step="0.01" min="0" required>
        @error('net_amount')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="manual_payment_method">Payment Method <span class="text-danger">*</span></label>
                <select class="form-control @error('payment_method') is-invalid @enderror" id="manual_payment_method" name="payment_method" required>
                    <option value="">Select Payment Method</option>
                    <option value="Cash" {{ old('payment_method') == 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Bank Transfer" {{ old('payment_method') == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="Cheque" {{ old('payment_method') == 'Cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="Online" {{ old('payment_method') == 'Online' ? 'selected' : '' }}>Online</option>
                </select>
                @error('payment_method')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="manual_payment_status">Payment Status <span class="text-danger">*</span></label>
                <select class="form-control @error('payment_status') is-invalid @enderror" id="manual_payment_status" name="payment_status" required>
                    <option value="Pending" {{ old('payment_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Paid" {{ old('payment_status') == 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Failed" {{ old('payment_status') == 'Failed' ? 'selected' : '' }}>Failed</option>
                    <option value="Cancelled" {{ old('payment_status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
                @error('payment_status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="manual_payment_date">Payment Date</label>
                <input type="date" class="form-control @error('payment_date') is-invalid @enderror" id="manual_payment_date" name="payment_date" value="{{ old('payment_date') }}">
                @error('payment_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="manual_transaction_id">Transaction ID</label>
                <input type="text" class="form-control @error('transaction_id') is-invalid @enderror" id="manual_transaction_id" name="transaction_id" value="{{ old('transaction_id') }}" maxlength="255">
                @error('transaction_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="manual_notes">Notes</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" id="manual_notes" name="notes" rows="3">{{ old('notes') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-0">
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Create Payment
            </button>
        </div>
    </div>
</form>

