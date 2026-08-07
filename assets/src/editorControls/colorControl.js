import React from "react";
import { IoColorPaletteOutline } from "react-icons/io5";
import { __ } from "@wordpress/i18n";
import Select from "../editor/components/Common/Select";

export default class ColorControl extends DragwybEditor.editor.extends.ControlBase {
    pickr = null;

    controlName() {
        return 'color';
    }

    onDestroy() {
        if (this.pickr) {
            this.pickr.destroyAndRemove();
            this.pickr = null;
        }
    }

    getMode() {
        if (this.state && this.state.mode) {
            return this.state.mode;
        }
        const { default: defaultColor = '' } = this.settings || {};
        const { value = defaultColor } = this.state || {};
        if (value && typeof value === 'string') {
            const trimmed = value.trim();
            if (trimmed.startsWith('--') || (/^var\(--[a-zA-Z0-9\-_]+\)$/).test(trimmed)) {
                return 'var';
            }
            if (trimmed.startsWith('rgb(') || trimmed.startsWith('rgba(') || trimmed.startsWith('hsl(') || trimmed.startsWith('hsla(') || (trimmed.includes('var(') && !(/^var\(--[a-zA-Z0-9\-_]+\)$/).test(trimmed))) {
                return 'custom';
            }
        }
        return 'color';
    }

    setMode(mode) {
        if (this.pickr) {
            try {
                this.pickr.hide();
            } catch (e) {}
        }
        this.setState({ mode });
    }

    initPickr() {
        const { id } = this;
        const { value } = this.state;
        const { default: defaultColor } = this.settings;
        this.resetApply = false;

        // Already initialized
        if (this.pickr) {
            this.pickr.show();
            return;
        }

        const colorVal = (value && !value.startsWith('var(') && !value.startsWith('--') && !value.includes('(')) ? value : '';

        // Initialize new Pickr instance
        this.pickr = Pickr.create({
            el: `.dragwyb-color__preview[data-id="${id}"]`,
            theme: 'monolith',
            default: colorVal || defaultColor || '',
            comparison: false,
            appClass: 'dragwyb-color__pickr',

            components: {
                preview: false,
                opacity: true,
                hue: true,

                interaction: {
                    input: true,
                    save: true,
                    clear: true,
                    rgba: true,
                    hex: true,
                    hsla: true
                }
            }
        });

        // Update control value
        this.pickr.on('change', (color) => {
            const hex = color.toHEXA().toString();
            this.updateControlHandler(id, hex);
        });

        this.pickr.on('save', (color) => {
            const hex = color ? color.toHEXA().toString() : '';
            this.updateControlHandler(id, hex);
            this.pickr.hide();
        });

        this.pickr.on('clear', (e) => {
            this.resetControl();
        });

        this.pickr.show();
    }

    getVarDisplayValue(val) {
        if (!val || typeof val !== 'string') return '';
        if (val.startsWith('var(') && val.endsWith(')')) {
            return val.substring(4, val.length - 1).trim();
        }
        return val;
    }

    handleVarChange(inputVal) {
        const { id } = this;
        let trimmed = inputVal.trim();
        if (!trimmed) {
            this.updateControlHandler(id, '');
            return;
        }

        if (trimmed.startsWith('var(') && trimmed.endsWith(')')) {
            trimmed = trimmed.substring(4, trimmed.length - 1).trim();
        }

        if (trimmed.startsWith('-')) {
            trimmed = trimmed.replace(/^-+/, '--');
        } else {
            trimmed = `--${trimmed}`;
        }

        const finalVal = `var(${trimmed})`;
        this.updateControlHandler(id, finalVal);
    }

    handleCustomChange(inputVal) {
        const { id } = this;
        this.updateControlHandler(id, inputVal);
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { id, settings } = this;
        const { label = __('Color', 'smart-form-builder-by-dragwyb'), default: defaultColor = '' } = settings;
        const { value = defaultColor } = this.state;
        const currentMode = this.getMode();
        const rawVarValue = (value && typeof value === 'string' && (value.startsWith('var(') || value.startsWith('--'))) ? value : '';
        const varInputValue = this.getVarDisplayValue(rawVarValue);

        return (
            <div className="dragwyb-control dragwyb-control--color" data-control="color" id={`control-${id}`}>
                <this.RenderLabel
                    attr={{ htmlFor: id }}
                />

                <div className="dragwyb-color-row">
                    {currentMode === 'color' && (
                        <div className="dragwyb-color__wrapper" onClick={() => this.initPickr()}>
                            <input
                                type="hidden"
                                id={id}
                                name={id}
                                value={value || defaultColor}
                            />

                            <span
                                className="dragwyb-color__preview"
                                data-id={id}
                                style={{ '--pcr-color': value || defaultColor }}
                            />

                            <span className="dragwyb-color__code" data-id={id}>{value || defaultColor}</span>

                            <IoColorPaletteOutline size="1.3rem" />
                        </div>
                    )}

                    {currentMode === 'var' && (
                        <input
                            type="text"
                            className="dragwyb-control__input dragwyb-color-var__input"
                            id={id}
                            name={id}
                            placeholder="--primary"
                            value={varInputValue}
                            onChange={(e) => this.handleVarChange(e.target.value)}
                        />
                    )}

                    {currentMode === 'custom' && (
                        <input
                            type="text"
                            className="dragwyb-control__input dragwyb-color-var__input"
                            id={id}
                            name={id}
                            placeholder="rgb(var(--on-surface-variant))"
                            value={value || ''}
                            onChange={(e) => this.handleCustomChange(e.target.value)}
                        />
                    )}

                    <Select
                        options={{
                            color: __('Color', 'smart-form-builder-by-dragwyb'),
                            var: __('Var', 'smart-form-builder-by-dragwyb'),
                            custom: __('Custom', 'smart-form-builder-by-dragwyb')
                        }}
                        value={currentMode}
                        onChange={(newMode) => this.setMode(newMode)}
                        className="dragwyb-unit-selector dragwyb-color-mode-selector"
                    />
                </div>

                {currentMode === 'var' && (
                    <p className="dragwyb-color-var__help">
                        {__('CSS variable start -- ex --primary only pass css variable name', 'smart-form-builder-by-dragwyb')}
                    </p>
                )}

                {currentMode === 'custom' && (
                    <p className="dragwyb-color-var__help">
                        {__('Pass custom CSS color format (e.g. rgb(var(--on-surface-variant)))', 'smart-form-builder-by-dragwyb')}
                    </p>
                )}
            </div>
        );
    }

    resetControl() {
        if (this.resetApply === true) return;

        const { id, settings } = this;
        const { default: defaultValue = null } = settings;
        const value = this.getValidValue(defaultValue);
        this.resetApply = true;

        this.updateControlHandler(id, '');
        this.setState({ mode: 'color' });

        if (this.pickr) {
            this.pickr.setColor(value || '');
        }

        this.resetApply = false;
    }
}
