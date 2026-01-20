/**
 * Form Validation Script
 * Automatically validates all forms with required fields
 * Works with both regular forms and modal forms
 */

(function($) {
    'use strict';

    // Custom validation methods
    $.validator.addMethod("phoneNumber", function(value, element) {
        return this.optional(element) || /^[\d\s\-\+\(\)]+$/.test(value);
    }, "Please enter a valid phone number");

    $.validator.addMethod("passwordStrength", function(value, element) {
        if (!value) return true; // Optional field
        return value.length >= 8;
    }, "Password must be at least 8 characters long");

    $.validator.addMethod("fileSize", function(value, element, param) {
        if (!element.files || !element.files[0]) return true; // Optional field
        var maxSize = param * 1024 * 1024; // Convert MB to bytes
        return element.files[0].size <= maxSize;
    }, "File size must be less than {0} MB");

    $.validator.addMethod("fileType", function(value, element, param) {
        if (!element.files || !element.files[0]) return true; // Optional field
        var allowedTypes = param.split(',');
        var fileType = element.files[0].type.toLowerCase();
        var fileName = element.files[0].name.toLowerCase();
        var fileExt = fileName.substring(fileName.lastIndexOf('.') + 1);
        
        for (var i = 0; i < allowedTypes.length; i++) {
            if (fileType.indexOf(allowedTypes[i]) !== -1 || fileExt === allowedTypes[i]) {
                return true;
            }
        }
        return false;
    }, "Please select a valid file type");

    // Initialize validation for all forms
    function initFormValidation() {
        // Validate all forms on the page
        $('form').each(function() {
            var $form = $(this);
            var formId = $form.attr('id') || 'form_' + Math.random().toString(36).substr(2, 9);
            
            // Skip if already validated
            if ($form.data('validator')) {
                return;
            }

            // Skip forms that explicitly disable validation
            if ($form.hasClass('no-validation')) {
                return;
            }

            // Build validation rules based on form fields
            var rules = {};
            var messages = {};

            $form.find('input, select, textarea').each(function() {
                var $field = $(this);
                var fieldName = $field.attr('name');
                var fieldType = $field.attr('type') || $field.prop('tagName').toLowerCase();
                var isRequired = $field.prop('required') || $field.attr('required') !== undefined;
                var fieldId = $field.attr('id') || fieldName;

                if (!fieldName || fieldName === '_token' || fieldName === '_method') {
                    return; // Skip CSRF and method fields
                }

                // Initialize rules object
                if (!rules[fieldName]) {
                    rules[fieldName] = {};
                    messages[fieldName] = {};
                }

                // Required validation
                if (isRequired) {
                    rules[fieldName].required = true;
                    var label = $form.find('label[for="' + fieldId + '"]').text().replace(/\*/g, '').trim();
                    messages[fieldName].required = label ? (label + ' is required') : 'This field is required';
                }

                // Email validation
                if (fieldType === 'email' || $field.attr('type') === 'email') {
                    rules[fieldName].email = true;
                    messages[fieldName].email = 'Please enter a valid email address';
                }

                // Password validation
                if (fieldType === 'password') {
                    if (isRequired) {
                        rules[fieldName].passwordStrength = true;
                        messages[fieldName].passwordStrength = 'Password must be at least 8 characters long';
                    }
                    
                    // Password confirmation
                    if (fieldName === 'password_confirmation' || fieldName === 'password_confirm') {
                        var passwordField = $form.find('input[name="password"]');
                        if (passwordField.length) {
                            rules[fieldName].equalTo = passwordField;
                            messages[fieldName].equalTo = 'Passwords do not match';
                        }
                    }
                }

                // Phone validation
                if (fieldName.toLowerCase().includes('phone') || fieldName.toLowerCase().includes('mobile')) {
                    rules[fieldName].phoneNumber = true;
                    messages[fieldName].phoneNumber = 'Please enter a valid phone number';
                }

                // File validation
                if (fieldType === 'file') {
                    var acceptAttr = $field.attr('accept');
                    var maxSizeAttr = $field.data('max-size') || $field.attr('data-max-size');
                    
                    if (acceptAttr) {
                        // Parse accept attribute (e.g., "image/*" or "image/jpeg,image/png")
                        var acceptedTypes = acceptAttr.split(',').map(function(type) {
                            return type.trim().replace(/\*/g, '');
                        });
                        rules[fieldName].fileType = acceptedTypes.join(',');
                        messages[fieldName].fileType = 'Please select a valid file type';
                    }
                    
                    if (maxSizeAttr) {
                        rules[fieldName].fileSize = parseInt(maxSizeAttr);
                        messages[fieldName].fileSize = 'File size must be less than ' + maxSizeAttr + ' MB';
                    }
                }

                // Number validation
                if (fieldType === 'number') {
                    var min = $field.attr('min');
                    var max = $field.attr('max');
                    if (min !== undefined) {
                        rules[fieldName].min = parseFloat(min);
                        messages[fieldName].min = 'Value must be at least ' + min;
                    }
                    if (max !== undefined) {
                        rules[fieldName].max = parseFloat(max);
                        messages[fieldName].max = 'Value must be at most ' + max;
                    }
                }

                // URL validation
                if (fieldType === 'url' || fieldName.toLowerCase().includes('url') || fieldName.toLowerCase().includes('website')) {
                    rules[fieldName].url = true;
                    messages[fieldName].url = 'Please enter a valid URL';
                }

                // Date validation
                if (fieldType === 'date') {
                    rules[fieldName].date = true;
                    messages[fieldName].date = 'Please enter a valid date';
                }

                // Textarea max length
                if ($field.is('textarea')) {
                    var maxLength = $field.attr('maxlength');
                    if (maxLength) {
                        rules[fieldName].maxlength = parseInt(maxLength);
                        messages[fieldName].maxlength = 'Maximum ' + maxLength + ' characters allowed';
                    }
                }
            });

            // Only validate if there are rules
            if (Object.keys(rules).length > 0) {
                $form.validate({
                    rules: rules,
                    messages: messages,
                    errorElement: 'div',
                    errorClass: 'invalid-feedback',
                    highlight: function(element) {
                        $(element).addClass('is-invalid').removeClass('is-valid');
                    },
                    unhighlight: function(element) {
                        $(element).removeClass('is-invalid').addClass('is-valid');
                    },
                    errorPlacement: function(error, element) {
                        if (element.parent('.input-group').length) {
                            error.insertAfter(element.parent());
                        } else if (element.is(':radio') || element.is(':checkbox')) {
                            error.insertAfter(element.parent('label'));
                        } else {
                            error.insertAfter(element);
                        }
                    },
                    submitHandler: function(form) {
                        // Allow form to submit normally if no custom handler
                        // This works with both regular forms and AJAX forms
                        return true;
                    },
                    invalidHandler: function(event, validator) {
                        // Scroll to first error
                        var firstError = $(validator.errorList[0].element);
                        if (firstError.length) {
                            $('html, body').animate({
                                scrollTop: firstError.offset().top - 100
                            }, 500);
                            
                            // Focus on first error field
                            firstError.focus();
                        }
                    }
                });
            }
        });
    }

    // Initialize on document ready
    $(document).ready(function() {
        initFormValidation();
    });

    // Re-initialize validation when modals are shown (for dynamically loaded forms)
    $(document).on('shown.bs.modal', '.modal', function() {
        initFormValidation();
    });

    // Re-initialize validation when content is loaded via AJAX
    $(document).on('ajaxComplete', function() {
        setTimeout(initFormValidation, 100);
    });

    // Re-initialize validation when forms are dynamically added
    var observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                setTimeout(initFormValidation, 100);
            }
        });
    });

    // Observe body for new forms
    if (document.body) {
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    // Export function for manual initialization
    window.initFormValidation = initFormValidation;

})(jQuery);
