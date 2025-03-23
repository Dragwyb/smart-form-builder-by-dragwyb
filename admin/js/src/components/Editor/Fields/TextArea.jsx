import React from 'react';

const TextArea = ({ field, value, onChange, disabled }) => (
    <textarea
        id={field.id}
        name={field.name}
        value={value || ''}
        onChange={(e) => onChange(e.target.value)}
        placeholder={field.placeholder}
        required={field.required}
        disabled={disabled}
        rows={field.rows || 4}
        maxLength={field.maxLength}
        minLength={field.minLength}
    />
);

export default TextArea; 