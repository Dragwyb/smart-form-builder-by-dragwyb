import React, { useState, useEffect, useMemo } from 'react';

const ImportExportTab = ({ showToast }) => {
    const { ajaxUrl, adminNonce, pluginSlug, version } = window.DragwybSettingsData || {};

    // Export states
    const [exportType, setExportType] = useState('all'); // 'all' or 'selected'
    const [selectedExportFormIds, setSelectedExportFormIds] = useState([]); // array of string IDs
    const [formsList, setFormsList] = useState([]);
    const [isFetchingForms, setIsFetchingForms] = useState(false);
    const [isExporting, setIsExporting] = useState(false);
    const [exportSearchQuery, setExportSearchQuery] = useState('');
    const [isExportDropdownOpen, setIsExportDropdownOpen] = useState(false);

    // Import states
    const [importJsonText, setImportJsonText] = useState('');
    const [selectedFileName, setSelectedFileName] = useState('');
    const [selectedImportFormIndices, setSelectedImportFormIndices] = useState([]); // array of index numbers
    const [isImporting, setIsImporting] = useState(false);
    const [importSearchQuery, setImportSearchQuery] = useState('');
    const [isImportDropdownOpen, setIsImportDropdownOpen] = useState(false);

    // Fetch forms list on mount
    useEffect(() => {
        fetchForms();
    }, []);

    const fetchForms = async () => {
        setIsFetchingForms(true);
        try {
            const formData = new FormData();
            formData.append('action', 'dragwyb_get_forms');
            formData.append('_wpnonce', adminNonce);

            const response = await fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success && result.data.forms) {
                setFormsList(result.data.forms);
            } else {
                showToast(result.data?.message || 'Failed to fetch forms', 'error');
            }
        } catch (error) {
            showToast('Error loading forms list', 'error');
        } finally {
            setIsFetchingForms(false);
        }
    };

    // Filter site forms for export
    const filteredExportForms = formsList.filter(form => {
        const query = exportSearchQuery.toLowerCase().trim();
        if (!query) return true;
        return (
            form.title.toLowerCase().includes(query) ||
            form.id.toString().includes(query)
        );
    });

    // Parse JSON input for import dynamically
    const parsedImportForms = useMemo(() => {
        if (!importJsonText.trim()) return [];
        try {
            const parsed = JSON.parse(importJsonText);
            let forms = [];
            if (parsed && typeof parsed === 'object') {
                if (Array.isArray(parsed.forms)) {
                    forms = parsed.forms;
                } else if (parsed.form_data || parsed.title) {
                    forms = [parsed];
                } else if (parsed.fields || parsed.rootContainers) {
                    forms = [{ form_data: parsed, title: 'Imported Form' }];
                } else if (Array.isArray(parsed)) {
                    forms = parsed;
                }
            }
            return forms.map((item, idx) => ({
                index: idx,
                id: item.id || idx + 1,
                title: item.title || (item.form_data?.settings?.title) || `Form #${item.id || idx + 1}`,
                raw: item
            }));
        } catch (e) {
            return [];
        }
    }, [importJsonText]);

    // When parsed import forms change, default select all indices
    useEffect(() => {
        if (parsedImportForms.length > 0) {
            setSelectedImportFormIndices(parsedImportForms.map(f => f.index));
        } else {
            setSelectedImportFormIndices([]);
        }
    }, [parsedImportForms]);

    // Filter import forms by search query
    const filteredImportForms = parsedImportForms.filter(form => {
        const query = importSearchQuery.toLowerCase().trim();
        if (!query) return true;
        return (
            form.title.toLowerCase().includes(query) ||
            form.id.toString().includes(query)
        );
    });

    // Handle Export Form Selection Checkbox Toggle
    const toggleExportFormSelect = (idStr) => {
        setSelectedExportFormIds(prev =>
            prev.includes(idStr)
                ? prev.filter(i => i !== idStr)
                : [...prev, idStr]
        );
    };

    const toggleAllExportForms = () => {
        if (selectedExportFormIds.length === filteredExportForms.length) {
            setSelectedExportFormIds([]);
        } else {
            setSelectedExportFormIds(filteredExportForms.map(f => f.id.toString()));
        }
    };

    // Handle Import Form Selection Checkbox Toggle
    const toggleImportFormSelect = (index) => {
        setSelectedImportFormIndices(prev =>
            prev.includes(index)
                ? prev.filter(i => i !== index)
                : [...prev, index]
        );
    };

    const toggleAllImportForms = () => {
        if (selectedImportFormIndices.length === filteredImportForms.length) {
            setSelectedImportFormIndices([]);
        } else {
            setSelectedImportFormIndices(filteredImportForms.map(f => f.index));
        }
    };

    // Handle Export Action
    const handleExport = async () => {
        if (exportType === 'selected' && selectedExportFormIds.length === 0) {
            showToast('Please select at least one form to export', 'error');
            return;
        }

        setIsExporting(true);
        try {
            const formData = new FormData();
            formData.append('action', 'dragwyb_export_forms');
            formData.append('_wpnonce', adminNonce);
            formData.append('export_type', exportType);

            if (exportType === 'selected') {
                selectedExportFormIds.forEach(id => {
                    formData.append('form_ids[]', id);
                });
            }

            const response = await fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success && result.data.forms) {
                const exportData = {
                    plugin: pluginSlug || 'smart-form-builder-by-dragwyb',
                    version: version || '1.0.0',
                    exported_at: new Date().toISOString(),
                    forms: result.data.forms
                };

                const dataStr = 'data:text/json;charset=utf-8,' + encodeURIComponent(JSON.stringify(exportData, null, 2));
                const downloadAnchor = document.createElement('a');
                const filename = exportType === 'selected' && selectedExportFormIds.length === 1
                    ? `dragwyb-form-export-${selectedExportFormIds[0]}.json`
                    : `dragwyb-forms-export.json`;

                downloadAnchor.setAttribute('href', dataStr);
                downloadAnchor.setAttribute('download', filename);
                document.body.appendChild(downloadAnchor);
                downloadAnchor.click();
                downloadAnchor.remove();

                showToast('Form(s) exported successfully!', 'success');
            } else {
                showToast(result.data?.message || 'Export failed', 'error');
            }
        } catch (error) {
            showToast('An error occurred during export', 'error');
        } finally {
            setIsExporting(false);
        }
    };

    // Handle File Upload
    const handleFileUpload = (e) => {
        const file = e.target.files[0];
        if (!file) return;

        setSelectedFileName(file.name);
        const reader = new FileReader();
        reader.onload = (event) => {
            setImportJsonText(event.target.result);
        };
        reader.readAsText(file);
    };

    // Handle Import Action
    const handleImport = async () => {
        if (!importJsonText.trim()) {
            showToast('Please select a JSON file or paste JSON data to import', 'error');
            return;
        }

        if (parsedImportForms.length === 0) {
            showToast('Invalid JSON structure or no forms found in input', 'error');
            return;
        }

        if (selectedImportFormIndices.length === 0) {
            showToast('Please select at least one form to import', 'error');
            return;
        }

        const importPayload = parsedImportForms
            .filter(f => selectedImportFormIndices.includes(f.index))
            .map(f => f.raw);

        setIsImporting(true);
        try {
            const formData = new FormData();
            formData.append('action', 'dragwyb_import_forms');
            formData.append('_wpnonce', adminNonce);
            formData.append('import_data', JSON.stringify(importPayload));

            const response = await fetch(ajaxUrl, {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            if (result.success) {
                showToast(result.data.message || 'Import completed successfully!', 'success');
                setImportJsonText('');
                setSelectedFileName('');
                setSelectedImportFormIndices([]);
                fetchForms();
            } else {
                showToast(result.data?.message || 'Import failed', 'error');
            }
        } catch (error) {
            showToast('An error occurred during import', 'error');
        } finally {
            setIsImporting(false);
        }
    };

    return (
        <div className="dragwyb-settings-section">
            <h2>Form Import & Export</h2>
            <p className="dragwyb-settings-desc">
                Export your forms as JSON data or import forms into your website.
            </p>

            <div className="dragwyb-import-export-container" style={{ display: 'flex', flexDirection: 'column', gap: '30px' }}>

                {/* Export Section */}
                <div className="dragwyb-card-box" style={{
                    background: 'hsl(var(--dragwyb-card, 0 0% 100%))',
                    border: '1px solid hsl(var(--dragwyb-border, 0 0% 90%))',
                    borderRadius: '8px',
                    padding: '24px'
                }}>
                    <h3 style={{ margin: '0 0 12px 0', fontSize: '18px', color: 'hsl(var(--dragwyb-foreground))' }}>
                        <i className="fas fa-file-export" style={{ marginRight: '10px', color: 'hsl(var(--dragwyb-primary))' }}></i>
                        Export Forms
                    </h3>
                    <p style={{ margin: '0 0 20px 0', color: 'hsl(var(--dragwyb-muted-foreground))', fontSize: '14px' }}>
                        Select forms to export into a downloadable JSON file.
                    </p>

                    <div className="dragwyb-form-group" style={{ marginBottom: '20px' }}>
                        <label style={{ display: 'block', fontWeight: '600', marginBottom: '8px' }}>Export Scope:</label>
                        <div style={{ display: 'flex', gap: '20px', alignItems: 'center' }}>
                            <label style={{ display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' }}>
                                <input
                                    type="radio"
                                    name="exportType"
                                    value="all"
                                    checked={exportType === 'all'}
                                    onChange={() => setExportType('all')}
                                />
                                All Forms ({formsList.length})
                            </label>
                            <label style={{ display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' }}>
                                <input
                                    type="radio"
                                    name="exportType"
                                    value="selected"
                                    checked={exportType === 'selected'}
                                    onChange={() => setExportType('selected')}
                                />
                                Select Forms ({selectedExportFormIds.length} selected)
                            </label>
                        </div>
                    </div>

                    {/* Selected Forms Multi-Select Dropdown */}
                    {exportType === 'selected' && (
                        <div className="dragwyb-form-group" style={{ marginBottom: '20px', maxWidth: '500px' }}>
                            <label style={{ display: 'block', fontWeight: '600', marginBottom: '8px' }}>
                                Select Form(s) to Export:
                            </label>
                            {isFetchingForms ? (
                                <div style={{ fontSize: '14px', color: 'hsl(var(--dragwyb-muted-foreground))' }}>Loading forms...</div>
                            ) : (
                                <div style={{ position: 'relative' }}>
                                    <div
                                        onClick={() => setIsExportDropdownOpen(!isExportDropdownOpen)}
                                        style={{
                                            padding: '10px 14px',
                                            border: '1px solid hsl(var(--dragwyb-input))',
                                            borderRadius: '6px',
                                            background: 'hsl(var(--dragwyb-background))',
                                            cursor: 'pointer',
                                            display: 'flex',
                                            justify: 'space-between',
                                            alignItems: 'center'
                                        }}
                                    >
                                        <span style={{ fontSize: '14px', fontWeight: '500' }}>
                                            {selectedExportFormIds.length === 0
                                                ? 'Choose form(s)...'
                                                : `${selectedExportFormIds.length} form(s) selected`}
                                        </span>
                                        <i className={`fas fa-chevron-${isExportDropdownOpen ? 'up' : 'down'}`} style={{ fontSize: '12px' }}></i>
                                    </div>

                                    {isExportDropdownOpen && (
                                        <div style={{
                                            position: 'absolute',
                                            top: '100%',
                                            left: 0,
                                            right: 0,
                                            marginTop: '4px',
                                            background: '#ffffff',
                                            border: '1px solid hsl(var(--dragwyb-border))',
                                            borderRadius: '6px',
                                            boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                                            zIndex: 100,
                                            maxHeight: '260px',
                                            overflowY: 'auto'
                                        }}>
                                            <div style={{
                                                padding: '8px',
                                                borderBottom: '1px solid hsl(var(--dragwyb-border))',
                                                display: 'flex',
                                                gap: '8px',
                                                alignItems: 'center'
                                            }}>
                                                <input
                                                    type="text"
                                                    placeholder="Search form title or ID..."
                                                    value={exportSearchQuery}
                                                    onChange={(e) => setExportSearchQuery(e.target.value)}
                                                    onClick={(e) => e.stopPropagation()}
                                                    style={{
                                                        flex: 1,
                                                        padding: '6px 10px',
                                                        border: '1px solid hsl(var(--dragwyb-input))',
                                                        borderRadius: '4px',
                                                        fontSize: '13px',
                                                        boxSizing: 'border-box'
                                                    }}
                                                />
                                                <button
                                                    type="button"
                                                    onClick={(e) => {
                                                        e.stopPropagation();
                                                        toggleAllExportForms();
                                                    }}
                                                    style={{
                                                        padding: '6px 10px',
                                                        fontSize: '12px',
                                                        cursor: 'pointer',
                                                        border: '1px solid #ccc',
                                                        borderRadius: '4px',
                                                        background: '#f8f8f8'
                                                    }}
                                                >
                                                    {selectedExportFormIds.length === filteredExportForms.length ? 'Deselect All' : 'Select All'}
                                                </button>
                                            </div>

                                            {filteredExportForms.length === 0 ? (
                                                <div style={{ padding: '10px 14px', fontSize: '13px', color: 'hsl(var(--dragwyb-muted-foreground))' }}>
                                                    No forms found
                                                </div>
                                            ) : (
                                                filteredExportForms.map((form) => {
                                                    const isChecked = selectedExportFormIds.includes(form.id.toString());
                                                    return (
                                                        <div
                                                            key={form.id}
                                                            onClick={(e) => {
                                                                e.stopPropagation();
                                                                if (e.target.tagName.toLowerCase() !== 'input') {
                                                                    toggleExportFormSelect(form.id.toString());
                                                                }
                                                            }}
                                                            style={{
                                                                padding: '8px 14px',
                                                                cursor: 'pointer',
                                                                background: isChecked ? '#f0f7ff' : 'transparent',
                                                                fontSize: '14px',
                                                                borderBottom: '1px solid #f0f0f0',
                                                                display: 'flex',
                                                                alignItems: 'center',
                                                                gap: '10px'
                                                            }}
                                                        >
                                                            <input
                                                                type="checkbox"
                                                                checked={isChecked}
                                                                onChange={(e) => {
                                                                    e.stopPropagation();
                                                                    toggleExportFormSelect(form.id.toString());
                                                                }}
                                                            />
                                                            <div>
                                                                <strong>{form.title}</strong>{' '}
                                                                <span style={{ color: '#888', fontSize: '12px' }}>(ID: {form.id})</span>
                                                            </div>
                                                        </div>
                                                    );
                                                })
                                            )}
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    )}

                    <button
                        className="dragwyb-btn-primary"
                        onClick={handleExport}
                        disabled={isExporting}
                        style={{ display: 'inline-flex', alignItems: 'center', gap: '8px' }}
                    >
                        <i className={`fas ${isExporting ? 'fa-spinner fa-spin' : 'fa-download'}`}></i>
                        {isExporting ? 'Exporting...' : 'Export Forms'}
                    </button>
                </div>

                {/* Import Section */}
                <div className="dragwyb-card-box" style={{
                    background: 'hsl(var(--dragwyb-card, 0 0% 100%))',
                    border: '1px solid hsl(var(--dragwyb-border, 0 0% 90%))',
                    borderRadius: '8px',
                    padding: '24px'
                }}>
                    <h3 style={{ margin: '0 0 12px 0', fontSize: '18px', color: 'hsl(var(--dragwyb-foreground))' }}>
                        <i className="fas fa-file-import" style={{ marginRight: '10px', color: 'hsl(var(--dragwyb-primary))' }}></i>
                        Import Forms
                    </h3>
                    <p style={{ margin: '0 0 20px 0', color: 'hsl(var(--dragwyb-muted-foreground))', fontSize: '14px' }}>
                        Upload a previously exported JSON file or paste form JSON data directly.
                    </p>

                    <div className="dragwyb-form-group" style={{ marginBottom: '20px' }}>
                        <label style={{ display: 'block', fontWeight: '600', marginBottom: '8px' }}>Select JSON File:</label>
                        <input
                            type="file"
                            accept=".json,application/json"
                            onChange={handleFileUpload}
                            style={{
                                display: 'block',
                                width: '100%',
                                maxWidth: '400px',
                                padding: '8px',
                                border: '1px solid hsl(var(--dragwyb-input))',
                                borderRadius: '4px',
                                background: 'hsl(var(--dragwyb-background))'
                            }}
                        />
                        {selectedFileName && (
                            <div style={{ marginTop: '6px', fontSize: '13px', color: 'green' }}>
                                Selected File: <strong>{selectedFileName}</strong> ({parsedImportForms.length} form(s) found)
                            </div>
                        )}
                    </div>

                    <div className="dragwyb-form-group" style={{ marginBottom: '20px' }}>
                        <label style={{ display: 'block', fontWeight: '600', marginBottom: '8px' }}>Or Paste JSON Data:</label>
                        <textarea
                            rows={5}
                            value={importJsonText}
                            onChange={(e) => setImportJsonText(e.target.value)}
                            placeholder="Paste JSON form data here..."
                            style={{
                                width: '100%',
                                maxWidth: '600px',
                                padding: '10px',
                                border: '1px solid hsl(var(--dragwyb-input))',
                                borderRadius: '6px',
                                fontFamily: 'monospace',
                                fontSize: '13px',
                                background: 'hsl(var(--dragwyb-background))'
                            }}
                        />
                    </div>

                    {/* Selected Forms for Import Dropdown list with Checkboxes & Search */}
                    {parsedImportForms.length > 0 && (
                        <div className="dragwyb-form-group" style={{ marginBottom: '20px', maxWidth: '500px' }}>
                            <label style={{ display: 'block', fontWeight: '600', marginBottom: '8px' }}>
                                Select Form(s) to Import:
                            </label>

                            <div style={{ position: 'relative' }}>
                                <div
                                    onClick={() => setIsImportDropdownOpen(!isImportDropdownOpen)}
                                    style={{
                                        padding: '10px 14px',
                                        border: '1px solid hsl(var(--dragwyb-input))',
                                        borderRadius: '6px',
                                        background: 'hsl(var(--dragwyb-background))',
                                        cursor: 'pointer',
                                        display: 'flex',
                                        justify: 'space-between',
                                        alignItems: 'center'
                                    }}
                                >
                                    <span style={{ fontSize: '14px', fontWeight: '500' }}>
                                        {selectedImportFormIndices.length === 0
                                            ? 'Choose form(s)...'
                                            : `${selectedImportFormIndices.length} of ${parsedImportForms.length} form(s) selected`}
                                    </span>
                                    <i className={`fas fa-chevron-${isImportDropdownOpen ? 'up' : 'down'}`} style={{ fontSize: '12px' }}></i>
                                </div>

                                {isImportDropdownOpen && (
                                    <div style={{
                                        position: 'absolute',
                                        top: '100%',
                                        left: 0,
                                        right: 0,
                                        marginTop: '4px',
                                        background: '#ffffff',
                                        border: '1px solid hsl(var(--dragwyb-border))',
                                        borderRadius: '6px',
                                        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
                                        zIndex: 100,
                                        maxHeight: '260px',
                                        overflowY: 'auto'
                                    }}>
                                        <div style={{
                                            padding: '8px',
                                            borderBottom: '1px solid hsl(var(--dragwyb-border))',
                                            display: 'flex',
                                            gap: '8px',
                                            alignItems: 'center'
                                        }}>
                                            <input
                                                type="text"
                                                placeholder="Search form title or ID..."
                                                value={importSearchQuery}
                                                onChange={(e) => setImportSearchQuery(e.target.value)}
                                                onClick={(e) => e.stopPropagation()}
                                                style={{
                                                    flex: 1,
                                                    padding: '6px 10px',
                                                    border: '1px solid hsl(var(--dragwyb-input))',
                                                    borderRadius: '4px',
                                                    fontSize: '13px',
                                                    boxSizing: 'border-box'
                                                }}
                                            />
                                            <button
                                                type="button"
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    toggleAllImportForms();
                                                }}
                                                style={{
                                                    padding: '6px 10px',
                                                    fontSize: '12px',
                                                    cursor: 'pointer',
                                                    border: '1px solid #ccc',
                                                    borderRadius: '4px',
                                                    background: '#f8f8f8'
                                                }}
                                            >
                                                {selectedImportFormIndices.length === filteredImportForms.length ? 'Deselect All' : 'Select All'}
                                            </button>
                                        </div>

                                        {filteredImportForms.length === 0 ? (
                                            <div style={{ padding: '10px 14px', fontSize: '13px', color: 'hsl(var(--dragwyb-muted-foreground))' }}>
                                                No matching forms found in JSON
                                            </div>
                                        ) : (
                                            filteredImportForms.map((form) => {
                                                const isChecked = selectedImportFormIndices.includes(form.index);
                                                return (
                                                    <div
                                                        key={form.index}
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            if (e.target.tagName.toLowerCase() !== 'input') {
                                                                toggleImportFormSelect(form.index);
                                                            }
                                                        }}
                                                        style={{
                                                            padding: '8px 14px',
                                                            cursor: 'pointer',
                                                            background: isChecked ? '#f0f7ff' : 'transparent',
                                                            fontSize: '14px',
                                                            borderBottom: '1px solid #f0f0f0',
                                                            display: 'flex',
                                                            alignItems: 'center',
                                                            gap: '10px'
                                                        }}
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            checked={isChecked}
                                                            onChange={(e) => {
                                                                e.stopPropagation();
                                                                toggleImportFormSelect(form.index);
                                                            }}
                                                        />
                                                        <div>
                                                            <strong>{form.title}</strong>{' '}
                                                            <span style={{ color: '#888', fontSize: '12px' }}>(ID: {form.id})</span>
                                                        </div>
                                                    </div>
                                                );
                                            })
                                        )}
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    <button
                        className="dragwyb-btn-primary"
                        onClick={handleImport}
                        disabled={isImporting || !importJsonText.trim()}
                        style={{ display: 'inline-flex', alignItems: 'center', gap: '8px' }}
                    >
                        <i className={`fas ${isImporting ? 'fa-spinner fa-spin' : 'fa-upload'}`}></i>
                        {isImporting ? 'Importing...' : 'Import Forms'}
                    </button>
                </div>

            </div>
        </div>
    );
};

export default ImportExportTab;
