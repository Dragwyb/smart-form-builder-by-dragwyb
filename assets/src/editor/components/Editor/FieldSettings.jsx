import React from 'react';
import { useDispatch, useSelector, useStore } from 'react-redux';
import { updateField, updateSectionSettings, resetSectionSettings } from '../../store/actions';
import { Panel } from '../Common';
import shouldRenderField from './shouldRenderField';
import { Utils as Helper } from '../Utils';

const FieldSettings = ({ activeFieldID, fieldValue, fieldSettings, sectionSettings, onClose }) => {

    const dispatch = useDispatch();
    let activeSection = false;

    const store = useStore();
    const state = store.getState();

    const Utils = Helper(state, dispatch);

    const defautlActiveSection = (key) => {
        if (((sectionSettings && sectionSettings.section) || activeSection) || (sectionSettings && sectionSettings.section === '')) {
            return;
        }

        activeSection = true;

        handleChange(key, true, 'section');
    }

    const defautlActiveTab = (key, settings) => {
        if (sectionSettings && sectionSettings[key]) {
            return;
        }

        handleChange(key, Object.keys(settings.tabs)[0], 'tabs')
    }


    const handleChange = (key, value, type = null) => {
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

        if (!fieldSettings.controls[key].type) {
            return;
        }

        dispatch(updateField(activeFieldID, {
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

        if (!DragwybEditor.controlTypes[settings.type]) {
            return <></>;
        }

        const selectedSettings = { ...fieldValue.attributes, ...sectionSettings };
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

        let fieldVal = selectedSettings[key];

        if (settings.type === 'section' && !fieldVal) {
            fieldVal = selectedSettings['section'];
        }

        let html = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/ControlRender/' + settings.type, getHtml(), key, settings, fieldVal, handleChange, Utils);

        return <div key={key} className="setting-row" dataType={settings.type}>{html}</div>;
    };

    return (
        <Panel
            title={`${fieldSettings.label} ${DragwybBuilder.i18n.settings}`}
            onClose={onClose}
        >
            {fieldSettings?.controls?.header_controls &&
                <div className='field-header_controls'>
                    {renderControls({ key: 'header_controls', settings: fieldSettings.controls.header_controls })}
                </div>
            }
            <div className="field-settings">
                {Object.keys(fieldSettings.controls).map(key => (
                    <>
                        {key === 'header_controls' ? null : renderControls({ key, settings: fieldSettings.controls[key] })}
                    </>
                ))}
            </div>
        </Panel>
    );
};

export default FieldSettings; 