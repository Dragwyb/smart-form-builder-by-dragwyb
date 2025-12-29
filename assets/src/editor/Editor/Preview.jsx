import React from 'react';
import { useSelector } from 'react-redux';
import Field from './Fields/Field';

const Preview = ({ onChange }) => {
    const values = useSelector(state => state.values); // Assuming values are stored in Redux
    const fields = useSelector(state => state.form.fields); // Assuming fields are stored in Redux
    const errors = useSelector(state => state.errors); // Assuming errors are stored in Redux

    console.log(fields)
    return (
        <div className="dragwyb-preview">
            {fields ?
                <>
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
                </>
                : <p>No fields found</p>}
        </div>
    );
};

export default Preview; 