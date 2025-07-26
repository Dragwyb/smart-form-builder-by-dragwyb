import React from 'react';
import Field from './Field';

const Preview = ({ fields, values, onChange, errors }) => {
    return (
        <div className="dragwyb-preview">
            {fields.map((field) => (
                <>
                {field && field.settings && field.settings.label && <label>{field.settings.label}</label>}
                <Field
                    key={field._id}
                    field={field}
                    value={values[field._id] || ''}
                    onChange={({fieldId=field._id, value}) => onChange({fieldId, value})}
                    errors={errors[field.name] || []}
                />
                </>
            ))}
        </div>
    );
};

export default Preview; 