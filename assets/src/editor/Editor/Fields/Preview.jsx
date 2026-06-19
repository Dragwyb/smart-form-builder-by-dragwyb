import React from 'react';
import Field from './Field';

const Preview = ({ fields, values, errors, children, childrens, Utils, perviewIFrame }) => {
    return (
        fields.map((field) => (
            <React.Fragment key={field._id}>
                {field && field.settings && field.settings.label && <label>{field.settings.label}</label>}
                <Field
                    field={field}
                    value={values[field._id] || ''}
                    errors={errors?.[field.name] || []}
                    childrens={childrens}
                    Utils={Utils}
                    perviewIFrame={perviewIFrame}
                >
                    {children}
                </Field>
            </React.Fragment>
        ))
    );
};

export default Preview;