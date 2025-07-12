import { __ } from '@wordpress/i18n';

class FormBuilderAPI {
    constructor() {
        this.nonce = window.DragwybEditor?.nonce || '';
        this.ajaxUrl = window.DragwybEditor?.ajaxUrl || '/wp-admin/admin-ajax.php';
        this.formId = window.DragwybBuilder?.formId || null;
    }

    /**
     * Makes an AJAX request to WordPress
     * @param {string} action - The AJAX action
     * @param {Object} data - The data to send
     * @returns {Promise}
     */
    async request(action, data = {}) {
        try {
            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: `dragwyb_${action}`,
                    _ajax_nonce: this.nonce,
                    ...data,
                }),
                credentials: 'same-origin',
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.data?.message || __('An error occurred', 'dragwyb'));
            }

            return result.data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    /**
     * Saves form data
     * @param {Object} formData - The form data to save
     * @returns {Promise}
     */
    async saveForm(formData) {
        return this.request('save_form', {
            form_id: this.formId,
            form_data: JSON.stringify(formData),
        });
    }

    /**
     * Loads form data
     * @returns {Promise}
     */
    async loadForm() {
        if (!this.formId) {
            throw new Error(__('No form ID provided', 'dragwyb'));
        }

        return this.request('load_form', {
            form_id: this.formId,
        });
    }

    /**
     * Updates form status
     * @param {string} status - The new status
     * @returns {Promise}
     */
    async updateStatus(status) {
        return this.request('update_status', {
            form_id: this.formId,
            status,
        });
    }

    /**
     * Duplicates a form
     * @returns {Promise}
     */
    async duplicateForm() {
        return this.request('duplicate_form', {
            form_id: this.formId,
        });
    }

    /**
     * Saves form settings
     * @param {Object} settings - The settings to save
     * @returns {Promise}
     */
    async saveSettings(settings) {
        return this.request('save_settings', {
            form_id: this.formId,
            settings: JSON.stringify(settings),
        });
    }

    /**
     * Uploads a file
     * @param {File} file - The file to upload
     * @returns {Promise}
     */
    async uploadFile(file) {
        const formData = new FormData();
        formData.append('action', 'dragwyb_upload_file');
        formData.append('_ajax_nonce', this.nonce);
        formData.append('file', file);
        formData.append('form_id', this.formId);

        try {
            const response = await fetch(this.ajaxUrl, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin',
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.data?.message || __('Upload failed', 'dragwyb'));
            }

            return result.data;
        } catch (error) {
            console.error('Upload Error:', error);
            throw error;
        }
    }

    /**
     * Saves a form template
     * @param {Object} template - The template data
     * @returns {Promise}
     */
    async saveTemplate(template) {
        return this.request('save_template', {
            form_id: this.formId,
            template: JSON.stringify(template),
        });
    }

    /**
     * Loads available form templates
     * @returns {Promise}
     */
    async loadTemplates() {
        return this.request('load_templates');
    }

    /**
     * Imports a form from JSON
     * @param {Object} importData - The form data to import
     * @returns {Promise}
     */
    async importForm(importData) {
        return this.request('import_form', {
            import_data: JSON.stringify(importData),
        });
    }

    /**
     * Exports form data
     * @returns {Promise}
     */
    async exportForm() {
        return this.request('export_form', {
            form_id: this.formId,
        });
    }

    /**
     * Validates form settings
     * @param {Object} settings - The settings to validate
     * @returns {Promise}
     */
    async validateSettings(settings) {
        return this.request('validate_settings', {
            form_id: this.formId,
            settings: JSON.stringify(settings),
        });
    }

    /**
     * Tests email notifications
     * @param {Object} emailConfig - The email configuration to test
     * @returns {Promise}
     */
    async testEmail(emailConfig) {
        return this.request('test_email', {
            form_id: this.formId,
            email_config: JSON.stringify(emailConfig),
        });
    }

    /**
     * Gets form statistics
     * @param {string} period - The time period for stats
     * @returns {Promise}
     */
    async getStats(period = '7days') {
        return this.request('get_stats', {
            form_id: this.formId,
            period,
        });
    }
}

// Create and export a single instance
const api = new FormBuilderAPI();
export default api; 