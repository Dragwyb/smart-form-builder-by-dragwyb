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

        const submissionEndpoint = window.DragwybFrontendData?.frontendRoute + 'submit';

        jQuery.ajax({
            url: submissionEndpoint,
            type: 'POST',
            data: JSON.stringify(formData),
            processData: false,
            contentType: 'application/json',
            beforeSend: () => {
                this.elements.$submitButton.prop('disabled', true);
                DragwybBuilder.Hooks.doAction('dragwyb/frontend/form/submit_start' + this.formId, this);
            },
            success: (response) => {
                this.elements.$submitButton.prop('disabled', false);
                DragwybBuilder.Hooks.doAction('dragwyb/frontend/form/submit_success' + this.formId, response, this);

                if (response.success && response.data && response.data.message) {
                    this.#showMessage(response.data.message, 'success');
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

    #showMessage(message, type = 'success') {
        if (!this.elements.$message || !this.elements.$message.length) {
            // Create message container if it doesn't exist
            this.elements.$message = jQuery('<div class="dragwyb-form-message"></div>');
            this.elements.$form.append(this.elements.$message);
        }

        this.elements.$message
            .removeClass('dragwyb-success dragwyb-error')
            .addClass(`dragwyb-${type}`)
            .html(message)
            .show();
    }

    validateForm() {
        let isValid = true;
        this.clearErrors();

        const $fields = this.elements.$form.find('input, select, textarea').not('[type="submit"], [type="button"], [type="hidden"]');

        $fields.each((_, el) => {
            const $field = jQuery(el);
            let value = $field.val();

            if ($field.is(':checkbox') || $field.is(':radio')) {
                value = $field.is(':checked') ? $field.val() : '';
            }

            const isRequired = $field.prop('required') || $field.attr('required') === 'required' || $field.hasClass('dragwyb-required');

            // Check Required
            if (isRequired && (!value || (typeof value === 'string' && value.trim() === ''))) {
                const requiredMsg = window.DragwybFrontendData?.required_message || window.DragwybFrontendData?.messages?.required || 'This field is required.';
                this.showFieldError($field, requiredMsg);
                isValid = false;
                return true; // continue to next field
            }

            // Check Regex Pattern
            if (value && typeof value === 'string' && value.trim() !== '') {
                // Try to get pattern from field attribute or localize PHP
                let pattern = $field.attr('pattern') || $field.attr('data-pattern');

                if (!pattern && window.DragwybFrontendData?.regex_patterns) {
                    const fieldName = $field.attr('name');
                    const fieldType = $field.attr('type');
                    pattern = window.DragwybFrontendData.regex_patterns[fieldName] || window.DragwybFrontendData.regex_patterns[fieldType];
                }

                if (pattern) {
                    try {
                        const regex = new RegExp(pattern);
                        if (!regex.test(value)) {
                            const fieldMsg = $field.attr('data-error-message') || window.DragwybFrontendData?.field_message || window.DragwybFrontendData?.messages?.invalid || 'Invalid field format.';
                            this.showFieldError($field, fieldMsg);
                            isValid = false;
                        }
                    } catch (error) {
                        console.error('Invalid regex pattern:', pattern, error);
                    }
                }
            }
        });

        return isValid;
    }

    showFieldError($field, message) {
        $field.addClass('dragwyb-error');
        const $errorMsg = jQuery('<div class="dragwyb-field-error-message" style="color: #dc3232; font-size: 13px; margin-top: 5px;"></div>').text(message);

        if ($field.is(':radio') || $field.is(':checkbox')) {
            $field.parent().after($errorMsg);
        } else {
            $field.after($errorMsg);
        }
    }

    clearErrors() {
        this.elements.$form.find('.dragwyb-error').removeClass('dragwyb-error');
        this.elements.$form.find('.dragwyb-field-error-message').remove();
    }
}

export default DragwybFormHandler;
