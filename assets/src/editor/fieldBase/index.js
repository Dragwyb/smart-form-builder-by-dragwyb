class DragwybFieldBase {
    constructor(args) {
        this.fieldName = this.fieldName();

        return this.#renderContent(args);
    }

    #renderContent(args) {

        if (!this.fieldName) {
            return;
        }

        return this.renderComponent(args)
    }

    renderComponent(args) {
        this.#setDisplaySetting(args);
        return this.bind();
    }

    #setDisplaySetting(args) {
        this.html = args[0];
        this.children = args[1]
        this.type = args[2];
        this.id = args[3];
        this.value = args[4];
        this.field = args[5];
        this.Utils = args[6];
        this.childrenIds = args[7];

        this.attributes = this.field.attributes
    }

    RenderLabel({ id, label, required, settings }) {
        if (!label || '' === label) {
            return;
        }

        const field_icon = settings.label_icon;

        const icon_to_render = field_icon && '' !== field_icon['icon'] ? field_icon : false;

        let field_label_class = 'dragwyb-field-label';

        if (icon_to_render && '' !== icon_to_render['icon']) {
            field_label_class += ' dragwyb-field-label-icon';
        }

        return (

            <label htmlFor={id} className={field_label_class}>
                {
                    icon_to_render && '' !== icon_to_render['icon'] ?
                        <>
                            <DragwybEditor.editor.IconsManager.Render icon={{ type: icon_to_render['type'], icon: icon_to_render['icon'] }} className="dragwyb-label-icon" />
                            <span className="dragwyb-field-label-text">
                                {label}
                                {required === 'yes' &&
                                    <span className="dragwyb-required">*</span>}
                            </span>
                        </> :
                        <>
                            {label}

                            {
                                required === 'yes' &&
                                <span className="dragwyb-required">*</span>
                            }
                        </>
                }
            </label>
        )
    }

    /**
     * ✅ Shared method: Check if this control should render based on settings.type
     */
    shouldRender() {
        return this.field?.type === this.fieldName;
    }
}

export default DragwybFieldBase;