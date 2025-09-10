import React, { act, useEffect, useState } from 'react';
import { useDispatch, useStore } from 'react-redux';
import { updateSectionSettings, resetSectionSettings } from '../store/actions';
import { Panel } from '../components/Common';
import DragwybControlBase from '../controlBase'
import RenderControl from './RenderControls';

const FieldSettings = ({ selectedTab, toolbarValue, toolbarSettings, sectionSettings, onSettingChange, onClose }) => {

    const dispatch = useDispatch();

    const getSectionSettings=()=>{
        const store=useStore();
        const state=store.getState();

        return state.sectionSettings;
    }

    const defautlActiveSection = (key) => {
        const sectionSettings=getSectionSettings();
        if (((sectionSettings && sectionSettings.section)) || (sectionSettings && sectionSettings.section === '')) {
            return;
        }
        
        sectionUpdateHandler(key, true);
    }

    const defautlActiveTab = (key, settings) => {
        const sectionSettings=getSectionSettings();
        if (sectionSettings && sectionSettings[key]) {
            return;
        }

        tabsUpdateHandler(key, Object.keys(settings.tabs)[0]);
    }

    const tabsUpdateHandler = (key, value) => {
        if ('header_controls' === key) {
            dispatch(resetSectionSettings());
        }

        const handle = 'Dragwyb/Editor/' + selectedTab + '/Control_Update/' + key;

        DragwybBuilder.Hooks.doAction(handle);
        dispatch(updateSectionSettings(key, value));
    }

    const sectionUpdateHandler = (key, value) => {
        dispatch(updateSectionSettings('section', value ? key : ''));
    }

    const handleChange = (key, value, type = null, from) => {
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
    };

    return (
        <Panel
            title={`${toolbarSettings.label} ${DragwybBuilder.i18n.settings}`}
            onClose={onClose}
        >
            {toolbarSettings?.controls?.header_controls &&
                <div className='field-header_controls'>
                    <RenderControl
                        selectedTab={selectedTab}
                        controlKey={'header_controls'}
                        settings={toolbarSettings.controls.header_controls}
                        fieldValue={toolbarValue}
                        handleChange={handleChange}
                        defautlActiveSection={defautlActiveSection}
                        defautlActiveTab={defautlActiveTab}
                    />
                </div>
            }
            <div className="field-settings">
                {Object.keys(toolbarSettings.controls).map(key => (
                    <>
                        {key === 'header_controls' ? null
                            : <RenderControl
                                key={key}
                                selectedTab={selectedTab}
                                controlKey={key}
                                settings={toolbarSettings.controls[key]}
                                fieldValue={toolbarValue}
                                handleChange={handleChange}
                                defautlActiveSection={defautlActiveSection}
                                defautlActiveTab={defautlActiveTab}
                            />}
                    </>
                ))}
            </div>
        </Panel>
    );
};

export default FieldSettings; 