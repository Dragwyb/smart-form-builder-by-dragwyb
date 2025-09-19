export default class PopoverToggleControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return "popover-toggle";
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--popover-toggle" data-control="popover-toggle" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label">
                        {settings.label}
                    </label>
                )}
                <button
                    type="button"
                    className={`dragwyb-popover__trigger ${value ? 'is-active' : ''}`}
                    onClick={() => this.updateControlHandler(id, !value)}
                >
                    {settings.buttonLabel || 'Toggle'}
                </button>
                {value && (
                    <div className="dragwyb-popover__content">
                        {this.props.children || settings.content}
                    </div>
                )}
            </div>
        );
    }
}