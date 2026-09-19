import React, { useState, useMemo, useCallback } from 'react';
import { createPortal } from 'react-dom';
import { useDispatch, useSelector, useStore } from 'react-redux';
import { __ } from '@wordpress/i18n';
import { FaSearch, FaTimes, FaCheck } from 'react-icons/fa';

/**
 * Helper to extract size string or px from control value
 */
const getDimensionValue = (val, fallback = 0) => {
    if (!val && val !== 0) return `${fallback}px`;
    if (typeof val === 'number') return `${val}px`;
    if (typeof val === 'string') return val.trim().endsWith('px') || val.trim().endsWith('%') ? val : `${val}px`;
    if (typeof val === 'object' && val !== null) {
        const obj = typeof val.size === 'object' && val.size !== null ? val.size : val;
        if (obj.top !== undefined || obj.right !== undefined || obj.bottom !== undefined || obj.left !== undefined) {
            const unit = obj.unit || val.unit || 'px';
            return `${obj.top || 0}${unit} ${obj.right || 0}${unit} ${obj.bottom || 0}${unit} ${obj.left || 0}${unit}`;
        }
        if (val.size !== undefined && val.size !== '' && typeof val.size !== 'object') {
            return `${val.size}${val.unit || 'px'}`;
        }
    }
    return `${fallback}px`;
};

/**
 * Helper to extract border radius
 */
const getBorderRadius = (val, fallback = 0) => {
    if (!val && val !== 0) return `${fallback}px`;
    if (typeof val === 'number') return `${val}px`;
    if (typeof val === 'string') return val;
    if (typeof val === 'object' && val !== null) {
        const obj = typeof val.size === 'object' && val.size !== null ? val.size : val;
        if (obj.top !== undefined || obj.right !== undefined || obj.bottom !== undefined || obj.left !== undefined) {
            const unit = obj.unit || val.unit || 'px';
            return `${obj.top || 0}${unit} ${obj.right || 0}${unit} ${obj.bottom || 0}${unit} ${obj.left || 0}${unit}`;
        }
        if (val.size !== undefined && val.size !== '' && typeof val.size !== 'object') {
            return `${val.size}${val.unit || 'px'}`;
        }
    }
    return `${fallback}px`;
};

/**
 * Interactive Live Preview for each Preset Card
 */
const InteractivePresetPreview = ({ presetKey, styles }) => {
    const [name, setName] = useState('');
    const [email, setEmail] = useState('');
    const [message, setMessage] = useState('');
    const [focusedField, setFocusedField] = useState(null);
    const [isBtnHovered, setIsBtnHovered] = useState(false);

    const labelPosition = styles?.label_position || 'top';
    const floatingStyle = styles?.floating_style || 'outlined';
    const isFloating = labelPosition === 'floating';

    // Form Container Style
    const formContainerStyle = useMemo(() => {
        const style = {
            width: '100%',
            boxSizing: 'border-box',
            padding: '10px 12px',
            borderRadius: '8px',
            position: 'relative',
            transition: 'all 0.2s ease',
            backgroundColor: '#ffffff',
            border: '1px solid #e5e7eb',
        };

        if (presetKey === 'theme' || presetKey === 'clean') {
            style.backgroundColor = '#ffffff';
            style.border = '1px solid #e5e7eb';
        } else if (presetKey === 'card') {
            style.backgroundColor = '#ffffff';
            style.border = '1px solid #e5e7eb';
            style.boxShadow = '0 2px 8px rgba(0, 0, 0, 0.06)';
        } else if (presetKey === 'modern') {
            style.backgroundColor = '#ffffff';
            style.border = '1px solid #e5e7eb';
            style.borderRadius = '10px';
        } else if (presetKey === 'soft') {
            style.backgroundColor = '#fff7fa';
            style.border = '1px solid #ffe4ec';
            style.borderRadius = '10px';
        } else if (presetKey === 'outline') {
            style.backgroundColor = '#ffffff';
            style.border = '1px solid #e5e7eb';
        } else if (presetKey === 'filled') {
            style.backgroundColor = '#f0f4f8';
            style.border = '1px solid #e2e8f0';
        } else if (presetKey === 'glass') {
            style.backgroundColor = 'rgba(255, 255, 255, 0.25)';
            style.backdropFilter = 'blur(8px)';
            style.WebkitBackdropFilter = 'blur(8px)';
            style.border = '1px solid rgba(255, 255, 255, 0.45)';
            style.boxShadow = '0 4px 16px rgba(0, 0, 0, 0.08)';
        } else if (presetKey === 'dark') {
            style.backgroundColor = '#111827';
            style.border = '1px solid #1f2937';
            style.boxShadow = '0 4px 14px rgba(0, 0, 0, 0.4)';
        } else if (presetKey === 'gradient') {
            style.backgroundColor = '#ffffff';
            style.border = '1px solid #e5e7eb';
            style.boxShadow = '0 2px 10px rgba(139, 92, 246, 0.08)';
        } else if (presetKey === 'minimal') {
            style.backgroundColor = '#ffffff';
            style.border = '1px solid #e5e7eb';
        } else if (presetKey === 'elevated') {
            style.backgroundColor = '#ffffff';
            style.border = '1px solid #e5e7eb';
            style.boxShadow = '0 10px 22px -4px rgba(0, 0, 0, 0.12), 0 4px 6px -2px rgba(0, 0, 0, 0.05)';
        } else if (presetKey === 'side_border') {
            style.backgroundColor = '#ffffff';
            style.border = '1px solid #e5e7eb';
            style.borderLeft = '4px solid #f4256a';
            style.borderRadius = '6px';
        } else if (presetKey === 'top_accent') {
            style.backgroundColor = '#ffffff';
            style.border = '1px solid #e5e7eb';
            style.borderTop = '4px solid #f4256a';
            style.borderRadius = '6px';
        } else if (presetKey === 'transparent') {
            style.backgroundColor = 'rgba(255, 255, 255, 0.08)';
            style.backdropFilter = 'blur(8px)';
            style.WebkitBackdropFilter = 'blur(8px)';
            style.border = '1px solid rgba(255, 255, 255, 0.2)';
            style.boxShadow = '0 4px 16px rgba(0, 0, 0, 0.2)';
        }

        return style;
    }, [presetKey]);

    // Input Base Styles
    const getInputStyle = useCallback((fieldName) => {
        const isFocused = focusedField === fieldName;
        let border = '1px solid #e2e8f0';
        let bg = '#ffffff';
        let color = '#111827';
        let borderRadius = '6px';
        let borderBottom = null;

        if (presetKey === 'outline') {
            border = '1px solid #94a3b8';
        } else if (presetKey === 'filled') {
            bg = '#e2e8f0';
            border = '1px solid transparent';
            color = '#1f2937';
        } else if (presetKey === 'modern') {
            borderRadius = '20px';
            border = '1px solid #e2e8f0';
        } else if (presetKey === 'soft') {
            borderRadius = '8px';
            border = '1px solid #e2e8f0';
        } else if (presetKey === 'glass') {
            bg = 'rgba(255, 255, 255, 0.35)';
            border = '1px solid rgba(255, 255, 255, 0.5)';
            color = '#ffffff';
        } else if (presetKey === 'dark') {
            bg = '#1f2937';
            border = '1px solid #374151';
            color = '#f9fafb';
        } else if (presetKey === 'minimal') {
            bg = 'transparent';
            border = 'none';
            borderBottom = '1.5px solid #9ca3af';
            borderRadius = '0px';
        } else if (presetKey === 'transparent') {
            bg = 'rgba(255, 255, 255, 0.12)';
            border = '1px solid rgba(255, 255, 255, 0.25)';
            color = '#ffffff';
        }

        if (isFocused) {
            if (presetKey === 'minimal') {
                borderBottom = '1.5px solid #f4256a';
            } else if (presetKey === 'glass' || presetKey === 'transparent') {
                border = '1px solid #ffffff';
            } else if (presetKey === 'dark') {
                border = '1px solid #f4256a';
            } else {
                border = '1px solid #f4256a';
            }
        }

        const inputStyle = {
            width: '100%',
            boxSizing: 'border-box',
            backgroundColor: bg,
            color,
            border,
            borderRadius,
            padding: '5px 8px',
            fontSize: '11px',
            outline: 'none',
            transition: 'all 0.15s ease',
            fontFamily: 'inherit',
            display: 'block',
        };

        if (borderBottom) {
            inputStyle.borderBottom = borderBottom;
        }

        return inputStyle;
    }, [presetKey, focusedField]);

    // Submit button style
    const buttonStyle = useMemo(() => {
        let bg = '#f4256a';
        let color = '#ffffff';
        let border = 'none';
        let borderRadius = '6px';

        if (presetKey === 'modern') {
            bg = 'linear-gradient(90deg, #f4256a 0%, #8b5cf6 100%)';
            borderRadius = '20px';
        } else if (presetKey === 'soft') {
            borderRadius = '8px';
        } else if (presetKey === 'outline') {
            bg = '#ffffff';
            color = '#f4256a';
            border = '1px solid #f4256a';
        } else if (presetKey === 'filled') {
            bg = '#2563eb';
        } else if (presetKey === 'gradient') {
            bg = 'linear-gradient(90deg, #ec4899 0%, #8b5cf6 100%)';
            borderRadius = '6px';
        }

        const isGradient = typeof bg === 'string' && bg.includes('gradient');

        return {
            width: '100%',
            height: '26px',
            background: isGradient ? bg : undefined,
            backgroundColor: isGradient ? undefined : bg,
            color,
            border,
            borderRadius,
            padding: '0 10px',
            fontSize: '11px',
            fontWeight: 600,
            cursor: 'pointer',
            transition: 'all 0.15s ease',
            textAlign: 'center',
            display: 'flex',
            alignItems: 'center',
            justifyContent: 'center',
            boxSizing: 'border-box',
            outline: 'none',
            opacity: isBtnHovered ? 0.9 : 1,
        };
    }, [presetKey, isBtnHovered]);

    const isDarkTheme = ['dark', 'glass', 'transparent'].includes(presetKey);

    return (
        <div className={`dragwyb-preset-interactive-form ${isDarkTheme ? 'is-dark' : ''}`} style={formContainerStyle}>
            {/* Field 1: Name */}
            <div style={{ position: 'relative', marginBottom: '6px' }}>
                <input
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    onClick={(e) => e.stopPropagation()}
                    onFocus={() => setFocusedField('name')}
                    onBlur={() => setFocusedField(null)}
                    placeholder={__('Your Name', 'smart-form-builder-by-dragwyb')}
                    style={getInputStyle('name')}
                />
            </div>

            {/* Field 2: Email */}
            <div style={{ position: 'relative', marginBottom: '6px' }}>
                <input
                    type="email"
                    value={email}
                    onChange={(e) => setEmail(e.target.value)}
                    onClick={(e) => e.stopPropagation()}
                    onFocus={() => setFocusedField('email')}
                    onBlur={() => setFocusedField(null)}
                    placeholder={__('Your Email', 'smart-form-builder-by-dragwyb')}
                    style={getInputStyle('email')}
                />
            </div>

            {/* Field 3: Message */}
            <div style={{ position: 'relative', marginBottom: '8px' }}>
                <textarea
                    rows={2}
                    value={message}
                    onChange={(e) => setMessage(e.target.value)}
                    onClick={(e) => e.stopPropagation()}
                    onFocus={() => setFocusedField('message')}
                    onBlur={() => setFocusedField(null)}
                    placeholder={__('Message', 'smart-form-builder-by-dragwyb')}
                    style={{ ...getInputStyle('message'), minHeight: '36px', height: '36px', resize: 'vertical' }}
                />
            </div>

            {/* Field 4: Submit Button */}
            <button
                type="button"
                style={buttonStyle}
                onMouseEnter={() => setIsBtnHovered(true)}
                onMouseLeave={() => setIsBtnHovered(false)}
            >
                {__('Submit', 'smart-form-builder-by-dragwyb')}
            </button>
        </div>
    );
};

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

            if (
                val !== undefined &&
                val !== null &&
                val !== '' &&
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

