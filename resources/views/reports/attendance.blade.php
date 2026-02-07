@extends('layouts.app')

@section('title', 'Attendance Report')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Attendance Report</h2>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('reports.attendance') }}" id="attendanceReportFilterForm" class="row">
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
                                <button type="button" class="btn btn-secondary btn-block" onclick="resetAttendanceReportFilters()">
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
    <div class="row mb-4" id="attendanceReportStats">
        @include('reports._attendance-stats')
    </div>

    <!-- Attendance Table -->
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
                                    <th>Class</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Duration</th>
                                </tr>
                            </thead>
                            <tbody id="attendanceReportTableBody">
                                @include('reports._attendance-table-body')
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
    
    // Auto-filter attendance report
    window.applyAttendanceReportFilters = function() {
        var formData = {
            start_date: $('#attendanceReportFilterForm #start_date').val(),
            end_date: $('#attendanceReportFilterForm #end_date').val(),
        };
        
        // Show loading
        $('#attendanceReportStats, #attendanceReportTableBody').html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        $.ajax({
            url: '{{ route("reports.attendance") }}',
            type: 'GET',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    $('#attendanceReportStats').html(response.statsHtml);
                    $('#attendanceReportTableBody').html(response.tableHtml);
                    
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
    $('#attendanceReportFilterForm #start_date, #attendanceReportFilterForm #end_date').on('change input', function() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(function() {
            window.applyAttendanceReportFilters();
        }, 300);
    });
    
    // Reset filters
    window.resetAttendanceReportFilters = function() {
        $('#start_date').val('{{ now()->startOfMonth()->toDateString() }}');
        $('#end_date').val('{{ now()->endOfMonth()->toDateString() }}');
        window.history.pushState({}, '', '{{ route("reports.attendance") }}');
        window.applyAttendanceReportFilters();
    };
})();
</script>
@endpush
@endsection
