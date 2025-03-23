(function($) {
    'use strict';

    const DragwybFormFrontend = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $(document).on('submit', '.dragwyb-form', this.handleSubmit.bind(this));
            $(document).on('input', '.dragwyb-form input, .dragwyb-form textarea', this.validateField);
        },

        handleSubmit: function(e) {
            e.preventDefault();
            const $form = $(e.currentTarget);
            
            // Clear previous errors
            this.clearErrors($form);

            // Validate all fields
            if (!this.validateForm($form)) {
                return;
            }

            // Disable submit button
            const $submit = $form.find('[type="submit"]');
            const originalText = $submit.text();
            $submit.prop('disabled', true).text(dragwybFront.strings.submitting);

            // Collect form data
            const formData = new FormData($form[0]);
            formData.append('action', 'dragwyb_submit_form');
            formData.append('nonce', dragwybFront.nonce);

            // Submit form
            $.ajax({
                url: dragwybFront.ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: (response) => {
                    if (response.success) {
                        this.handleSuccess($form, response);
                    } else {
                        this.handleError($form, response);
                    }
                },
                error: () => {
                    this.handleError($form, {
                        message: dragwybFront.strings.error
                    });
                },
                complete: () => {
                    $submit.prop('disabled', false).text(originalText);
                }
            });
        },

        validateForm: function($form) {
            let isValid = true;
            
            $form.find('[required]').each((i, field) => {
                if (!this.validateField(field)) {
                    isValid = false;
                }
            });

            return isValid;
        },

        validateField: function(field) {
            const $field = $(field);
            const value = $field.val();
            const type = $field.attr('type');
            let isValid = true;

            // Required validation
            if ($field.prop('required') && !value) {
                isValid = false;
            }

            // Email validation
            if (type === 'email' && value && !this.isValidEmail(value)) {
                isValid = false;
            }

            // Number validation
            if (type === 'number') {
                const min = parseFloat($field.attr('min'));
                const max = parseFloat($field.attr('max'));
                const numValue = parseFloat(value);

                if (value && isNaN(numValue)) {
                    isValid = false;
                }
                if (typeof min !== 'undefined' && numValue < min) {
                    isValid = false;
                }
                if (typeof max !== 'undefined' && numValue > max) {
                    isValid = false;
                }
            }

            // Update field status
            this.updateFieldStatus($field, isValid);

            return isValid;
        },

        isValidEmail: function(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        },

        updateFieldStatus: function($field, isValid) {
            const $wrapper = $field.closest('.dragwyb-field-wrapper');
            
            if (!isValid) {
                $wrapper.addClass('has-error');
                if (!$wrapper.find('.dragwyb-error-message').length) {
                    $wrapper.append(`<div class="dragwyb-error-message">${this.getErrorMessage($field)}</div>`);
                }
            } else {
                $wrapper.removeClass('has-error');
                $wrapper.find('.dragwyb-error-message').remove();
            }
        },

        getErrorMessage: function($field) {
            const type = $field.attr('type');
            const messages = dragwybFront.strings.validation;

            if ($field.prop('required') && !$field.val()) {
                return messages.required;
            }

            switch (type) {
                case 'email':
                    return messages.email;
                case 'number':
                    return messages.number;
                default:
                    return messages.default;
            }
        },

        clearErrors: function($form) {
            $form.find('.has-error').removeClass('has-error');
            $form.find('.dragwyb-error-message').remove();
            $form.find('.dragwyb-form-error').remove();
        },

        handleSuccess: function($form, response) {
            // Show success message
            $form.before(`<div class="dragwyb-form-success">${response.message}</div>`);

            // Reset form
            $form[0].reset();

            // Redirect if specified
            if (response.redirect) {
                window.location.href = response.redirect;
            }
        },

        handleError: function($form, response) {
            // Show general error message
            $form.before(`<div class="dragwyb-form-error">${response.message}</div>`);

            // Show field-specific errors
            if (response.errors) {
                Object.entries(response.errors).forEach(([fieldId, message]) => {
                    const $field = $form.find(`[name="${fieldId}"]`);
                    this.updateFieldStatus($field, false);
                });
            }
        }
    };

    $(document).ready(function() {
        DragwybFormFrontend.init();
    });

})(jQuery); 