import React, { useState, useEffect, useRef } from 'react';
import { createPortal } from 'react-dom';
import { useDispatch } from 'react-redux';
import { replaceFormState } from '../../store/actions';
import { __ } from '@wordpress/i18n';
import { FaSearch, FaTimes, FaSpinner, FaEye, FaFolderPlus } from 'react-icons/fa';
import * as Fields from '../Fields';
import PreviewIframe from '../PreviewIframe';
import DragwybControlBase from '../../controlBase';
import store from '../../store';

const controlCache = {}

const TemplateFieldItem = ({ fieldId, template, perviewIFrame }) => {
    if (!fieldId || !template || !template.fields) return null;
    const field = template.fields[fieldId];
    if (!field) return null;

    const fieldSettings = window.DragwybEditor?.fields?.fields?.[field.type];
    const allowedChildren = fieldSettings?.allow_child || false;
    const isRootContainer = field?.is_root_container || false;
    const childrens = field.children;

    let wrapperClass = [];
    let id = `dragwyb-row-${field._id}`;
    if (field.type !== 'row') {
        wrapperClass = ['dragwyb-field-wrapper', `dragwyb-${field.type}-field`];
        let id = `dragwyb-field-wrapper-${field._id}`;
    }

    if (field.css_classes) {
        wrapperClass.push(field.css_classes);
    }

    if (allowedChildren === true || isRootContainer === true) {
        wrapperClass.push(`dragwyb-${field.type}`, 'dragwyb-has-actions');
        id = `dragwyb-${field.type}-${field._id}`;
    }

    if (['button', 'file', 'radio', 'checkbox', 'range'].includes(field.type)) {
        wrapperClass.push("dragwyb-no-float");
    }

    if (window.DragwybBuilder?.Hooks?.applyFilter) {
        wrapperClass = window.DragwybBuilder.Hooks.applyFilter('Dragwyb/Field/WrapperClass', wrapperClass, fieldId, field.type, field.attributes, {});
        wrapperClass = window.DragwybBuilder.Hooks.applyFilter(`Dragwyb/Field/WrapperClass/${field.type}`, wrapperClass, fieldId, field.type, field.attributes, {});
    }

    return (
        <div className={wrapperClass.join(' ')} id={id}>
            <Fields.Preview fields={[field]} values={{}} errors={[]} childrens={childrens} Utils={{}} perviewIFrame={perviewIFrame.contentWindow.document}>
                {childrens && childrens.length > 0 && (
                    childrens.map((childId) => (
                        <TemplateFieldItem key={childId} fieldId={childId} template={template} perviewIFrame={perviewIFrame} />
                    ))
                )}
            </Fields.Preview>
        </div>
    );
};

const getExistingSelector = (existingCss, cssCacheObj) => {
    Object.keys(existingCss).forEach(key => {
        const entry = existingCss[key];
        const selector = Object.keys(entry)[0];

        if (key.startsWith('fields_')) {
            return;
        };

        if (typeof entry[selector] === 'object') {
            if (!cssCacheObj.hasOwnProperty(key)) {
                cssCacheObj[key] = {};
            }

            getExistingSelector(entry, cssCacheObj[key]);
            return;
        }

        cssCacheObj[key] = entry;
    })
}

const generateCssStrings = (cssSelectors) => {
    const cssCache = {};
    let cssString = "";

    Object.keys(cssSelectors).forEach(key => {
        const entry = cssSelectors[key];
        const selector = Object.keys(entry)[0];

        if (!selector) return;

        if (typeof entry[selector] === 'object') {
            if (Object.keys(entry[selector]).length > 0) {
                cssString += generateCssStrings(entry);
            }
            return;
        }

        let rule = Object.values(entry)[0];

        if (!rule) return;

        rule = rule.trim();
        rule = rule.endsWith(';') ? rule : rule + ';';

        if (!cssCache[selector]) {
            cssCache[selector] = [];
        }
        cssCache[selector].push(rule);
    });

    for (const selector in cssCache) {
        if (cssCache.hasOwnProperty(selector)) {
            const rules = cssCache[selector].join(' ');
            cssString += `${selector} { ${rules} }\n`;
        }
    }

    return cssString;
};

const extractCSS = ({ key, value, selectors, placeholders, toolbarType, itemId, currentItemId, responsiveType = 'desktop', initialRender = false, formId, fieldType, cssCache }) => {
    const tempCssCache = {};

    if (Object.keys(placeholders).length === 0) {
        return;
    }

    Object.keys(selectors).forEach((selector) => {
        let wrapperId = formId;

        if (toolbarType === 'fields' && itemId && itemId !== '') {
            if (fieldType === 'row') {
                wrapperId += ' #dragwyb-row-' + itemId;
            } else {
                wrapperId += ' #dragwyb-field-wrapper-' + itemId;
            }
        }

        let targetSelector = selector.replaceAll("{{WRAPPER}}", `#dragwyb-form-wrapper-${wrapperId}`);

        if (currentItemId && '' !== currentItemId && targetSelector.includes('{{CURRENT_ITEM}}')) {
            targetSelector = targetSelector.replaceAll('{{CURRENT_ITEM}}', `.repeat-item-${currentItemId}`);
        }

        tempCssCache[targetSelector] = selectors[selector];

        Object.keys(placeholders).forEach((placeholder) => {
            if (placeholder === 'VALUE' && placeholders[placeholder] === true && ['string', 'number', 'BigInt'].includes(typeof value)) {
                tempCssCache[targetSelector] = tempCssCache[targetSelector].replaceAll("{{VALUE}}", value);
            } else {
                tempCssCache[targetSelector] = tempCssCache[targetSelector].replaceAll("{{" + placeholder + "}}", value[placeholders[placeholder]]);
            }
        });

        // Remove any remaining placeholders and their surrounding text until space or special characters
        Object.keys(tempCssCache).forEach((selector) => {
            if (tempCssCache[selector].includes('{{') && tempCssCache[selector].includes('}}')) {
                let cleanSelectors = tempCssCache[selector].replace(/[^\s:;"'#,()]*\{\{[A-Z0-9_]+\}\}[^\s:;"'#,()]*/g, '');

                cleanSelectors = cleanSelectors.split(';');

                let newCleanSelectors = [];

                cleanSelectors.forEach((cleanSelector) => {
                    const splitValue = cleanSelector.split(':');
                    let valueExist = false;

                    if (splitValue && splitValue[1]) {
                        if (splitValue[1].trim() !== '') {
                            valueExist = splitValue.join(':');
                        } else {
                            valueExist = false;
                        }
                    }
                    if (valueExist && valueExist.trim() !== '') {
                        newCleanSelectors.push(valueExist);
                    }
                });

                if (newCleanSelectors.length > 0) {
                    tempCssCache[selector] = newCleanSelectors.join(';');
                } else {
                    delete tempCssCache[selector];
                }
            }
        });
    });

    if (tempCssCache && Object.keys(tempCssCache).length > 0) {
        if (responsiveType !== 'desktop') {
            if (!cssCache[responsiveType]) {
                cssCache[responsiveType] = {};
            }
            cssCache[responsiveType][key] = tempCssCache;
        } else {
            cssCache[key] = tempCssCache;
        }
    }
}

const getControl = (type, controlCache) => {
    if (controlCache.hasOwnProperty(type)) {
        return controlCache[type];
    }

    let Ctrl = DragwybBuilder.Hooks.applyFilter(
        "Dragwyb/Editor/ControlRender/" + type,
        false
    );

    // 🔹 Validate Control class
    const isValidControl =
        Ctrl &&
        (Ctrl.prototype instanceof DragwybControlBase ||
            Ctrl.prototype instanceof DragwybEditor.editor.extends.ControlBase);

    if (!isValidControl) {
        console.warn(
            `[RenderControl] Invalid or missing Control for type "${type}". Falling back to ControlBase.`
        );
        Ctrl = DragwybEditor.editor.extends.ControlBase;
    }

    controlCache[type] = Ctrl;

    return Ctrl;
}

const generateStyle = (fields, formId, controlCache) => {
    const cssCache = {};
    Object.keys(fields).map((fieldId) => {
        const field = fields[fieldId];
        const fieldSettings = window.DragwybEditor?.fields?.fields?.[field.type];
        const attributes = field.attributes;

        if (!field.attributes || Object.keys(field.attributes).length === 0) {
            return;
        }

        Object.keys(attributes).map((attrId) => {
            if (!fieldSettings?.controls[attrId]?.type) {
                return;
            }

            const Control = getControl(fieldSettings.controls[attrId].type, controlCache);

            new Control({
                id: attrId,
                toolbarId: 'fields',
                selectedSetting: fieldId,
                settings: fieldSettings.controls[attrId],
                value: attributes[attrId],
                Utils: {
                    updateStyleSelectors: (props) => {
                        return extractCSS({ ...props, formId, fieldType: field.type, cssCache })
                    }
                },
            }).renderStyleSelector();
        })
    })

    return cssCache;
}

const TemplatePreviewIframe = ({ template, templateId, fullPreviewTemplate = false, height = '220px', updateCSSCache, cssCache, controlCache }) => {
    const iframeRef = useRef(null);
    const PREVIEW_URL = DragwybEditor?.previewUrl;
    const [mountNode, setMountNode] = useState(null);

    const initIframe = (event) => {
        const iframe = event.target;
        const doc = iframe.contentWindow.document;

        setMountNode(doc.body);
        const editorToolBars = DragwybEditor?.EditorToolbars?.toolbars;

        if (fullPreviewTemplate) {
            let cssCacheObject = {};
            if (cssCache[templateId]) {
                cssCacheObject = cssCache[templateId];
            } else {
                cssCacheObject = generateStyle(template.fields, DragwybEditor?.formId, controlCache);
                updateCSSCache(templateId, cssCacheObject);
            }

            const styleElement = doc.createElement("style");
            styleElement.id = 'dragwyb-template-style-' + template?.advance?.form_name?.split(/\s+|_/gi)?.join('_');

            const tableStyleSelectors = cssCacheObject['tablet'] || {};
            const mobileStyleSelectors = cssCacheObject['mobile'] || {};

            delete cssCacheObject['tablet']
            delete cssCacheObject['mobile']

            let cssString = generateCssStrings(cssCacheObject);

            if (Object.keys(tableStyleSelectors).length > 0) {
                cssString += `@media (max-width: 768px) {
                        ${generateCssStrings(tableStyleSelectors)}
                    }`;
            }

            if (Object.keys(mobileStyleSelectors).length > 0) {
                cssString += `@media (max-width: 480px) {
                        ${generateCssStrings(mobileStyleSelectors)}
                    }`;
            }

            styleElement.textContent = cssString;
            doc.head.appendChild(styleElement);
        }

        if (editorToolBars) {
            const toolbarKeys = Object.keys(editorToolBars);
            toolbarKeys.forEach((key) => {
                const toolbarLocalizeData = DragwybEditor?.[key];

                const iframeWindow = doc.defaultView;

                if (!iframeWindow.hasOwnProperty('DragwybEditor')) {
                    iframeWindow.DragwybEditor = {};
                }

                if (!iframeWindow.hasOwnProperty('faIconsList')) {
                    iframeWindow.DragwybEditor.faIconsList = DragwybEditor.faIconsList;
                }

                if (!iframeWindow.DragwybEditor.hasOwnProperty(key)) {
                    iframeWindow.DragwybEditor[key] = toolbarLocalizeData;
                }
            });
        }
    };

    return (
        <iframe
            ref={iframeRef}
            onLoad={initIframe}
            src={`${PREVIEW_URL}&dragwyb_iframe_mode=true`}
            title="Form Template Preview"
            style={{
                width: '100%',
                height: height,
                border: '1px solid #e2e8f0',
                borderRadius: '8px',
                backgroundColor: '#ffffff',
                overflow: 'auto'
            }}
        >
            {mountNode && iframeRef.current && createPortal(
                <div className={`dragwyb-form-wrapper`} id={`dragwyb-form-wrapper-${DragwybEditor?.formId}`}>
                    <div className='dragwyb-form'>
                        {template.rootContainers && template.rootContainers.map((containerId) => (
                            <TemplateFieldItem
                                key={containerId}
                                fieldId={containerId}
                                template={template}
                                perviewIFrame={iframeRef.current}
                            />
                        ))}
                    </div>
                </div>,
                mountNode
            )}
        </iframe>
    );
};

const TemplateLibrary = ({ isOpen, onClose }) => {
    const dispatch = useDispatch();

    const [templates, setTemplates] = useState(null);
    const [loading, setLoading] = useState(false);
    const [activeTab, setActiveTab] = useState('contact');
    const [searchQuery, setSearchQuery] = useState('');
    const [fullPreviewTemplate, setFullPreviewTemplate] = useState(null);
    const [cssCache, setCssCache] = useState({});

    useEffect(() => {
        if (!isOpen) return;

        if (templates && Object.keys(templates).length > 0) return;

        setLoading(true);
        fetch(`${DragwybEditor.restUrl}dragwyb/v1/templates`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success' || res.data) {
                    setTemplates(res.data);
                }
                setLoading(false);
            })
            .catch(err => {
                console.error(err);
                setLoading(false);
            });
    }, [isOpen]);

    if (!isOpen) return null;

    const handleUpdateCssCache = (id, css) => {
        if (!cssCache.hasOwnProperty(id)) {
            setCssCache(prev => ({
                ...prev,
                [id]: css
            }));
        }
    }

    const handleImport = (templateData, typeKey) => {
        const confirmMsg = __('Warning: Importing this template will overwrite all your current fields. Any unsaved changes will be lost. Do you want to proceed?', 'smart-form-builder-by-dragwyb');
        if (window.confirm(confirmMsg)) {
            let styleSelectors = '';

            if (cssCache[typeKey]) {
                styleSelectors = cssCache[typeKey];
            } else {
                styleSelectors = generateStyle(templateData.fields, DragwybEditor?.formId, controlCache);
                handleUpdateCssCache(typeKey, styleSelectors);
            }

            const storeState = store.getState();
            const storeStyleSelectors = storeState.styleSelectors || {};

            const existingStyleSelectors = {};
            getExistingSelector(storeStyleSelectors, existingStyleSelectors);

            dispatch(replaceFormState(templateData, { ...existingStyleSelectors, ...styleSelectors }));
            onClose();
        }
    };

    const categories = [
        { id: 'contact', name: __('Contact Forms', 'smart-form-builder-by-dragwyb') },
        { id: 'bussiness', name: __('Business Request', 'smart-form-builder-by-dragwyb') },
        { id: 'marketing', name: __('Marketing Lead Gen', 'smart-form-builder-by-dragwyb') },
        { id: 'feedback', name: __('Product Feedback', 'smart-form-builder-by-dragwyb') }
    ];

    // Filter templates
    const getFilteredTemplates = () => {
        if (!templates) return [];

        const query = searchQuery.trim().toLowerCase();

        // If searching, search across all categories
        if (query !== '') {
            const results = [];

            Object.keys(templates).forEach(catId => {
                const list = templates[catId];
                if (Array.isArray(list)) {
                    list.forEach((item, index) => {
                        const title = (item.advance?.form_name || '').toLowerCase();
                        const desc = (item.advance?.form_description || '').toLowerCase();

                        if (title.includes(query) || desc.includes(query)) {
                            results.push({
                                id: `${catId}_${index}`,
                                category: catId,
                                data: item
                            });
                        }
                    });
                }
            });
            return results;
        }

        // Otherwise return active category templates
        const list = templates[activeTab];
        if (Array.isArray(list)) {
            return list.map((item, index) => ({
                id: `${activeTab}_${index}`,
                category: activeTab,
                data: item
            }));
        }

        return [];
    };

    const filteredList = getFilteredTemplates();

    const safeTemplate = (index, activeTab) => {
        const url = DragwybEditor?.pluginUrl + 'assets/img/templates/' + activeTab + '-template-' + (parseInt(index) + 1) + '.png';

        // Trim spaces and minimize to lowercase for evaluation
        const cleanedUrl = url.trim();

        // Reject URLs that attempt to execute JavaScript code or local files
        if (cleanedUrl.toLowerCase().startsWith('javascript:') || cleanedUrl.toLowerCase().startsWith('data:')) {
            return '#';
        }

        return cleanedUrl;
    }

    return (
        <div className="dragwyb-template-modal-overlay" onClick={onClose}>
            <div className="dragwyb-template-modal" onClick={(e) => e.stopPropagation()}>
                <div className="dragwyb-template-modal__header">
                    <h2>
                        <FaFolderPlus className="modal-title-icon" />
                        {__('Form Template Library', 'smart-form-builder-by-dragwyb')}
                    </h2>
                    <button className="close-btn" onClick={onClose}>
                        <FaTimes />
                    </button>
                </div>

                <div className="dragwyb-template-modal__search-wrapper">
                    <div className="search-bar">
                        <FaSearch className="search-icon" />
                        <input
                            type="text"
                            placeholder={__('Search templates by name, description...', 'smart-form-builder-by-dragwyb')}
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                        />
                        {searchQuery && (
                            <button className="clear-search-btn" onClick={() => setSearchQuery('')}>
                                <FaTimes />
                            </button>
                        )}
                    </div>
                </div>

                {/* Only show categories buttons if not searching */}
                {searchQuery.trim() === '' && (
                    <div className="dragwyb-template-modal__categories">
                        {categories.map((cat) => (
                            <button
                                key={cat.id}
                                className={`category-btn ${activeTab === cat.id ? 'active' : ''}`}
                                onClick={() => setActiveTab(cat.id)}
                            >
                                {cat.name}
                            </button>
                        ))}
                    </div>
                )}

                <div className="dragwyb-template-modal__body">
                    {loading && (
                        <div className="loading-spinner">
                            <FaSpinner className="spin" />
                            <p>{__('Loading premium templates...', 'smart-form-builder-by-dragwyb')}</p>
                        </div>
                    )}

                    {!loading && filteredList.length === 0 && (
                        <div className="no-templates-message">
                            <p>{__('No templates found matching your search query.', 'smart-form-builder-by-dragwyb')}</p>
                        </div>
                    )}

                    {!loading && filteredList.length > 0 && (
                        <div className="templates-grid">
                            {filteredList.map((item, index) => {
                                const templateId = item.id;
                                const tData = item.data;

                                return (
                                    <div key={templateId} className="template-card">
                                        <div className="template-card__details">
                                            <h3>{tData.advance?.form_name || __('Prebuilt Form Template', 'smart-form-builder-by-dragwyb')}</h3>
                                            <p className="description">
                                                {tData.advance?.form_description || __('Load this layout to replace your editor canvas.', 'smart-form-builder-by-dragwyb')}
                                            </p>
                                        </div>

                                        <div className="template-card__preview">
                                            <img src={safeTemplate(index, activeTab)} alt={tData.advance?.form_name || __('Prebuilt Form Template', 'smart-form-builder-by-dragwyb')} style={{ width: '100%' }} />
                                        </div>

                                        {/* 3 columns in one section under the iframe */}
                                        <div className="template-card__actions-row">
                                            {/* Column 1: Full Preview Icon Button */}
                                            <div className="action-col button-col">
                                                <button
                                                    className="action-icon-btn preview-btn"
                                                    title={__('Full Screen Preview', 'smart-form-builder-by-dragwyb')}
                                                    onClick={() => setFullPreviewTemplate({ data: tData, templateId })}
                                                >
                                                    <FaEye />
                                                </button>
                                            </div>

                                            {/* Column 2: Insert Icon Button */}
                                            <div className="action-col button-col">
                                                <button
                                                    className="action-icon-btn insert-btn"
                                                    title={__('Insert Fields into Canvas', 'smart-form-builder-by-dragwyb')}
                                                    onClick={() => handleImport(tData, templateId)}
                                                >
                                                    {__('Insert Form', 'smart-form-builder-by-dragwyb')}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>
            </div>

            {/* Full Screen Preview Modal Overlay */}
            {fullPreviewTemplate && (
                <div className="dragwyb-template-full-preview-overlay" onClick={() => setFullPreviewTemplate(null)}>
                    <div className="dragwyb-template-full-preview-modal" onClick={(e) => e.stopPropagation()}>
                        <div className="full-preview-header">
                            <h3>
                                {__('Previewing:', 'smart-form-builder-by-dragwyb')}{' '}
                                {fullPreviewTemplate.data.advance?.form_name}
                            </h3>
                            <button className="close-btn" onClick={() => setFullPreviewTemplate(null)}>
                                <FaTimes />
                            </button>
                        </div>
                        <div className="full-preview-body">
                            <TemplatePreviewIframe
                                template={fullPreviewTemplate.data}
                                templateId={fullPreviewTemplate.templateId}
                                fullPreviewTemplate={true}
                                height="100%"
                                updateCSSCache={handleUpdateCssCache}
                                cssCache={cssCache}
                                controlCache={controlCache}
                            />
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default TemplateLibrary;
