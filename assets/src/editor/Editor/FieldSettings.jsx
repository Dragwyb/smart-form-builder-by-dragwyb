import React, { useEffect } from 'react';
import { useDispatch, useStore } from 'react-redux';
import { updateSectionSettings, resetSectionSettings } from '../store/actions';
import { Panel } from '../components/Common';
import DragwybControlBase from '../controlBase'
import RenderControl from './RenderControls';

const FieldSettings = ({ fieldValue, fieldSettings, sectionSettings, onSettingChange, onClose }) => {

    const dispatch = useDispatch();
    let activeSection = false;

    const defautlActiveSection = (key) => {
        if (((sectionSettings && sectionSettings.section) || activeSection) || (sectionSettings && sectionSettings.section === '')) {
            return;
        }

        activeSection = true;

        sectionUpdateHandler(key, true);
    }

    const defautlActiveTab = (key, settings) => {
        if (sectionSettings && sectionSettings[key]) {
            return;
        }

        tabsUpdateHandler(key, Object.keys(settings.tabs)[0]);
    }

    const tabsUpdateHandler = (key, value) => {
        if (sectionSettings && sectionSettings[key] && sectionSettings[key] === value) {
            return;
        }

        if ('header_controls' === key) {
            dispatch(resetSectionSettings());
            dispatch(updateSectionSettings(key, value));
            return;
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
            return;
        }

        if ('section' === type) {
            sectionUpdateHandler(key, value)
            return;
        }

        if (!fieldSettings.controls[key].type) {
            return;
        }

        onSettingChange(key, value);
    };

    return (
        <Panel
            title={`${fieldSettings.label} ${DragwybBuilder.i18n.settings}`}
            onClose={onClose}
        >
            {fieldSettings?.controls?.header_controls &&
                <div className='field-header_controls'>
                    <RenderControl
                        controlKey={'header_controls'}
                        settings={fieldSettings.controls.header_controls}
                        fieldValue={fieldValue}
                        sectionSettings={sectionSettings}
                        handleChange={handleChange}
                        defautlActiveSection={defautlActiveSection}
                        defautlActiveTab={defautlActiveTab}
                    />
                </div>
            }
            <div className="field-settings">
                {Object.keys(fieldSettings.controls).map(key => (
                    <>
                        {key === 'header_controls' ? null
                            : <RenderControl
                                controlKey={key}
                                settings={fieldSettings.controls[key]}
                                fieldValue={fieldValue}
                                sectionSettings={sectionSettings}
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