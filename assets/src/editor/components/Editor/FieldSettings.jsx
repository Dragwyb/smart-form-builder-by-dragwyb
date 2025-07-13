import React from 'react';
import { useDispatch } from 'react-redux';
import { updateField, updateTempField } from '../../store/actions';
import { Panel } from '../Common';
import shouldRenderField from './shouldRenderField';

const FieldSettings = ({ activeField, fieldValue, onClose }) => {
    const dispatch = useDispatch();
    const fieldType = DragwybEditor.fieldTypes[activeField.type];


    const handleChange = (key, value) => {
        const type=fieldType.controls[key];

        if(['section','tab'].includes(type)){
            dispatch(updateField(activeField.id, {
                ...fieldValue,
                settings: {
                    ...fieldValue.settings,
                    [key]: value
                }
            }));
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

        const shouldRender=shouldRenderField(settings, fieldValue.settings);

        if(!shouldRender){
            return;
        }

        const getHtml=(type)=>{
            switch (type) {
                case 'text':
                    return <input
                    type="text"
                    value={fieldValue.settings[key] || ''}
                    onChange={e => handleChange(key, e.target.value)}
                />;
                case 'checkbox':
                    return  <input
                    type="checkbox"
                    checked={fieldValue.settings[key] || false}
                    onChange={e => handleChange(key, e.target.checked)}
                />;
                case 'select':
                    return  <select
                    value={fieldValue.settings[key] || ''}
                    onChange={e => handleChange(key, e.target.value)}
                >
                    {settings.options && settings.options.map(option => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>;
                default:
                    return <div>Unsupported Controller type: {settings.type}</div>;
            }
        }

        return DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/ControlRender/'+settings.type, getHtml(settings.type), key, settings, fieldValue.settings[key], handleChange);
    };

    return (
        <Panel
            title={`${fieldType.label} ${DragwybBuilder.i18n.settings}`}
            onClose={onClose}
        >
            <div className="field-settings">
                {Object.keys(fieldType.controls).map(key => (
                    <div key={key} className="setting-row">
                        {renderControls({ key, settings: fieldType.controls[key] })}
                    </div>
                ))}
            </div>
        </Panel>
    );
};

export default FieldSettings; 