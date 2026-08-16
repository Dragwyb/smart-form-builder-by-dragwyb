document.addEventListener('DOMContentLoaded', () => {
    if (!window.DragwybEntriesApp) return;

    const { ajax_url, nonce, i18n } = window.DragwybEntriesApp;

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
        currentEntryId: null // for modals
    };

    // DOM Elements
    const elements = {
        formFilter: document.getElementById('dragwyb-form-filter'),
        searchInput: document.getElementById('dragwyb-entry-search'),
        searchBtn: document.getElementById('dragwyb-search-btn'),
        tbody: document.getElementById('dragwyb-entries-body'),
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
        editModal: document.getElementById('dragwyb-edit-modal'),
        viewModalBody: document.getElementById('dragwyb-view-modal-body'),
        editModalBody: document.getElementById('dragwyb-edit-modal-body'),
        editEntryId: document.getElementById('dragwyb-edit-entry-id'),
        saveEntryBtn: document.getElementById('dragwyb-save-entry-btn'),
        modalCloseBtns: document.querySelectorAll('.dragwyb-modal-close, .dragwyb-modal-close-btn')
    };

    // Initialize
    function init() {
        loadForms();
        loadEntries();
        bindEvents();
    }

    function clearElement(element) {
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

    function appendStatusRow(message, options = {}) {
        clearElement(elements.tbody);
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 6;
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
            loadEntries();
        });

        elements.searchBtn.addEventListener('click', () => {
            state.search = elements.searchInput.value.trim();
            state.offset = 0;
            state.currentPage = 1;
            loadEntries();
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
                loadEntries();
            }
        });

        elements.nextPage.addEventListener('click', (e) => {
            e.preventDefault();
            if (state.currentPage < state.totalPages) {
                state.currentPage++;
                state.offset = (state.currentPage - 1) * state.limit;
                loadEntries();
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
                loadEntries();
            });
        });

        // Row Actions Delegation
        elements.tbody.addEventListener('click', (e) => {
            const target = e.target;
            const actionBtn = target.closest('.dragwyb-row-action');

            if (actionBtn) {
                e.preventDefault();
                const action = actionBtn.dataset.action;
                const id = actionBtn.dataset.id;

                if (action === 'view') {
                    openViewModal(id);
                } else if (action === 'edit') {
                    openEditModal(id);
                } else if (action === 'delete') {
                    deleteEntry(id);
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

        // Save Full Edit
        elements.saveEntryBtn.addEventListener('click', saveFullEdit);

        // Select All checkboxes
        if (elements.selectAll) {
            elements.selectAll.addEventListener('change', (e) => {
                const checkboxes = elements.tbody.querySelectorAll('.dragwyb-entry-checkbox');
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

                const checkedCbs = elements.tbody.querySelectorAll('.dragwyb-entry-checkbox:checked');
                if (checkedCbs.length === 0) {
                    alert('Please select at least one entry.');
                    return;
                }

                if (!confirm(`Are you sure you want to delete ${checkedCbs.length} selected entries?`)) {
                    return;
                }

                const ids = Array.from(checkedCbs).map(cb => cb.value);
                bulkDeleteEntries(ids);
            });
        }

        // Open Export Modal
        if (elements.exportBtn) {
            elements.exportBtn.addEventListener('click', (e) => {
                e.preventDefault();
                const checkedCbs = elements.tbody.querySelectorAll('.dragwyb-entry-checkbox:checked');

                if (checkedCbs.length === 0) {
                    elements.exportWarning.style.display = 'block';
                    elements.exportSelectionInfo.style.display = 'none';
                } else {
                    elements.exportWarning.style.display = 'none';
                    elements.exportSelectionText.textContent = `You have selected ${checkedCbs.length} entries to export.`;
                    elements.exportSelectionInfo.style.display = 'block';
                }

                elements.viewModal.style.display = 'none';
                elements.editModal.style.display = 'none';
                elements.exportModal.style.display = 'flex';
                elements.modalOverlay.classList.add('dragwyb-active');
            });
        }

        // Start Export Process
        if (elements.startExportBtn) {
            elements.startExportBtn.addEventListener('click', () => {
                const checkedCbs = elements.tbody.querySelectorAll('.dragwyb-entry-checkbox:checked');
                const selectedIds = Array.from(checkedCbs).map(cb => cb.value);
                const format = document.querySelector('input[name="export_format"]:checked').value;
                runEntriesExport(selectedIds, format);
            });
        }
    }

    // Load Forms for Dropdown
    function loadForms() {
        const formData = new FormData();
        formData.append('action', 'dragwyb_get_forms');
        formData.append('_ajax_nonce', nonce);

        fetch(ajax_url, {
            method: 'POST',
            body: formData
        })
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

    // Load Entries Table
    function loadEntries() {
        setLoadingState(true);

        const formData = new FormData();
        formData.append('action', 'dragwyb_get_entries');
        formData.append('_ajax_nonce', nonce);
        formData.append('limit', state.limit);
        formData.append('offset', state.offset);
        formData.append('orderby', state.orderby);
        formData.append('order', state.order);
        formData.append('search', state.search);
        formData.append('form_id', state.form_id);

        fetch(ajax_url, {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    renderTable(res.data.entries);
                    updatePagination(res.data.total_items, res.data.total_pages);
                } else {
                    showError(i18n.error_loading || 'Error loading entries.');
                }
            })
            .catch(err => {
                console.error(err);
                showError(i18n.error_loading || 'Error loading entries.');
            })
            .finally(() => {
                setLoadingState(false);
            });
    }

    // Helper to un-highlight unread entry row when opened
    function markRowAsRead(id) {
        const row = document.getElementById(`entry-row-${id}`);
        if (row) {
            row.classList.remove('dragwyb-entry-unread');
            const badge = row.querySelector('.dragwyb-unread-badge');
            if (badge) badge.remove();
        }
    }

    // Render Table Rows
    function renderTable(entries) {
        if (elements.selectAll) {
            elements.selectAll.checked = false;
        }

        if (!entries || entries.length === 0) {
            appendStatusRow(i18n.no_entries || 'No entries found.');
            return;
        }

        clearElement(elements.tbody);
        entries.forEach(entry => {
            const id = String(parseInt(entry.id, 10) || 0);
            const row = document.createElement('tr');
            row.id = `entry-row-${id}`;

            if (entry.status === 'unread' || entry.status === 'init') {
                row.classList.add('dragwyb-entry-unread');
            }

            // Checkbox column
            const cbCell = document.createElement('td');
            cbCell.className = 'check-column';
            const cbInput = document.createElement('input');
            cbInput.type = 'checkbox';
            cbInput.className = 'dragwyb-entry-checkbox';
            cbInput.value = id;
            cbCell.appendChild(cbInput);
            row.appendChild(cbCell);

            const titleCell = appendTextCell(row, '', 'dragwyb-title-column');
            const strong = document.createElement('strong');
            strong.textContent = `#${id}`;
            titleCell.appendChild(strong);

            if (entry.status === 'unread' || entry.status === 'init') {
                const unreadBadge = document.createElement('span');
                unreadBadge.className = 'dragwyb-unread-badge';
                unreadBadge.textContent = 'Unread';
                titleCell.appendChild(unreadBadge);
            }

            const actions = document.createElement('div');
            actions.className = 'row-actions';

            const view = document.createElement('span');
            view.className = 'view';
            view.appendChild(createActionLink('View', 'view', id));
            view.appendChild(document.createTextNode(' | '));

            const edit = document.createElement('span');
            edit.className = 'edit';
            edit.appendChild(createActionLink('Edit', 'edit', id));
            edit.appendChild(document.createTextNode(' | '));

            const trash = document.createElement('span');
            trash.className = 'trash';
            const deleteLink = createActionLink('Delete', 'delete', id, 'submitdelete');
            deleteLink.style.color = '#a00';
            trash.appendChild(deleteLink);

            actions.appendChild(view);
            actions.appendChild(edit);
            actions.appendChild(trash);
            titleCell.appendChild(actions);

            appendTextCell(row, entry.form_id);
            appendTextCell(row, entry.submission_data_summary_text || entry.submission_data_summary || '');
            appendTextCell(row, entry.ip_address);
            appendTextCell(row, entry.created_at_formatted);

            elements.tbody.appendChild(row);
        });
    }

    // Row Actions: Delete
    function deleteEntry(id) {
        if (!confirm('Are you sure you want to delete this entry?')) return;

        const formData = new FormData();
        formData.append('action', 'dragwyb_delete_entry');
        formData.append('_ajax_nonce', nonce);
        formData.append('id', id);

        fetch(ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    // Instantly remove row from DOM
                    const row = document.getElementById(`entry-row-${id}`);
                    if (row) row.remove();

                    // Adjust count slightly, or just reload full page
                    state.totalItems--;
                    elements.totalItemsText.textContent = `${state.totalItems} items`;
                } else {
                    alert(res.data.message || 'Error deleting entry');
                }
            });
    }

    function bulkDeleteEntries(ids) {
        setLoadingState(true);

        const formData = new FormData();
        formData.append('action', 'dragwyb_delete_entries');
        formData.append('_ajax_nonce', nonce);
        ids.forEach(id => formData.append('ids[]', id));

        fetch(ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    loadEntries();
                } else {
                    alert(res.data.message || 'Error deleting entries');
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred while deleting entries.');
            })
            .finally(() => {
                setLoadingState(false);
            });
    }

    async function modalWait(startTime, minDuration = 2000) {
        const endTime = performance.now();
        const duration = endTime - startTime;

        if (duration < minDuration) {
            await new Promise(resolve => setTimeout(resolve, minDuration - duration));
        }
    }

    // Modals Logic
    function openViewModal(id) {
        clearElement(elements.viewModalBody);
        const loading = document.createElement('p');
        loading.className = 'dragwyb-entries-loading';
        loading.textContent = 'Loading...';
        elements.viewModalBody.appendChild(loading);
        elements.viewModal.style.display = 'flex';
        elements.editModal.style.display = 'none';

        const startTime = performance.now();

        // Add active class for fade in
        elements.modalOverlay.classList.add('dragwyb-active');

        fetchEntry(id).then(async entry => {
            markRowAsRead(id);
            await modalWait(startTime);

            clearElement(elements.viewModalBody);

            // Build premium grid layout
            const grid = document.createElement('div');
            grid.className = 'dragwyb-entry-details-grid';

            // Left Panel: Submitted Data
            const leftPanel = document.createElement('div');
            leftPanel.className = 'dragwyb-detail-section';
            const leftTitle = document.createElement('h3');
            leftTitle.textContent = 'Submitted Data Fields';
            leftPanel.appendChild(leftTitle);

            const table = document.createElement('table');
            table.className = 'dragwyb-detail-table';
            const tbody = document.createElement('tbody');

            let hasData = false;
            if (entry.submission_data_decoded) {
                const decoded = entry.submission_data_decoded;
                if (typeof decoded === 'object' && Object.keys(decoded).length > 0) {
                    hasData = true;
                    for (const [key, value] of Object.entries(decoded)) {
                        const tr = document.createElement('tr');
                        const th = document.createElement('th');

                        let label = key;
                        let displayVal = value;
                        if (value && typeof value === 'object' && 'value' in value) {
                            label = value.label !== key ? `${value.label} (${key})` : value.label;
                            displayVal = value.value;
                        }

                        th.textContent = label;
                        const td = document.createElement('td');
                        td.textContent = Array.isArray(displayVal) ? displayVal.join(', ') : (displayVal == null ? '' : String(displayVal));

                        tr.appendChild(th);
                        tr.appendChild(td);
                        tbody.appendChild(tr);
                    }
                }
            }

            if (!hasData) {
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

            // Right Panel: Metadata
            const rightPanel = document.createElement('div');
            rightPanel.className = 'dragwyb-detail-section';
            const rightTitle = document.createElement('h3');
            rightTitle.textContent = 'Metadata';
            rightPanel.appendChild(rightTitle);

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

            addMetaItem('Entry ID', entry.id);
            addMetaItem('Form ID', entry.form_id);
            addMetaItem('IP Address', entry.ip_address);
            addMetaItem('Status', entry.status);
            addMetaItem('Date Submitted', entry.created_at);
            addMetaItem('User Agent', entry.user_agent, 'user-agent-text');

            rightPanel.appendChild(metaList);

            grid.appendChild(leftPanel);
            grid.appendChild(rightPanel);

            elements.viewModalBody.appendChild(grid);
        });
    }

    function openEditModal(id) {
        clearElement(elements.editModalBody);
        const loading = document.createElement('p');
        loading.className = 'dragwyb-entries-loading';
        loading.textContent = 'Loading...';
        elements.editModalBody.appendChild(loading);
        elements.editEntryId.value = id;
        elements.viewModal.style.display = 'none';
        elements.editModal.style.display = 'flex';
        elements.editModal.querySelector('.dragwyb-modal-footer').style.display = 'none';
        const startTime = performance.now();

        // Add active class for fade in
        elements.modalOverlay.classList.add('dragwyb-active');

        fetchEntry(id).then(async entry => {
            markRowAsRead(id);
            await modalWait(startTime);

            clearElement(elements.editModalBody);
            if (entry.submission_data_decoded) {
                for (const [key, value] of Object.entries(entry.submission_data_decoded)) {
                    // We only support editing strings/numbers easily in this basic dynamic form
                    const isArray = Array.isArray(value.value);
                    const displayVal = isArray ? value.value.join(', ') : value.value;

                    const field = document.createElement('div');
                    field.className = 'dragwyb-modal-field';

                    const label = document.createElement('label');
                    label.textContent = value.label !== key ? `${value.label} (${key})` : value.label;

                    const input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'regular-text dragwyb-dynamic-input';
                    input.dataset.key = key;
                    input.dataset.isArray = isArray ? 'true' : 'false';
                    input.value = displayVal == null ? '' : String(displayVal);

                    field.appendChild(label);
                    field.appendChild(input);
                    elements.editModalBody.appendChild(field);
                }
            } else {
                const empty = document.createElement('p');
                empty.textContent = 'No editable JSON data found.';
                elements.editModalBody.appendChild(empty);
            }
            elements.editModal.querySelector('.dragwyb-modal-footer').style.display = 'flex';
        });
    }

    function closeModal() {
        elements.modalOverlay.classList.remove('dragwyb-active');

        // Wait for CSS transition to finish before hiding display
        setTimeout(() => {
            elements.viewModal.style.display = 'none';
            elements.editModal.style.display = 'none';
            if (elements.exportModal) elements.exportModal.style.display = 'none';
            // Reset progress
            if (elements.exportProgressContainer) elements.exportProgressContainer.style.display = 'none';
            if (elements.exportProgressBar) elements.exportProgressBar.style.width = '0%';
            if (elements.exportProgressPercent) elements.exportProgressPercent.textContent = '0%';
            if (elements.startExportBtn) elements.startExportBtn.disabled = false;
        }, 300);
    }

    function saveFullEdit() {
        const id = elements.editEntryId.value;
        const inputs = elements.editModalBody.querySelectorAll('.dragwyb-dynamic-input');

        let newData = {};
        inputs.forEach(input => {
            const key = input.dataset.key;
            const isArray = input.dataset.isArray === 'true';
            let val = input.value;

            if (isArray) {
                val = val.split(',').map(s => s.trim());
            }
            newData[key] = val;
        });

        const formData = new FormData();
        formData.append('action', 'dragwyb_update_entry');
        formData.append('_ajax_nonce', nonce);
        formData.append('id', id);
        formData.append('submission_data', JSON.stringify(newData));

        const btn = elements.saveEntryBtn;
        btn.disabled = true;
        btn.textContent = 'Saving...';

        fetch(ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    closeModal();
                    loadEntries(); // Reload to show updated summary
                } else {
                    alert(res.data.message || 'Error updating entry');
                }
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Save Changes';
            });
    }

    function fetchEntry(id) {
        const formData = new FormData();
        formData.append('action', 'dragwyb_get_entry');
        formData.append('_ajax_nonce', nonce);
        formData.append('id', id);

        return fetch(ajax_url, { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if (res.success) {
                    return res.data.entry;
                } else {
                    alert(res.data.message || 'Error loading entry');
                    throw new Error(res.data.message);
                }
            });
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

    function showError(message) {
        appendStatusRow(message, { color: 'red' });
    }

    function runEntriesExport(selectedIds, format) {
        elements.exportProgressContainer.style.display = 'block';
        elements.startExportBtn.disabled = true;

        // Disable close buttons during export
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
            formData.append('type', 'entries');

            if (selectedIds.length > 0) {
                // If ids are selected, we send selected ids
                selectedIds.forEach(id => formData.append('ids[]', id));
            } else {
                // Otherwise we send filters
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
                            // Finished fetching
                            downloadExportFile(allItems, format, 'entries');
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

        // Flatten data fields
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
