class DragwybEntryViewer {
    constructor() {
        this.viewer = document.querySelector('.dragwyb-entry-viewer');
        this.currentEntry = null;
        this.initialize();
    }

    initialize() {
        this.initializeEventListeners();
        this.initializeTabs();
    }

    initializeEventListeners() {
        // View entry button
        document.querySelectorAll('.view-entry').forEach(button => {
            button.addEventListener('click', () => {
                this.loadEntry(button.dataset.entryId);
            });
        });

        // Close viewer
        this.viewer.querySelector('.close-viewer').addEventListener('click', () => {
            this.closeViewer();
        });

        // Entry actions
        this.viewer.querySelector('.mark-read').addEventListener('click', () => {
            this.updateEntryStatus('read');
        });

        this.viewer.querySelector('.mark-spam').addEventListener('click', () => {
            this.updateEntryStatus('spam');
        });

        this.viewer.querySelector('.delete-entry').addEventListener('click', () => {
            this.deleteEntry();
        });

        // Add note
        this.viewer.querySelector('.save-note').addEventListener('click', () => {
            this.saveNote();
        });
    }

    initializeTabs() {
        const tabs = this.viewer.querySelectorAll('.entry-tabs button');
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                this.switchTab(tab.dataset.tab);
            });
        });
    }

    switchTab(tabId) {
        // Update tab buttons
        this.viewer.querySelectorAll('.entry-tabs button').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabId);
        });

        // Update tab content
        this.viewer.querySelectorAll('[data-tab-content]').forEach(content => {
            content.classList.toggle('active', content.dataset.tabContent === tabId);
        });
    }

    async loadEntry(entryId) {
        try {
            const response = await this.makeRequest('get_entry_details', {
                entry_id: entryId
            });

            if (response.success) {
                this.currentEntry = response.data;
                this.displayEntry();
                this.showViewer();
            } else {
                this.showError(response.data.message);
            }
        } catch (error) {
            this.showError(error.message);
        }
    }

    displayEntry() {
        // Update entry meta
        this.viewer.querySelector('.entry-id').textContent = `#${this.currentEntry.id}`;
        this.viewer.querySelector('.entry-date').textContent = this.currentEntry.submitted_on;
        this.viewer.querySelector('.entry-status').textContent = this.currentEntry.status;

        // Display fields
        const fieldsContainer = this.viewer.querySelector('.entry-fields');
        fieldsContainer.innerHTML = this.generateFieldsHTML();

        // Display files
        const filesContainer = this.viewer.querySelector('.entry-files');
        filesContainer.innerHTML = this.generateFilesHTML();

        // Display notes
        const notesList = this.viewer.querySelector('.notes-list');
        notesList.innerHTML = this.generateNotesHTML();

        // Display submitter info
        const submitterInfo = this.viewer.querySelector('.submitter-info');
        submitterInfo.innerHTML = this.generateSubmitterHTML();
    }

    generateFieldsHTML() {
        return `
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
                    ${this.currentEntry.fields.map(field => `
                        <tr>
                            <td>${field.label}</td>
                            <td>${this.formatFieldValue(field)}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
    }

    generateFilesHTML() {
        if (!this.currentEntry.files.length) {
            return '<p>No files attached</p>';
        }

        return `
            <div class="entry-files-grid">
                ${this.currentEntry.files.map(file => `
                    <div class="file-item">
                        <div class="file-preview">
                            ${this.getFilePreview(file)}
                        </div>
                        <div class="file-info">
                            <span class="file-name">${file.name}</span>
                            <a href="${file.url}" class="button" download>
                                Download
                            </a>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    }

    generateNotesHTML() {
        if (!this.currentEntry.notes.length) {
            return '<p>No notes yet</p>';
        }

        return this.currentEntry.notes.map(note => `
            <div class="note-item">
                <div class="note-meta">
                    <span class="note-author">${note.author}</span>
                    <span class="note-date">${note.date}</span>
                </div>
                <div class="note-content">${note.content}</div>
            </div>
        `).join('');
    }

    generateSubmitterHTML() {
        const submitter = this.currentEntry.submitter;
        return `
            <table class="widefat">
                <tbody>
                    <tr>
                        <th>IP Address</th>
                        <td>${submitter.ip_address}</td>
                    </tr>
                    <tr>
                        <th>User Agent</th>
                        <td>${submitter.user_agent}</td>
                    </tr>
                    <tr>
                        <th>Referer</th>
                        <td>${submitter.referer || 'N/A'}</td>
                    </tr>
                    ${submitter.user_id ? `
                        <tr>
                            <th>User</th>
                            <td>
                                <a href="user-edit.php?user_id=${submitter.user_id}">
                                    View User Profile
                                </a>
                            </td>
                        </tr>
                    ` : ''}
                </tbody>
            </table>
        `;
    }

    formatFieldValue(field) {
        switch (field.type) {
            case 'file':
                return `<a href="${field.value}" target="_blank">View File</a>`;
            case 'checkbox':
                return field.value ? 'Yes' : 'No';
            case 'array':
                return Array.isArray(field.value) ? field.value.join(', ') : field.value;
            default:
                return field.value;
        }
    }

    getFilePreview(file) {
        const imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        const extension = file.name.split('.').pop().toLowerCase();

        if (imageExtensions.includes(extension)) {
            return `<img src="${file.url}" alt="${file.name}">`;
        }

        return `<div class="file-icon">${extension.toUpperCase()}</div>`;
    }

    async updateEntryStatus(status) {
        try {
            const response = await this.makeRequest('update_entry_status', {
                entry_id: this.currentEntry.id,
                status: status
            });

            if (response.success) {
                this.currentEntry.status = status;
                this.viewer.querySelector('.entry-status').textContent = status;
                this.showSuccess(response.data.message);
                this.refreshEntryList();
            } else {
                this.showError(response.data.message);
            }
        } catch (error) {
            this.showError(error.message);
        }
    }

    async deleteEntry() {
        if (!confirm('Are you sure you want to delete this entry?')) {
            return;
        }

        try {
            const response = await this.makeRequest('delete_entry', {
                entry_id: this.currentEntry.id
            });

            if (response.success) {
                this.closeViewer();
                this.showSuccess(response.data.message);
                this.refreshEntryList();
            } else {
                this.showError(response.data.message);
            }
        } catch (error) {
            this.showError(error.message);
        }
    }

    async saveNote() {
        const textarea = this.viewer.querySelector('.add-note textarea');
        const content = textarea.value.trim();

        if (!content) {
            return;
        }

        try {
            const response = await this.makeRequest('save_entry_note', {
                entry_id: this.currentEntry.id,
                content: content
            });

            if (response.success) {
                this.currentEntry.notes.push(response.data.note);
                this.viewer.querySelector('.notes-list').innerHTML = this.generateNotesHTML();
                textarea.value = '';
                this.showSuccess('Note added successfully.');
            } else {
                this.showError(response.data.message);
            }
        } catch (error) {
            this.showError(error.message);
        }
    }

    showViewer() {
        this.viewer.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }

    closeViewer() {
        this.viewer.style.display = 'none';
        document.body.style.overflow = '';
        this.currentEntry = null;
    }

    refreshEntryList() {
        // Reload the entries list
        window.location.reload();
    }

    async makeRequest(action, data) {
        const formData = new FormData();
        formData.append('action', `dragwyb_${action}`);
        formData.append('nonce', dragwybEntries.nonce);

        for (const [key, value] of Object.entries(data)) {
            formData.append(key, value);
        }

        const response = await fetch(dragwybEntries.ajaxUrl, {
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
    new DragwybEntryViewer();
}); 