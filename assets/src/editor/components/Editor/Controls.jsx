import React from 'react';
import { useDispatch } from 'react-redux';
import { addField, updateFieldValues } from '../../store/actions';

const Controls = ({ onFieldSelect }) => {
    const dispatch = useDispatch();
    const fieldTypes = DragwybEditor.fieldTypes;

    const handleAddField = (type) => {
        const field = {
            id: `field_${Date.now()}`,
            type,
            settings: {}
        };
        dispatch(addField(field));
        onFieldSelect(field);
    };

    return (
        <div className="dragwyb-controls">
            <div className="dragwyb-controls__header">
                <h2>{DragwybBuilder.i18n.addField}</h2>
            </div>
            <div className="dragwyb-controls__fields">
                {Object.entries(fieldTypes).map(([type, config]) => (
                    <button
                        key={type}
                        className="field-type"
                        onClick={() => handleAddField(type)}
                    >
                        <span className={`dashicons dashicons-${config.icon}`} />
                        <span>{config.label}</span>
                    </button>
                ))}
            </div>
        </div>
    );
};

export default Controls; 