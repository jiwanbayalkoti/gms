@extends('layouts.app')

@section('title', 'Salary Payments (Payroll)')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Salary Payments (Payroll)</h2>
                <div>
                    <button type="button" class="btn btn-success mr-2" data-toggle="modal" data-target="#generatePayrollModal">
                        <i class="fas fa-calculator"></i> Generate Payroll
                    </button>
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#manualPaymentModal">
                        <i class="fas fa-plus"></i> Manual Payment
                    </button>
                </div>
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

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('salary-payments.index') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-3">
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
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="payment_status">Status</label>
                                    <select class="form-control" id="payment_status" name="payment_status">
                                        <option value="">All Statuses</option>
                                        <option value="Pending" {{ request('payment_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="Paid" {{ request('payment_status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                                        <option value="Failed" {{ request('payment_status') === 'Failed' ? 'selected' : '' }}>Failed</option>
                                        <option value="Cancelled" {{ request('payment_status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <button type="button" class="btn btn-sm btn-secondary" onclick="resetFilters()">
                                    <i class="fas fa-redo"></i> Reset Filters
                                </button>
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
                        <table class="table table-striped table-hover" id="paymentsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Period</th>
                                    <th>Base Amount</th>
                                    <th>Commission</th>
                                    <th>Bonus</th>
                                    <th>Tax</th>
                                    <th>Deductions</th>
                                    <th>Net Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="paymentsTableBody">
                                @include('salary-payments._table-body')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate Payroll Modal -->
<div class="modal fade" id="generatePayrollModal" tabindex="-1" role="dialog" aria-labelledby="generatePayrollModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="generatePayrollModalLabel">
                    <i class="fas fa-calculator"></i> Generate Payroll
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="generatePayrollModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-success" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manual Payment Modal -->
<div class="modal fade" id="manualPaymentModal" tabindex="-1" role="dialog" aria-labelledby="manualPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="manualPaymentModalLabel">
                    <i class="fas fa-plus"></i> Manual Payment
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="manualPaymentModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Update Payment Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="updateStatusModalLabel">
                    <i class="fas fa-check-circle"></i> Mark Payment as Paid
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updateStatusForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="status_payment_id" name="payment_id">
                    <input type="hidden" name="payment_status" value="Paid">
                    
                    <div class="form-group">
                        <label for="status_payment_method">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-control" id="status_payment_method" name="payment_method" required>
                            <option value="">Select Payment Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer" selected>Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Online">Online</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status_payment_date">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="status_payment_date" name="payment_date" value="{{ now()->toDateString() }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="status_transaction_id">Transaction ID</label>
                        <input type="text" class="form-control" id="status_transaction_id" name="transaction_id" placeholder="Enter transaction ID (optional)">
                    </div>
                    
                    <div class="form-group">
                        <label for="status_payment_receipt">Payment Receipt / Payslip Photo</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="status_payment_receipt" name="payment_receipt" accept="image/*,.pdf">
                            <label class="custom-file-label" for="status_payment_receipt">Choose file (Image or PDF)</label>
                        </div>
                        <small class="form-text text-muted">Upload photo of payment receipt or payslip (JPG, PNG, or PDF)</small>
                        <div id="receipt_preview" class="mt-2" style="display: none;">
                            <img id="receipt_preview_img" src="" alt="Receipt Preview" class="img-thumbnail" style="max-height: 200px;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Payslip Modal -->
<div class="modal fade" id="payslipModal" tabindex="-1" role="dialog" aria-labelledby="payslipModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="payslipModalLabel">
                    <i class="fas fa-file-invoice"></i> Salary Payslip
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="payslipModalBody" style="max-height: 80vh; overflow-y: auto;">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" onclick="window.printPayslip()">
                    <i class="fas fa-print"></i> Print Payslip
                </button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function() {
    // Ensure jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded. Please check if jQuery is included in the layout.');
        return;
    }
    
    var $ = jQuery;
    
    $(document).ready(function() {
        // Auto-filter without page refresh
        var filterTimeout;
        
        // Auto-filter function (without page refresh)
        window.applyFilters = function() {
            var $ = jQuery;
            var formData = {
                employee_id: $('#employee_id').val(),
                payment_status: $('#payment_status').val(),
                start_date: $('#start_date').val(),
                end_date: $('#end_date').val(),
            };
            
            // Show loading indicator
            var $tableBody = $('#paymentsTableBody');
            $tableBody.html('<tr><td colspan="11" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
            
            $.ajax({
                url: '{{ route("salary-payments.index") }}',
                type: 'GET',
                data: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    // Update URL without refresh
                    var url = new URL(window.location.href);
                    Object.keys(formData).forEach(function(key) {
                        if (formData[key]) {
                            url.searchParams.set(key, formData[key]);
                        } else {
                            url.searchParams.delete(key);
                        }
                    });
                    window.history.pushState({}, '', url);
                    
                    // Update table body with new HTML
                    if (response.success && response.html) {
                        $tableBody.html(response.html);
                    } else {
                        // Fallback: reload page
                        window.location.reload();
                    }
                },
                error: function(xhr) {
                    console.error('Error filtering payments');
                    // On error, reload page
                    window.location.reload();
                }
            });
        };
        
        // Auto-filter on select change (immediate)
        $('#employee_id, #payment_status').on('change', function() {
            clearTimeout(filterTimeout);
            window.applyFilters();
        });
        
        // Auto-filter on date change with debounce (500ms delay)
        $('#start_date, #end_date').on('change', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                window.applyFilters();
            }, 500);
        });
        
        // Handle generate payroll modal
        $('#generatePayrollModal').on('show.bs.modal', function(event) {
            var modal = $(this);
            var modalBody = modal.find('#generatePayrollModalBody');
            
            modalBody.html('<div class="text-center py-4"><div class="spinner-border text-success" role="status"><span class="sr-only">Loading...</span></div></div>');
            
            $.ajax({
                url: '{{ route("salary-payments.generate") }}',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success && response.html) {
                        modalBody.html(response.html);
                    } else {
                        modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                    }
                },
                error: function(xhr) {
                    modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                }
            });
        });
        
        // Auto-fill period when staff is selected
        $(document).on('change', '#generate_salary_id', function() {
            var salaryId = $(this).val();
            var $alertDiv = $('#generateFormAlert');
            
            // Clear previous alerts
            $alertDiv.html('');
            
            if (!salaryId) {
                $('#generate_payment_period_start').val('');
                $('#generate_payment_period_end').val('');
                return;
            }
            
            $.ajax({
                url: '{{ url("salary-payments/get-next-period") }}/' + salaryId,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        $('#generate_payment_period_start').val(response.period_start);
                        $('#generate_payment_period_end').val(response.period_end);
                        // Clear any previous warnings
                        $alertDiv.html('');
                    } else {
                        $('#generate_payment_period_start').val('');
                        $('#generate_payment_period_end').val('');
                        // Show warning in modal
                        if (response.message) {
                            var alertHtml = '<div class="alert alert-warning alert-dismissible fade show" role="alert">' +
                                '<i class="fas fa-exclamation-triangle"></i> ' + response.message +
                                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                                '<span aria-hidden="true">&times;</span></button></div>';
                            $alertDiv.html(alertHtml);
                        }
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching next period');
                    var errorMessage = 'Error checking pending salary period.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    var alertHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                        '<i class="fas fa-exclamation-circle"></i> ' + errorMessage +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span></button></div>';
                    $alertDiv.html(alertHtml);
                }
            });
        });
        
        // Handle generate payroll form submission
        $(document).on('submit', '#generatePayrollForm', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            var originalText = $submitBtn.html();
            
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');
            
            $.ajax({
                url: '{{ route("salary-payments.store-generated") }}',
                type: 'POST',
                data: $form.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        window.showAlert('success', response.message || 'Payroll generated successfully.');
                        $('#generatePayrollModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        window.showAlert('danger', response.message || 'Error generating payroll.');
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    var message = 'Error generating payroll.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = Object.values(xhr.responseJSON.errors).flat();
                        message = errors.join('<br>');
                    }
                    window.showAlert('danger', message);
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
        
        // Handle manual payment modal
        $('#manualPaymentModal').on('show.bs.modal', function(event) {
            var modal = $(this);
            var modalBody = modal.find('#manualPaymentModalBody');
            
            modalBody.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
            
            $.ajax({
                url: '{{ route("salary-payments.create") }}',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success && response.html) {
                        modalBody.html(response.html);
                    } else {
                        modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                    }
                },
                error: function(xhr) {
                    modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                }
            });
        });
        
        // Handle manual payment form submission
        $(document).on('submit', '#manualPaymentForm', function(e) {
            e.preventDefault();
            var $form = $(this);
            var $submitBtn = $form.find('button[type="submit"]');
            var originalText = $submitBtn.html();
            
            $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
            
            $.ajax({
                url: '{{ route("salary-payments.store") }}',
                type: 'POST',
                data: $form.serialize(),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        window.showAlert('success', response.message || 'Payment created successfully.');
                        $('#manualPaymentModal').modal('hide');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        window.showAlert('danger', response.message || 'Error creating payment.');
                        $submitBtn.prop('disabled', false).html(originalText);
                    }
                },
                error: function(xhr) {
                    var message = 'Error creating payment.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        message = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        var errors = Object.values(xhr.responseJSON.errors).flat();
                        message = errors.join('<br>');
                    }
                    window.showAlert('danger', message);
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });
        
        // Handle payslip modal
        $('#payslipModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var paymentId = button.data('payment-id');
            var modal = $(this);
            var modalBody = modal.find('#payslipModalBody');
            
            // Check if button is disabled (not paid)
            if (button.prop('disabled')) {
                event.preventDefault();
                return false;
            }
            
            modalBody.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
            
            $.ajax({
                url: '{{ url("salary-payments") }}/' + paymentId + '/payslip',
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success && response.html) {
                        modalBody.html(response.html);
                    } else {
                        modalBody.html('<div class="alert alert-danger">Error loading payslip. Please try again.</div>');
                    }
                },
                error: function(xhr) {
                    var message = 'Error loading payslip.';
                    if (xhr.status === 403 || (xhr.responseJSON && xhr.responseJSON.message)) {
                        message = xhr.responseJSON.message || 'Payslip is only available for paid payments.';
                    }
                    modalBody.html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> ' + message + '</div>');
                }
            });
        });
    });
    
    window.printPayslip = function() {
        var $ = jQuery;
        var payslipContent = $('#payslipModalBody').html();
        
        // Create a new window for printing
        var printWindow = window.open('', '_blank', 'width=800,height=600');
        
        // Write the HTML content
        printWindow.document.write('<!DOCTYPE html>');
        printWindow.document.write('<html><head><title>Salary Payslip</title>');
        printWindow.document.write('<link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">');
        printWindow.document.write('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">');
        printWindow.document.write('<style>');
        printWindow.document.write('@media print {');
        printWindow.document.write('  body { margin: 0; padding: 20px; background: white; }');
        printWindow.document.write('  .payslip-content { padding: 0; }');
        printWindow.document.write('}');
        printWindow.document.write('body { padding: 20px; }');
        printWindow.document.write('.payslip-content { padding: 15px; }');
        printWindow.document.write('</style>');
        printWindow.document.write('</head><body>');
        printWindow.document.write(payslipContent);
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        
        // Wait for content to load, then print
        printWindow.onload = function() {
            setTimeout(function() {
                printWindow.print();
            }, 250);
        };
    };
    
    window.updatePaymentStatus = function(paymentId, status) {
        var $ = jQuery;
        
        // If marking as paid, show modal
        if (status === 'Paid') {
            $('#status_payment_id').val(paymentId);
            $('#status_payment_method').val('Bank Transfer');
            $('#status_payment_date').val('{{ now()->toDateString() }}');
            $('#status_transaction_id').val('');
            $('#status_payment_receipt').val('');
            $('#receipt_preview').hide();
            $('#updateStatusModal').modal('show');
            return;
        }
        
        // For other statuses, confirm and update directly
        if (!confirm('Change payment status to ' + status + '?')) {
            // Reset to original value
            location.reload();
            return;
        }
        
        var data = {
            payment_status: status,
            _token: '{{ csrf_token() }}'
        };
        
        $.ajax({
            url: '{{ url("salary-payments") }}/' + paymentId + '/update-status',
            type: 'POST',
            data: data,
            success: function(response) {
                if (response.success) {
                    window.showAlert('success', 'Payment status updated successfully.');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    window.showAlert('danger', response.message || 'Error updating payment status.');
                    location.reload();
                }
            },
            error: function(xhr) {
                var message = 'Error updating payment status.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                window.showAlert('danger', message);
                location.reload();
            }
        });
    };
    
    // Handle receipt preview
    $(document).on('change', '#status_payment_receipt', function() {
        var file = this.files[0];
        var $preview = $('#receipt_preview');
        var $previewImg = $('#receipt_preview_img');
        
        if (file && file.type.startsWith('image/')) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $previewImg.attr('src', e.target.result);
                $preview.show();
            };
            reader.readAsDataURL(file);
        } else {
            $preview.hide();
        }
        
        // Update file label
        var fileName = file ? file.name : 'Choose file';
        $(this).next('.custom-file-label').html(fileName);
    });
    
    // Handle update status form submission
    $(document).on('submit', '#updateStatusForm', function(e) {
        e.preventDefault();
        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        var originalText = $submitBtn.html();
        var paymentId = $('#status_payment_id').val();
        
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating...');
        
        var formData = new FormData($form[0]);
        
        $.ajax({
            url: '{{ url("salary-payments") }}/' + paymentId + '/update-status',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    window.showAlert('success', response.message || 'Payment status updated successfully.');
                    $('#updateStatusModal').modal('hide');
                    setTimeout(function() {
                        location.reload();
                    }, 1000);
                } else {
                    window.showAlert('danger', response.message || 'Error updating payment status.');
                    $submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                var message = 'Error updating payment status.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = Object.values(xhr.responseJSON.errors).flat();
                    message = errors.join('<br>');
                }
                window.showAlert('danger', message);
                $submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    window.showAlert = function(type, message) {
        // Use JavaScript alert box for all messages
        alert(message);
    };
    
    // Reset filters function
    window.resetFilters = function() {
        $('#employee_id').val('');
        $('#payment_status').val('');
        $('#start_date').val('');
        $('#end_date').val('');
        // Update URL
        window.history.pushState({}, '', '{{ route("salary-payments.index") }}');
        // Apply filters (which will load all data)
        window.applyFilters();
    };
})();
</script>
@endpush
@endsection

