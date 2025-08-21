import React from 'react';
import { useSelector } from 'react-redux';
import Field from './Fields/Field';

const Preview = ({ values, onChange, errors }) => {
    const fields = useSelector(state => state.form.fields); // Assuming fields are stored in Redux

    return (
        <div className="dragwyb-preview">
            <h2 className="dragwyb-preview__title">Form Preview</h2>
            {fields.map((field) => (
                <Field
                    key={field._id}
                    field={field}
                    value={values[field._id]}
                    errors={errors[field.name] || []}
                    onChange={onChange}
                />
            ))}
        </div>
    );
};

export default Preview; 