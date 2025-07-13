import React from 'react';

const RadioField = ({ field, value, onChange, disabled }) => (
    <div className="dragwyb-radio-group">
        {field.options && field.options.map((option, index) => (
            <label key={index} className="dragwyb-radio">
                <input
                    type="radio"
                    name={field.name}
                    value={option.value}
                    checked={value === option.value}
                    onChange={() => onChange(option.value)}
                    disabled={disabled}
                />
                <span className="dragwyb-radio__label">{option.label}</span>
            </label>
        ))}
    </div>
);

export default RadioField; 