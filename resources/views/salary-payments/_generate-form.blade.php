{{-- Generate Payroll Form - For Modal --}}
<div id="generateFormAlert"></div>
<form id="generatePayrollForm" method="POST">
    @csrf
    
    <div class="form-group">
        <label for="generate_salary_id">Employee Salary <span class="text-danger">*</span></label>
        <select class="form-control @error('salary_id') is-invalid @enderror" id="generate_salary_id" name="salary_id" required>
            <option value="">Select Employee</option>
            @foreach($salaries ?? [] as $salary)
                <option value="{{ $salary->id }}" 
                    data-employee="{{ $salary->employee->name }}"
                    data-type="{{ $salary->salary_type }}"
                    {{ old('salary_id') == $salary->id ? 'selected' : '' }}>
                    {{ $salary->employee->name }} - {{ ucfirst($salary->salary_type) }}
                    @if($salary->salary_type === 'fixed')
                        (NPR {{ number_format($salary->base_salary, 2) }}/month)
                    @elseif($salary->salary_type === 'hourly')
                        (NPR {{ number_format($salary->hourly_rate, 2) }}/hr)
                    @elseif($salary->salary_type === 'commission')
                        ({{ number_format($salary->commission_percentage, 2) }}%)
                    @else
                        (NPR {{ number_format($salary->base_salary, 2) }} + {{ number_format($salary->commission_percentage, 2) }}%)
                    @endif
                </option>
            @endforeach
        </select>
        @error('salary_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">Select employee to auto-fill the period</small>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="generate_payment_period_start">Payment Period Start <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('payment_period_start') is-invalid @enderror" id="generate_payment_period_start" name="payment_period_start" value="{{ old('payment_period_start', now()->startOfMonth()->toDateString()) }}" required>
                @error('payment_period_start')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="generate_payment_period_end">Payment Period End <span class="text-danger">*</span></label>
                <input type="date" class="form-control @error('payment_period_end') is-invalid @enderror" id="generate_payment_period_end" name="payment_period_end" value="{{ old('payment_period_end', now()->endOfMonth()->toDateString()) }}" required>
                @error('payment_period_end')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="generate_bonus_amount">Bonus Amount (NPR)</label>
                <input type="number" class="form-control @error('bonus_amount') is-invalid @enderror" id="generate_bonus_amount" name="bonus_amount" value="{{ old('bonus_amount', 0) }}" step="0.01" min="0">
                @error('bonus_amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="generate_deductions">Deductions (NPR)</label>
                <input type="number" class="form-control @error('deductions') is-invalid @enderror" id="generate_deductions" name="deductions" value="{{ old('deductions', 0) }}" step="0.01" min="0">
                @error('deductions')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="generate_notes">Notes</label>
        <textarea class="form-control @error('notes') is-invalid @enderror" id="generate_notes" name="notes" rows="3">{{ old('notes') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> 
        <strong>Note:</strong> The salary will be automatically calculated based on the employee's salary type and the selected payment period. Payments will be generated separately for each month in the period (e.g., 1/1, 2/1, etc.). Tax will be calculated automatically based on employee's marital status (Nepal tax rules).
        @if($salaries && $salaries->count() > 0)
            Commission will be calculated from member payments during the selected period.
        @endif
    </div>

    <div class="form-group mb-0">
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-calculator"></i> Generate Payroll
            </button>
        </div>
    </div>
</form>

