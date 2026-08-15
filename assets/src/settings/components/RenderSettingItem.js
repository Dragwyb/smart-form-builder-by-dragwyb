import React from 'react';
import ToggleSwitch from './ToggleSwitch';

const RenderSettingItem = ({ itemKey, itemData, tabKey, handleSettingChange }) => {
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

    const currentValue = value !== undefined ? value : (defaultVal ?? '');

    const isChecked = currentValue === true || currentValue === 'yes';

    const handleValueChange = (newVal) => {
        handleSettingChange(tabKey, itemKey, newVal);
    };
    const selectOptions = options || (valid_values ? valid_values.map(v => ({ label: String(v).toUpperCase(), value: v })) : []);

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

        return <div className={`dragwyb-setting-row ${inline === false ? 'dragwyb-setting-inline' : ''}`} key={itemKey} style={{ flexDirection: inline === false ? 'column' : 'row', alignItems: inline === false ? 'flex-start' : 'center' }}>
            <div className="dragwyb-setting-info">
                <h4>{label}</h4>
                {description && <p>{description}</p>}
            </div>
            <div className="dragwyb-setting-control">
                {type === 'select' ?
                    <>
                        <select
                            value={currentValue}
                            onChange={e => handleValueChange(e.target.value)}
                        >
                            {selectOptions.map(opt => (
                                <option key={opt.value} value={opt.value}>{opt.label}</option>
                            ))}
                        </select>
                    </>
                    :
                    (
                        type === 'number' ?
                            <input
                                type="number"
                                min={min}
                                max={max}
                                value={currentValue}
                                onChange={e => handleValueChange(parseInt(e.target.value, 10) || 0)}
                                placeholder={placeholder}
                                style={{ width: '100px' }}
                            /> :
                            <input
                                type={type === 'password' ? 'password' : type === 'email' ? 'email' : 'text'}
                                value={currentValue}
                                onChange={e => handleValueChange(e.target.value)}
                                placeholder={placeholder || 'Enter value'}
                            />
                    )
                }
            </div>
        </div>
    };

    return (
        <React.Fragment key={itemKey}>
            {start_section && (
                <div style={{
                    marginTop: '24px',
                    paddingTop: '20px',
                    borderTop: '1px solid #e2e8f0',
                    marginBottom: '16px'
                }}>
                    {typeof start_section === 'string' && (
                        <h3 style={{ fontSize: '16px', fontWeight: '600', color: '#1e293b', margin: 0 }}>
                            {start_section}
                        </h3>
                    )}
                </div>
            )}
            {renderControl()}
        </React.Fragment>
    );
};

export default RenderSettingItem;
