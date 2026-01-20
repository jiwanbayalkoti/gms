{{-- Create Salary Form - Used in Modal --}}
<form action="{{ route('salaries.store') }}" method="POST" id="salaryForm">
    @include('salaries._form', ['employees' => $employees ?? []])
</form>

<script>
$(document).ready(function() {
    $('#salaryForm').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = new FormData(this);
        var action = form.attr('action');
        var method = form.find('input[name="_method"]').val() || 'POST';
        
        $.ajax({
            url: action,
            type: method,
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#salaryModal').modal('hide');
                    location.reload();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;
                    // Handle validation errors
                    $('.is-invalid').removeClass('is-invalid');
                    $.each(errors, function(key, value) {
                        $('#' + key).addClass('is-invalid');
                        $('#' + key + '_error').remove();
                        $('#' + key).after('<div class="invalid-feedback" id="' + key + '_error">' + value[0] + '</div>');
                    });
                } else {
                    alert('Error creating salary. Please try again.');
                }
            }
        });
    });
});
</script>

