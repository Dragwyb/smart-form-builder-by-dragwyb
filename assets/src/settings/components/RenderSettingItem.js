import React, { useState } from 'react';
import ToggleSwitch from './ToggleSwitch';
import { FaEye, FaEyeSlash } from 'react-icons/fa';

const RenderSettingItem = ({ itemKey, itemData, tabKey, handleSettingChange, hideStartSectionHeader = false }) => {
    if (!itemData) return null;

    const {
        type = 'string',
        label = itemKey,
        description = '',
        start_section,
        value,
        default: defaultVal,
        options,
        valid_values,
        placeholder = '',
        min,
        max,
        inline = true
    } = itemData;

    const [showPassword, setShowPassword] = useState(false);

    const currentValue = value !== undefined ? value : (defaultVal ?? '');
    const isChecked = currentValue === true || currentValue === 'yes';

    const handleValueChange = (newVal) => {
        handleSettingChange(tabKey, itemKey, newVal);
    };

    const selectOptions = options || (valid_values ? valid_values.map(v => ({ label: String(v).toUpperCase(), value: v })) : []);
    const isSecretField = type === 'password' || itemKey.toLowerCase().includes('secret') || itemKey.toLowerCase().includes('key');

    const renderControl = () => {
        if (type === 'bool' || type === 'toggle') {
            const isStringBool = typeof defaultVal === 'string' && (defaultVal === 'yes' || defaultVal === 'no');
            return (
                <ToggleSwitch
                    label={label}
                    description={description}
                    checked={isChecked}
                    onChange={val => handleValueChange(isStringBool ? (val ? 'yes' : 'no') : val)}
                />
            );
        }

        return (
            <div
                className={`dragwyb-setting-row ${inline === false ? 'dragwyb-setting-inline' : ''}`}
                key={itemKey}
            >
                <div className="dragwyb-setting-info">
                    <h4>{label}</h4>
                    {description && <p>{description}</p>}
                </div>
                <div className="dragwyb-setting-control">
                    {type === 'select' ? (
                        <select
                            value={currentValue}
                            onChange={e => handleValueChange(e.target.value)}
                            className="dragwyb-select-control"
                        >
                            {selectOptions.map(opt => (
                                <option key={opt.value} value={opt.value}>{opt.label}</option>
                            ))}
                        </select>
                    ) : type === 'number' ? (
                        <input
                            type="number"
                            min={min}
                            max={max}
                            value={currentValue}
                            onChange={e => handleValueChange(parseInt(e.target.value, 10) || 0)}
                            placeholder={placeholder}
                            className="dragwyb-input-control number-input"
                        />
                    ) : isSecretField ? (
                        <div className="dragwyb-input-group">
                            <input
                                type={showPassword ? 'text' : 'password'}
                                value={currentValue}
                                onChange={e => handleValueChange(e.target.value)}
                                placeholder={placeholder || (itemKey.includes('secret') ? 'Enter Secret Key' : 'Enter API Key')}
                                className="dragwyb-input-control secret-input"
                            />
                            <button
                                type="button"
                                className="dragwyb-password-toggle-btn"
                                onClick={() => setShowPassword(!showPassword)}
                                title={showPassword ? 'Hide value' : 'Show value'}
                            >
                                {showPassword ? <FaEyeSlash /> : <FaEye />}
                            </button>
                        </div>
                    ) : (
                        <input
                            type={type === 'email' ? 'email' : 'text'}
                            value={currentValue}
                            onChange={e => handleValueChange(e.target.value)}
                            placeholder={placeholder || 'Enter value'}
                            className="dragwyb-input-control"
                        />
                    )}
                </div>
            </div>
        );
    };

    return (
        <React.Fragment key={itemKey}>
            {start_section && !hideStartSectionHeader && (
                <div className="dragwyb-start-section-header">
                    {typeof start_section === 'string' && (
                        <h3>{start_section}</h3>
                    )}
                </div>
            )}
            {renderControl()}
        </React.Fragment>
    );
};

export default RenderSettingItem;
