document.addEventListener('DOMContentLoaded', () => {
    if (!window.DragwybErrorLogApp) return;

    const { ajax_url, nonce, i18n } = window.DragwybErrorLogApp;

    // State
    const state = {
        limit: 20,
        offset: 0,
        orderby: 'created_at',
        order: 'DESC',
        search: '',
        form_id: 0,
        currentPage: 1,
        totalPages: 1,
        totalItems: 0,
        entries: [] // cached current page logs
    };

    // DOM Elements
    const elements = {
        formFilter: document.getElementById('dragwyb-form-filter'),
        searchInput: document.getElementById('dragwyb-error-search'),
        searchBtn: document.getElementById('dragwyb-search-btn'),
        tbody: document.getElementById('dragwyb-error-log-body'),
        prevPage: document.getElementById('dragwyb-prev-page'),
        nextPage: document.getElementById('dragwyb-next-page'),
        currentPageText: document.getElementById('dragwyb-current-page'),
        totalPagesText: document.getElementById('dragwyb-total-pages'),
        totalItemsText: document.getElementById('dragwyb-total-items'),
        sortableHeaders: document.querySelectorAll('.dragwyb-custom-table th.sortable'),
        selectAll: document.getElementById('dragwyb-select-all'),
        bulkAction: document.getElementById('dragwyb-bulk-action'),
        bulkActionBtn: document.getElementById('dragwyb-bulk-action-btn'),
        exportBtn: document.getElementById('dragwyb-export-btn'),
        exportModal: document.getElementById('dragwyb-export-modal'),
        exportWarning: document.getElementById('dragwyb-export-warning'),
        exportSelectionInfo: document.getElementById('dragwyb-export-selection-info'),
        exportSelectionText: document.getElementById('dragwyb-export-selection-text'),
        startExportBtn: document.getElementById('dragwyb-start-export-btn'),
        exportProgressContainer: document.getElementById('dragwyb-export-progress-container'),
        exportProgressText: document.getElementById('dragwyb-export-progress-text'),
        exportProgressPercent: document.getElementById('dragwyb-export-progress-percent'),
        exportProgressBar: document.getElementById('dragwyb-export-progress-bar'),

        // Modals
        modalOverlay: document.getElementById('dragwyb-modal-overlay'),
        viewModal: document.getElementById('dragwyb-view-modal'),
        viewModalBody: document.getElementById('dragwyb-view-modal-body'),
        modalCloseBtns: document.querySelectorAll('.dragwyb-modal-close')
    };

    // Initialize
    function init() {
        loadForms();
        loadErrors();
        bindEvents();
    }

    function clearElement(element) {
        if (!element) return;
        while (element.firstChild) {
            element.removeChild(element.firstChild);
        }
    }

    function appendTextCell(row, value, className = '') {
        const cell = document.createElement('td');
        if (className) cell.className = className;
        cell.textContent = value == null ? '' : String(value);
        row.appendChild(cell);
        return cell;
    }

    function appendHtmlCell(row, value, className = '') {
        const cell = document.createElement('td');
        if (className) cell.className = className;

        // Safely parse HTML and append only safe tags (strong, em, br, span with allowed class)
        const parser = new DOMParser();
        const doc = parser.parseFromString(value || '', 'text/html');

        function sanitizeNode(node) {
            if (node.nodeType === Node.TEXT_NODE) {
                return document.createTextNode(node.textContent);
            }
            if (node.nodeType === Node.ELEMENT_NODE) {
                const tag = node.tagName.toLowerCase();
                if (['strong', 'em', 'br', 'span'].includes(tag)) {
                    const el = document.createElement(tag);
                    if (tag === 'span' && node.className === 'dragwyb-error-badge') {
                        el.className = 'dragwyb-error-badge';
                    }
                    for (const child of node.childNodes) {
                        const sanitizedChild = sanitizeNode(child);
                        if (sanitizedChild) {
                            el.appendChild(sanitizedChild);
                        }
                    }
                    return el;
                }
            }
            return document.createTextNode(node.textContent);
        }

        for (const child of doc.body.childNodes) {
            const sanitized = sanitizeNode(child);
            if (sanitized) {
                cell.appendChild(sanitized);
            }
        }

        row.appendChild(cell);
        return cell;
    }

    function appendStatusRow(message, options = {}) {
        clearElement(elements.tbody);
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 7;
        cell.style.textAlign = 'center';
        cell.style.padding = '20px';
        if (options.color) cell.style.color = options.color;
        if (options.className) cell.className = options.className;
        cell.textContent = message;
        row.appendChild(cell);
        elements.tbody.appendChild(row);
    }

    function createActionLink(label, action, id, extraClass = '') {
        const link = document.createElement('a');
        link.href = '#';
        link.className = `dragwyb-row-action${extraClass ? ` ${extraClass}` : ''}`;
        link.dataset.action = action;
        link.dataset.id = id;
        link.textContent = label;
        return link;
    }

    // Bind Events
    function bindEvents() {
        elements.formFilter.addEventListener('change', (e) => {
            state.form_id = parseInt(e.target.value, 10);
            state.offset = 0;
            state.currentPage = 1;
            loadErrors();
        });

        elements.searchBtn.addEventListener('click', () => {
            state.search = elements.searchInput.value.trim();
            state.offset = 0;
            state.currentPage = 1;
            loadErrors();
        });

        elements.searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                elements.searchBtn.click();
            }
        });

        elements.prevPage.addEventListener('click', (e) => {
            e.preventDefault();
            if (state.currentPage > 1) {
                state.currentPage--;
                state.offset = (state.currentPage - 1) * state.limit;
                loadErrors();
            }
        });

        elements.nextPage.addEventListener('click', (e) => {
            e.preventDefault();
            if (state.currentPage < state.totalPages) {
                state.currentPage++;
                state.offset = (state.currentPage - 1) * state.limit;
                loadErrors();
            }
        });

        elements.sortableHeaders.forEach(th => {
            th.addEventListener('click', (e) => {
                e.preventDefault();
                const orderby = th.dataset.orderby;

                elements.sortableHeaders.forEach(otherTh => {
                    if (otherTh !== th) {
                        otherTh.classList.remove('sorted', 'asc', 'desc');
                        otherTh.classList.add('sortable', 'desc');
                    }
                });

                if (state.orderby === orderby) {
                    state.order = state.order === 'ASC' ? 'DESC' : 'ASC';
                } else {
                    state.orderby = orderby;
                    state.order = 'DESC';
                }

                th.classList.remove('sortable', 'asc', 'desc');
                th.classList.add('sorted', state.order.toLowerCase());

                state.offset = 0;
                state.currentPage = 1;
                loadErrors();
            });
        });

        // Row Actions Delegation
        elements.tbody.addEventListener('click', (e) => {
            const target = e.target;
            const actionBtn = target.closest('.dragwyb-row-action');

            if (actionBtn) {
                e.preventDefault();
                const action = actionBtn.dataset.action;
                const id = parseInt(actionBtn.dataset.id, 10);

                if (action === 'view') {
                    openViewModal(id);
                } else if (action === 'delete') {
                    deleteError(id);
                }
            }
        });

        // Modal Close
        elements.modalCloseBtns.forEach(btn => {
            btn.addEventListener('click', closeModal);
        });

        // Close on clicking outside
        elements.modalOverlay.addEventListener('click', (e) => {
            if (e.target === elements.modalOverlay) {
                closeModal();
            }
        });

        // Select All checkboxes
        if (elements.selectAll) {
            elements.selectAll.addEventListener('change', (e) => {
                const checkboxes = elements.tbody.querySelectorAll('.dragwyb-error-checkbox');
                checkboxes.forEach(cb => {
                    cb.checked = e.target.checked;
                });
            });
        }

        // Bulk action Apply button
        if (elements.bulkActionBtn) {
            elements.bulkActionBtn.addEventListener('click', () => {
                const action = elements.bulkAction.value;
                if (action !== 'delete') {
                    alert('Please select a valid bulk action.');
                    return;
                }

                const checkedCbs = elements.tbody.querySelectorAll('.dragwyb-error-checkbox:checked');
                if (checkedCbs.length === 0) {
                    alert('Please select at least one log entry.');
                    return;
                }

                if (!confirm(`Are you sure you want to delete ${checkedCbs.length} selected log entries?`)) {
                    return;
                }

                const ids = Array.from(checkedCbs).map(cb => cb.value);
                bulkDeleteErrors(ids);
            });
        }

        // Open Export Modal
        if (elements.exportBtn) {
            elements.exportBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const checkedCbs = elements.tbody.querySelectorAll('.dragwyb-error-checkbox:checked');

                if (checkedCbs.length === 0) {
                    elements.exportWarning.style.display = 'block';
                    elements.exportSelectionInfo.style.display = 'none';
                } else {
                    elements.exportWarning.style.display = 'none';
                    elements.exportSelectionText.textContent = `You have selected ${checkedCbs.length} log entries to export.`;
                    elements.exportSelectionInfo.style.display = 'block';
                }

                elements.viewModal.style.display = 'none';
                elements.exportModal.style.display = 'flex';
                elements.modalOverlay.classList.add('dragwyb-active');
            });
        }

        // Start Export Process
        if (elements.startExportBtn) {
            elements.startExportBtn.addEventListener('click', () => {
                const checkedCbs = elements.tbody.querySelectorAll('.dragwyb-error-checkbox:checked');
                const selectedIds = Array.from(checkedCbs).map(cb => cb.value);
                const format = document.querySelector('input[name="export_format"]:checked').value;
                runErrorsExport(selectedIds, format);
            });
        }
    }

    // Load Forms for Dropdown
    function loadForms() {
        const formData = new FormData();
        formData.append('action', 'dragwyb_get_forms');
        formData.append('_ajax_nonce', nonce);

        fetch(ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data && res.data.forms) {
                    clearElement(elements.formFilter);

                    const defaultOption = document.createElement('option');
                    defaultOption.value = '0';
                    defaultOption.textContent = i18n.all_forms || 'All Forms';
                    elements.formFilter.appendChild(defaultOption);

                    res.data.forms.forEach(form => {
                        const option = document.createElement('option');
                        option.value = String(parseInt(form.id, 10) || 0);
                        option.textContent = form.title || '';
                        elements.formFilter.appendChild(option);
                    });
                }
            })
            .catch(err => console.error(err));
    }

    // Load Errors Table
    function loadErrors() {
        setLoadingState(true);

        const formData = new FormData();
        formData.append('action', 'dragwyb_get_errors');
        formData.append('_ajax_nonce', nonce);
        formData.append('limit', state.limit);
        formData.append('offset', state.offset);
        formData.append('orderby', state.orderby);
        formData.append('order', state.order);
        formData.append('search', state.search);
        formData.append('form_id', state.form_id);

        fetch(ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    state.entries = res.data.entries || [];
                    renderTable(state.entries);
                    updatePagination(res.data.total_items, res.data.total_pages);
                } else {
                    showError(i18n.error_loading || 'Error loading error logs.');
                }
            })
            .catch(err => {
                console.error(err);
                showError(i18n.error_loading || 'Error loading error logs.');
            })
            .finally(() => {
                setLoadingState(false);
            });
    }

    // Render Table Rows
    function renderTable(entries) {
        if (elements.selectAll) {
            elements.selectAll.checked = false;
        }

        if (!entries || entries.length === 0) {
            appendStatusRow(i18n.no_errors || 'No validation errors logged.');
            return;
        }

        clearElement(elements.tbody);
        entries.forEach(entry => {
            const id = String(parseInt(entry.id, 10) || 0);
            const row = document.createElement('tr');
            row.id = `error-row-${id}`;

            // Checkbox column
            const cbCell = document.createElement('td');
            cbCell.className = 'check-column';
            const cbInput = document.createElement('input');
            cbInput.type = 'checkbox';
            cbInput.className = 'dragwyb-error-checkbox';
            cbInput.value = id;
            cbCell.appendChild(cbInput);
            row.appendChild(cbCell);

            const titleCell = appendTextCell(row, '', 'dragwyb-title-column');
            const strong = document.createElement('strong');
            strong.textContent = `#${id}`;
            titleCell.appendChild(strong);

            const actions = document.createElement('div');
            actions.className = 'row-actions';

            const view = document.createElement('span');
            view.className = 'view';
            view.appendChild(createActionLink('View Details', 'view', id));
            view.appendChild(document.createTextNode(' | '));

            const trash = document.createElement('span');
            trash.className = 'trash';
            const deleteLink = createActionLink('Delete', 'delete', id, 'submitdelete');
            deleteLink.style.color = '#a00';
            trash.appendChild(deleteLink);

            actions.appendChild(view);
            actions.appendChild(trash);
            titleCell.appendChild(actions);

            appendTextCell(row, entry.form_title || entry.form_id);
            appendHtmlCell(row, entry.errors_summary || '');
            appendHtmlCell(row, entry.submission_data_summary || '');
            appendTextCell(row, entry.ip_address);
            appendTextCell(row, entry.created_at_formatted);

            elements.tbody.appendChild(row);
        });
    }

    // Row Actions: Delete
    function deleteError(id) {
        if (!confirm(i18n.delete_confirm || 'Are you sure you want to delete this log entry?')) return;

        const formData = new FormData();
        formData.append('action', 'dragwyb_delete_error');
        formData.append('_ajax_nonce', nonce);
        formData.append('id', id);

        fetch(ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    const row = document.getElementById(`error-row-${id}`);
                    if (row) row.remove();

                    state.totalItems--;
                    elements.totalItemsText.textContent = `${state.totalItems} items`;

                    // Reload if we just deleted the last item on the page
                    if (elements.tbody.children.length === 0) {
                        state.offset = Math.max(0, state.offset - state.limit);
                        state.currentPage = Math.max(1, state.currentPage - 1);
                        loadErrors();
                    }
                } else {
                    alert(res.data.message || 'Error deleting log entry');
                }
            });
    }

    function bulkDeleteErrors(ids) {
        setLoadingState(true);

        const formData = new FormData();
        formData.append('action', 'dragwyb_delete_errors');
        formData.append('_ajax_nonce', nonce);
        ids.forEach(id => formData.append('ids[]', id));

        fetch(ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    loadErrors();
                } else {
                    alert(res.data.message || 'Error deleting log entries');
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred while deleting log entries.');
            })
            .finally(() => {
                setLoadingState(false);
            });
    }

    // Modal view
    function openViewModal(id) {
        const entry = state.entries.find(e => parseInt(e.id, 10) === id);
        if (!entry) return;

        clearElement(elements.viewModalBody);
        elements.viewModal.style.display = 'flex';

        // Build premium grid layout
        const grid = document.createElement('div');
        grid.className = 'dragwyb-error-details-grid';

        // Left Panel: Submitted Data
        const leftPanel = document.createElement('div');
        leftPanel.className = 'dragwyb-detail-section';
        const leftTitle = document.createElement('h3');
        leftTitle.textContent = 'Submitted Data Fields';
        leftPanel.appendChild(leftTitle);

        const table = document.createElement('table');
        table.className = 'dragwyb-detail-table';
        const tbody = document.createElement('tbody');

        const submissionData = JSON.parse(entry.submission_data || '{}');
        const hasData = Object.keys(submissionData).length > 0;

        if (hasData) {
            for (const [key, value] of Object.entries(submissionData)) {
                const tr = document.createElement('tr');
                const th = document.createElement('th');
                th.textContent = value.label !== key ? `${value.label} (${key})` : value.label;
                const td = document.createElement('td');
                td.textContent = Array.isArray(value.value) ? value.join(', ') : String(value.value);
                tr.appendChild(th);
                tr.appendChild(td);
                tbody.appendChild(tr);
            }
        } else {
            const tr = document.createElement('tr');
            const td = document.createElement('td');
            td.colSpan = 2;
            td.style.color = '#888';
            td.style.fontStyle = 'italic';
            td.textContent = 'No submission data recorded.';
            tr.appendChild(td);
            tbody.appendChild(tr);
        }
        table.appendChild(tbody);
        leftPanel.appendChild(table);

        // Right Panel: Validation Errors + Meta
        const rightPanel = document.createElement('div');
        rightPanel.style.display = 'flex';
        rightPanel.style.flexDirection = 'column';
        rightPanel.style.gap = '20px';

        // Errors section
        const errorsSection = document.createElement('div');
        errorsSection.className = 'dragwyb-detail-section errors-section';
        const errorsTitle = document.createElement('h3');
        errorsTitle.textContent = 'Validation Failures';
        errorsSection.appendChild(errorsTitle);

        const errorsList = JSON.parse(entry.errors || '{}');
        for (const [field, message] of Object.entries(errorsList)) {
            const card = document.createElement('div');
            card.className = 'dragwyb-error-detail-card';

            const fieldName = document.createElement('div');
            fieldName.className = 'error-field-name';
            fieldName.textContent = submissionData && submissionData[field] && submissionData[field].label ? `${submissionData[field].label} (${field}) :` : field;

            const errMsg = document.createElement('div');
            errMsg.className = 'error-message';
            errMsg.textContent = message;

            card.appendChild(fieldName);
            card.appendChild(errMsg);
            errorsSection.appendChild(card);
        }
        rightPanel.appendChild(errorsSection);

        // Metadata section
        const metaSection = document.createElement('div');
        metaSection.className = 'dragwyb-detail-section';
        const metaTitle = document.createElement('h3');
        metaTitle.textContent = 'Metadata';
        metaSection.appendChild(metaTitle);

        const metaList = document.createElement('ul');
        metaList.className = 'dragwyb-meta-list';

        const addMetaItem = (label, val, className = '') => {
            const li = document.createElement('li');
            const strong = document.createElement('strong');
            strong.textContent = label + ':';
            li.appendChild(strong);
            li.appendChild(document.createTextNode(' '));
            if (className) {
                const span = document.createElement('span');
                span.className = className;
                span.textContent = val;
                li.appendChild(span);
            } else {
                li.appendChild(document.createTextNode(val));
            }
            metaList.appendChild(li);
        };

        addMetaItem('Form Title', entry.form_title || '#' + entry.form_id);
        addMetaItem('Form ID', entry.form_id);
        addMetaItem('IP Address', entry.ip_address);
        addMetaItem('Date Logged', entry.created_at_formatted);
        addMetaItem('User Agent', entry.user_agent, 'user-agent-text');

        metaSection.appendChild(metaList);
        rightPanel.appendChild(metaSection);

        grid.appendChild(leftPanel);
        grid.appendChild(rightPanel);

        elements.viewModalBody.appendChild(grid);

        elements.modalOverlay.classList.add('dragwyb-active');
    }

    function closeModal() {
        elements.modalOverlay.classList.remove('dragwyb-active');

        setTimeout(() => {
            elements.viewModal.style.display = 'none';
            if (elements.exportModal) elements.exportModal.style.display = 'none';
            // Reset progress
            if (elements.exportProgressContainer) elements.exportProgressContainer.style.display = 'none';
            if (elements.exportProgressBar) elements.exportProgressBar.style.width = '0%';
            if (elements.exportProgressPercent) elements.exportProgressPercent.textContent = '0%';
            if (elements.startExportBtn) elements.startExportBtn.disabled = false;
        }, 300);
    }

    function updatePagination(totalItems, totalPages) {
        state.totalItems = totalItems;
        state.totalPages = totalPages;

        elements.totalItemsText.textContent = `${totalItems} items`;
        elements.currentPageText.textContent = state.currentPage;
        elements.totalPagesText.textContent = totalPages;

        elements.prevPage.disabled = state.currentPage <= 1;
        elements.nextPage.disabled = state.currentPage >= totalPages;
    }

    function setLoadingState(isLoading) {
        if (isLoading) {
            elements.tbody.style.opacity = '0.5';
            elements.tbody.style.pointerEvents = 'none';
        } else {
            elements.tbody.style.opacity = '1';
            elements.tbody.style.pointerEvents = 'auto';
        }
    }

    // eslint-disable-next-line no-unused-vars
    function showError(message) {
        appendStatusRow(message, { color: 'red' });
    }

    function runErrorsExport(selectedIds, format) {
        elements.exportProgressContainer.style.display = 'block';
        elements.startExportBtn.disabled = true;

        const closeBtns = elements.exportModal.querySelectorAll('.dragwyb-modal-close, .dragwyb-modal-close-btn');
        closeBtns.forEach(btn => btn.style.pointerEvents = 'none');

        let allItems = [];
        const limit = 100;
        let offset = 0;

        function updateProgress(current, total) {
            const pct = total > 0 ? Math.round((current / total) * 100) : 0;
            elements.exportProgressPercent.textContent = `${pct}%`;
            elements.exportProgressBar.style.width = `${pct}%`;
            elements.exportProgressText.textContent = `Exporting page ${Math.ceil(current / limit)}...`;
        }

        function fetchBatch() {
            const formData = new FormData();
            formData.append('action', 'dragwyb_get_export_data');
            formData.append('_ajax_nonce', nonce);
            formData.append('type', 'errors');

            if (selectedIds.length > 0) {
                selectedIds.forEach(id => formData.append('ids[]', id));
            } else {
                formData.append('form_id', state.form_id);
                formData.append('search', state.search);
                formData.append('limit', limit);
                formData.append('offset', offset);
            }

            fetch(ajax_url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(res => {
                    if (res.success && res.data) {
                        const items = res.data.items || [];
                        const total = res.data.total || 0;
                        allItems = allItems.concat(items);

                        updateProgress(allItems.length, total);

                        if (selectedIds.length === 0 && allItems.length < total && items.length > 0) {
                            offset += limit;
                            fetchBatch();
                        } else {
                            downloadExportFile(allItems, format, 'errors');
                            cleanupExport();
                        }
                    } else {
                        alert(res.data && res.data.message ? res.data.message : 'Error fetching export data.');
                        cleanupExport();
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('An error occurred during export.');
                    cleanupExport();
                });
        }

        function cleanupExport() {
            closeBtns.forEach(btn => btn.style.pointerEvents = 'auto');
            setTimeout(() => {
                closeModal();
            }, 500);
        }

        fetchBatch();
    }

    function downloadExportFile(items, format, type) {
        if (items.length === 0) {
            alert('No items to export.');
            return;
        }

        let content = '';
        let mimeType = 'text/plain';
        let filename = `${type}_export_${new Date().toISOString().slice(0, 10)}`;

        let headers = ['ID', 'Form ID', 'IP Address', 'User Agent', 'Date'];
        if (type === 'entries') {
            headers.push('Status');
        }

        let submissionKeys = new Set();
        let errorKeys = new Set();
        let itemsData = items.map(item => {
            let fields = {};
            try {
                const parsed = JSON.parse(item.submission_data || '{}');
                for (const [k, v] of Object.entries(parsed)) {
                    const val = (v && typeof v === 'object' && 'value' in v) ? v.value : v;
                    const label = (v && typeof v === 'object' && 'label' in v) ? v.label : k;
                    fields[label] = Array.isArray(val) ? val.join(', ') : val;
                    submissionKeys.add(label);
                }
            } catch (e) {
                console.error('Failed to parse submission data for item ' + item.id, e);
            }

            if (type === 'errors') {
                try {
                    const parsedErrors = JSON.parse(item.errors || '{}');
                    for (const [k, v] of Object.entries(parsedErrors)) {
                        fields['Error: ' + k] = v;
                        errorKeys.add('Error: ' + k);
                    }
                } catch (e) {
                    console.error('Failed to parse errors for item ' + item.id, e);
                }
            }
            return fields;
        });

        const submissionHeaders = Array.from(submissionKeys);
        const errorHeaders = Array.from(errorKeys);

        let allHeaders = [];
        if (type === 'entries') {
            allHeaders = ['ID', 'Form ID', ...submissionHeaders, 'IP Address', 'User Agent', 'Status', 'Date'];
        } else {
            allHeaders = ['ID', 'Form ID', ...submissionHeaders.map(h => 'Field: ' + h), ...errorHeaders, 'IP Address', 'User Agent', 'Date'];
            itemsData = itemsData.map(fields => {
                let mapped = {};
                for (const [k, v] of Object.entries(fields)) {
                    if (k.startsWith('Error: ')) {
                        mapped[k] = v;
                    } else {
                        mapped['Field: ' + k] = v;
                    }
                }
                return mapped;
            });
        }

        function escapeXML(unsafe) {
            if (unsafe == null) return '';
            return String(unsafe).replace(/[<>&'"]/g, function (c) {
                switch (c) {
                    case '<': return '&lt;';
                    case '>': return '&gt;';
                    case '&': return '&amp;';
                    case '\'': return '&apos;';
                    case '"': return '&quot;';
                }
                return c;
            });
        }

        function escapeCSV(val) {
            if (val == null) return '';
            let str = String(val);
            if (str.includes(',') || str.includes('"') || str.includes('\n') || str.includes('\r')) {
                return '"' + str.replace(/"/g, '""') + '"';
            }
            return str;
        }

        if (format === 'json') {
            const jsonItems = items.map((item, index) => {
                let record = {
                    id: parseInt(item.id, 10),
                    form_id: parseInt(item.form_id, 10),
                    ip_address: item.ip_address,
                    user_agent: item.user_agent,
                    created_at: item.created_at
                };
                if (type === 'entries') {
                    record.status = item.status;
                    record.submission_data = itemsData[index];
                } else {
                    let errors = {};
                    try { errors = JSON.parse(item.errors || '{}'); } catch (e) { }
                    record.submission_data = {};
                    for (const [k, v] of Object.entries(itemsData[index])) {
                        if (k.startsWith('Field: ')) {
                            record.submission_data[k.replace('Field: ', '')] = v;
                        }
                    }
                    record.errors = errors;
                }
                return record;
            });
            content = JSON.stringify(jsonItems, null, 2);
            mimeType = 'application/json';
            filename += '.json';
        } else if (format === 'xml') {
            let xml = '<?xml version="1.0" encoding="UTF-8"?>\n<export>\n';
            items.forEach((item, index) => {
                xml += '  <item>\n';
                xml += `    <id>${item.id}</id>\n`;
                xml += `    <form_id>${item.form_id}</form_id>\n`;
                xml += `    <ip_address>${escapeXML(item.ip_address)}</ip_address>\n`;
                xml += `    <user_agent>${escapeXML(item.user_agent)}</user_agent>\n`;
                if (type === 'entries') {
                    xml += `    <status>${escapeXML(item.status)}</status>\n`;
                }
                xml += `    <date>${escapeXML(item.created_at)}</date>\n`;

                xml += '    <data>\n';
                const data = itemsData[index];
                for (const [k, v] of Object.entries(data)) {
                    const cleanTag = k.replace(/[^a-zA-Z0-9_]/g, '_');
                    xml += `      <${cleanTag}>${escapeXML(v)}</${cleanTag}>\n`;
                }
                xml += '    </data>\n';
                xml += '  </item>\n';
            });
            xml += '</export>';
            content = xml;
            mimeType = 'application/xml';
            filename += '.xml';
        } else if (format === 'csv') {
            const csvRows = [];
            csvRows.push(allHeaders.map(escapeCSV).join(','));

            items.forEach((item, index) => {
                const rowData = [];
                allHeaders.forEach(header => {
                    if (header === 'ID') rowData.push(item.id);
                    else if (header === 'Form ID') rowData.push(item.form_id);
                    else if (header === 'IP Address') rowData.push(item.ip_address);
                    else if (header === 'User Agent') rowData.push(item.user_agent);
                    else if (header === 'Status') rowData.push(item.status);
                    else if (header === 'Date') rowData.push(item.created_at);
                    else {
                        const val = itemsData[index][header];
                        rowData.push(val != null ? val : '');
                    }
                });
                csvRows.push(rowData.map(escapeCSV).join(','));
            });
            content = '\uFEFF' + csvRows.join('\n');
            mimeType = 'text/csv;charset=utf-8';
            filename += '.csv';
        } else if (format === 'excel') {
            let excelXml = '<?xml version="1.0" encoding="utf-8"?>\n' +
                '<?mso-application progid="Excel.Sheet"?>\n' +
                '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"\n' +
                ' xmlns:o="urn:schemas-microsoft-com:office:office"\n' +
                ' xmlns:x="urn:schemas-microsoft-com:office:excel"\n' +
                ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"\n' +
                ' xmlns:html="http://www.w3.org/TR/REC-html40">\n' +
                ' <Worksheet ss:Name="Sheet1">\n' +
                ' <Table>\n';

            excelXml += '  <Row>\n';
            allHeaders.forEach(header => {
                excelXml += `   <Cell><Data ss:Type="String">${escapeXML(header)}</Data></Cell>\n`;
            });
            excelXml += '  </Row>\n';

            items.forEach((item, index) => {
                excelXml += '  <Row>\n';
                allHeaders.forEach(header => {
                    let val = '';
                    if (header === 'ID') val = item.id;
                    else if (header === 'Form ID') val = item.form_id;
                    else if (header === 'IP Address') val = item.ip_address;
                    else if (header === 'User Agent') val = item.user_agent;
                    else if (header === 'Status') val = item.status;
                    else if (header === 'Date') val = item.created_at;
                    else {
                        val = itemsData[index][header];
                    }
                    const escapedVal = escapeXML(val);
                    const typeAttr = (header === 'ID' || header === 'Form ID') ? 'Number' : 'String';
                    excelXml += `   <Cell><Data ss:Type="${typeAttr}">${escapedVal}</Data></Cell>\n`;
                });
                excelXml += '  </Row>\n';
            });

            excelXml += ' </Table>\n </Worksheet>\n</Workbook>';
            content = excelXml;
            mimeType = 'application/vnd.ms-excel';
            filename += '.xls';
        }

        const blob = new Blob([content], { type: mimeType });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    init();
});
