import { IoColorPaletteOutline } from "react-icons/io5";
import { __ } from "@wordpress/i18n";
import Reset from '../editor/components/Common/Reset';

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

    initPickr() {
        const { id } = this;
        const { value } = this.state;
        const { default: defaultColor } = this.settings;

        // Already initialized
        if (this.pickr) {
            this.pickr.show();
            return;
        }

        // Initialize new Pickr instance
        this.pickr = Pickr.create({
            el: `.dragwyb-color__preview[data-id="${id}"]`,
            theme: 'monolith',
            default: value || defaultColor || '',
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

        this.pickr.show();
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { id, settings } = this;
        const { label = __('Color', 'dragwyb-form-builder'), default: defaultColor = '' } = settings;
        const { value = defaultColor } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--color" data-control="color" id={`control-${id}`}>

                <label className="dragwyb-control__label" htmlFor={id}>
                    {label}
                    <Reset handler={this.resetControl.bind(this)} disabled={value === defaultColor} />
                </label>
                <div className="dragwyb-color__wrapper" onClick={() => this.initPickr()}>

                    {/* Hidden input so your PHP receives value */}
                    <input
                        type="hidden"
                        id={id}
                        name={id}
                        value={value || defaultColor}
                    />

                    {/* Preview Box — click to open Pickr */}
                    <span
                        className="dragwyb-color__preview"
                        data-id={id}
                        style={{ '--pcr-color': value || defaultColor }}
                    />

                    <span className="dragwyb-color__code" data-id={id}>{value || defaultColor}</span>

                    <IoColorPaletteOutline size="1.3rem" />
                </div>
            </div>
        );
    }

    resetControl() {
        const { id, settings } = this;
        const { default: defaultValue = '' } = settings;
        const value = this.getValidValue(defaultValue, '');

        if (this.pickr) {
            this.pickr.setColor(value);
        }
        this.updateControlHandler(id, value);
    }
}
