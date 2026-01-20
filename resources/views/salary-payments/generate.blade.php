@extends('layouts.app')

@section('title', 'Generate Payroll')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Generate Payroll</h2>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Generate Salary Payment</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('salary-payments.store-generated') }}" method="POST" id="generateForm">
                        @csrf
                        
                        <div class="form-group">
                            <label for="salary_id">Employee Salary <span class="text-danger">*</span></label>
                            <select class="form-control @error('salary_id') is-invalid @enderror" id="salary_id" name="salary_id" required>
                                <option value="">Select Employee</option>
                                @foreach($salaries ?? [] as $salary)
                                    <option value="{{ $salary->id }}" 
                                        data-employee="{{ $salary->employee->name }}"
                                        data-type="{{ $salary->salary_type }}"
                                        {{ old('salary_id') == $salary->id ? 'selected' : '' }}>
                                        {{ $salary->employee->name }} - {{ ucfirst($salary->salary_type) }}
                                        @if($salary->salary_type === 'fixed')
                                            (${{ number_format($salary->base_salary, 2) }}/month)
                                        @elseif($salary->salary_type === 'hourly')
                                            (${{ number_format($salary->hourly_rate, 2) }}/hr)
                                        @elseif($salary->salary_type === 'commission')
                                            ({{ number_format($salary->commission_percentage, 2) }}%)
                                        @else
                                            (${{ number_format($salary->base_salary, 2) }} + {{ number_format($salary->commission_percentage, 2) }}%)
                                        @endif
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
                                    <label for="payment_period_start">Payment Period Start <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('payment_period_start') is-invalid @enderror" id="payment_period_start" name="payment_period_start" value="{{ old('payment_period_start', now()->startOfMonth()->toDateString()) }}" required>
                                    @error('payment_period_start')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="payment_period_end">Payment Period End <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('payment_period_end') is-invalid @enderror" id="payment_period_end" name="payment_period_end" value="{{ old('payment_period_end', now()->endOfMonth()->toDateString()) }}" required>
                                    @error('payment_period_end')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="bonus_amount">Bonus Amount ($)</label>
                                    <input type="number" class="form-control @error('bonus_amount') is-invalid @enderror" id="bonus_amount" name="bonus_amount" value="{{ old('bonus_amount', 0) }}" step="0.01" min="0">
                                    @error('bonus_amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="deductions">Deductions ($)</label>
                                    <input type="number" class="form-control @error('deductions') is-invalid @enderror" id="deductions" name="deductions" value="{{ old('deductions', 0) }}" step="0.01" min="0">
                                    @error('deductions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Note:</strong> The salary will be automatically calculated based on the employee's salary type and the selected payment period.
                            @if($salaries && $salaries->count() > 0)
                                Commission will be calculated from member payments during the selected period.
                            @endif
                        </div>

                        <div class="form-group mb-0">
                            <div class="d-flex justify-content-end">
                                <a href="{{ route('salary-payments.index') }}" class="btn btn-secondary mr-2">Cancel</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-calculator"></i> Generate Payroll
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

