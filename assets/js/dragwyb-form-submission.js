class DragwybFormSubmission {
    constructor(formElement) {
        this.form = formElement;
        this.formId = this.form.dataset.formId;
        this.submitButton = this.form.querySelector('[type="submit"]');
        this.setupEventListeners();
    }

    setupEventListeners() {
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    async handleSubmit(e) {
        e.preventDefault();

        try {
            // Disable submit button
            this.setSubmitting(true);

            // Validate form
            if (!this.validateForm()) {
                return;
            }

            // Collect form data
            const formData = new FormData(this.form);
            formData.append('action', 'dragwyb_submit_form');
            formData.append('form_id', this.formId);
            formData.append('nonce', dragwybForm.nonce);

            // Submit form
            const response = await this.submitForm(formData);

            // Handle response
            if (response.success) {
                this.handleSuccess(response.data);
            } else {
                this.handleError(response.data.message);
            }

        } catch (error) {
            this.handleError(error.message);
        } finally {
            this.setSubmitting(false);
        }
    }

    validateForm() {
        // Clear previous errors
        this.clearErrors();

        let isValid = true;
        const requiredFields = this.form.querySelectorAll('[required]');

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                this.showFieldError(field, dragwybForm.strings.required_field);
                isValid = false;
            }
        });

        return isValid;
    }

    async submitForm(formData) {
        const response = await fetch(dragwybForm.ajaxUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(dragwybForm.strings.network_error);
        }

        return await response.json();
    }

    handleSuccess(data) {
        // Show success message
        this.showMessage(data.message, 'success');

        // Handle redirect if specified
        if (data.redirect_url) {
            window.location.href = data.redirect_url;
            return;
        }

        // Reset form
        this.form.reset();

        // Trigger success event
        const event = new CustomEvent('dragwybFormSubmitted', {
            detail: { submissionId: data.submission_id }
        });
        this.form.dispatchEvent(event);
    }

    handleError(message) {
        this.showMessage(message, 'error');
    }

    showMessage(message, type) {
        const messageElement = document.createElement('div');
        messageElement.className = `dragwyb-form-message dragwyb-form-message-${type}`;
        messageElement.textContent = message;

        // Remove any existing messages
        this.form.querySelectorAll('.dragwyb-form-message').forEach(el => el.remove());

        // Add new message
        this.form.insertBefore(messageElement, this.form.firstChild);

        // Auto-remove success messages
        if (type === 'success') {
            setTimeout(() => messageElement.remove(), 5000);
        }
    }

    showFieldError(field, message) {
        const errorElement = document.createElement('div');
        errorElement.className = 'dragwyb-field-error';
        errorElement.textContent = message;

        field.classList.add('dragwyb-field-invalid');
        field.parentNode.appendChild(errorElement);

        // Remove error when field is changed
        field.addEventListener('input', () => {
            field.classList.remove('dragwyb-field-invalid');
            errorElement.remove();
        }, { once: true });
    }

    clearErrors() {
        this.form.querySelectorAll('.dragwyb-field-error').forEach(el => el.remove());
        this.form.querySelectorAll('.dragwyb-field-invalid').forEach(el => {
            el.classList.remove('dragwyb-field-invalid');
        });
    }

    setSubmitting(isSubmitting) {
        this.submitButton.disabled = isSubmitting;
        this.submitButton.classList.toggle('dragwyb-submitting', isSubmitting);
        
        if (isSubmitting) {
            this.submitButton.dataset.originalText = this.submitButton.textContent;
            this.submitButton.textContent = dragwybForm.strings.submitting;
        } else if (this.submitButton.dataset.originalText) {
            this.submitButton.textContent = this.submitButton.dataset.originalText;
        }
    }
}

// Initialize forms
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.dragwyb-form').forEach(form => {
        new DragwybFormSubmission(form);
    });
}); 