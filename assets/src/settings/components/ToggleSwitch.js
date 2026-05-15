import React from 'react';

const ToggleSwitch = ({ checked, onChange, label, description }) => {
    return (
        <div className="dragwyb-setting-row">
            <div className="dragwyb-setting-info">
                <h4>{label}</h4>
                {description && <p>{description}</p>}
            </div>
            <div className="dragwyb-setting-control">
                <label className="dragwyb-toggle-switch">
                    <input 
                        type="checkbox" 
                        checked={checked} 
                        onChange={(e) => onChange(e.target.checked)} 
                    />
                    <span className="dragwyb-slider round"></span>
                </label>
            </div>
        </div>
    );
};

export default ToggleSwitch;
