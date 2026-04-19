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
        this.resetApply = false;

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

        this.pickr.on('clear', (e) => {
            this.resetControl();
        })

        this.pickr.show();
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { id, settings } = this;
        const { label = __('Color', 'smart-form-builder-by-dragwyb'), default: defaultColor = '' } = settings;
        const { value = defaultColor } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--color" data-control="color" id={`control-${id}`}>
                <this.RenderLabel
                    attr={
                        { htmlFor: id }
                    }
                />
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
        if (this.resetApply === true) return;

        const { id, settings } = this;
        const { default: defaultValue = null } = settings;
        const value = this.getValidValue(defaultValue);
        this.resetApply = true;

        this.updateControlHandler(id, '');

        if (this.pickr) {
            this.pickr.setColor(value);
        }

        this.resetApply = false;
    }
}
