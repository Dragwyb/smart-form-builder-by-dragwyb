import React from 'react';
import { __ } from '@wordpress/i18n';

const FormField = ({
    label,
    type = 'text',
    value,
    onChange,
    error,
    help,
    required = false,
    className = '',
    ...props
}) => {
    const id = `field-${Math.random().toString(36).substr(2, 9)}`;
    const inputProps = {
        id,
        type,
        value,
        onChange: (e) => onChange(e.target.value),
        className: `dragwyb-form-field__input ${error ? 'has-error' : ''}`,
        required,
        ...props
    };

    return (
        <div className={`dragwyb-form-field ${className}`}>
            {label && (
                <label htmlFor={id} className="dragwyb-form-field__label">
                    {label}
                    {required && <span className="required">*</span>}
                </label>
            )}
            {type === 'textarea' ? (
                <textarea {...inputProps} />
            ) : type === 'select' ? (
                <select {...inputProps}>
                    {props.options?.map(option => (
                        <option key={option.value} value={option.value}>
                            {option.label}
                        </option>
                    ))}
                </select>
            ) : (
                <input {...inputProps} />
            )}
            {help && (
                <p className="dragwyb-form-field__help">{help}</p>
            )}
            {error && (
                <p className="dragwyb-form-field__error">{error}</p>
            )}
        </div>
    );
};

export default FormField; 