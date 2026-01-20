<form id="pauseRequestForm" method="POST" action="{{ route('pause-requests.store') }}">
    @csrf
    
    <div class="alert alert-info">
        <strong>Current Membership:</strong><br>
        Plan: {{ $activePayment->membershipPlan->name ?? 'N/A' }}<br>
        @if($activePayment->expiry_date)
            Expiry Date: {{ $activePayment->expiry_date->format('M d, Y') }}
        @else
            No expiry date set
        @endif
    </div>

    <div class="alert alert-warning">
        <strong>Note:</strong> Minimum pause period is <strong>{{ $settings->minimum_pause_days }} days</strong>.
    </div>

    <div class="form-group">
        <label for="pause_start_date">Pause Start Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('pause_start_date') is-invalid @enderror" 
               id="pause_start_date" name="pause_start_date" 
               value="{{ old('pause_start_date') }}" 
               min="{{ now()->format('Y-m-d') }}" required>
        @error('pause_start_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="pause_end_date">Pause End Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control @error('pause_end_date') is-invalid @enderror" 
               id="pause_end_date" name="pause_end_date" 
               value="{{ old('pause_end_date') }}" required>
        @error('pause_end_date')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        <small class="form-text text-muted">Must be at least {{ $settings->minimum_pause_days }} days after start date</small>
    </div>

    <div class="form-group">
        <label for="reason">Reason (Optional)</label>
        <textarea class="form-control @error('reason') is-invalid @enderror" 
                  id="reason" name="reason" rows="3" 
                  placeholder="Please provide a reason for the pause request...">{{ old('reason') }}</textarea>
        @error('reason')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group mb-0">
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Submit Request
            </button>
        </div>
    </div>
</form>

<script>
(function() {
    // Wait for jQuery to be loaded
    function initPauseRequestForm() {
        if (typeof jQuery === 'undefined') {
            setTimeout(initPauseRequestForm, 100);
            return;
        }
        
        var $ = jQuery;
        
        $(document).ready(function() {
            // Update end date minimum when start date changes
            $('#pause_start_date').on('change', function() {
                var startDate = $(this).val();
                if (startDate) {
                    var minEndDate = new Date(startDate);
                    minEndDate.setDate(minEndDate.getDate() + {{ $settings->minimum_pause_days }});
                    $('#pause_end_date').attr('min', minEndDate.toISOString().split('T')[0]);
                }
            });

            // Handle form submission
            $('#pauseRequestForm').on('submit', function(e) {
                e.preventDefault();
                
                var form = $(this);
                var formData = form.serialize();
                var submitBtn = form.find('button[type="submit"]');
                var originalBtnText = submitBtn.html();
                
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
                
                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#pauseRequestModal').modal('hide');
                        location.reload();
                    },
                    error: function(xhr) {
                        submitBtn.prop('disabled', false).html(originalBtnText);
                        
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            form.find('.is-invalid').removeClass('is-invalid');
                            form.find('.invalid-feedback').remove();
                            
                            $.each(errors, function(field, messages) {
                                var input = form.find('[name="' + field + '"]');
                                input.addClass('is-invalid');
                                input.after('<div class="invalid-feedback">' + messages[0] + '</div>');
                            });
                        } else {
                            alert(xhr.responseJSON?.message || 'Error submitting request.');
                        }
                    }
                });
            });
        });
    }
    
    // Start initialization
    initPauseRequestForm();
})();
</script>

