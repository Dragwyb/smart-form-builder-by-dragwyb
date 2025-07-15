import React from 'react';
import { useDispatch } from 'react-redux';
import { updateField, updateSectionSettings, resetSectionSettings } from '../../store/actions';
import { Panel } from '../Common';
import shouldRenderField from './shouldRenderField';

const FieldSettings = ({ activeField, fieldValue, sectionSettings, onClose }) => {
    const dispatch = useDispatch();
    let activeSection=false;
    const fieldType = DragwybEditor.fieldTypes[activeField.type];

    const defautlActiveSection = (key) => {
        if (((sectionSettings && sectionSettings.section) || activeSection) || (sectionSettings && sectionSettings.section === '')) {
            return;
        }

        activeSection=true;

        handleChange(key, true);
    }

    const defautlActiveTab = (key, settings) => {
        if (sectionSettings && sectionSettings[key]) {
            return;
        }

        handleChange(key, Object.keys(settings.tabs)[0])
    }


    const handleChange = (key, value) => {
        const type = fieldType.controls[key].type;

        if ('tabs' === type) {
            if ('header_controls' === key) {
                dispatch(resetSectionSettings());
                dispatch(updateSectionSettings(key, value));
                return;
            }

            dispatch(updateSectionSettings(key, value));
            return;
        }

        if ('section' === type) {
            dispatch(updateSectionSettings('section', value ? key : ''));
            return;
        }

        dispatch(updateField(activeField.id, {
            ...fieldValue,
            settings: {
                ...fieldValue.settings,
                [key]: value
            }
        }));
    };

    const renderControls = ({ key, settings }) => {

        if (!settings.type) {
            return;
        }

        const selectedSettings={ ...fieldValue.settings, ...sectionSettings };
        const shouldRender = shouldRenderField(settings, selectedSettings);

        if (!shouldRender) {
            return;
        }

        if (settings.type === 'section' && settings.conditions) {
            defautlActiveSection(key, settings, settings.conditions);
        }

        if (settings.type === 'tabs') {
            defautlActiveTab(key, settings)
        }

        const getHtml = () => {
            return <div>Unsupported Controller type: {settings.type}</div>;
        }

        let fieldVal=selectedSettings[key];

        if(settings.type === 'section' && !fieldVal){
            fieldVal=selectedSettings['section'];
        }

        let html = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/ControlRender/' + settings.type, getHtml(), key, settings, fieldVal, handleChange);

        return <div key={key} className="setting-row" dataType={settings.type}>{html}</div>;
    };

    return (
        <Panel
            title={`${fieldType.label} ${DragwybBuilder.i18n.settings}`}
            onClose={onClose}
        >
            <div className="field-settings">
                {Object.keys(fieldType.controls).map(key => (
                    <>
                        {renderControls({ key, settings: fieldType.controls[key] })}
                    </>
                ))}
            </div>
        </Panel>
    );
};

export default FieldSettings; 