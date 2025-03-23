import React from 'react';

const CheckboxField = ({ field, value, onChange, disabled }) => {
    const handleChange = (optionValue) => {
        if (field.multiple) {
            const newValue = Array.isArray(value) ? [...value] : [];
            const index = newValue.indexOf(optionValue);
            if (index === -1) {
                newValue.push(optionValue);
            } else {
                newValue.splice(index, 1);
            }
            onChange(newValue);
        } else {
            onChange(optionValue);
        }
    };

    return (
        <div className="dragwyb-checkbox-group">
            {field.options.map((option, index) => (
                <label key={index} className="dragwyb-checkbox">
                    <input
                        type="checkbox"
                        name={field.name}
                        value={option.value}
                        checked={Array.isArray(value) 
                            ? value.includes(option.value)
                            : value === option.value}
                        onChange={() => handleChange(option.value)}
                        disabled={disabled}
                    />
                    <span className="dragwyb-checkbox__label">{option.label}</span>
                </label>
            ))}
        </div>
    );
};

export default CheckboxField; 