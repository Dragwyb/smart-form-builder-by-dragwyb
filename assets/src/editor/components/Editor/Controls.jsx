import React from 'react';
import { useDispatch } from 'react-redux';
import { addField, updateFieldValues } from '../../store/actions';
import { useSelector } from 'react-redux';
import Helper  from '../Utils';

const Controls = ({ onFieldSelect }) => {
    const fieldTypes = DragwybEditor.fieldTypes;
    const dispatch=useDispatch();
    const state=useSelector(state => state);

    const Utils=Helper(state, dispatch);

    const handleAddField = (type) => {
        const field = {
            _id: `${Date.now()}`,
            type,
        };

        if(DragwybEditor.fieldTypes[type] && DragwybEditor.fieldTypes[type].controls){
            const fieldControls=DragwybEditor.fieldTypes[type].controls;
            field.settings={};
            Object.keys(fieldControls).forEach(id=>{
                if(!['tabs','tab','section'].includes(fieldControls[id].type)){

                    let defaultValue=fieldControls[id].default ? fieldControls[id].default : '';
                        
                    defaultValue=DragwybBuilder.Hooks.applyFilter(`Dragwyb/Editor/AddControl/${fieldControls[id].type}.defaultValue`, defaultValue, Utils);
                    field.settings[id]=defaultValue;
                }
            })
        }

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