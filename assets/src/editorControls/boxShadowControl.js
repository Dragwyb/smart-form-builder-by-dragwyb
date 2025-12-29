import React from 'react';

export default class BoxShadowControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'box_shadow';
    }

    handleParamChange = (property, value) => {
        const currentValue = this.state.value || {};
        const newValue = { ...currentValue, [property]: value };
        this.updateControlHandler(this.id, newValue);
    };

    bind() {
        if (!this.shouldRender()) return <></>;
        const { settings, id } = this;
        const value = this.state.value || {};

        return (
            <div className="dragwyb-control dragwyb-control--box-shadow" id={`control-${id}`}>
                <div className="dragwyb-control-header">
                    <label className="dragwyb-control__label">{settings.label}</label>
                </div>
                
                <div className="dragwyb-box-shadow-inputs">
                    {/* Color Picker */}
                    <div className="dragwyb-control-row">
                        <label>Color</label>
                        <input 
                            type="color" 
                            value={value.color || '#000000'} 
                            onChange={(e) => this.handleParamChange('color', e.target.value)}
                        />
                    </div>

                    {/* Sliders for Position */}
                    {['horizontal', 'vertical', 'blur', 'spread'].map((param) => (
                        <div className="dragwyb-control-slider-row" key={param}>
                            <label>{param.charAt(0).toUpperCase() + param.slice(1)}</label>
                            <div className="dragwyb-slider-wrapper">
                                <input 
                                    type="range" 
                                    min="-100" 
                                    max="100" 
                                    value={value[param] || 0} 
                                    onChange={(e) => this.handleParamChange(param, e.target.value)}
                                />
                                <input 
                                    type="number" 
                                    className="dragwyb-small-input"
                                    value={value[param] || 0} 
                                    onChange={(e) => this.handleParamChange(param, e.target.value)}
                                />
                            </div>
                        </div>
                    ))}

                    {/* Inset Toggle */}
                    <div className="dragwyb-control-row">
                        <label>Position</label>
                        <select 
                            value={value.inset || ''} 
                            onChange={(e) => this.handleParamChange('inset', e.target.value)}
                        >
                            <option value="">Outline</option>
                            <option value="inset">Inset</option>
                        </select>
                    </div>
                </div>
            </div>
        );
    }
}