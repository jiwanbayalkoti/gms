@extends('layouts.app')

@section('title', 'Payments')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Payments</h2>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#paymentModal" data-action="create">
                    <i class="fas fa-plus"></i> New Payment
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

    <!-- Filters -->
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('payments.index') }}" id="paymentsFilterForm">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label for="member_id">Member</label>
                                    <select class="form-control" id="member_id" name="member_id">
                                        <option value="">All Members</option>
                                        @foreach($members ?? [] as $member)
                                            <option value="{{ $member->id }}" {{ request('member_id') == $member->id ? 'selected' : '' }}>
                                                {{ $member->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label for="payment_status">Status</label>
                                    <select class="form-control" id="payment_status" name="payment_status">
                                        <option value="">All Status</option>
                                        <option value="Completed" {{ request('payment_status') === 'Completed' ? 'selected' : '' }}>Completed</option>
                                        <option value="Failed" {{ request('payment_status') === 'Failed' ? 'selected' : '' }}>Failed</option>
                                        <option value="Refunded" {{ request('payment_status') === 'Refunded' ? 'selected' : '' }}>Refunded</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label for="start_date">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label for="end_date">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group mb-0">
                                    <label>&nbsp;</label>
                                    <button type="button" class="btn btn-secondary btn-block" onclick="resetPaymentsFilters()">
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
                        <table class="table table-striped table-hover" id="paymentsTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Member</th>
                                    <th>Plan</th>
                                    <th>Amount</th>
                                    <th>Payment Method</th>
                                    <th>Payment Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="paymentsTableBody">
                                @include('payments._table-body')
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add/Edit Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <form id="paymentForm" method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="paymentModalLabel">New Payment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="paymentModalBody">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Payment Modal -->
<div class="modal fade" id="viewPaymentModal" tabindex="-1" role="dialog" aria-labelledby="viewPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="viewPaymentModalLabel">Payment Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="viewPaymentModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-info" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Modal -->
<div class="modal fade" id="invoiceModal" tabindex="-1" role="dialog" aria-labelledby="invoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="invoiceModalLabel">Invoice</h5>
                <div>
                    <button type="button" class="btn btn-sm btn-primary" onclick="printInvoice()">
                        <i class="fas fa-print"></i> Print
                    </button>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>
            <div class="modal-body" id="invoiceModalBody">
                <!-- Invoice will be loaded here via AJAX -->
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    
    function initWhenReady() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initWhenReady, 100);
            return;
        }
        
        var $ = jQuery;
        
        $(document).ready(function() {
            // Handle Add/Edit Modal
            $('#paymentModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var action = button.data('action');
                var paymentId = button.data('payment-id');
                var modal = $(this);
                var modalBody = modal.find('#paymentModalBody');
                
                if (action === 'create') {
                    modal.find('#paymentModalLabel').text('New Payment');
                } else {
                    modal.find('#paymentModalLabel').text('Edit Payment');
                }
                
                var url = action === 'create' 
                    ? '{{ route("payments.create") }}' 
                    : '{{ route("payments.edit", ":id") }}'.replace(':id', paymentId);
                
                modalBody.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.ajax({
                    url: url,
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    },
                    success: function(response) {
                        var formHtml = response.trim();
                        if (formHtml.includes('<form')) {
                            var $temp = $('<div>').html(response);
                            formHtml = $temp.find('form').html() || response;
                        }
                        
                        modalBody.html(formHtml);
                        
                        var formAction = action === 'create' 
                            ? '{{ route("payments.store") }}' 
                            : '{{ route("payments.update", ":id") }}'.replace(':id', paymentId);
                        
                        $('#paymentForm').attr('action', formAction);
                        
                        if (action === 'edit') {
                            $('#paymentForm').find('input[name="_method"]').remove();
                            $('#paymentForm').append('<input type="hidden" name="_method" value="PUT">');
                        }
                        
                        if (!$('#paymentForm').find('input[name="_token"]').length) {
                            $('#paymentForm').prepend('<input type="hidden" name="_token" value="{{ csrf_token() }}">');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading form:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading form. Please try again.</div>');
                    }
                });
            });
            
            // Handle form submission
            $(document).on('submit', '#paymentForm', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var formData = new FormData(this);
                var submitBtn = form.find('button[type="submit"]');
                var originalBtnText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                
                form.find('.is-invalid').removeClass('is-invalid');
                form.find('.invalid-feedback').remove();
                form.find('.alert').remove();
                
                var method = form.find('input[name="_method"]').val();
                var ajaxType = 'POST';
                
                if (method) {
                    formData.append('_method', method);
                }
                
                $.ajax({
                    url: form.attr('action'),
                    type: ajaxType,
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#paymentModal').modal('hide');
                        var message = (typeof response === 'object' && response.message) ? response.message : 'Payment saved successfully.';
                        showAlert('success', message);
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                        
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorHtml = '<div class="alert alert-danger"><ul class="mb-0">';
                            
                            $.each(errors, function(field, messages) {
                                $.each(messages, function(i, message) {
                                    errorHtml += '<li>' + message + '</li>';
                                    var input = form.find('[name="' + field + '"]');
                                    input.addClass('is-invalid');
                                    input.after('<div class="invalid-feedback">' + message + '</div>');
                                });
                            });
                            
                            errorHtml += '</ul></div>';
                            form.prepend(errorHtml);
                        } else {
                            form.prepend('<div class="alert alert-danger">' + (xhr.responseJSON.message || 'An error occurred. Please try again.') + '</div>');
                        }
                    }
                });
            });
            
            // Handle View Modal
            $('#viewPaymentModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var paymentId = button.data('payment-id');
                var modal = $(this);
                var modalBody = modal.find('#viewPaymentModalBody');
                
                modalBody.html('<div class="text-center py-4"><div class="spinner-border text-info" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.ajax({
                    url: '{{ route("payments.show", ":id") }}'.replace(':id', paymentId),
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (typeof response === 'object' && response.html) {
                            modalBody.html(response.html);
                        } else {
                            var $response = $('<div>').html(response);
                            var content = $response.find('.row').html() || $response.find('.container-fluid').html() || response;
                            modalBody.html(content);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading payment:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading payment details. Please try again.</div>');
                    }
                });
            });

            // Handle Invoice Modal
            $('#invoiceModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var paymentId = button.data('payment-id');
                var modal = $(this);
                var modalBody = modal.find('#invoiceModalBody');
                
                modalBody.html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></div>');
                
                $.ajax({
                    url: '{{ route("payments.invoice", ":id") }}'.replace(':id', paymentId),
                    type: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (typeof response === 'object' && response.html) {
                            modalBody.html(response.html);
                        } else {
                            var $response = $('<div>').html(response);
                            var content = $response.find('.invoice-container').html() || $response.find('.row').html() || response;
                            modalBody.html(content);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error loading invoice:', xhr);
                        modalBody.html('<div class="alert alert-danger">Error loading invoice. Please try again.</div>');
                    }
                });
            });
            
            function showAlert(type, message) {
                // Use JavaScript alert box for all messages
                alert(message);
            }
        });
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWhenReady);
    } else {
        initWhenReady();
    }
})();

// Print Invoice Function
function printInvoice() {
    var printContent = document.getElementById('invoiceModalBody').innerHTML;
    
    // Create a new window for printing
    var printWindow = window.open('', '_blank');
    printWindow.document.write('<!DOCTYPE html><html><head><title>Invoice</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">');
    printWindow.document.write('<style>@media print { body { margin: 0; padding: 20px; } .no-print { display: none !important; } }</style>');
    printWindow.document.write('</head><body>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    
    // Wait for content to load, then print
    setTimeout(function() {
        printWindow.print();
        printWindow.close();
    }, 250);
}

// Auto-filter for payments
(function() {
    if (typeof jQuery === 'undefined') {
        return;
    }
    
    var $ = jQuery;
    var filterTimeout;
    
    // Auto-filter function
    window.applyPaymentsFilters = function() {
        var formData = {
            member_id: $('#member_id').val(),
            payment_status: $('#payment_status').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
        };
        
        // Show loading
        var $tableBody = $('#paymentsTableBody');
        $tableBody.html('<tr><td colspan="8" class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="sr-only">Loading...</span></div></td></tr>');
        
        $.ajax({
            url: '{{ route("payments.index") }}',
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
        $('#member_id, #payment_status').on('change', function() {
            clearTimeout(filterTimeout);
            window.applyPaymentsFilters();
        });
        
        // Auto-filter on date change with debounce (500ms delay)
        $('#start_date, #end_date').on('change', function() {
            clearTimeout(filterTimeout);
            filterTimeout = setTimeout(function() {
                window.applyPaymentsFilters();
            }, 500);
        });
        
        // Reset filters
        window.resetPaymentsFilters = function() {
            $('#member_id').val('');
            $('#payment_status').val('');
            $('#start_date').val('');
            $('#end_date').val('');
            window.history.pushState({}, '', '{{ route("payments.index") }}');
            window.applyPaymentsFilters();
        };
    });
})();
</script>
@endpush
