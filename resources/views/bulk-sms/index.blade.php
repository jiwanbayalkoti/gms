@extends('layouts.app')

@section('title', 'Bulk SMS')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Bulk SMS</h2>
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

    @if($provider === 'twilio' && !$twilioInstalled)
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-circle"></i> Twilio SDK Not Installed!</strong>
            <p class="mb-0">Twilio SDK package is not installed. Please run the following command in your terminal:</p>
            <p class="mb-0 mt-2">
                <code style="background: #f8f9fa; padding: 5px 10px; border-radius: 4px; display: inline-block;">
                    composer install
                </code>
            </p>
            <p class="mb-0 mt-2"><small>After installation, refresh this page.</small></p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @elseif(!$isConfigured)
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <strong><i class="fas fa-exclamation-triangle"></i> SMS Provider not configured!</strong>
            <p class="mb-0">Please configure your SMS provider credentials in <a href="{{ route('settings.index') }}" class="alert-link">Settings</a> to send SMS.</p>
            @if($provider === 'twilio')
                <p class="mb-0 mt-2"><small>You need: <strong>Account SID</strong>, <strong>Auth Token</strong>, and <strong>Phone Number</strong> from your Twilio account.</small></p>
            @elseif($provider === 'sparrow')
                <p class="mb-0 mt-2"><small>You need: <strong>Token</strong> and <strong>Sender ID</strong> from your Sparrow SMS account.</small></p>
            @else
                <p class="mb-0 mt-2"><small>You need: <strong>API Key</strong> and <strong>Sender ID</strong> from your TextLocal account.</small></p>
            @endif
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Statistics Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-info"><i class="fas fa-sms"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Today's SMS</span>
                    <span class="info-box-number">{{ $todayCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-success"><i class="fas fa-rupee-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Today's Cost</span>
                    <span class="info-box-number">₹{{ number_format($todayCost, 2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-primary"><i class="fas fa-calendar-alt"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">This Month</span>
                    <span class="info-box-number">{{ $monthCount }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-warning"><i class="fas fa-money-bill-wave"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Monthly Cost</span>
                    <span class="info-box-number">₹{{ number_format($monthCost, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Send SMS Form --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Send Bulk SMS</h3>
                </div>
                <div class="card-body">
                    <form id="bulkSmsForm">
                        @csrf
                        <div class="form-group">
                            <label>Select Recipients</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" id="send_to_all_members" name="send_to_all_members" value="1">
                                <label class="form-check-label" for="send_to_all_members">
                                    <strong>Send to All Members</strong>
                                </label>
                            </div>
                            <div id="recipientsList" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                @foreach($members as $member)
                                    <div class="form-check">
                                        <input class="form-check-input recipient-checkbox" type="checkbox" name="recipients[]" value="{{ $member->id }}" id="member_{{ $member->id }}">
                                        <label class="form-check-label" for="member_{{ $member->id }}">
                                            {{ $member->name }} ({{ $member->phone ?? 'No phone' }})
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                            <small class="form-text text-muted">Selected: <span id="selectedCount">0</span> recipients</small>
                        </div>

                        <div class="form-group">
                            <label for="message">Message <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="message" name="message" rows="5" placeholder="Enter your message here..." required maxlength="1000"></textarea>
                            <small class="form-text text-muted">
                                <span id="charCount">0</span> / 1000 characters
                            </small>
                        </div>

                        <button type="submit" class="btn btn-primary" id="sendBtn" {{ !$isConfigured ? 'disabled' : '' }}>
                            <i class="fas fa-paper-plane"></i> Send SMS
                        </button>
                        @if(!$isConfigured)
                            <small class="d-block text-danger mt-2">
                                <i class="fas fa-info-circle"></i> Configure TextLocal API in Settings first
                            </small>
                        @endif
                        <button type="button" class="btn btn-secondary" id="clearBtn">
                            <i class="fas fa-times"></i> Clear
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Recent SMS Logs --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent SMS Logs</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th>Error</th>
                                    <th>Cost</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                    <tr>
                                        <td>{{ substr($log->phone_number, -4) }}****</td>
                                        <td>
                                            @if($log->status === 'sent')
                                                <span class="badge badge-success">Sent</span>
                                            @elseif($log->status === 'failed')
                                                <span class="badge badge-danger">Failed</span>
                                            @else
                                                <span class="badge badge-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($log->status === 'failed' && $log->provider_response)
                                                @php
                                                    $errorMsg = 'Unknown error';
                                                    if (isset($log->provider_response['errors']) && is_array($log->provider_response['errors']) && count($log->provider_response['errors']) > 0) {
                                                        $errorMsg = $log->provider_response['errors'][0]['message'] ?? $errorMsg;
                                                    } elseif (isset($log->provider_response['message'])) {
                                                        $errorMsg = $log->provider_response['message'];
                                                    } elseif (isset($log->provider_response['error'])) {
                                                        $errorMsg = $log->provider_response['error'];
                                                    }
                                                @endphp
                                                <small class="text-danger" title="{{ $errorMsg }}">
                                                    {{ Str::limit($errorMsg, 30) }}
                                                </small>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>₹{{ number_format($log->cost, 2) }}</td>
                                        <td>{{ $log->sent_at ? $log->sent_at->format('H:i') : '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No SMS sent yet</td>
                                    </tr>
                                @endforelse
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
$(document).ready(function() {
    // Character counter
    $('#message').on('input', function() {
        var length = $(this).val().length;
        $('#charCount').text(length);
    });

    // Recipient selection
    $('#send_to_all_members').on('change', function() {
        if ($(this).is(':checked')) {
            $('.recipient-checkbox').prop('checked', false).prop('disabled', true);
            $('#selectedCount').text('All Members');
        } else {
            $('.recipient-checkbox').prop('disabled', false);
            updateSelectedCount();
        }
    });

    $('.recipient-checkbox').on('change', function() {
        if ($(this).is(':checked')) {
            $('#send_to_all_members').prop('checked', false);
        }
        updateSelectedCount();
    });

    function updateSelectedCount() {
        var count = $('.recipient-checkbox:checked').length;
        $('#selectedCount').text(count);
    }

    // Form submission
    $('#bulkSmsForm').on('submit', function(e) {
        e.preventDefault();

        var formData = $(this).serialize();
        var sendToAll = $('#send_to_all_members').is(':checked');
        
        if (!sendToAll && $('.recipient-checkbox:checked').length === 0) {
            alert('Please select at least one recipient or choose "Send to All Members"');
            return;
        }

        if ($('#message').val().trim() === '') {
            alert('Please enter a message');
            return;
        }

        // Disable submit button
        $('#sendBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending...');

        $.ajax({
            url: '{{ route("bulk-sms.send") }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message || 'Failed to send SMS');
                    $('#sendBtn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send SMS');
                }
            },
            error: function(xhr) {
                var message = 'Failed to send SMS';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert(message);
                $('#sendBtn').prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send SMS');
            }
        });
    });

    // Clear form
    $('#clearBtn').on('click', function() {
        $('#bulkSmsForm')[0].reset();
        $('.recipient-checkbox').prop('checked', false).prop('disabled', false);
        $('#selectedCount').text('0');
        $('#charCount').text('0');
    });
});
</script>
@endpush
@endsection

