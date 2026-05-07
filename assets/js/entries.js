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

        // Save Full Edit
        elements.saveEntryBtn.addEventListener('click', saveFullEdit);
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
                    let options = `<option value="0">${i18n.all_forms || 'All Forms'}</option>`;
                    res.data.forms.forEach(form => {
                        options += `<option value="${form.id}">${form.title}</option>`;
                    });
                    elements.formFilter.innerHTML = options;
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

    // Render Table Rows
    function renderTable(entries) {
        if (!entries || entries.length === 0) {
            elements.tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; padding: 20px;">${i18n.no_entries || 'No entries found.'}</td></tr>`;
            return;
        }

        let html = '';
        entries.forEach(entry => {
            html += `
                <tr id="entry-row-${entry.id}">
                    <td class="dragwyb-title-column">
                        <strong>#${entry.id}</strong>
                        <div class="row-actions">
                            <span class="view"><a href="#" class="dragwyb-row-action" data-action="view" data-id="${entry.id}">View</a> | </span>
                            <span class="edit"><a href="#" class="dragwyb-row-action" data-action="edit" data-id="${entry.id}">Edit</a> | </span>
                            <span class="trash"><a href="#" class="dragwyb-row-action submitdelete" data-action="delete" data-id="${entry.id}" style="color: #a00;">Delete</a></span>
                        </div>
                    </td>
                    <td>${entry.form_id}</td>
                    <td>${entry.submission_data_summary}</td>
                    <td>${entry.ip_address}</td>
                    <td>${entry.created_at_formatted}</td>
                </tr>
            `;
        });

        elements.tbody.innerHTML = html;
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

    async function modalWait(startTime, minDuration = 2000) {
        const endTime = performance.now();
        const duration = endTime - startTime;

        if (duration < minDuration) {
            await new Promise(resolve => setTimeout(resolve, minDuration - duration));
        }
    }

    // Modals Logic
    function openViewModal(id) {
        elements.viewModalBody.innerHTML = '<p class="dragwyb-entries-loading">Loading...</p>';
        elements.viewModal.style.display = 'block';
        elements.editModal.style.display = 'none';

        const startTime = performance.now();

        // Add active class for fade in
        elements.modalOverlay.classList.add('dragwyb-active');

        fetchEntry(id).then(async entry => {
            await modalWait(startTime);

            let html = '<table class="widefat striped"><tbody>';
            html += `<tr><td><strong>ID</strong></td><td>${entry.id}</td></tr>`;
            html += `<tr><td><strong>Form ID</strong></td><td>${entry.form_id}</td></tr>`;
            html += `<tr><td><strong>IP Address</strong></td><td>${entry.ip_address}</td></tr>`;
            html += `<tr><td><strong>User Agent</strong></td><td>${entry.user_agent}</td></tr>`;
            html += `<tr><td><strong>Date</strong></td><td>${entry.created_at}</td></tr>`;

            if (entry.submission_data_decoded) {
                for (const [key, value] of Object.entries(entry.submission_data_decoded)) {
                    const displayVal = Array.isArray(value) ? value.join(', ') : value;
                    html += `<tr><td><strong>${key}</strong></td><td>${displayVal}</td></tr>`;
                }
            }
            html += '</tbody></table>';
            elements.viewModalBody.innerHTML = html;
        });
    }

    function openEditModal(id) {
        elements.editModalBody.innerHTML = '<p class="dragwyb-entries-loading">Loading...</p>';
        elements.editEntryId.value = id;
        elements.viewModal.style.display = 'none';
        elements.editModal.style.display = 'block';
        elements.editModal.querySelector('.dragwyb-modal-footer').style.display = 'none';
        const startTime = performance.now();

        // Add active class for fade in
        elements.modalOverlay.classList.add('dragwyb-active');

        fetchEntry(id).then(async entry => {
            await modalWait(startTime);

            let html = '';
            if (entry.submission_data_decoded) {
                for (const [key, value] of Object.entries(entry.submission_data_decoded)) {
                    // We only support editing strings/numbers easily in this basic dynamic form
                    const isArray = Array.isArray(value);
                    const displayVal = isArray ? value.join(', ') : value;

                    html += `
                        <div class="dragwyb-modal-field">
                            <label>${key}</label>
                            <input type="text" class="regular-text dragwyb-dynamic-input" data-key="${key}" data-is-array="${isArray}" value="${displayVal.replace(/"/g, '&quot;')}">
                        </div>
                    `;
                }
            } else {
                html = '<p>No editable JSON data found.</p>';
            }
            elements.editModalBody.innerHTML = html;
            elements.editModal.querySelector('.dragwyb-modal-footer').style.display = 'flex';
        });
    }

    function closeModal() {
        elements.modalOverlay.classList.remove('dragwyb-active');

        // Wait for CSS transition to finish before hiding display
        setTimeout(() => {
            elements.viewModal.style.display = 'none';
            elements.editModal.style.display = 'none';
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
        elements.tbody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: red; padding: 20px;">${message}</td></tr>`;
    }

    init();
});
