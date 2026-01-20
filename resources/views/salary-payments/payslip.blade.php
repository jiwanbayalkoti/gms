{{-- Payslip Full Page View --}}
@extends('layouts.app')
@section('title', 'Payslip - Payment #' . $payment->id)
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-file-invoice"></i> Salary Payslip</h4>
                    <div>
                        <button onclick="window.print()" class="btn btn-light btn-sm">
                            <i class="fas fa-print"></i> Print
                        </button>
                        <a href="{{ route('salary-payments.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @include('salary-payments._payslip-content')
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media print {
        .card-header .btn,
        .card-header a {
            display: none !important;
        }
        
        .card {
            border: none;
            box-shadow: none;
        }
        
        body {
            background: white;
        }
        
        .container-fluid {
            padding: 0;
        }
    }
</style>
@endpush
@endsection

