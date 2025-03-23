import React from 'react';
import { useDispatch } from 'react-redux';
import { updateField } from '../../store/actions';
import { Panel } from '../Common';

const FieldSettings = ({ activeField, fieldValue, onClose }) => {
    const dispatch = useDispatch();
    const fieldType = DragwybEditor.fieldTypes[activeField.type];

    const handleChange = (setting, value) => {
        dispatch(updateField(activeField.id, {
            ...fieldValue,
            settings: {
                ...fieldValue.settings,
                [setting]: value
            }
        }));
    };

  

    return (
        <Panel
            title={`${fieldType.label} ${DragwybEditor.i18n.settings}`}
            onClose={onClose}
        >
            <div className="field-settings">
                {Object.entries(fieldType.settings).map(([key, setting]) => (
                    <div key={key} className="setting-row">
                        <label>{setting.label}</label>
                        {setting.type === 'text' && (
                            <input
                                type="text"
                                value={fieldValue.settings[key] || ''}
                                onChange={e => handleChange(key, e.target.value)}
                            />
                        )}
                        {setting.type === 'checkbox' && (
                            <input
                                type="checkbox"
                                checked={fieldValue.settings[key] || false}
                                onChange={e => handleChange(key, e.target.checked)}
                            />
                        )}
                        {setting.type === 'select' && (
                            <select
                                value={fieldValue.settings[key] || ''}
                                onChange={e => handleChange(key, e.target.value)}
                            >
                                {setting.options.map(option => (
                                    <option key={option.value} value={option.value}>
                                        {option.label}
                                    </option>
                                ))}
                            </select>
                        )}
                    </div>
                ))}
            </div>
        </Panel>
    );
};

export default FieldSettings; 