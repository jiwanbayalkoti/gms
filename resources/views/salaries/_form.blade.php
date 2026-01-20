{{-- Salary Form Partial --}}
@csrf

@if(isset($salary))
    @method('PUT')
@endif

<div class="form-group">
    <label for="employee_id">Employee <span class="text-danger">*</span></label>
    <select class="form-control @error('employee_id') is-invalid @enderror" id="employee_id" name="employee_id" required>
        <option value="">Select Employee</option>
        @foreach($employees ?? [] as $employee)
            <option value="{{ $employee->id }}" {{ old('employee_id', $salary->employee_id ?? '') == $employee->id ? 'selected' : '' }}>
                {{ $employee->name }} ({{ $employee->role }})
            </option>
        @endforeach
    </select>
    @error('employee_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="form-text text-muted">Select Trainer or Staff member</small>
</div>

<div class="form-group">
    <label for="salary_type">Salary Type <span class="text-danger">*</span></label>
    <select class="form-control @error('salary_type') is-invalid @enderror" id="salary_type" name="salary_type" required>
        <option value="fixed" {{ old('salary_type', $salary->salary_type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed (Monthly)</option>
        <option value="hourly" {{ old('salary_type', $salary->salary_type ?? '') === 'hourly' ? 'selected' : '' }}>Hourly</option>
        <option value="commission" {{ old('salary_type', $salary->salary_type ?? '') === 'commission' ? 'selected' : '' }}>Commission</option>
        <option value="hybrid" {{ old('salary_type', $salary->salary_type ?? '') === 'hybrid' ? 'selected' : '' }}>Hybrid (Base + Commission)</option>
    </select>
    @error('salary_type')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="row" id="fixed_salary_group" style="{{ old('salary_type', $salary->salary_type ?? 'fixed') === 'fixed' || old('salary_type', $salary->salary_type ?? '') === 'hybrid' ? '' : 'display: none;' }}">
    <div class="col-md-12">
        <div class="form-group">
            <label for="base_salary">Base Salary ($) <span class="text-danger" id="base_salary_required">*</span></label>
            <input type="number" class="form-control @error('base_salary') is-invalid @enderror" id="base_salary" name="base_salary" value="{{ old('base_salary', $salary->base_salary ?? '') }}" step="0.01" min="0">
            @error('base_salary')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Monthly base salary amount</small>
        </div>
    </div>
</div>

<div class="row" id="hourly_rate_group" style="{{ old('salary_type', $salary->salary_type ?? '') === 'hourly' || old('salary_type', $salary->salary_type ?? '') === 'hybrid' ? '' : 'display: none;' }}">
    <div class="col-md-12">
        <div class="form-group">
            <label for="hourly_rate">Hourly Rate ($) <span class="text-danger" id="hourly_rate_required">*</span></label>
            <input type="number" class="form-control @error('hourly_rate') is-invalid @enderror" id="hourly_rate" name="hourly_rate" value="{{ old('hourly_rate', $salary->hourly_rate ?? '') }}" step="0.01" min="0">
            @error('hourly_rate')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Rate per hour worked</small>
        </div>
    </div>
</div>

<div class="row" id="commission_group" style="{{ old('salary_type', $salary->salary_type ?? '') === 'commission' || old('salary_type', $salary->salary_type ?? '') === 'hybrid' ? '' : 'display: none;' }}">
    <div class="col-md-12">
        <div class="form-group">
            <label for="commission_percentage">Commission Percentage (%) <span class="text-danger" id="commission_required">*</span></label>
            <input type="number" class="form-control @error('commission_percentage') is-invalid @enderror" id="commission_percentage" name="commission_percentage" value="{{ old('commission_percentage', $salary->commission_percentage ?? '') }}" step="0.01" min="0" max="100">
            @error('commission_percentage')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Percentage of revenue/sales</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="payment_frequency">Payment Frequency <span class="text-danger">*</span></label>
            <select class="form-control @error('payment_frequency') is-invalid @enderror" id="payment_frequency" name="payment_frequency" required>
                <option value="monthly" {{ old('payment_frequency', $salary->payment_frequency ?? 'monthly') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                <option value="weekly" {{ old('payment_frequency', $salary->payment_frequency ?? '') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                <option value="bi-weekly" {{ old('payment_frequency', $salary->payment_frequency ?? '') === 'bi-weekly' ? 'selected' : '' }}>Bi-weekly</option>
                <option value="daily" {{ old('payment_frequency', $salary->payment_frequency ?? '') === 'daily' ? 'selected' : '' }}>Daily</option>
            </select>
            @error('payment_frequency')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="status">Status <span class="text-danger">*</span></label>
            <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
                <option value="active" {{ old('status', $salary->status ?? 'active') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ old('status', $salary->status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="terminated" {{ old('status', $salary->status ?? '') === 'terminated' ? 'selected' : '' }}>Terminated</option>
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="start_date">Start Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date', isset($salary) && $salary->start_date ? $salary->start_date->format('Y-m-d') : now()->toDateString()) }}" required>
            @error('start_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="end_date">End Date</label>
            <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date', isset($salary) && $salary->end_date ? $salary->end_date->format('Y-m-d') : '') }}">
            @error('end_date')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Leave empty if ongoing</small>
        </div>
    </div>
</div>

<div class="form-group">
    <label for="notes">Notes</label>
    <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $salary->notes ?? '') }}</textarea>
    @error('notes')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group mb-0">
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> {{ isset($salary) ? 'Update' : 'Create' }} Salary
        </button>
    </div>
</div>

<script>
(function() {
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function($) {
            // Show/hide fields based on salary type
            function toggleSalaryFields() {
                var salaryType = $('#salary_type').val();
                
                // Hide all groups first
                $('#fixed_salary_group, #hourly_rate_group, #commission_group').hide();
                $('#base_salary_required, #hourly_rate_required, #commission_required').hide();
                
                // Show relevant groups
                if (salaryType === 'fixed') {
                    $('#fixed_salary_group').show();
                    $('#base_salary_required').show();
                } else if (salaryType === 'hourly') {
                    $('#hourly_rate_group').show();
                    $('#hourly_rate_required').show();
                } else if (salaryType === 'commission') {
                    $('#commission_group').show();
                    $('#commission_required').show();
                } else if (salaryType === 'hybrid') {
                    $('#fixed_salary_group').show();
                    $('#commission_group').show();
                    $('#base_salary_required').show();
                    $('#commission_required').show();
                }
            }
            
            $('#salary_type').on('change', toggleSalaryFields);
            
            // Run on page load
            toggleSalaryFields();
        });
    }
})();
</script>

