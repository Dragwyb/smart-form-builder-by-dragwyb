import React, { useState, useMemo, useCallback, useRef, useEffect } from 'react';
import { createPortal } from 'react-dom';
import { useDispatch, useSelector, useStore } from 'react-redux';
import { __ } from '@wordpress/i18n';
import { FaSearch, FaTimes, FaCheck } from 'react-icons/fa';

import DragwybControlBase from '../../controlBase';

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
const getPresetCompiledCss = (presetKey, styles, formId) => {
    if (presetCssCache[presetKey]) {
        return presetCssCache[presetKey];
    }

    const cssCacheObject = generateStyleForToolbar('style', styles, formId, controlCache);

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

    presetCssCache[presetKey] = cssString;
    return cssString;
};

/**
 * Cached Preview Iframe URL with dragwyb_iframe_mode=true
 */
const PREVIEW_URL = window.DragwybEditor?.previewUrl || (typeof DragwybEditor !== 'undefined' ? DragwybEditor?.previewUrl : '');
const PREVIEW_IFRAME_URL = PREVIEW_URL ? `${PREVIEW_URL}&dragwyb_iframe_mode=true` : '';

/**
 * Dynamic Preset Preview Component rendering real form HTML inside an isolated iframe
 */
const DynamicPresetPreview = React.memo(({ presetKey, styles, onApply }) => {
    const iframeRef = useRef(null);
    const [mountNode, setMountNode] = useState(null);
    const formId = `preset_${presetKey}`;

    // Memoize the iframe source URL
    const iframeSrc = useMemo(() => {
        return PREVIEW_IFRAME_URL;
    }, []);

    const compiledCss = useMemo(() => {
        return getPresetCompiledCss(presetKey, styles, formId);
    }, [presetKey, styles, formId]);

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

    // Initialize iframe document and inject dynamic styles
    const initIframe = useCallback((event) => {
        const iframe = event.target || iframeRef.current;
        if (!iframe || !iframe.contentWindow) return;
        const doc = iframe.contentWindow.document;
        if (!doc || !doc.body) return;

        // Ensure transparent background for glass/gradient preset cards
        if (doc.documentElement) {
            doc.documentElement.style.background = 'transparent';
        }
        if (doc.body) {
            doc.body.style.background = 'transparent';
            doc.body.style.margin = '0';
            doc.body.style.padding = '0';
            doc.body.style.overflowX = 'hidden';
        }

        // Localize DragwybEditor onto iframe window if needed
        const iframeWindow = doc.defaultView || iframe.contentWindow;
        if (iframeWindow && window.DragwybEditor) {
            if (!iframeWindow.hasOwnProperty('DragwybEditor')) {
                iframeWindow.DragwybEditor = {};
            }
            if (!iframeWindow.hasOwnProperty('faIconsList')) {
                iframeWindow.DragwybEditor.faIconsList = window.DragwybEditor.faIconsList;
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

        // Base styling for iframe (transparent bg + sleek custom scrollbar)
        let baseStyleTag = doc.getElementById('dragwyb-preset-base-style');
        if (!baseStyleTag && doc.head) {
            baseStyleTag = doc.createElement('style');
            baseStyleTag.id = 'dragwyb-preset-base-style';
            baseStyleTag.textContent = `
                html, body {
                    background: transparent !important;
                    overflow-x: hidden !important;
                }
                body {
                    scrollbar-width: thin;
                    scrollbar-color: rgba(148, 163, 184, 0.4) transparent;
                }
                body::-webkit-scrollbar {
                    width: 4px;
                }
                body::-webkit-scrollbar-thumb {
                    background-color: rgba(148, 163, 184, 0.4);
                    border-radius: 4px;
                }
            `;
            doc.head.appendChild(baseStyleTag);
        }

        // Inject dynamic preset CSS into iframe head
        let styleTag = doc.getElementById('dragwyb-preset-dynamic-style');
        if (!styleTag && doc.head) {
            styleTag = doc.createElement('style');
            styleTag.id = 'dragwyb-preset-dynamic-style';
            doc.head.appendChild(styleTag);
        }
        if (styleTag) {
            styleTag.textContent = compiledCss;
        }

        setMountNode(doc.body);
    }, [compiledCss]);

    // Keep dynamic styles in iframe head in sync without reloading the iframe
    useEffect(() => {
        if (!mountNode && iframeRef.current && iframeRef.current.contentWindow) {
            try {
                const doc = iframeRef.current.contentWindow.document;
                if (doc && doc.body) {
                    setMountNode(doc.body);
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
    }, [compiledCss, mountNode]);

    // Live form content rendered into the iframe body
    const formContent = useMemo(() => {
        return (
            <div
                className={wrapperClasses}
                id={`dragwyb-form-wrapper-${formId}`}
                data-step-indicator={stepIndicatorType}
            >
                <form
                    className="dragwyb-form"
                    id={`dragwyb-form-${formId}`}
                    data-step-indicator={stepIndicatorType}
                    onSubmit={(e) => {
                        e.preventDefault();
                    }}
                >
                    <div id={`dragwyb-row-${formId}_header`} className="dragwyb-row">
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
                                    onApply?.();
                                }}
                            >
                                {__('Submit', 'smart-form-builder-by-dragwyb')}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        );
    }, [wrapperClasses, formId, stepIndicatorType, onApply]);

    return (
        <div className="preset-card__preview-inner">
            <iframe
                ref={iframeRef}
                onLoad={initIframe}
                src={iframeSrc}
                title={`Preset Preview ${presetKey}`}
                className="preset-card__iframe"
                style={{
                    width: '100%',
                    height: '285px',
                    border: 'none',
                    backgroundColor: 'transparent',
                    display: 'block',
                    overflow: 'hidden',
                }}
            />
            {mountNode && createPortal(formContent, mountNode)}
        </div>
    );
});

const InteractivePresetPreview = DynamicPresetPreview;

/**
 * Main Preset Modal Component
 */
const PresetModal = ({ isOpen, onClose, Utils }) => {
    const dispatch = useDispatch();
    const store = useStore();

    const [activeCategory, setActiveCategory] = useState('all');
    const [searchQuery, setSearchQuery] = useState('');

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

    // Categories in fixed, exact order matching the screenshot
    const categories = useMemo(() => {
        const order = ['all', 'basic', 'minimal', 'popular', 'premium', 'creative'];
        return order.map((catKey) => {
            const count = catKey === 'all'
                ? presetsList.length
                : presetsList.filter((p) => p.category?.toLowerCase() === catKey).length;
            const label = catKey === 'all' ? 'All' : catKey.charAt(0).toUpperCase() + catKey.slice(1);
            return {
                key: catKey,
                label: `${label} (${count})`,
                count,
            };
        });
    }, [presetsList]);

    // Filtered Presets
    const filteredPresets = useMemo(() => {
        return presetsList.filter((preset) => {
            const matchesCategory = activeCategory === 'all' || preset.category?.toLowerCase() === activeCategory.toLowerCase();
            const matchesQuery = searchQuery.trim() === '' ||
                preset.name.toLowerCase().includes(searchQuery.toLowerCase());
            return matchesCategory && matchesQuery;
        });
    }, [presetsList, activeCategory, searchQuery]);

    // Apply Preset Handler
    const handleApplyPreset = useCallback((preset) => {
        setAppliedPresetKey(preset.key);

        if (!Utils) return;

        const storeStateCurrent = store.getState ? store.getState() : {};
        const storeStyleSelectors = storeStateCurrent.styleSelectors || {};

        const mergedPresetStyle = {
            ...baseStyles,
            ...preset.styles,
            preset_style: preset.key,
        };

        // 1. Remove existing style selectors for style toolbar only
        const deleteStyleKeys = (selectorsObj, responsiveType = 'desktop') => {
            if (!selectorsObj) return;
            Object.keys(selectorsObj).forEach((key) => {
                if (key.startsWith('style_')) {
                    Utils.deleteStyleSelectors({ key, responsiveType });
                }
            });
        };

        deleteStyleKeys(storeStyleSelectors, 'desktop');
        if (storeStyleSelectors.tablet) {
            deleteStyleKeys(storeStyleSelectors.tablet, 'tablet');
        }
        if (storeStyleSelectors.mobile) {
            deleteStyleKeys(storeStyleSelectors.mobile, 'mobile');
        }

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
                        }).renderStyleSelector();
                        applied = true;
                    }
                } catch (err) {
                    // Fallback to direct updateStyleSelectors
                }
            }

            if (
                !applied &&
                controlConfig?.selectors &&
                controlConfig?.selectors_placeholders
            ) {
                const uniqueSelector = `style_${controlKey}`;
                const styleSelectorsData = {
                    key: uniqueSelector,
                    value: val,
                    selectors: controlConfig.selectors,
                    placeholders: controlConfig.selectors_placeholders,
                    toolbarType: 'style',
                    itemId: false,
                };

                if (controlConfig.responsive_control && controlConfig.responsive_type) {
                    styleSelectorsData.responsiveType = controlConfig.responsive_type;
                }

                Utils.updateStyleSelectors(styleSelectorsData);
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
    }, [Utils, store, baseStyles, dispatch]);

    // Handle Preset Card Click with confirmation alert
    const handlePresetClick = useCallback((preset) => {
        const confirmMessage = __('Do you want to apply preset style? It will overwrite or change your existing styles.', 'smart-form-builder-by-dragwyb');
        if (!window.confirm(confirmMessage)) {
            return;
        }
        handleApplyPreset(preset);
    }, [handleApplyPreset]);

    if (!isOpen) return null;

    return createPortal(
        <div className="dragwyb-preset-modal-overlay" onClick={onClose}>
            <div className="dragwyb-preset-modal" onClick={(e) => e.stopPropagation()}>
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

                {/* Category Filter Pills Row */}
                <div className="dragwyb-preset-modal__categories">
                    {categories.map((cat) => (
                        <button
                            key={cat.key}
                            className={`category-pill ${activeCategory === cat.key ? 'active' : ''}`}
                            onClick={() => setActiveCategory(cat.key)}
                        >
                            {cat.label}
                        </button>
                    ))}
                </div>

                {/* Modal Body / Presets Grid */}
                <div className="dragwyb-preset-modal__body">
                    {filteredPresets.length === 0 ? (
                        <div className="no-presets-message">
                            <p>{__('No preset styles found matching your query.', 'smart-form-builder-by-dragwyb')}</p>
                        </div>
                    ) : (
                        <div className="presets-grid">
                            {filteredPresets.map((preset, index) => {
                                const isApplied = appliedPresetKey === preset.key;

                                return (
                                    <div
                                        key={preset.key}
                                        className={`preset-card preset-card--${preset.key} ${isApplied ? 'preset-card--applied' : ''}`}
                                    >
                                        {/* Card Top: 01 Name + Apply button */}
                                        <div className="preset-card__top">
                                            <div className="preset-card__title-wrap">
                                                <span className="preset-card__num">
                                                    {String(preset.number || index + 1).padStart(2, '0')}
                                                </span>
                                                <span className="preset-card__name">
                                                    {preset.name}
                                                </span>
                                            </div>
                                            <button
                                                type="button"
                                                className={`preset-card__apply-btn ${isApplied ? 'is-applied' : ''}`}
                                                onClick={(e) => {
                                                    e.stopPropagation();
                                                    handlePresetClick(preset);
                                                }}
                                                title={isApplied ? __('Currently Active', 'smart-form-builder-by-dragwyb') : __('Apply this preset', 'smart-form-builder-by-dragwyb')}
                                            >
                                                {isApplied ? (
                                                    <>
                                                        <FaCheck className="apply-icon" />
                                                        <span>{__('Applied', 'smart-form-builder-by-dragwyb')}</span>
                                                    </>
                                                ) : (
                                                    <span>{__('Apply', 'smart-form-builder-by-dragwyb')}</span>
                                                )}
                                            </button>
                                        </div>

                                        {/* Live Interactive Form Preview */}
                                        <div className="preset-card__preview-wrapper">
                                            <InteractivePresetPreview
                                                presetKey={preset.key}
                                                styles={preset.styles}
                                                onApply={() => handlePresetClick(preset)}
                                            />
                                        </div>
                                    </div>
                                );
                            })}
                        </div>
                    )}
                </div>

                {/* Modal Footer */}
                <div className="dragwyb-preset-modal__footer">
                    <button className="done-btn" onClick={onClose}>
                        {__('Done', 'smart-form-builder-by-dragwyb')}
                    </button>
                </div>
            </div>
        </div>,
        document.body
    );
};

export default PresetModal;

