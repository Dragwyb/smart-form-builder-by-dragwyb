import React from 'react';
import { useDispatch } from 'react-redux';
import { __ } from '@wordpress/i18n';
import TextInput from './TextInput';
import TextArea from './TextArea';
import SelectField from './SelectField';
import CheckboxField from './CheckboxField';
import RadioField from './RadioField';
import FileUpload from './FileUpload';
// import DateField from './DateField';
// import EmailField from './EmailField';
// import PhoneField from './PhoneField';
// import NumberField from './NumberField';
// import HiddenField from './HiddenField';
// import SignatureField from './SignatureField';
// import RatingField from './RatingField';

const Field = ({ field, value = '', onChange, errors = [], disabled = false }) => {
    const getHtml = () => {
        return <div>Unsupported field type: {field.type}</div>;
    };

    let Html = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/FieldRender/' + field.type, getHtml(), field.type, field.id, value, field, onChange);

    return (
        <div className={`dragwyb-field dragwyb-field--${field.type} ${field.className || ''}`}>
            {field.label && field.type !== 'hidden' && (
                <label className="dragwyb-field__label" htmlFor={field.id}>
                    {field.label}
                    {field.required && <span className="dragwyb-field__required">*</span>}
                </label>
            )}
            {field.description && (
                <div className="dragwyb-field__description">{field.description}</div>
            )}
            <div className="dragwyb-field__input">
                {Html}
            </div>
            {errors.length > 0 && (
                <div className="dragwyb-field__errors">
                    {errors.map((error, index) => (
                        <div key={index} className="dragwyb-field__error">{error}</div>
                    ))}
                </div>
            )}
        </div>
    );
};

export default Field; 