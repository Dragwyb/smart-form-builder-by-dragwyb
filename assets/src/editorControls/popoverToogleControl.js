export default class PopoverToggleControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return "popover-toggle";
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;
        const isActive = id === value;
        const labelInline = settings.label_inline || true;

        const clickHandler = (e) => {
            const ele = e.target;
            const popoverWrp = jQuery(ele).closest('.dragwyb-setting-row').next('.dragwyb-popover');

            const status = popoverWrp.css('display') === 'none';

            jQuery('.dragwyb-popover').hide();

            if (status) {
                popoverWrp.show();
            } else {
                popoverWrp.hide();
            }
        }

        return (
            <div className="dragwyb-control dragwyb-control--popover-toggle" data-control="popover-toggle" id={`control-${id}`}>
                <div
                    id={id}
                    type="button"
                    className={`dragwyb-popover__trigger ${isActive ? "is-active" : ""}${labelInline ? " dragwyb-label-inline" : ""}`}
                    aria-pressed={isActive}
                    onClick={clickHandler}
                >
                    {settings.label && (
                        <label className="dragwyb-control__label">
                            {settings.label}
                        </label>
                    )}
                    {settings.icon && (
                        <i className={`dragwyb-popover-toggle__icon ${settings.icon}`} aria-hidden="true" />
                    )}
                </div>
            </div>
        );
    }
}