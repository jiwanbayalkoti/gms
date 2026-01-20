@extends('layouts.app')

@section('title', 'Salaries')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Salaries</h2>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#salaryModal" data-action="create">
                    <i class="fas fa-plus"></i> Add New Salary
                </button>
            </div>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('salaries.index') }}" id="salariesFilterForm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label for="employee_id">Employee</label>
                                    <select class="form-control" id="employee_id" name="employee_id">
                                        <option value="">All Employees</option>
                                        @foreach($employees ?? [] as $employee)
                                            <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                                {{ $employee->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status">
                                        <option value="">All Status</option>
                                        <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                                        <option value="Inactive" {{ request('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-secondary btn-block" onclick="resetSalariesFilters()">
                                        <i class="fas fa-redo"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="salariesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Salary Type</th>
                                    <th>Base Salary</th>
                                    <th>Hourly Rate</th>
                                    <th>Commission %</th>
                                    <th>Frequency</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="salariesTableBody">
                                @include('salaries._table-body')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Salary Modal -->
<div class="modal fade" id="salaryModal" tabindex="-1" role="dialog" aria-labelledby="salaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salaryModalLabel">Salary</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="salaryModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Salary Modal -->
<div class="modal fade" id="viewSalaryModal" tabindex="-1" role="dialog" aria-labelledby="viewSalaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSalaryModalLabel">Salary Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewSalaryModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('partials.delete-confirm-modal')
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Handle modal open
    $('#salaryModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var action = button.data('action');
        var salaryId = button.data('salary-id');
        var modal = $(this);
        var modalBody = modal.find('#salaryModalBody');
        var modalLabel = modal.find('#salaryModalLabel');
        
        modalBody.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        if (action === 'create') {
            modalLabel.text('Create Salary');
            $.get('{{ route("salaries.create") }}', function(data) {
                modalBody.html(data);
            });
        } else if (action === 'edit' && salaryId) {
            modalLabel.text('Edit Salary');
            $.get('{{ url("salaries") }}/' + salaryId + '/edit', function(data) {
                modalBody.html(data);
            });
        }
    });

    // Handle view modal
    $('#viewSalaryModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget);
        var salaryId = button.data('salary-id');
        var modal = $(this);
        var modalBody = modal.find('#viewSalaryModalBody');
        
        modalBody.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        $.get('{{ url("salaries") }}/' + salaryId, function(data) {
            if (data.html) {
                modalBody.html(data.html);
            } else {
                modalBody.html(data);
            }
        });
    });
});

function toggleSalaryStatus(id) {
    if (!confirm('Are you sure you want to toggle the status of this salary?')) {
        return;
    }
    
    $.ajax({
        url: '{{ url("salaries") }}/' + id + '/toggle-status',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                location.reload();
            }
        },
        error: function(xhr) {
            alert('Error updating salary status.');
        }
    });
}

// Auto-filter for salaries
(function() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    
    var $ = jQuery;
    
    // Auto-filter function
    window.applySalariesFilters = function() {
        var formData = {
            employee_id: $('#employee_id').val(),
            status: $('#status').val(),
        };
        
        // Show loading
        var $tableBody = $('#salariesTableBody');
        $tableBody.html('<tr><td colspan="9" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        $.ajax({
            url: '{{ route("salaries.index") }}',
            type: 'GET',
            data: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
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
                
                // Update table body
                if (response.success && response.html) {
                    $tableBody.html(response.html);
                } else {
                    window.location.reload();
                }
            },
            error: function() {
                window.location.reload();
            }
        });
    };
    
    $(document).ready(function() {
        // Auto-filter on select change (immediate)
        $('#employee_id, #status').on('change', function() {
            window.applySalariesFilters();
        });
        
        // Reset filters
        window.resetSalariesFilters = function() {
            $('#employee_id').val('');
            $('#status').val('');
            window.history.pushState({}, '', '{{ route("salaries.index") }}');
            window.applySalariesFilters();
        };
    });
})();
</script>
@endpush

