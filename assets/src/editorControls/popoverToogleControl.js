export default class PopoverToggleControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return "popover-toggle";
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;
        const isActive = id === value;

        const clickHandler = (e) => {
            const ele = e.target;
            const popoverWrp = jQuery(ele).closest('.setting-row').next('.dragwyb-popover');

            popoverWrp.toggle();
        }

        return (
            <div className="dragwyb-control dragwyb-control--popover-toggle" data-control="popover-toggle" id={`control-${id}`}>
                <button
                    id={id}
                    type="button"
                    className={`dragwyb-popover__trigger ${isActive ? "is-active" : ""}`}
                    aria-pressed={isActive}
                    onClick={() => this.updateControlHandler(id, !isActive)}
                >
                    {settings.label && (
                        <label className="dragwyb-control__label">
                            {settings.label}
                        </label>
                    )}
                    {settings.icon && (
                        <i className={`dragwyb-popover-toggle__icon ${settings.icon}`} aria-hidden="true" onClick={clickHandler} />
                    )}
                </button>
            </div>
        );
    }
}