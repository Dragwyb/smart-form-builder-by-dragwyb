import React, { useState, useMemo, useCallback, useRef, useEffect } from 'react';
import { createPortal } from 'react-dom';
import { useDispatch, useSelector, useStore } from 'react-redux';
import { __ } from '@wordpress/i18n';
import { FaSearch, FaTimes, FaCheck, FaDesktop, FaTabletAlt, FaMobileAlt, FaSpinner } from 'react-icons/fa';

import DragwybControlBase from '../../controlBase';
import * as Fields from '../Fields';

/**
 * Cache for instantiated Control classes
 */
const controlCache = {};

/**
 * Cache for generated CSS string per preset to avoid re-generating styles
 */
const presetCssCache = {};

/**
 * Get or cache control instance class
 */
const getControl = (type, cache = controlCache) => {
    if (cache.hasOwnProperty(type)) {
        return cache[type];
    }

    let Ctrl = window.DragwybBuilder?.Hooks?.applyFilter(
        "Dragwyb/Editor/ControlRender/" + type,
        false
    );

    const isValidControl =
        Ctrl &&
        (Ctrl.prototype instanceof DragwybControlBase ||
            Ctrl.prototype instanceof window.DragwybEditor?.editor?.extends?.ControlBase);

    if (!isValidControl) {
        Ctrl = DragwybControlBase || window.DragwybEditor?.editor?.extends?.ControlBase;
    }

    cache[type] = Ctrl;
    return Ctrl;
};

/**
 * Convert css selectors cache object into formatted CSS string
 */
const generateCssStrings = (cssSelectors) => {
    const cssRulesMap = {};
    let cssString = "";

    if (!cssSelectors || typeof cssSelectors !== 'object') {
        return cssString;
    }

    Object.keys(cssSelectors).forEach((key) => {
        const entry = cssSelectors[key];
        if (!entry || typeof entry !== 'object') return;

        Object.keys(entry).forEach((selector) => {
            const rawRule = entry[selector];

            if (!selector) return;

            if (typeof rawRule === 'object' && rawRule !== null) {
                if (Object.keys(rawRule).length > 0) {
                    cssString += generateCssStrings({ [selector]: rawRule });
                }
                return;
            }

            if (typeof rawRule === 'string') {
                let rule = rawRule.trim();
                if (!rule) return;
                rule = rule.endsWith(';') ? rule : rule + ';';

                if (!cssRulesMap[selector]) {
                    cssRulesMap[selector] = [];
                }
                cssRulesMap[selector].push(rule);
            }
        });
    });

    for (const selector in cssRulesMap) {
        if (Object.prototype.hasOwnProperty.call(cssRulesMap, selector)) {
            const rules = cssRulesMap[selector].join(' ');
            cssString += `${selector} { ${rules} }\n`;
        }
    }

    return cssString;
};

/**
 * Extract CSS rules replacing {{WRAPPER}} and placeholders with control values
 */
const extractCSS = ({ key, value, selectors, placeholders, toolbarType, itemId, currentItemId, responsiveType = 'desktop', formId, fieldType, cssCache }) => {
    const tempCssCache = {};

    if (!placeholders || Object.keys(placeholders).length === 0 || !selectors) {
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
            } else if (value && typeof value === 'object' && placeholders[placeholder] !== undefined) {
                tempCssCache[targetSelector] = tempCssCache[targetSelector].replaceAll("{{" + placeholder + "}}", value[placeholders[placeholder]]);
            }
        });
    });

    // Remove any remaining placeholders and their empty property declarations
    Object.keys(tempCssCache).forEach((cacheKey) => {
        if (tempCssCache[cacheKey].includes('{{') && tempCssCache[cacheKey].includes('}}')) {
            let cleanSelectors = tempCssCache[cacheKey].replace(/[^\s:;"'#,()]*\{\{[A-Z0-9_]+\}\}[^\s:;"'#,()]*/g, '');

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
                tempCssCache[cacheKey] = newCleanSelectors.join(';') + ';';
            } else {
                delete tempCssCache[cacheKey];
            }
        }
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
};

/**
 * Dynamically generate style rules for toolbar controls (e.g. 'style' toolbar)
 */
const generateStyleForToolbar = (toolbarId, toolbarValues, formId, cache = controlCache) => {
    const cssCache = {};
    const toolbarSettings = window.DragwybEditor?.[toolbarId]?.controls || {};

    if (!toolbarSettings || Object.keys(toolbarSettings).length === 0) {
        return cssCache;
    }

    Object.keys(toolbarSettings).forEach((controlKey) => {
        const controlConfig = toolbarSettings[controlKey];
        if (!controlConfig) return;

        const value = toolbarValues?.[controlKey] !== undefined ? toolbarValues[controlKey] : controlConfig.default;
        if (value === undefined || value === null || value === '') {
            return;
        }

        let rendered = false;
        if (controlConfig.type) {
            try {
                const Control = getControl(controlConfig.type, cache);
                if (Control) {
                    new Control({
                        id: controlKey,
                        toolbarId: toolbarId,
                        selectedSetting: false,
                        settings: controlConfig,
                        value: value,
                        Utils: {
                            updateStyleSelectors: (props) => {
                                rendered = true;
                                return extractCSS({ ...props, formId, cssCache });
                            }
                        },
                    }).renderStyleSelector();
                }
            } catch (err) {
                // fallback to direct extractCSS below
            }
        }

        // Direct extractCSS fallback if not already extracted
        if (!rendered && controlConfig.selectors && controlConfig.selectors_placeholders) {
            const uniqueSelector = `${toolbarId}_${controlKey}`;
            extractCSS({
                key: uniqueSelector,
                value: value,
                selectors: controlConfig.selectors,
                placeholders: controlConfig.selectors_placeholders,
                toolbarType: toolbarId,
                itemId: false,
                formId,
                cssCache,
                responsiveType: controlConfig.responsive_control && controlConfig.responsive_type ? controlConfig.responsive_type : 'desktop'
            });
        }
    });

    return cssCache;
};

/**
 * Retrieve compiled CSS for preset and cache to avoid recomputing
 */
const getPresetCompiledCss = (presetKey, styles, formId, existingFieldSelectors = {}) => {
    const cacheKey = `${presetKey}_${formId}`;
    if (presetCssCache[cacheKey]) {
        return presetCssCache[cacheKey];
    }

    const cssCacheObject = generateStyleForToolbar('style', styles, formId, controlCache);

    // Merge existing field-level selectors (column layouts, row grids, etc.)
    if (existingFieldSelectors && typeof existingFieldSelectors === 'object') {
        Object.keys(existingFieldSelectors).forEach((key) => {
            if (key !== 'tablet' && key !== 'mobile' && !cssCacheObject[key]) {
                cssCacheObject[key] = existingFieldSelectors[key];
            }
        });
        if (existingFieldSelectors.tablet) {
            if (!cssCacheObject.tablet) cssCacheObject.tablet = {};
            Object.assign(cssCacheObject.tablet, existingFieldSelectors.tablet);
        }
        if (existingFieldSelectors.mobile) {
            if (!cssCacheObject.mobile) cssCacheObject.mobile = {};
            Object.assign(cssCacheObject.mobile, existingFieldSelectors.mobile);
        }
    }

    const tableStyleSelectors = cssCacheObject['tablet'] || {};
    const mobileStyleSelectors = cssCacheObject['mobile'] || {};

    delete cssCacheObject['tablet'];
    delete cssCacheObject['mobile'];

    let cssString = generateCssStrings(cssCacheObject);

    if (Object.keys(tableStyleSelectors).length > 0) {
        cssString += `@media (max-width: 768px) {\n${generateCssStrings(tableStyleSelectors)}\n}`;
    }

    if (Object.keys(mobileStyleSelectors).length > 0) {
        cssString += `@media (max-width: 480px) {\n${generateCssStrings(mobileStyleSelectors)}\n}`;
    }

    presetCssCache[cacheKey] = cssString;
    return cssString;
};

/**
 * Cached Preview Iframe URL with dragwyb_iframe_mode=true
 */
const PREVIEW_URL = window.DragwybEditor?.previewUrl || (typeof DragwybEditor !== 'undefined' ? DragwybEditor?.previewUrl : '');
const PREVIEW_IFRAME_URL = PREVIEW_URL ? `${PREVIEW_URL}&dragwyb_iframe_mode=true` : '';

/**
 * Recursive Real Form Field Renderer (re-uses Fields.Preview without editor dnd handles)
 */
const RealFormFieldItem = ({ fieldId, fields, iframeDoc }) => {
    if (!fieldId || !fields || !iframeDoc) return null;
    const field = fields[fieldId];
    if (!field) return null;

    const fieldSettings = window.DragwybEditor?.fields?.fields?.[field.type];
    const allowedChildren = fieldSettings?.allow_child || false;
    const isRootContainer = field?.is_root_container || false;
    const childrens = field.children;

    let wrapperClass = [];
    let id = `dragwyb-row-${field._id}`;
    if (field.type !== 'row') {
        wrapperClass = ['dragwyb-field-wrapper', `dragwyb-${field.type}-field`];
        id = `dragwyb-field-wrapper-${field._id}`;
    }

    if (field.css_classes) {
        wrapperClass.push(field.css_classes);
    }

    if (allowedChildren === true || isRootContainer === true) {
        wrapperClass.push(`dragwyb-${field.type}`, 'dragwyb-has-actions');
        id = `dragwyb-${field.type}-${field._id}`;
    }

    if (['button', 'file', 'radio', 'checkbox', 'range', 'gdpr'].includes(field.type)) {
        wrapperClass.push("dragwyb-no-float");
    }

    if (window.DragwybBuilder?.Hooks?.applyFilter) {
        wrapperClass = window.DragwybBuilder.Hooks.applyFilter('Dragwyb/Field/WrapperClass', wrapperClass, fieldId, field.type, field.attributes, {});
        wrapperClass = window.DragwybBuilder.Hooks.applyFilter(`Dragwyb/Field/WrapperClass/${field.type}`, wrapperClass, fieldId, field.type, field.attributes, {});
    }

    return (
        <div className={wrapperClass.join(' ')} id={id}>
            <Fields.Preview fields={[field]} values={{}} errors={[]} childrens={childrens} Utils={{}} perviewIFrame={iframeDoc}>
                {childrens && childrens.length > 0 && (
                    childrens.map((childId) => (
                        <RealFormFieldItem
                            key={childId}
                            fieldId={childId}
                            fields={fields}
                            iframeDoc={iframeDoc}
                        />
                    ))
                )}
            </Fields.Preview>
        </div>
    );
};

/**
 * Form Content Component rendered inside iframe portal with scale(0.7)
 */
const PresetFormContent = ({ hasRealFields, rootContainers, formFields, wrapperClasses, formId, stepIndicatorType, iframeDoc, onHeightChange }) => {
    const contentRef = useRef(null);

    useEffect(() => {
        if (!contentRef.current) return;

        const updateHeight = () => {
            if (contentRef.current) {
                const unscaledHeight = contentRef.current.offsetHeight || contentRef.current.scrollHeight;
                if (unscaledHeight > 0 && onHeightChange) {
                    onHeightChange(Math.ceil(unscaledHeight * 0.7));
                }
            }
        };

        updateHeight();

        let observer;
        if (typeof ResizeObserver !== 'undefined') {
            observer = new ResizeObserver((entries) => {
                for (let entry of entries) {
                    const unscaledHeight = entry.borderBoxSize?.[0]?.blockSize || entry.contentRect?.height || contentRef.current?.offsetHeight;
                    if (unscaledHeight > 0 && onHeightChange) {
                        onHeightChange(Math.ceil(unscaledHeight * 0.7));
                    }
                }
            });
            observer.observe(contentRef.current);
        }

        return () => {
            if (observer) observer.disconnect();
        };
    }, [onHeightChange, hasRealFields, rootContainers, formFields]);

    const innerContent = hasRealFields ? (
        <div
            className={wrapperClasses}
            id={`dragwyb-form-wrapper-${formId}`}
            data-step-indicator={stepIndicatorType}
        >
            <div
                className="dragwyb-form"
                id={`dragwyb-form-${formId}`}
                data-step-indicator={stepIndicatorType}
            >
                {rootContainers.map((containerId) => (
                    <RealFormFieldItem
                        key={containerId}
                        fieldId={containerId}
                        fields={formFields}
                        iframeDoc={iframeDoc}
                    />
                ))}
            </div>
        </div>
    ) : (
        <div
            className={wrapperClasses}
            id={`dragwyb-form-wrapper-${formId}`}
            data-step-indicator={stepIndicatorType}
        >
            <form
                className="dragwyb-form"
                id={`dragwyb-form-${formId}`}
                data-step-indicator={stepIndicatorType}
                onSubmit={(e) => e.preventDefault()}
            >
                <div id={`dragwyb-row-${formId}_name_row`} className="dragwyb-row">
                    <div
                        id={`dragwyb-field-wrapper-${formId}_name`}
                        className="dragwyb-field-wrapper dragwyb-text-field dragwyb-last-field"
                    >
                        <div className="dragwyb-input-group">
                            <input
                                type="text"
                                id={`field_${formId}_name`}
                                name={`field_${formId}_name`}
                                defaultValue=""
                                placeholder=" "
                                className="dragwyb-field-input"
                                onClick={(e) => e.stopPropagation()}
                            />
                            <label
                                htmlFor={`field_${formId}_name`}
                                className="dragwyb-field-label"
                            >
                                {__('Your Name', 'smart-form-builder-by-dragwyb')}
                            </label>
                        </div>
                    </div>
                </div>

                <div id={`dragwyb-row-${formId}_email_row`} className="dragwyb-row">
                    <div
                        id={`dragwyb-field-wrapper-${formId}_email`}
                        className="dragwyb-field-wrapper dragwyb-email-field dragwyb-last-field"
                    >
                        <div className="dragwyb-input-group">
                            <input
                                type="email"
                                id={`field_${formId}_email`}
                                name={`field_${formId}_email`}
                                defaultValue=""
                                placeholder=" "
                                className="dragwyb-field-input"
                                onClick={(e) => e.stopPropagation()}
                            />
                            <label
                                htmlFor={`field_${formId}_email`}
                                className="dragwyb-field-label"
                            >
                                {__('Your Email', 'smart-form-builder-by-dragwyb')}
                            </label>
                        </div>
                    </div>
                </div>

                <div id={`dragwyb-row-${formId}_message_row`} className="dragwyb-row">
                    <div
                        id={`dragwyb-field-wrapper-${formId}_message`}
                        className="dragwyb-field-wrapper dragwyb-textarea-field dragwyb-last-field"
                    >
                        <div className="dragwyb-input-group">
                            <textarea
                                id={`field_${formId}_message`}
                                name={`field_${formId}_message`}
                                rows={3}
                                placeholder=" "
                                className="dragwyb-field-input dragwyb-field-textarea"
                                onClick={(e) => e.stopPropagation()}
                            />
                            <label
                                htmlFor={`field_${formId}_message`}
                                className="dragwyb-field-label"
                            >
                                {__('Message', 'smart-form-builder-by-dragwyb')}
                            </label>
                        </div>
                    </div>
                </div>

                <div id={`dragwyb-row-${formId}_submit`} className="dragwyb-row dragwyb-last-row">
                    <div
                        id={`dragwyb-field-wrapper-${formId}_submit_btn`}
                        className="dragwyb-field-wrapper dragwyb-button-field dragwyb-last-field business-submit dragwyb-no-float"
                    >
                        <button
                            type="button"
                            id={`btn_${formId}_submit`}
                            className="dragwyb-btn dragwyb-btn-submit"
                            onClick={(e) => {
                                e.stopPropagation();
                                e.preventDefault();
                            }}
                        >
                            {__('Submit', 'smart-form-builder-by-dragwyb')}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    );

    return (
        <div
            className="dragwyb-preset-scale-content"
            ref={contentRef}
            style={{
                width: '142.857143%',
                transform: 'scale(0.7)',
                transformOrigin: 'top left',
                boxSizing: 'border-box',
            }}
        >
            {innerContent}
        </div>
    );
};

/**
 * Single High-Performance Live Preview Component
 */
const SinglePresetLivePreview = React.memo(({ preset, formId, formState, styleSelectors }) => {
    const iframeRef = useRef(null);
    const [mountNode, setMountNode] = useState(null);
    const [isLoading, setIsLoading] = useState(true);
    const [scaledHeight, setScaledHeight] = useState(null);

    const handleHeightChange = useCallback((height) => {
        setScaledHeight(height);
    }, []);

    const presetKey = preset?.key || 'theme';
    const styles = preset?.styles || {};

    // Extract existing field-level selectors from store
    const existingFieldSelectors = useMemo(() => {
        if (!styleSelectors || typeof styleSelectors !== 'object') return {};
        const fieldCss = {};
        Object.keys(styleSelectors).forEach((key) => {
            if (key.startsWith('fields_')) {
                fieldCss[key] = styleSelectors[key];
            }
        });
        if (styleSelectors.tablet) {
            fieldCss.tablet = {};
            Object.keys(styleSelectors.tablet).forEach((key) => {
                if (key.startsWith('fields_')) {
                    fieldCss.tablet[key] = styleSelectors.tablet[key];
                }
            });
        }
        if (styleSelectors.mobile) {
            fieldCss.mobile = {};
            Object.keys(styleSelectors.mobile).forEach((key) => {
                if (key.startsWith('fields_')) {
                    fieldCss.mobile[key] = styleSelectors.mobile[key];
                }
            });
        }
        return fieldCss;
    }, [styleSelectors]);

    const compiledCss = useMemo(() => {
        return getPresetCompiledCss(presetKey, styles, formId, existingFieldSelectors);
    }, [presetKey, styles, formId, existingFieldSelectors]);

    const labelPosition = styles?.label_position || 'top';
    const floatingStyle = styles?.floating_style || 'outlined';
    const formBgType = styles?.form_container_bg_background || 'color';
    const stepIndicatorType = styles?.step_indicator_type || 'number';

    const wrapperClasses = useMemo(() => {
        const classes = ['dragwyb-form-wrapper'];
        if (labelPosition) {
            classes.push(`dragwyb-layout-${labelPosition}`);
            if (labelPosition === 'floating') {
                classes.push(`dragwyb-float-${floatingStyle}`);
            }
        }
        if (formBgType) {
            classes.push(`dragwyb-bg-${formBgType}`);
        }
        return classes.join(' ');
    }, [labelPosition, floatingStyle, formBgType]);

    // Check if the real form in editor has fields to render
    const rootContainers = formState?.rootContainers || [];
    const formFields = formState?.fields || {};
    const hasRealFields = rootContainers.length > 0 && Object.keys(formFields).length > 0;

    // Helper to ensure DragwybBuilder and DragwybEditor are fully wired in iframe
    const setupIframeDragwyb = useCallback((iframeWindow) => {
        if (!iframeWindow) return false;

        const mainPreviewWindow = document.getElementById('dragwyb-preview-iframe')?.contentWindow;

        // Check if iframeWindow or mainPreviewWindow has field render filters
        const iframeHasHooks = !!(iframeWindow.DragwybBuilder?.Hooks?.hasFilter?.('Dragwyb/Editor/FieldRender/row'));
        const mainHasHooks = !!(mainPreviewWindow?.DragwybBuilder?.Hooks?.hasFilter?.('Dragwyb/Editor/FieldRender/row'));

        if (!iframeHasHooks) {
            if (mainHasHooks) {
                // Borrow the ready DragwybBuilder from the main editor preview iframe
                iframeWindow.DragwybBuilder = mainPreviewWindow.DragwybBuilder;
            } else if (!iframeWindow.DragwybBuilder && window.DragwybBuilder) {
                iframeWindow.DragwybBuilder = window.DragwybBuilder;
            }
        }

        // Trigger Dragwyb:init and Dragwyb:editorAppLoaded inside iframe if jQuery exists
        if (iframeWindow.jQuery) {
            try {
                iframeWindow.jQuery(iframeWindow.document).trigger('Dragwyb:init');
                iframeWindow.jQuery(iframeWindow.document).trigger('Dragwyb:editorAppLoaded');
            } catch (e) {}
        }

        // Setup DragwybEditor on iframe window
        if (!iframeWindow.hasOwnProperty('DragwybEditor')) {
            iframeWindow.DragwybEditor = {};
        }
        if (window.DragwybEditor) {
            if (!iframeWindow.hasOwnProperty('faIconsList')) {
                iframeWindow.DragwybEditor.faIconsList = window.DragwybEditor.faIconsList;
            }
            if (window.DragwybEditor.fields && !iframeWindow.DragwybEditor.fields) {
                iframeWindow.DragwybEditor.fields = window.DragwybEditor.fields;
            }
            if (window.DragwybEditor.editor && !iframeWindow.DragwybEditor.editor) {
                iframeWindow.DragwybEditor.editor = window.DragwybEditor.editor;
            }
            if (window.DragwybEditor.EditorToolbars) {
                iframeWindow.DragwybEditor.EditorToolbars = window.DragwybEditor.EditorToolbars;
            }
            const editorToolBars = window.DragwybEditor?.EditorToolbars?.toolbars;
            if (editorToolBars) {
                Object.keys(editorToolBars).forEach((key) => {
                    if (!iframeWindow.DragwybEditor.hasOwnProperty(key)) {
                        iframeWindow.DragwybEditor[key] = window.DragwybEditor[key];
                    }
                });
            }
        }

        return !!(iframeWindow.DragwybBuilder?.Hooks?.hasFilter?.('Dragwyb/Editor/FieldRender/row'));
    }, []);

    // Initialize iframe document and base styles
    const initIframe = useCallback((event) => {
        const iframe = event?.target || iframeRef.current;
        if (!iframe || !iframe.contentWindow) return;

        // Prevent premature initialization on about:blank before PREVIEW_IFRAME_URL loads
        if (PREVIEW_IFRAME_URL) {
            try {
                const loc = iframe.contentWindow.location?.href;
                if (!loc || loc === 'about:blank') {
                    return;
                }
            } catch (e) {
                // Cross-origin fallback safety
            }
        }

        const doc = iframe.contentWindow.document;
        if (!doc || !doc.body) return;

        if (doc.documentElement) {
            doc.documentElement.style.background = 'transparent';
        }
        if (doc.body) {
            doc.body.style.background = 'transparent';
            doc.body.style.margin = '0';
            doc.body.style.padding = '0';
            doc.body.style.overflowX = 'hidden';
        }

        const iframeWindow = doc.defaultView || iframe.contentWindow;

        // Base styling for iframe with scale wrapper support
        let baseStyleTag = doc.getElementById('dragwyb-preset-base-style');
        if (!baseStyleTag && doc.head) {
            baseStyleTag = doc.createElement('style');
            baseStyleTag.id = 'dragwyb-preset-base-style';
            baseStyleTag.textContent = `
                html, body {
                    background: transparent !important;
                    overflow-x: hidden !important;
                    padding: 0 !important;
                    margin: 0 !important;
                }
                body {
                    scrollbar-width: thin;
                    scrollbar-color: rgba(148, 163, 184, 0.4) transparent;
                    padding: 24px !important;
                    box-sizing: border-box !important;
                }
                body::-webkit-scrollbar {
                    width: 6px;
                }
                body::-webkit-scrollbar-thumb {
                    background-color: rgba(148, 163, 184, 0.4);
                    border-radius: 4px;
                }
                .dragwyb-preset-scale-wrapper {
                    width: 100%;
                    position: relative;
                    box-sizing: border-box;
                }
                .dragwyb-preset-scale-content {
                    width: 142.857143% !important;
                    transform: scale(0.7) !important;
                    transform-origin: top left !important;
                    box-sizing: border-box !important;
                }
            `;
            doc.head.appendChild(baseStyleTag);
        }

        // Inject dynamic preset CSS
        let styleTag = doc.getElementById('dragwyb-preset-dynamic-style');
        if (!styleTag && doc.head) {
            styleTag = doc.createElement('style');
            styleTag.id = 'dragwyb-preset-dynamic-style';
            doc.head.appendChild(styleTag);
        }
        if (styleTag) {
            styleTag.textContent = compiledCss;
        }

        // Setup DragwybBuilder and DragwybEditor.
        // Wait until hooks are ready before finalizing mount and hiding spinner.
        let checkCount = 0;
        const finalize = () => {
            checkCount++;
            const isReady = setupIframeDragwyb(iframeWindow);
            if (isReady || checkCount >= 20) {
                setMountNode(doc.body);
                setIsLoading(false);
            } else {
                setTimeout(finalize, 50);
            }
        };

        finalize();
    }, [compiledCss, setupIframeDragwyb]);

    // Keep dynamic styles in iframe head in sync without reloading the iframe
    useEffect(() => {
        if (!mountNode && iframeRef.current) {
            try {
                const doc = iframeRef.current.contentDocument || iframeRef.current.contentWindow?.document;
                const loc = iframeRef.current.contentWindow?.location?.href;
                if (doc && doc.body && (!PREVIEW_IFRAME_URL || (loc && loc !== 'about:blank'))) {
                    initIframe({ target: iframeRef.current });
                }
            } catch (e) {
                // Cross-origin fallback safety
            }
        }
        if (mountNode && mountNode.ownerDocument) {
            const doc = mountNode.ownerDocument;
            let styleTag = doc.getElementById('dragwyb-preset-dynamic-style');
            if (!styleTag && doc.head) {
                styleTag = doc.createElement('style');
                styleTag.id = 'dragwyb-preset-dynamic-style';
                doc.head.appendChild(styleTag);
            }
            if (styleTag && styleTag.textContent !== compiledCss) {
                styleTag.textContent = compiledCss;
            }
        }
    }, [compiledCss, mountNode, initIframe]);

    const iframeHeight = scaledHeight ? `${scaledHeight + 50}px` : '460px';

    return (
        <div className="preset-preview-iframe-wrapper" style={{ position: 'relative', minHeight: '300px' }}>
            {isLoading && (
                <div
                    className="dragwyb-preset-preview-loader"
                    style={{
                        position: 'absolute',
                        top: 0,
                        left: 0,
                        right: 0,
                        bottom: 0,
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        justifyContent: 'center',
                        zIndex: 10,
                        minHeight: '260px',
                    }}
                >
                    <div className="dragwyb-preset-spinner">
                        <FaSpinner className="dragwyb-preset-spinner-icon" />
                    </div>
                    <span className="dragwyb-preset-loader-text">
                        {__('Loading preview...', 'smart-form-builder-by-dragwyb')}
                    </span>
                </div>
            )}
            <iframe
                ref={iframeRef}
                onLoad={initIframe}
                src={PREVIEW_IFRAME_URL}
                title={`Preset Live Preview ${presetKey}`}
                className="preset-preview-iframe"
                style={{
                    width: '100%',
                    height: iframeHeight,
                    minHeight: '260px',
                    border: 'none',
                    backgroundColor: 'transparent',
                    display: 'block',
                    opacity: isLoading ? 0 : 1,
                    pointerEvents: isLoading ? 'none' : 'auto',
                    transition: 'opacity 0.2s ease, height 0.15s ease',
                }}
            />
            {!isLoading && mountNode && createPortal(
                <div
                    className="dragwyb-preset-scale-wrapper"
                    style={{
                        width: '100%',
                        position: 'relative',
                        height: scaledHeight ? `${scaledHeight}px` : 'auto',
                        boxSizing: 'border-box',
                    }}
                >
                    <PresetFormContent
                        hasRealFields={hasRealFields}
                        rootContainers={rootContainers}
                        formFields={formFields}
                        wrapperClasses={wrapperClasses}
                        formId={formId}
                        stepIndicatorType={stepIndicatorType}
                        iframeDoc={mountNode.ownerDocument}
                        onHeightChange={handleHeightChange}
                    />
                </div>,
                mountNode
            )}
        </div>
    );
});

/**
 * Main Preset Modal Component
 */
const PresetModal = ({ isOpen, onClose, Utils }) => {
    const dispatch = useDispatch();
    const store = useStore();

    const [searchQuery, setSearchQuery] = useState('');
    const [previewDevice, setPreviewDevice] = useState('desktop');

    // Read presetStyle data from DragwybEditor
    const presetData = useMemo(() => {
        return window.DragwybEditor?.presetStyle || DragwybEditor?.presetStyle || {};
    }, []);

    const baseStyles = useMemo(() => {
        return presetData.base || {};
    }, [presetData]);

    // Track active/applied preset key
    const storeState = store.getState ? store.getState() : {};
    const initialApplied = storeState?.toolbarSettings?.style?.preset_style || presetData.active_preset || 'theme';
    const [appliedPresetKey, setAppliedPresetKey] = useState(initialApplied);
    const [selectedPresetKey, setSelectedPresetKey] = useState(initialApplied);

    const formId = window.DragwybEditor?.formId || storeState?.form?.id || 'preview';
    const formState = storeState?.form || {};
    const styleSelectors = storeState?.styleSelectors || {};

    // Build presets list in ordered format
    const presetsList = useMemo(() => {
        if (!presetData) return [];

        let list = [];
        // Schema v2 with "presets" object
        if (presetData.presets && typeof presetData.presets === 'object') {
            list = Object.keys(presetData.presets).map((key) => {
                const item = presetData.presets[key];
                return {
                    key,
                    number: item.number || 1,
                    name: item.name || key,
                    description: item.description || '',
                    category: item.category || 'basic',
                    styles: { ...baseStyles, ...(item.styles || {}) },
                };
            });
        } else {
            // Schema v1 fallback
            list = Object.keys(presetData)
                .filter((k) => !['version', 'active_preset', 'base'].includes(k))
                .map((key, index) => {
                    const item = presetData[key];
                    return {
                        key,
                        number: item.number || index + 1,
                        name: item.name || key.replace('_', ' ').replace(/\b\w/g, (c) => c.toUpperCase()),
                        description: item.description || '',
                        category: item.category || 'basic',
                        styles: item,
                    };
                });
        }

        return list.sort((a, b) => (a.number || 0) - (b.number || 0));
    }, [presetData, baseStyles]);

    // Filtered Presets based on search
    const filteredPresets = useMemo(() => {
        return presetsList.filter((preset) => {
            const matchesQuery = searchQuery.trim() === '' ||
                preset.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                (preset.description && preset.description.toLowerCase().includes(searchQuery.toLowerCase()));
            return matchesQuery;
        });
    }, [presetsList, searchQuery]);

    // Active selected preset object
    const selectedPreset = useMemo(() => {
        const found = presetsList.find((p) => p.key === selectedPresetKey);
        return found || presetsList[0] || null;
    }, [presetsList, selectedPresetKey]);

    // Ensure selected preset key remains valid when opening or filtering
    useEffect(() => {
        if (isOpen) {
            const currentApplied = storeState?.toolbarSettings?.style?.preset_style || presetData.active_preset || 'theme';
            setAppliedPresetKey(currentApplied);
            setSelectedPresetKey(currentApplied);
        }
    }, [isOpen]);

    // Apply Preset Handler
    const handleApplyPreset = useCallback((preset) => {
        if (!preset) return;

        const confirmMessage = __('Do you want to apply preset style? It will overwrite or change your existing form style settings.', 'smart-form-builder-by-dragwyb');
        if (!window.confirm(confirmMessage)) {
            return;
        }

        setAppliedPresetKey(preset.key);

        if (!Utils) {
            onClose();
            return;
        }

        const mergedPresetStyle = {
            ...baseStyles,
            ...preset.styles,
        };

        // 2. Update Redux store for style toolbar
        Utils.updateToolbarSetting({ id: 'style', value: mergedPresetStyle });


        // 3. Apply style selectors for the new preset style controls
        const styleControls = DragwybEditor?.style?.controls || {};

        Object.keys(styleControls).forEach((controlKey) => {
            const controlConfig = styleControls[controlKey];

            const val = mergedPresetStyle[controlKey] !== undefined ? mergedPresetStyle[controlKey] : controlConfig?.default;

            if (val === undefined || val === null || val === '') return;

            let applied = false;
            if (controlConfig?.type) {
                try {
                    const Control = getControl(controlConfig.type, controlCache);

                    if (Control) {
                        new Control({
                            id: controlKey,
                            toolbarId: 'style',
                            selectedSetting: false,
                            settings: controlConfig,
                            value: val,
                            Utils: Utils,
                            initialRender: false
                        }).renderStyleSelector();
                    }
                } catch (err) {
                    // Fallback to direct updateStyleSelectors
                }
            }
        });

        // 4. Trigger preview iframe update
        Utils.editorFormReady();

        // 5. Add history snapshot
        if (dispatch) {
            dispatch({
                type: 'ADD_HISTORY_SNAPSHOT',
                payload: { label: `Preset Style: ${preset.name}` },
            });
        }

        // Close modal after applying
        onClose();
    }, [Utils, store, baseStyles, dispatch, onClose]);

    if (!isOpen) return null;

    const isCurrentSelectedApplied = appliedPresetKey === selectedPresetKey;

    // Get preview stage responsive width based on device toggle
    const stageWidth = previewDevice === 'mobile' ? '380px' : previewDevice === 'tablet' ? '720px' : '100%';

    return createPortal(
        <div className="dragwyb-preset-modal-overlay" onClick={onClose}>
            <div className="dragwyb-preset-modal dragwyb-preset-modal--master-detail" onClick={(e) => e.stopPropagation()}>
                {/* Header */}
                <div className="dragwyb-preset-modal__header">
                    <h2 className="modal-title">
                        {__('Form Preset Styles', 'smart-form-builder-by-dragwyb')}
                    </h2>

                    <div className="header-actions">
                        <div className="search-box">
                            <FaSearch className="search-icon" />
                            <input
                                type="text"
                                placeholder={__('Search presets...', 'smart-form-builder-by-dragwyb')}
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                            />
                            {searchQuery && (
                                <button className="search-clear-btn" onClick={() => setSearchQuery('')}>
                                    <FaTimes />
                                </button>
                            )}
                        </div>
                        <button className="close-btn" onClick={onClose} title={__('Close', 'smart-form-builder-by-dragwyb')}>
                            <FaTimes />
                        </button>
                    </div>
                </div>

                {/* Master-Detail Split Body */}
                <div className="dragwyb-preset-modal__body-split">
                    {/* Left Sidebar: Presets List */}
                    <div className="preset-sidebar">
                        <div className="preset-sidebar__header">
                            <span className="preset-sidebar__title">
                                {__('Presets', 'smart-form-builder-by-dragwyb')}
                            </span>
                            <span className="preset-sidebar__count">
                                {filteredPresets.length}
                            </span>
                        </div>

                        <div className="preset-sidebar__list">
                            {filteredPresets.length === 0 ? (
                                <div className="preset-sidebar__empty">
                                    {__('No presets found.', 'smart-form-builder-by-dragwyb')}
                                </div>
                            ) : (
                                filteredPresets.map((preset) => {
                                    const isSelected = selectedPresetKey === preset.key;
                                    const isApplied = appliedPresetKey === preset.key;

                                    return (
                                        <button
                                            key={preset.key}
                                            type="button"
                                            className={`preset-sidebar__item ${isSelected ? 'active' : ''} ${isApplied ? 'is-applied' : ''}`}
                                            onClick={() => setSelectedPresetKey(preset.key)}
                                        >
                                            <span className="preset-item__name">
                                                {preset.name}
                                            </span>
                                            <div className="preset-item__indicators">
                                                {isApplied && (
                                                    <span className="preset-item__applied-badge" title={__('Currently Applied', 'smart-form-builder-by-dragwyb')}>
                                                        <FaCheck className="applied-check" />
                                                        <span>{__('Applied', 'smart-form-builder-by-dragwyb')}</span>
                                                    </span>
                                                )}
                                                {isSelected && (
                                                    <span className="preset-item__arrow" aria-hidden="true">
                                                        ←
                                                    </span>
                                                )}
                                            </div>
                                        </button>
                                    );
                                })
                            )}
                        </div>
                    </div>

                    {/* Right Panel: Live Preview */}
                    <div className="preset-preview-panel">
                        {/* Live Preview Top Bar */}
                        <div className="preset-preview-panel__header">
                            <div className="preview-header-left">
                                <span className="preview-badge">
                                    {__('LIVE PREVIEW', 'smart-form-builder-by-dragwyb')}
                                </span>
                                {selectedPreset && (
                                    <span className="preview-preset-name">
                                        {selectedPreset.name}
                                    </span>
                                )}
                            </div>

                            <div className="preview-header-right">
                                <div className="preview-device-toggle">
                                    <button
                                        type="button"
                                        className={`device-btn ${previewDevice === 'desktop' ? 'active' : ''}`}
                                        onClick={() => setPreviewDevice('desktop')}
                                        title={__('Desktop Preview', 'smart-form-builder-by-dragwyb')}
                                    >
                                        <FaDesktop />
                                    </button>
                                    <button
                                        type="button"
                                        className={`device-btn ${previewDevice === 'tablet' ? 'active' : ''}`}
                                        onClick={() => setPreviewDevice('tablet')}
                                        title={__('Tablet Preview (720px)', 'smart-form-builder-by-dragwyb')}
                                    >
                                        <FaTabletAlt />
                                    </button>
                                    <button
                                        type="button"
                                        className={`device-btn ${previewDevice === 'mobile' ? 'active' : ''}`}
                                        onClick={() => setPreviewDevice('mobile')}
                                        title={__('Mobile Preview (380px)', 'smart-form-builder-by-dragwyb')}
                                    >
                                        <FaMobileAlt />
                                    </button>
                                </div>
                            </div>
                            {/* Preset Style Description */}
                            <h4 style={{ width: "100%", margin: "0", marginTop: "5px" }}>{selectedPreset?.description}</h4>
                        </div>


                        {/* Preview Stage Area */}
                        <div className={`preset-preview-panel__stage preset-stage--${selectedPresetKey}`}>
                            <div
                                className={`preset-preview-container device-${previewDevice}`}
                            >
                                <SinglePresetLivePreview
                                    preset={selectedPreset}
                                    formId={formId}
                                    formState={formState}
                                    styleSelectors={styleSelectors}
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {/* Footer */}
                <div className="dragwyb-preset-modal__footer">
                    <div className="footer-status">
                        <span className="footer-status__label">
                            {__('Current:', 'smart-form-builder-by-dragwyb')}
                        </span>
                        <span className="footer-status__value">
                            {selectedPreset?.name || selectedPresetKey}
                        </span>
                        {isCurrentSelectedApplied && (
                            <span className="footer-status__badge">
                                <FaCheck /> {__('Active', 'smart-form-builder-by-dragwyb')}
                            </span>
                        )}
                    </div>

                    <div className="footer-actions">
                        <button
                            type="button"
                            className="preset-footer-btn preset-footer-btn--cancel"
                            onClick={onClose}
                        >
                            {__('Cancel', 'smart-form-builder-by-dragwyb')}
                        </button>
                        <button
                            type="button"
                            className="preset-footer-btn preset-footer-btn--apply"
                            onClick={() => handleApplyPreset(selectedPreset)}
                        >
                            {__('Apply Preset', 'smart-form-builder-by-dragwyb')}
                        </button>
                    </div>
                </div>
            </div>
        </div>,
        document.body
    );
};

export default PresetModal;

