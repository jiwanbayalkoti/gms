@extends('layouts.app')

@section('title', 'Classes Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Classes Report</h2>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.classes') }}" id="classesReportFilterForm" class="row">
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
                                <button type="button" class="btn btn-secondary btn-block" onclick="resetClassesReportFilters()">
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
    <div class="row mb-4" id="classesReportStats">
        @include('reports._classes-stats')
    </div>

    <!-- Classes Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Trainer</th>
                                    <th>Start Time</th>
                                    <th>Capacity</th>
                                    <th>Bookings</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="classesReportTableBody">
                                @include('reports._classes-table-body')
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
    
    // Auto-filter classes report
    window.applyClassesReportFilters = function() {
        var formData = {
            start_date: $('#classesReportFilterForm #start_date').val(),
            end_date: $('#classesReportFilterForm #end_date').val(),
        };
        
        // Show loading
        $('#classesReportStats, #classesReportTableBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        $.ajax({
            url: '{{ route("reports.classes") }}',
            type: 'GET',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    $('#classesReportStats').html(response.statsHtml);
                    $('#classesReportTableBody').html(response.tableHtml);
                    
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
    $('#classesReportFilterForm #start_date, #classesReportFilterForm #end_date').on('change input', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            window.applyClassesReportFilters();
        }, 300);
    });
    
    // Reset filters
    window.resetClassesReportFilters = function() {
        $('#start_date').val('{{ now()->startOfMonth()->toDateString() }}');
        $('#end_date').val('{{ now()->endOfMonth()->toDateString() }}');
        window.history.pushState({}, '', '{{ route("reports.classes") }}');
        window.applyClassesReportFilters();
    };
})();
</script>
@endpush
@endsection
