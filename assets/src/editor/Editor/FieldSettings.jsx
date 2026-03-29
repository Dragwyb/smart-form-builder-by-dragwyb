import React from 'react';
import { useDispatch, useStore } from 'react-redux';
import { updateSectionSettings, resetSectionSettings } from '../store/actions';
import DragwybControlBase from '../controlBase'
import RenderControl from './RenderControls';
import Scrollbar from '../components/Scrollbar';
import RenderPopoverControls from './RenderPopoverControls';

const FieldSettings = ({ selectedTab, toolbarValue, toolbarSettings, onSettingChange, setUpdateToolbarValue }) => {
    const dispatch = useDispatch();

    const getSectionSettings = () => {
        const store = useStore();
        const state = store.getState();

        return state.sectionSettings;
    }

    const defautlActiveSection = (key) => {
        const sectionSettings = getSectionSettings();
        if (((sectionSettings && sectionSettings.section)) || (sectionSettings && sectionSettings.section === '')) {
            return;
        }

        if (toolbarSettings?.controls[key]?.conditions?.header_controls && sectionSettings?.header_controls && toolbarSettings?.controls[key]?.conditions?.header_controls !== sectionSettings.header_controls) {
            return;
        }

        sectionUpdateHandler(key, true);
    }

    const defautlActiveTab = (key, settings) => {
        const sectionSettings = getSectionSettings();
        if (sectionSettings && sectionSettings[key]) {
            return;
        }

        tabsUpdateHandler(key, Object.keys(settings.tabs)[0]);
    }

    const tabsUpdateHandler = (key, value) => {
        if ('header_controls' === key) {
            dispatch(resetSectionSettings());
        }

        dispatch(updateSectionSettings(key, value));
    }

    const sectionUpdateHandler = (key, value) => {
        dispatch(updateSectionSettings('section', value ? key : ''));
    }

    const handleChange = (key, value, type = null, from) => {
        if (!(from instanceof DragwybControlBase || from instanceof DragwybEditor.editor.extends.ControlBase)) return;

        if ('tabs' === type) {
            tabsUpdateHandler(key, value)
            if (from && from.id === 'header_controls') {
                setUpdateToolbarValue();
            }
            return;
        }

        if ('section' === type) {
            sectionUpdateHandler(key, value)
            setUpdateToolbarValue();
            return;
        }

        if (!toolbarSettings.controls[key].type) {
            return;
        }

        onSettingChange(key, value);
    };

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
                            <>
                                {key === 'header_controls' ? null
                                    : toolbarSettings.controls[key].popover
                                        ? <RenderPopoverControls
                                            key={toolbarSettings.id + '_' + key}
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
                                            key={toolbarSettings.id + '_' + key}
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
                            </>
                        ))}
                    </Scrollbar>
                </div>
            </div>
        </div>
    );
};

export default FieldSettings; 