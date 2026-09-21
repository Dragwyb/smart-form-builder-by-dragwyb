import React, { useCallback, useState, useMemo, useEffect } from 'react';
import { useDispatch, useSelector, useStore } from 'react-redux';
import { updateSectionSettings, resetSectionSettings } from '../store/actions';
import DragwybControlBase from '../controlBase'
import RenderControl from './RenderControls';
import Scrollbar from '../components/Scrollbar';
import RenderPopoverControls from './RenderPopoverControls';
import { RiSearchLine, RiCloseLine, RiArrowDownSLine, RiArrowUpSLine } from 'react-icons/ri';
import { __, sprintf } from '@wordpress/i18n';

const FieldSettings = ({ selectedTab, toolbarValue, toolbarSettings, onSettingChange }) => {
    const dispatch = useDispatch();
    const store = useStore();
    const tabScope = selectedTab || 'fields';
    const [searchQuery, setSearchQuery] = useState('');

    const activeSection = useSelector(state => state.sectionSettings?.[tabScope]?.section);

    const getSectionSettings = useCallback(() => {
        return store.getState().sectionSettings?.[tabScope] || {};
    }, [store, tabScope]);

    const toggleAllSections = useCallback(() => {
        if (activeSection) {
            dispatch(updateSectionSettings('section', '', tabScope));
        } else {
            const firstSectionKey = Object.keys(toolbarSettings?.controls || {}).find(
                k => toolbarSettings.controls[k].type === 'section'
            );
            if (firstSectionKey) {
                dispatch(updateSectionSettings('section', firstSectionKey, tabScope));
            }
        }
    }, [dispatch, tabScope, activeSection, toolbarSettings]);

    const defautlActiveSection = useCallback((key) => {
        const sectionSettings = getSectionSettings();

        if (((sectionSettings && sectionSettings.section)) || (sectionSettings && sectionSettings.section === '')) {
            return;
        }

        if (toolbarSettings?.controls[key]?.conditions?.header_controls && sectionSettings?.header_controls && toolbarSettings?.controls[key]?.conditions?.header_controls !== sectionSettings.header_controls) {
            return;
        }

        sectionUpdateHandler(key, true);
    }, [toolbarSettings, getSectionSettings]);

    const defautlActiveTab = useCallback((key, settings) => {
        const sectionSettings = getSectionSettings();

        if (sectionSettings && sectionSettings[key]) {
            return;
        }

        tabsUpdateHandler(key, Object.keys(settings.tabs)[0]);
    }, [getSectionSettings]);

    const tabsUpdateHandler = useCallback((key, value) => {
        if ('header_controls' === key) {
            dispatch(resetSectionSettings(tabScope));
        }

        dispatch(updateSectionSettings(key, value, tabScope));
    }, [dispatch, tabScope]);

    const sectionUpdateHandler = useCallback((key, value) => {
        dispatch(updateSectionSettings('section', value ? key : '', tabScope));
    }, [dispatch, tabScope]);

    const handleChange = useCallback((key, value, type = null, from) => {
        if (!(from instanceof DragwybControlBase || from instanceof DragwybEditor.editor.extends.ControlBase)) return;

        if ('tabs' === type) {
            tabsUpdateHandler(key, value)
            return;
        }

        if ('section' === type) {
            sectionUpdateHandler(key, value)
            return;
        }

        if (!toolbarSettings.controls[key].type) {
            return;
        }

        onSettingChange(key, value);
    }, [tabsUpdateHandler, sectionUpdateHandler, toolbarSettings, onSettingChange]);

    // Search and Visibility Filter
    const searchInfo = useMemo(() => {
        const q = searchQuery.trim().toLowerCase();
        if (!q || !toolbarSettings?.controls) {
            return { isSearching: false, matchedKeys: new Set(), matchingSections: new Set(), matchCount: 0 };
        }

        const matchedKeys = new Set();
        const matchingSections = new Set();
        let count = 0;

        let currentSec = null;
        const controls = toolbarSettings.controls;

        Object.keys(controls).forEach(key => {
            if (key === 'header_controls') return;
            const ctrl = controls[key];
            if (ctrl.type === 'section') {
                currentSec = key;
                const labelMatch = (ctrl.label || '').toLowerCase().includes(q);
                if (labelMatch) {
                    matchedKeys.add(key);
                    matchingSections.add(key);
                }
            } else {
                const labelMatch = (ctrl.label || '').toLowerCase().includes(q);
                const descMatch = (ctrl.description || '').toLowerCase().includes(q);
                const keyMatch = key.toLowerCase().includes(q);

                if (labelMatch || descMatch || keyMatch) {
                    matchedKeys.add(key);
                    count++;
                    const sec = ctrl.conditions?.section || currentSec;
                    if (sec) {
                        matchingSections.add(sec);
                        matchedKeys.add(sec);
                    }
                }
            }
        });

        return {
            isSearching: true,
            matchedKeys,
            matchingSections,
            matchCount: count
        };
    }, [searchQuery, toolbarSettings]);

    // Auto-open first matching section on search
    useEffect(() => {
        if (searchInfo.isSearching && searchInfo.matchingSections.size > 0) {
            const sectionSettings = getSectionSettings();
            const currentActiveSec = sectionSettings?.section;
            if (!currentActiveSec || !searchInfo.matchingSections.has(currentActiveSec)) {
                const firstSec = Array.from(searchInfo.matchingSections)[0];
                if (firstSec) {
                    sectionUpdateHandler(firstSec, true);
                }
            }
        }
    }, [searchInfo.isSearching, searchInfo.matchingSections, getSectionSettings, sectionUpdateHandler]);

    const controlKeysToRender = useMemo(() => {
        const allKeys = Object.keys(toolbarSettings?.controls || {});
        if (!searchInfo.isSearching) {
            return allKeys;
        }
        return allKeys.filter(k => k === 'header_controls' || searchInfo.matchedKeys.has(k));
    }, [toolbarSettings, searchInfo]);

    return (
        <div className="dragwyb-panel">
            {selectedTab !== 'fields' &&
                <div className="dragwyb-panel__header">
                    <h3>
                        {toolbarSettings?.panelHeading}
                    </h3>
                </div>
            }
            <div className="dragwyb-panel__content">
                {toolbarSettings?.controls?.header_controls &&
                    <div className='field-header_controls'>
                        <RenderControl
                            key={toolbarSettings.id}
                            selectedToolbar={selectedTab}
                            selectedTab={toolbarSettings.id}
                            controlKey={'header_controls'}
                            settings={toolbarSettings.controls.header_controls}
                            toolbarSettings={toolbarSettings}
                            fieldValue={toolbarValue}
                            handleChange={handleChange}
                            defautlActiveSection={defautlActiveSection}
                            defautlActiveTab={defautlActiveTab}
                        />
                    </div>
                }

                {/* Search & Setting Visibility Management */}
                <div className="dragwyb-settings-search-bar">
                    <div className="dragwyb-settings-search-row">
                        <div className="dragwyb-settings-search-input-wrap">
                            <RiSearchLine className="dragwyb-settings-search-icon" />
                            <input
                                type="text"
                                className="dragwyb-settings-search-input"
                                placeholder={__('Search settings...', 'smart-form-builder-by-dragwyb')}
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                            />
                            {searchQuery && (
                                <button
                                    type="button"
                                    className="dragwyb-settings-search-clear"
                                    onClick={() => setSearchQuery('')}
                                    title={__('Clear search', 'smart-form-builder-by-dragwyb')}
                                >
                                    <RiCloseLine />
                                </button>
                            )}
                        </div>
                        <button
                            type="button"
                            className="dragwyb-settings-toggle-all-btn"
                            onClick={toggleAllSections}
                            title={activeSection ? __('Collapse section', 'smart-form-builder-by-dragwyb') : __('Expand section', 'smart-form-builder-by-dragwyb')}
                        >
                            {activeSection ? <RiArrowUpSLine size={13} /> : <RiArrowDownSLine size={13} />}
                            <span>{activeSection ? __('Collapse', 'smart-form-builder-by-dragwyb') : __('Expand', 'smart-form-builder-by-dragwyb')}</span>
                        </button>
                    </div>
                    {searchInfo.isSearching && (
                        <div className="dragwyb-settings-search-status">
                            <span className="dragwyb-settings-search-count">
                                {searchInfo.matchCount === 1
                                    ? sprintf(__('%d setting found', 'smart-form-builder-by-dragwyb'), searchInfo.matchCount)
                                    : sprintf(__('%d settings found', 'smart-form-builder-by-dragwyb'), searchInfo.matchCount)}
                            </span>
                        </div>
                    )}
                </div>

                <div className="dragwyb-panel__settings">
                    <Scrollbar>
                        {searchInfo.isSearching && searchInfo.matchCount === 0 ? (
                            <div className="dragwyb-settings-empty">
                                <div className="dragwyb-settings-empty__icon-wrap">
                                    <RiSearchLine />
                                </div>
                                <h4 className="dragwyb-settings-empty__title">{__('No settings found', 'smart-form-builder-by-dragwyb')}</h4>
                                <p className="dragwyb-settings-empty__desc">
                                    {sprintf(__('No settings matching "%s"', 'smart-form-builder-by-dragwyb'), searchQuery)}
                                </p>
                                <button
                                    type="button"
                                    className="dragwyb-settings-empty__clear-btn"
                                    onClick={() => setSearchQuery('')}
                                >
                                    {__('Clear Search', 'smart-form-builder-by-dragwyb')}
                                </button>
                            </div>
                        ) : (
                            controlKeysToRender.map(key => (
                                <React.Fragment key={toolbarSettings.id + '_' + key}>
                                    {key === 'header_controls' ? null
                                        : toolbarSettings.controls[key].popover
                                            ? <RenderPopoverControls
                                                selectedToolbar={selectedTab}
                                                selectedTab={toolbarSettings.id}
                                                controlKey={key}
                                                settings={toolbarSettings.controls[key]}
                                                toolbarSettings={toolbarSettings}
                                                fieldValue={toolbarValue}
                                                handleChange={handleChange}
                                                defautlActiveSection={defautlActiveSection}
                                                defautlActiveTab={defautlActiveTab}
                                            />
                                            : <RenderControl
                                                selectedToolbar={selectedTab}
                                                selectedTab={toolbarSettings.id}
                                                controlKey={key}
                                                settings={toolbarSettings.controls[key]}
                                                toolbarSettings={toolbarSettings}
                                                fieldValue={toolbarValue}
                                                handleChange={handleChange}
                                                defautlActiveSection={defautlActiveSection}
                                                defautlActiveTab={defautlActiveTab}
                                            />}
                                </React.Fragment>
                            ))
                        )}
                    </Scrollbar>
                </div>
            </div>
        </div>
    );
};

export default FieldSettings;