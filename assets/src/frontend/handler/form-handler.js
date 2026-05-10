/**
 * Individual Form Handler Class
 */
class DragwybFormHandler extends DragwybBuilder.DragwybFormFrontendBase {
    bindElements() {
        this.elements.$form = this.$container.is('form') ? this.$container : this.$container.find('form.dragwyb-form');
        this.elements.$submitButton = this.$container.find('button[type="submit"]');
        this.elements.$message = this.$container.find('.dragwyb-form-message');
    }

    init() {
        this.bindEvents();
        DragwybBuilder.Hooks.doAction('dragwyb/frontend/form/init' + this.formId, this);
    }

    bindEvents() {
        if (this.elements.$form.length) {
            this.elements.$form.on('submit', (event) => this.#onSubmit(event));

            // Validate on blur and change
            this.elements.$form.on('blur change', 'input, select, textarea', (event) => {
                this.handleFieldValidation(event.target);
            });

            // Clear errors on input if field becomes valid
            this.elements.$form.on('input', 'input, textarea', (event) => {
                const element = event.target;
                const $field = jQuery(element);
                if ($field.hasClass('dragwyb-error')) {
                    const validation = this.validateField(element);
                    if (validation.valid) {
                        this.clearFieldError($field);
                    }
                }
            });
        }
    }

    #onSubmit(event) {
        event.preventDefault();

        // Trigger action before submit
        DragwybBuilder.Hooks.doAction('dragwyb/frontend/form/before_submit' + this.formId, event, this);

        if (!this.validateForm()) {
            return;
        }

        const formData = {
            fields: jQuery(this.elements.$form[0]).serializeArray()
        };
        formData.form_id = this.formId;
        formData.nonce = window.DragwybFrontendData?.[`form_${this.formId}`]?.nonce || '';

        if (!formData.nonce || '' === formData.nonce) {
            console.warn(`Nonce not found for form ${this.formId}`);
            return;
        }

        const submissionEndpoint = window.DragwybFrontendData?.frontendRoute + 'submit';

        jQuery.ajax({
            url: submissionEndpoint,
            type: 'POST',
            data: JSON.stringify(formData),
            processData: false,
            contentType: 'application/json',
            beforeSend: (xhr) => {
                xhr.setRequestHeader('X-WP-Nonce', window.DragwybFrontendData?.nonce || '');
                this.elements.$submitButton.prop('disabled', true);
                DragwybBuilder.Hooks.doAction('dragwyb/frontend/form/submit_start/' + this.formId, this);
            },
            success: (response) => {
                this.elements.$submitButton.prop('disabled', false);
                DragwybBuilder.Hooks.doAction('dragwyb/frontend/form/submit_success/' + this.formId, response, this);

                if (response.success && response.data) {

                    this.clearFormData();

                    // Trigger specific frontend actions returned by the backend
                    if (response.data.actions_data && typeof response.data.actions_data === 'object') {
                        Object.entries(response.data.actions_data).forEach(([actionId, actionData]) => {
                            DragwybBuilder.Hooks.doAction('dragwyb/frontend/action/' + actionId + '/' + this.formId, actionData, response, this);
                        });
                    }

                    if (response.data.message) {
                        this.#showMessage(response.data.message, 'success');
                    }

                } else if (!response.success && response.errors) {
                    this.#showInputErrors(response.errors);
                } else if (response.data && response.data.message) {
                    this.#showMessage(response.data.message, 'error');
                }
            },
            error: (xhr, status, error) => {
                this.elements.$submitButton.prop('disabled', false);
                DragwybBuilder.Hooks.doAction('dragwyb/frontend/form/submit_error' + this.formId, xhr, status, error, this);
                this.#showMessage('An error occurred. Please try again.', 'error');
            }
        });
    }

    #showInputErrors(inputErrors) {
        if (!inputErrors) return;

        const inputIds = Object.keys(inputErrors);

        if (inputIds.length < 1) return;

        inputIds.forEach(id => {
            // Find fields by name, use collection to apply error to radio/checkbox groups
            const inputField = jQuery(`[name="${id}"]`);

            if (inputField.length) {
                this.showFieldError(inputField, inputErrors[id]);
            }
        });

        // Focus first field with error
        const firstErrorId = inputIds[0];
        const firstField = jQuery(`[name="${firstErrorId}"]`);
        if (firstField.length) {
            firstField.first().focus();
        }
    }

    #showMessage(message, type = 'success') {
        if (!this.elements.$message || !this.elements.$message.length) {
            // Create message container if it doesn't exist
            this.elements.$message = jQuery('<div class="dragwyb-form-message"></div>');
            this.elements.$form.append(this.elements.$message);
        }

        this.elements.$message
            .removeClass('dragwyb-success dragwyb-error')
            .addClass(`dragwyb-${type}`)
            .text(message)
            .show();
    }

    validateForm() {
        let isValid = true;
        this.clearErrors();

        const $fields = this.elements.$form.find('input, select, textarea').not('[type="submit"], [type="button"], [type="hidden"]');
        const validatedGroups = new Set();

        $fields.each((_, el) => {
            // Only validate one radio per group to avoid redundant checks
            if (el.type === 'radio') {
                if (validatedGroups.has(el.name)) return;
                validatedGroups.add(el.name);
            }

            if (!this.handleFieldValidation(el)) {
                isValid = false;
            }
        });

        if (!isValid) {
            this.elements.$form.find('.dragwyb-error').first().focus();
        }

        return isValid;
    }

    handleFieldValidation(element) {
        const $field = jQuery(element);
        if ($field.is('[type="submit"], [type="button"], [type="hidden"]')) return true;

        const validation = this.validateField(element);

        if (!validation.valid) {
            this.showFieldError($field, validation.message);
            return false;
        } else {
            this.clearFieldError($field);
            return true;
        }
    }

    validateField(element) {
        const $field = jQuery(element);

        // Native HTML5 Validation
        if (!element.checkValidity()) {
            let message = element.validationMessage;

            if (element.validity.valueMissing) {
                message = window.DragwybFrontendData?.required_message || window.DragwybFrontendData?.messages?.required || 'This field is required.';
            } else if (element.validity.patternMismatch) {
                message = $field.attr('data-pattern-error') || $field.attr('data-error-message') || window.DragwybFrontendData?.messages?.invalid || 'Invalid field format.';
            } else if (element.validity.tooShort) {
                message = $field.attr('data-minlength-error') || `Minimum length is ${$field.attr('minlength')}.`;
            } else if (element.validity.tooLong) {
                message = $field.attr('data-maxlength-error') || `Maximum length is ${$field.attr('maxlength')}.`;
            } else if (element.validity.rangeUnderflow) {
                message = $field.attr('data-min-error') || `Minimum value is ${$field.attr('min')}.`;
            } else if (element.validity.rangeOverflow) {
                message = $field.attr('data-max-error') || `Maximum value is ${$field.attr('max')}.`;
            } else if (element.validity.typeMismatch) {
                message = $field.attr('data-type-error') || 'Invalid value type.';
            }

            return { valid: false, message };
        }

        // Custom manual pattern validation if no native validity errors
        let value = $field.val();
        if ($field.is(':checkbox') || $field.is(':radio')) {
            if ($field.is(':radio')) {
                value = jQuery(`[name="${element.name}"]:checked`).val() || '';
            } else {
                value = $field.is(':checked') ? $field.val() : '';
            }
        }

        if (value && typeof value === 'string' && value.trim() !== '') {
            let pattern = $field.attr('data-pattern');
            if (!pattern && window.DragwybFrontendData?.regex_patterns) {
                const fieldName = $field.attr('name');
                const fieldType = $field.attr('type');
                pattern = window.DragwybFrontendData.regex_patterns[fieldName] || window.DragwybFrontendData.regex_patterns[fieldType];
            }

            if (pattern) {
                try {
                    const regex = new RegExp(pattern);
                    if (!regex.test(value)) {
                        const fieldMsg = $field.attr('data-pattern-error') || $field.attr('data-error-message') || window.DragwybFrontendData?.field_message || window.DragwybFrontendData?.messages?.invalid || 'Invalid field format.';
                        return { valid: false, message: fieldMsg };
                    }
                } catch (error) {
                    console.error('Invalid regex pattern:', pattern, error);
                }
            }
        }

        return { valid: true };
    }

    showFieldError($field, message) {
        this.clearFieldError($field);

        $field.addClass('dragwyb-error');

        // Append error message inside closest .dragwyb-field-wrapper
        const $wrapper = $field.first().closest('.dragwyb-field-wrapper');
        const $errorMsg = jQuery('<span class="dragwyb-field-validation-error" style="color: #dc3232; font-size: 13px; margin-top: 5px; display: block;"></span>').text(message);

        if ($wrapper.length) {
            $wrapper.append($errorMsg);
        } else {
            const $targetField = $field.last();
            if ($targetField.is(':radio') || $targetField.is(':checkbox')) {
                $targetField.parent().after($errorMsg);
            } else {
                $targetField.after($errorMsg);
            }
        }
    }

    clearFormData() {
        const formFields = this.elements.$form.find('input, select, textarea');

        formFields.each((_, el) => {
            const $field = jQuery(el);

            if ($field.is(':radio') || $field.is(':checkbox')) {
                $field.prop('checked', el.defaultChecked);
            } else if ($field.is('select')) {
                let hasDefault = false;
                $field.find('option').each(function () {
                    this.selected = this.defaultSelected;
                    if (this.defaultSelected) hasDefault = true;
                });
                if (!hasDefault) {
                    if (el.multiple) {
                        $field.val([]);
                    } else if (el.options.length > 0) {
                        el.selectedIndex = 0;
                    }
                }
            } else if ($field.is('[type="file"]')) {
                $field.val('');
            } else {
                $field.val(el.defaultValue !== undefined ? el.defaultValue : '');
            }
        });

        this.clearErrors();
    }

    clearFieldError($field) {
        const $wrapper = $field.first().closest('.dragwyb-field-wrapper');
        if ($wrapper.length) {
            $wrapper.find('.dragwyb-error').removeClass('dragwyb-error');
            $wrapper.find('.dragwyb-field-validation-error, .dragwyb-field-error-message').remove();
        } else {
            $field.removeClass('dragwyb-error');
            $field.parent().find('.dragwyb-field-validation-error, .dragwyb-field-error-message').remove();
            $field.siblings('.dragwyb-field-validation-error, .dragwyb-field-error-message').remove();
        }
    }

    clearErrors() {
        this.elements.$form.find('.dragwyb-error').removeClass('dragwyb-error');
        this.elements.$form.find('.dragwyb-field-validation-error, .dragwyb-field-error-message').remove();
    }
}

export default DragwybFormHandler;
