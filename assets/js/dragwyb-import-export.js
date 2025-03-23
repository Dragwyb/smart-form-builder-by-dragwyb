class DragwybImportExport {
    constructor() {
        this.initialize();
    }

    initialize() {
        this.initializeEventListeners();
        this.initializeTemplateHandlers();
    }

    initializeEventListeners() {
        // Select all forms checkbox
        const selectAll = document.getElementById('forms-select-all');
        if (selectAll) {
            selectAll.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('input[name="form_ids[]"]');
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
            });
        }

        // Form export via AJAX
        document.querySelectorAll('.export-form-ajax').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportForm(button.dataset.formId);
            });
        });

        // Form import via AJAX
        const importForm = document.querySelector('.dragwyb-import-form');
        if (importForm) {
            importForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.importForm(new FormData(importForm));
            });
        }
    }

    initializeTemplateHandlers() {
        document.querySelectorAll('.use-template').forEach(button => {
            button.addEventListener('click', () => {
                this.useTemplate(button.dataset.templateId);
            });
        });
    }

    async exportForm(formId) {
        try {
            const response = await this.makeRequest('export_form', {
                form_id: formId
            });

            if (response.success) {
                this.downloadFile(
                    response.data.data,
                    response.data.filename,
                    'application/json'
                );
            } else {
                this.showError(response.data.message);
            }

        } catch (error) {
            this.showError(error.message);
        }
    }

    async importForm(formData) {
        try {
            const response = await fetch(dragwybImportExport.ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showSuccess(data.data.message);
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                this.showError(data.data.message);
            }

        } catch (error) {
            this.showError(error.message);
        }
    }

    async useTemplate(templateId) {
        try {
            const response = await this.makeRequest('use_template', {
                template_id: templateId
            });

            if (response.success) {
                window.location.href = response.data.redirect_url;
            } else {
                this.showError(response.data.message);
            }

        } catch (error) {
            this.showError(error.message);
        }
    }

    downloadFile(data, filename, type) {
        const blob = new Blob([JSON.stringify(data, null, 2)], { type });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        
        window.URL.revokeObjectURL(url);
        document.body.removeChild(link);
    }

    async makeRequest(action, data) {
        const formData = new FormData();
        formData.append('action', `dragwyb_${action}`);
        formData.append('nonce', dragwybImportExport.nonce);

        for (const [key, value] of Object.entries(data)) {
            formData.append(key, value);
        }

        const response = await fetch(dragwybImportExport.ajaxUrl, {
            method: 'POST',
            body: formData
        });

        return await response.json();
    }

    showSuccess(message) {
        // Implementation depends on your notification system
        alert(message);
    }

    showError(message) {
        // Implementation depends on your notification system
        alert(message);
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', () => {
    new DragwybImportExport();
}); 