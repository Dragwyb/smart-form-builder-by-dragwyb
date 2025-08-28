import React from 'react';
import Field from './Field';
import { useDispatch } from 'react-redux';
import {updateFieldValue} from '../../utils/helpers'
import DragwybFieldBase from '../../fieldBase';

const Preview = ({ fields, values, errors }) => {
    const dispatch=useDispatch();

    const onChangeHandler=({ fieldId, fieldObject }) => {
        if(!(fieldObject instanceof DragwybFieldBase || fieldObject instanceof DragwybEditor.editor.extends.FieldBase)) return;

        updateFieldValue({dispatch, id: fieldId, value: fieldObject.value});
    };

    return (
        <div className="dragwyb-preview">
            {fields.map((field) => (
                <>
                {field && field.settings && field.settings.label && <label>{field.settings.label}</label>}
                <Field
                    key={field._id}
                    field={field}
                    value={values[field._id] || ''}
                    onChange={({fieldObject}) => onChangeHandler({fieldId: field._id, fieldObject})}
                    errors={errors[field.name] || []}
                />
                </>
            ))}
        </div>
    );
};

export default Preview; 