(function($) {
    'use strict';

    const DragwybValidationFeedback = {
        init: function() {
            this.initializeStrengthMeters();
            this.bindEvents();
        },

        bindEvents: function() {
            $(document).on('input', '.dragwyb-field-wrapper input', this.handleInput.bind(this));
            $(document).on('focusout', '.dragwyb-field-wrapper input', this.handleBlur.bind(this));
        },

        initializeStrengthMeters: function() {
            $('.dragwyb-password-field').each(function() {
                const $field = $(this);
                const $wrapper = $field.closest('.dragwyb-field-wrapper');
                
                $wrapper.append(`
                    <div class="dragwyb-strength-meter">
                        <div class="dragwyb-strength-bar"></div>
                        <div class="dragwyb-strength-text"></div>
                    </div>
                `);
            });
        },

        handleInput: function(e) {
            const $field = $(e.target);
            const $wrapper = $field.closest('.dragwyb-field-wrapper');
            
            // Clear previous validation state
            this.clearValidationState($wrapper);

            // Show real-time feedback based on field type
            switch ($field.attr('type')) {
                case 'password':
                    this.updatePasswordStrength($field);
                    break;
                    
                case 'text':
                    if ($field.data('word-count')) {
                        this.updateWordCount($field);
                    }
                    break;
            }

            // Trigger validation if field was previously validated
            if ($field.data('was-validated')) {
                this.validateField($field);
            }
        },

        handleBlur: function(e) {
            const $field = $(e.target);
            $field.data('was-validated', true);
            this.validateField($field);
        },

        updatePasswordStrength: function($field) {
            const password = $field.val();
            const $wrapper = $field.closest('.dragwyb-field-wrapper');
            const $meter = $wrapper.find('.dragwyb-strength-meter');
            const $bar = $meter.find('.dragwyb-strength-bar');
            const $text = $meter.find('.dragwyb-strength-text');

            const strength = this.calculatePasswordStrength(password);
            
            $bar.css('width', strength.score + '%')
                .removeClass('weak medium strong')
                .addClass(strength.level);
                
            $text.text(strength.message);
        },

        calculatePasswordStrength: function(password) {
            let score = 0;
            let level = 'weak';
            let message = '';

            if (password.length >= 8) score += 25;
            if (password.match(/[A-Z]/)) score += 25;
            if (password.match(/[0-9]/)) score += 25;
            if (password.match(/[^A-Za-z0-9]/)) score += 25;

            if (score >= 100) {
                level = 'strong';
                message = dragwybValidation.messages.password_strong;
            } else if (score >= 50) {
                level = 'medium';
                message = dragwybValidation.messages.password_medium;
            } else {
                message = dragwybValidation.messages.password_weak;
            }

            return { score, level, message };
        },

        updateWordCount: function($field) {
            const text = $field.val();
            const wordCount = text.trim().split(/\s+/).length;
            const $wrapper = $field.closest('.dragwyb-field-wrapper');
            
            let $counter = $wrapper.find('.dragwyb-word-counter');
            if (!$counter.length) {
                $counter = $('<div class="dragwyb-word-counter"></div>').appendTo($wrapper);
            }

            const minWords = parseInt($field.data('min-words')) || 0;
            const maxWords = parseInt($field.data('max-words')) || 0;

            let message = dragwybValidation.messages.word_count
                .replace('{count}', wordCount);

            if (minWords && maxWords) {
                message += ` (${minWords}-${maxWords} ${dragwybValidation.messages.words})`;
            } else if (minWords) {
                message += ` (${dragwybValidation.messages.min} ${minWords})`;
            } else if (maxWords) {
                message += ` (${dragwybValidation.messages.max} ${maxWords})`;
            }

            $counter.text(message);
        },

        validateField: function($field) {
            const value = $field.val();
            const rules = $field.data('validation-rules');
            
            if (!rules) return true;

            const validationPromise = $.ajax({
                url: dragwybValidation.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dragwyb_validate_field',
                    nonce: dragwybValidation.nonce,
                    field: $field.attr('name'),
                    value: value,
                    rules: rules
                }
            });

            validationPromise.then(response => {
                if (!response.success) {
                    this.showValidationError($field, response.data.message);
                } else {
                    this.showValidationSuccess($field);
                }
            });

            return validationPromise;
        },

        showValidationError: function($field, message) {
            const $wrapper = $field.closest('.dragwyb-field-wrapper');
            
            $wrapper.addClass('has-error')
                   .removeClass('has-success');
                   
            let $error = $wrapper.find('.dragwyb-error-message');
            if (!$error.length) {
                $error = $('<div class="dragwyb-error-message"></div>').appendTo($wrapper);
            }
            $error.text(message);
        },

        showValidationSuccess: function($field) {
            const $wrapper = $field.closest('.dragwyb-field-wrapper');
            
            $wrapper.addClass('has-success')
                   .removeClass('has-error');
            
            $wrapper.find('.dragwyb-error-message').remove();
        },

        clearValidationState: function($wrapper) {
            $wrapper.removeClass('has-error has-success');
            $wrapper.find('.dragwyb-error-message').remove();
        }
    };

    $(document).ready(function() {
        DragwybValidationFeedback.init();
    });

})(jQuery); 