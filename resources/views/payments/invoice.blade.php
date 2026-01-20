<div class="invoice-container" id="invoiceContent">
    <div class="invoice-wrapper">
        <!-- Decorative shapes -->
        <div class="invoice-shape-top"></div>
        <div class="invoice-shape-bottom"></div>
        
        <!-- Header Section -->
        <div class="invoice-header">
            <div class="row">
                <div class="col-md-6">
                    <h1 class="invoice-title">INVOICE</h1>
                    <div class="invoice-meta">
                        <p class="invoice-meta-item">
                            <span class="meta-label">Date Issued:</span>
                            <span class="meta-value">{{ $payment->payment_date->format('d F Y') }}</span>
                        </p>
                        <p class="invoice-meta-item">
                            <span class="meta-label">Invoice No:</span>
                            <span class="meta-value">{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</span>
                        </p>
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <div class="invoice-issued-to">
                        <p class="issued-label">Issued to:</p>
                        @if($payment->member)
                            <p class="issued-name">{{ $payment->member->name }}</p>
                            <p class="issued-address">{{ $payment->member->email }}</p>
                            @if($payment->member->phone)
                                <p class="issued-address">{{ $payment->member->phone }}</p>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="invoice-table-section">
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th class="col-no">NO</th>
                        <th class="col-description">DESCRIPTION</th>
                        <th class="col-qty">QTY</th>
                        <th class="col-price">PRICE</th>
                        <th class="col-subtotal">SUBTOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center">1</td>
                        <td>
                            <strong>{{ $payment->membershipPlan->name ?? 'Membership Payment' }}</strong>
                            @if($payment->membershipPlan && $payment->membershipPlan->description)
                                <br><small class="text-muted">{{ $payment->membershipPlan->description }}</small>
                            @endif
                            @if($payment->expiry_date)
                                <br><small class="text-muted">Valid until: {{ $payment->expiry_date->format('M d, Y') }}</small>
                            @endif
                        </td>
                        <td class="text-center">1</td>
                        <td class="text-right">${{ number_format($payment->amount, 2) }}</td>
                        <td class="text-right">${{ number_format($payment->amount, 2) }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="grand-total-row">
                        <td colspan="3" class="grand-total-label">GRAND TOTAL</td>
                        <td colspan="2" class="grand-total-amount text-right">${{ number_format($payment->amount, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Footer Section -->
        <div class="invoice-footer">
            <div class="row">
                <div class="col-md-6">
                    <div class="invoice-notes">
                        <p class="notes-label">Note:</p>
                        @if($payment->gym)
                            @if($payment->gym->name)
                                <p class="notes-text">Gym: {{ $payment->gym->name }}</p>
                            @endif
                            @if($payment->gym->address)
                                <p class="notes-text">{{ $payment->gym->address }}</p>
                            @endif
                            @if($payment->gym->phone)
                                <p class="notes-text">Phone: {{ $payment->gym->phone }}</p>
                            @endif
                            @if($payment->gym->email)
                                <p class="notes-text">Email: {{ $payment->gym->email }}</p>
                            @endif
                        @endif
                        @if($payment->transaction_id)
                            <p class="notes-text">Transaction ID: {{ $payment->transaction_id }}</p>
                        @endif
                        <p class="notes-text">Payment Method: {{ $payment->payment_method }}</p>
                        @if($payment->notes)
                            <p class="notes-text mt-2">{{ $payment->notes }}</p>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 text-right">
                    <div class="invoice-signature">
                        <div class="signature-line"></div>
                        <p class="signature-label">Account Manager</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        body * {
            visibility: hidden;
        }
        .invoice-container, .invoice-container * {
            visibility: visible;
        }
        .invoice-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 0;
        }
        .no-print {
            display: none !important;
        }
        .invoice-shape-top,
        .invoice-shape-bottom {
            display: none;
        }
    }

    .invoice-container {
        background: white;
        padding: 40px;
        max-width: 900px;
        margin: 0 auto;
        position: relative;
        min-height: 800px;
    }

    .invoice-wrapper {
        position: relative;
    }

    /* Decorative Shapes */
    .invoice-shape-top {
        position: absolute;
        top: -20px;
        right: -20px;
        width: 200px;
        height: 200px;
        background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
        clip-path: polygon(0 0, 100% 0, 100% 50%, 50% 100%);
        opacity: 0.1;
        z-index: 0;
    }

    .invoice-shape-top::after {
        content: '';
        position: absolute;
        top: -30px;
        right: 30px;
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #e83e8c 0%, #fd7e14 100%);
        clip-path: polygon(0 0, 100% 0, 100% 50%, 50% 100%);
        opacity: 0.15;
    }

    .invoice-shape-bottom {
        position: absolute;
        bottom: -20px;
        left: -20px;
        width: 200px;
        height: 200px;
        background: linear-gradient(45deg, #20c997 0%, #17a2b8 100%);
        clip-path: polygon(0 50%, 50% 0, 100% 100%, 0 100%);
        opacity: 0.1;
        z-index: 0;
    }

    .invoice-shape-bottom::after {
        content: '';
        position: absolute;
        bottom: -30px;
        left: 30px;
        width: 120px;
        height: 120px;
        background: linear-gradient(45deg, #e83e8c 0%, #fd7e14 100%);
        clip-path: polygon(0 50%, 50% 0, 100% 100%, 0 100%);
        opacity: 0.15;
    }

    /* Header Styles */
    .invoice-header {
        margin-bottom: 40px;
        position: relative;
        z-index: 1;
    }

    .invoice-title {
        font-size: 48px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 20px;
        letter-spacing: 2px;
    }

    .invoice-meta {
        margin-top: 20px;
    }

    .invoice-meta-item {
        margin-bottom: 8px;
        font-size: 14px;
        color: #6c757d;
    }

    .meta-label {
        font-weight: 500;
        color: #495057;
    }

    .meta-value {
        color: #212529;
        margin-left: 8px;
    }

    .invoice-issued-to {
        text-align: right;
    }

    .issued-label {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 8px;
        font-weight: 500;
    }

    .issued-name {
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
    }

    .issued-address {
        font-size: 14px;
        color: #6c757d;
        margin-bottom: 2px;
    }

    /* Table Styles */
    .invoice-table-section {
        margin: 40px 0;
        position: relative;
        z-index: 1;
    }

    .invoice-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0;
    }

    .invoice-table thead {
        background-color: #f8f9fa;
    }

    .invoice-table th {
        padding: 15px 12px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
    }

    .invoice-table th.col-no,
    .invoice-table td:first-child {
        width: 5%;
        text-align: center;
    }

    .invoice-table th.col-description,
    .invoice-table td:nth-child(2) {
        width: 50%;
    }

    .invoice-table th.col-qty,
    .invoice-table td:nth-child(3) {
        width: 10%;
        text-align: center;
    }

    .invoice-table th.col-price,
    .invoice-table td:nth-child(4) {
        width: 15%;
        text-align: right;
    }

    .invoice-table th.col-subtotal,
    .invoice-table td:nth-child(5) {
        width: 20%;
        text-align: right;
    }

    .invoice-table tbody td {
        padding: 20px 12px;
        border-bottom: 1px solid #e9ecef;
        color: #495057;
        font-size: 14px;
    }

    .invoice-table tbody tr:last-child td {
        border-bottom: none;
    }

    .grand-total-row {
        background-color: #f8f9fa;
        border-top: 2px solid #dee2e6;
    }

    .grand-total-label {
        padding: 20px 12px;
        font-weight: 700;
        font-size: 16px;
        color: #2c3e50;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .grand-total-amount {
        padding: 20px 12px;
        font-weight: 700;
        font-size: 18px;
        color: #2c3e50;
    }

    /* Footer Styles */
    .invoice-footer {
        margin-top: 50px;
        position: relative;
        z-index: 1;
    }

    .invoice-notes {
        margin-top: 20px;
    }

    .notes-label {
        font-weight: 600;
        font-size: 14px;
        color: #495057;
        margin-bottom: 8px;
    }

    .notes-text {
        font-size: 13px;
        color: #6c757d;
        margin-bottom: 4px;
        line-height: 1.6;
    }

    .invoice-signature {
        margin-top: 40px;
        display: inline-block;
    }

    .signature-line {
        width: 200px;
        height: 1px;
        background-color: #dee2e6;
        margin-bottom: 8px;
        margin-left: auto;
    }

    .signature-label {
        font-size: 13px;
        color: #6c757d;
        margin-top: 4px;
    }
</style>
