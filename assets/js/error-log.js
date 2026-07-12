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
        clearAllBtn: document.getElementById('dragwyb-clear-all-btn'),
        sortableHeaders: document.querySelectorAll('.dragwyb-custom-table th.sortable'),
        selectAll: document.getElementById('dragwyb-select-all'),
        bulkAction: document.getElementById('dragwyb-bulk-action'),
        bulkActionBtn: document.getElementById('dragwyb-bulk-action-btn'),

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

        // Clear All Logs
        elements.clearAllBtn.addEventListener('click', () => {
            if (!confirm(i18n.clear_confirm || 'Are you sure you want to clear ALL error logs?')) return;

            const formData = new FormData();
            formData.append('action', 'dragwyb_clear_errors');
            formData.append('_ajax_nonce', nonce);

            elements.clearAllBtn.disabled = true;
            elements.clearAllBtn.textContent = 'Clearing...';

            fetch(ajax_url, { method: 'POST', body: formData })
                .then(res => res.json())
                .then(res => {
                    if (res.success) {
                        state.offset = 0;
                        state.currentPage = 1;
                        loadErrors();
                    } else {
                        alert(res.data.message || 'Error clearing logs');
                    }
                })
                .finally(() => {
                    elements.clearAllBtn.disabled = false;
                    elements.clearAllBtn.textContent = '';
                    const icon = document.createElement('span');
                    icon.className = 'dashicons dashicons-trash';
                    elements.clearAllBtn.appendChild(icon);
                    elements.clearAllBtn.appendChild(document.createTextNode(' Clear All Logs'));
                });
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
            cbCell.className = 'column-cb check-column';
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

    init();
});
