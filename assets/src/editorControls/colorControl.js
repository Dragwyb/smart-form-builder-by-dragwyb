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

        // Already initialized
        if (this.pickr) {
            this.pickr.show();
            return;
        }

        // Initialize new Pickr instance
        this.pickr = Pickr.create({
            el: `.dragwyb-color__preview[data-id="${id}"]`,
            theme: 'monolith',
            default: value || '#000000',
            comparison: false,

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
        const { value } = this.state;
        console.log(value);

        return (
            <div className="dragwyb-control dragwyb-control--color" data-control="color" id={`control-${id}`}>

                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}

                <div className="dragwyb-color__wrapper">

                    {/* Hidden input so your PHP receives value */}
                    <input
                        type="hidden"
                        id={id}
                        name={id}
                        value={value}
                    />

                    {/* Preview Box — click to open Pickr */}
                    <span
                        className="dragwyb-color__preview"
                        data-id={id}
                        style={{ backgroundColor: value }}
                        onClick={() => this.initPickr()}
                    />
                </div>
            </div>
        );
    }
}
