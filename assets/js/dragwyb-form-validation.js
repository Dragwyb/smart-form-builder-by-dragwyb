(function($) {
    'use strict';

    const DragwybFormValidation = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $(document).on('submit', '.dragwyb-form', this.handleSubmit.bind(this));
            $(document).on('input', '.dragwyb-form input, .dragwyb-form textarea, .dragwyb-form select', 
                this.handleInput.bind(this));
        },

        handleSubmit: function(e) {
            const $form = $(e.currentTarget);
            
            // Clear previous errors
            this.clearErrors($form);

            // Validate all fields
            if (!this.validateForm($form)) {
                e.preventDefault();
                return false;
            }

            // If AJAX submission is enabled
            if ($form.data('ajax')) {
                e.preventDefault();
                this.submitForm($form);
            }
        },

        handleInput: function(e) {
            const $field = $(e.currentTarget);
            const $wrapper = $field.closest('.dragwyb-field-wrapper');
            
            // Clear error when user starts typing
            $wrapper.removeClass('has-error');
            $wrapper.find('.dragwyb-error-message').remove();

            // Validate on the fly if the field was previously validated
            if ($field.data('validated')) {
                this.validateField($field);
            }
        },

        validateForm: function($form) {
            let isValid = true;
            const self = this;

            $form.find('input, textarea, select').each(function() {
                if (!self.validateField($(this))) {
                    isValid = false;
                }
            });

            return isValid;
        },

        validateField: function($field) {
            const value = $field.val();
            const $wrapper = $field.closest('.dragwyb-field-wrapper');
            let isValid = true;
            let errorMessage = '';

            // Mark field as validated
            $field.data('validated', true);

            // Required validation
            if ($field.prop('required') && !value) {
                isValid = false;
                errorMessage = dragwybValidation.messages.required;
            }

            // Type-specific validation
            if (isValid && value) {
                switch ($field.attr('type')) {
                    case 'email':
                        if (!this.isValidEmail(value)) {
                            isValid = false;
                            errorMessage = dragwybValidation.messages.email;
                        }
                        break;

                    case 'number':
                        if (!this.isValidNumber(value)) {
                            isValid = false;
                            errorMessage = dragwybValidation.messages.number;
                        }
                        // Check min/max
                        const min = parseFloat($field.attr('min'));
                        const max = parseFloat($field.attr('max'));
                        if (isValid && !isNaN(min) && parseFloat(value) < min) {
                            isValid = false;
                            errorMessage = dragwybValidation.messages.min.replace('{0}', min);
                        }
                        if (isValid && !isNaN(max) && parseFloat(value) > max) {
                            isValid = false;
                            errorMessage = dragwybValidation.messages.max.replace('{0}', max);
                        }
                        break;

                    case 'url':
                        if (!this.isValidUrl(value)) {
                            isValid = false;
                            errorMessage = dragwybValidation.messages.url;
                        }
                        break;

                    case 'tel':
                        if (!this.isValidPhone(value)) {
                            isValid = false;
                            errorMessage = dragwybValidation.messages.tel;
                        }
                        break;

                    case 'file':
                        if (!this.validateFile($field)) {
                            isValid = false;
                            errorMessage = $field.data('error-message');
                        }
                        break;
                }

                // Length validation
                const minLength = parseInt($field.attr('minlength'));
                const maxLength = parseInt($field.attr('maxlength'));
                
                if (isValid && !isNaN(minLength) && value.length < minLength) {
                    isValid = false;
                    errorMessage = dragwybValidation.messages.minlength.replace('{0}', minLength);
                }
                if (isValid && !isNaN(maxLength) && value.length > maxLength) {
                    isValid = false;
                    errorMessage = dragwybValidation.messages.maxlength.replace('{0}', maxLength);
                }

                // Pattern validation
                const pattern = $field.attr('pattern');
                if (isValid && pattern && !new RegExp(pattern).test(value)) {
                    isValid = false;
                    errorMessage = dragwybValidation.messages.pattern;
                }
            }

            // Update field status
            if (!isValid) {
                this.showError($wrapper, errorMessage);
            } else {
                this.clearErrors($wrapper);
            }

            return isValid;
        },

        showError: function($wrapper, message) {
            $wrapper.addClass('has-error');
            if (!$wrapper.find('.dragwyb-error-message').length) {
                $wrapper.append(`<div class="dragwyb-error-message">${message}</div>`);
            }
        },

        clearErrors: function($context) {
            $context.find('.has-error').removeClass('has-error');
            $context.find('.dragwyb-error-message').remove();
        },

        isValidEmail: function(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },

        isValidNumber: function(value) {
            return !isNaN(parseFloat(value)) && isFinite(value);
        },

        isValidUrl: function(url) {
            try {
                new URL(url);
                return true;
            } catch {
                return false;
            }
        },

        isValidPhone: function(phone) {
            return /^[+]?[0-9\s-()]{10,}$/.test(phone);
        },

        validateFile: function($field) {
            const files = $field[0].files;
            if (!files.length) return true;

            const file = files[0];
            const maxSize = parseInt($field.data('max-size')) * 1024 * 1024; // Convert to bytes
            const allowedTypes = ($field.data('allowed-types') || '').split(',');

            // Check file size
            if (maxSize && file.size > maxSize) {
                $field.data('error-message', dragwybValidation.messages.file_size
                    .replace('{0}', Math.round(maxSize / 1024 / 1024)));
                return false;
            }

            // Check file type
            if (allowedTypes.length) {
                const fileExt = file.name.split('.').pop().toLowerCase();
                if (!allowedTypes.includes(fileExt)) {
                    $field.data('error-message', dragwybValidation.messages.file_type
                        .replace('{0}', allowedTypes.join(', ')));
                    return false;
                }
            }

            return true;
        },

        submitForm: function($form) {
            const formData = new FormData($form[0]);
            formData.append('action', 'dragwyb_submit_form');
            formData.append('nonce', dragwybValidation.nonce);

            $.ajax({
                url: dragwybValidation.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: () => {
                    $form.addClass('is-submitting');
                    $form.find('[type="submit"]').prop('disabled', true);
                },
                success: (response) => {
                    if (response.success) {
                        this.handleSuccess($form, response.data);
                    } else {
                        this.handleError($form, response.data);
                    }
                },
                error: () => {
                    this.handleError($form, {
                        message: dragwybValidation.messages.error
                    });
                },
                complete: () => {
                    $form.removeClass('is-submitting');
                    $form.find('[type="submit"]').prop('disabled', false);
                }
            });
        },

        handleSuccess: function($form, data) {
            // Show success message
            $form.before(`<div class="dragwyb-form-message success">${data.message}</div>`);

            // Reset form
            $form[0].reset();

            // Redirect if specified
            if (data.redirect) {
                window.location.href = data.redirect;
            }
        },

        handleError: function($form, data) {
            // Show general error message
            if (data.message) {
                $form.before(`<div class="dragwyb-form-message error">${data.message}</div>`);
            }

            // Show field-specific errors
            if (data.errors) {
                Object.entries(data.errors).forEach(([fieldId, message]) => {
                    const $wrapper = $form.find(`[name="${fieldId}"]`).closest('.dragwyb-field-wrapper');
                    this.showError($wrapper, message);
                });
            }
        }
    };

    $(document).ready(function() {
        DragwybFormValidation.init();
    });

})(jQuery); 