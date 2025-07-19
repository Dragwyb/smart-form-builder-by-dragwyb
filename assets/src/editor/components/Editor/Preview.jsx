import React from 'react';
import Field from './Fields/Field';

const Preview = ({ fields, values, onChange, errors }) => {
    return (
        <div className="dragwyb-preview">
            <h2 className="dragwyb-preview__title">Form Preview</h2>
            {fields.map((field) => (
                <Field
                    key={field.id}
                    field={field}
                    value={values[field.id]}
                    errors={errors[field.name] || []}
                    onChange={onChange}
                />
            ))}
        </div>
    );
};

export default Preview; 