{{-- Payslip Modal Content - No layout, just content --}}
@include('salary-payments._payslip-content')
<style>
    .payslip-content {
        padding: 15px;
    }
    @media print {
        .modal-header,
        .modal-footer {
            display: none !important;
        }
        
        body {
            background: white;
        }
    }
</style>

