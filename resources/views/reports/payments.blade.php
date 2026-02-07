@extends('layouts.app')

@section('title', 'Payments Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Payments Report</h2>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.payments') }}" id="paymentsReportFilterForm" class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-secondary btn-block" onclick="resetPaymentsReportFilters()">
                                    <i class="fas fa-redo"></i> Reset
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4" id="paymentsReportStats">
        @include('reports._payments-stats')
    </div>

    <!-- Payment Method Breakdown -->
    @if(isset($stats['by_method']) && $stats['by_method']->count() > 0)
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Payment Method Breakdown</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Payment Method</th>
                                    <th>Count</th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($stats['by_method'] as $method => $data)
                                    <tr>
                                        <td>{{ $method }}</td>
                                        <td>{{ $data['count'] }}</td>
                                        <td>${{ number_format($data['amount'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Payments Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Member</th>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Payment Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="paymentsReportTableBody">
                                @include('reports._payments-table-body')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded.');
        return;
    }
    
    var $ = jQuery;
    var filterTimeout;
    
    // Auto-filter payments report
    window.applyPaymentsReportFilters = function() {
        var formData = {
            start_date: $('#paymentsReportFilterForm #start_date').val(),
            end_date: $('#paymentsReportFilterForm #end_date').val(),
        };
        
        // Show loading
        $('#paymentsReportStats, #paymentsReportTableBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        $.ajax({
            url: '{{ route("reports.payments") }}',
            type: 'GET',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    $('#paymentsReportStats').html(response.statsHtml);
                    $('#paymentsReportTableBody').html(response.tableHtml);
                    
                    // Update URL
                    var url = new URL(window.location.href);
                    Object.keys(formData).forEach(function(key) {
                        if (formData[key]) {
                            url.searchParams.set(key, formData[key]);
                        } else {
                            url.searchParams.delete(key);
                        }
                    });
                    window.history.pushState({}, '', url);
                } else {
                    window.location.reload();
                }
            },
            error: function() {
                window.location.reload();
            }
        });
    };
    
    // Auto-filter on date change (change + input for better compatibility)
    $('#paymentsReportFilterForm #start_date, #paymentsReportFilterForm #end_date').on('change input', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            window.applyPaymentsReportFilters();
        }, 300);
    });
    
    // Reset filters
    window.resetPaymentsReportFilters = function() {
        $('#start_date').val('{{ now()->startOfMonth()->toDateString() }}');
        $('#end_date').val('{{ now()->endOfMonth()->toDateString() }}');
        window.history.pushState({}, '', '{{ route("reports.payments") }}');
        window.applyPaymentsReportFilters();
    };
})();
</script>
@endpush
@endsection
