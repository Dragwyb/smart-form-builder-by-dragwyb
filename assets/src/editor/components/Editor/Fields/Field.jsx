import React from 'react';
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

const Field = ({ field, value, onChange, errors = [], disabled = false }) => {
    const renderField = () => {
        switch (field.type) {
            case 'text':
                return <TextInput field={field} value={value} onChange={onChange} disabled={disabled} />;
            case 'textarea':
                return <TextArea field={field} value={value} onChange={onChange} disabled={disabled} />;
            case 'select':
                return <SelectField field={field} value={value} onChange={onChange} disabled={disabled} />;
            case 'checkbox':
                return <CheckboxField field={field} value={value} onChange={onChange} disabled={disabled} />;
            case 'radio':
                return <RadioField field={field} value={value} onChange={onChange} disabled={disabled} />;
            case 'file':
                return <FileUpload field={field} value={value} onChange={onChange} disabled={disabled} />;
            // case 'date':
            //     return <DateField field={field} value={value} onChange={onChange} disabled={disabled} />;
            // case 'email':
            //     return <EmailField field={field} value={value} onChange={onChange} disabled={disabled} />;
            // case 'phone':
            //     return <PhoneField field={field} value={value} onChange={onChange} disabled={disabled} />;
            // case 'number':
            //     return <NumberField field={field} value={value} onChange={onChange} disabled={disabled} />;
            // case 'hidden':
            //     return <HiddenField field={field} value={value} onChange={onChange} />;
            // case 'signature':
            //     return <SignatureField field={field} value={value} onChange={onChange} disabled={disabled} />;
            // case 'rating':
            //     return <RatingField field={field} value={value} onChange={onChange} disabled={disabled} />;
            default:
                return <div>Unsupported field type: {field.type}</div>;
        }
    };

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
                {renderField()}
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