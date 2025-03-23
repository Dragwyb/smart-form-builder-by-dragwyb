import React from 'react';

const SelectField = ({ field, value, onChange, disabled }) => (
    <select
        id={field.id}
        name={field.name}
        value={value || ''}
        onChange={(e) => onChange(e.target.value)}
        required={field.required}
        disabled={disabled}
        multiple={field.multiple}
    >
        {field.placeholder && (
            <option value="" disabled>
                {field.placeholder}
            </option>
        )}
        {field.options.map((option, index) => (
            <option key={index} value={option.value}>
                {option.label}
            </option>
        ))}
    </select>
);

export default SelectField; 