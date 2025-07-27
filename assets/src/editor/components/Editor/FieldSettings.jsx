import React from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { updateField, updateSectionSettings, resetSectionSettings } from '../../store/actions';
import { Panel } from '../Common';
import shouldRenderField from './shouldRenderField';
import Helper from '../Utils';

const FieldSettings = ({ activeField, fieldValue, sectionSettings, onClose }) => {
    
    const dispatch = useDispatch();
    let activeSection=false;
    const fieldType = DragwybEditor.fieldTypes[activeField.type];

    const state=useSelector(state => state);
    const Utils=Helper(state, dispatch);


    const defautlActiveSection = (key) => {
        if (((sectionSettings && sectionSettings.section) || activeSection) || (sectionSettings && sectionSettings.section === '')) {
            return;
        }

        activeSection=true;

        handleChange(key, true, 'section');
    }

    const defautlActiveTab = (key, settings) => {
        if (sectionSettings && sectionSettings[key]) {
            return;
        }

        handleChange(key, Object.keys(settings.tabs)[0], 'tabs')
    }


    const handleChange = (key, value, type=null) => {
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

        if(!fieldType.controls[key].type){
            return;
        }

        dispatch(updateField(activeField._id, {
            ...fieldValue,
            attributes: {
                ...fieldValue.attributes,
                [key]: value
            }
        }));
    };

    const renderControls = ({ key, settings }) => {

        if (!settings.type) {
            return;
        }

        if(!DragwybEditor.controlTypes[settings.type]){
            return <></>;
        }

        const selectedSettings={ ...fieldValue.attributes, ...sectionSettings };
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

        let html = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/ControlRender/' + settings.type, getHtml(), key, settings, fieldVal, handleChange, Utils);

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