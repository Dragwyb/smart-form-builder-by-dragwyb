import React, { useCallback } from 'react';
import { useDispatch, useStore } from 'react-redux';
import { updateSectionSettings, resetSectionSettings } from '../store/actions';
import DragwybControlBase from '../controlBase'
import RenderControl from './RenderControls';
import Scrollbar from '../components/Scrollbar';
import RenderPopoverControls from './RenderPopoverControls';

const FieldSettings = ({ selectedTab, toolbarValue, toolbarSettings, onSettingChange }) => {
    const dispatch = useDispatch();
    const store = useStore();
    const tabScope = selectedTab || 'fields';

    const getSectionSettings = useCallback(() => {
        return store.getState().sectionSettings?.[tabScope] || {};
    }, [store, tabScope]);

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
                <div className="dragwyb-panel__settings">
                    <Scrollbar>
                        {Object.keys(toolbarSettings.controls).map(key => (
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
                        ))}
                    </Scrollbar>
                </div>
            </div>
        </div>
    );
};

export default FieldSettings;