import React, { useCallback } from 'react';
import Field from './Field';
import { useDispatch } from 'react-redux';
import { updateFieldValue } from '../../utils/helpers'
import DragwybFieldBase from '../../fieldBase';

const Preview = ({ fields, values, errors, children, childrens, Utils }) => {
    const dispatch = useDispatch();

    const onChangeHandler = useCallback(({ fieldId, fieldObject }) => {
        if (!(fieldObject instanceof DragwybFieldBase || fieldObject instanceof DragwybEditor.editor.extends.FieldBase)) return;

        updateFieldValue({ dispatch, id: fieldId, value: fieldObject.value });
    }, [dispatch]);

    return (
        fields.map((field) => (
            <React.Fragment key={field._id}>
                {field && field.settings && field.settings.label && <label>{field.settings.label}</label>}
                <Field
                    field={field}
                    value={values[field._id] || ''}
                    onChange={({ fieldObject }) => onChangeHandler({ fieldId: field._id, fieldObject })}
                    errors={errors?.[field.name] || []}
                    childrens={childrens}
                    Utils={Utils}
                >
                    {children}
                </Field>
            </React.Fragment>
        ))
    );
};

export default Preview;