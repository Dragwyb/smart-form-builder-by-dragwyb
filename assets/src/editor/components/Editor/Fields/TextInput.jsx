import React from 'react';

const TextInput = ({ field, value, onChange, disabled }) => (
    <input
        type="text"
        id={field.id}
        name={field.name}
        value={value || ''}
        onChange={(e) => onChange(e.target.value)}
        placeholder={field.placeholder}
        required={field.required}
        disabled={disabled}
        maxLength={field.maxLength}
        minLength={field.minLength}
        pattern={field.pattern}
    />
);

export default TextInput; 