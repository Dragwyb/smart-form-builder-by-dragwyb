export default class ColorControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'color';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--color" data-control="color" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}
                <div className="dragwyb-color__wrapper">
                    <input
                        type="color"
                        id={id}
                        name={id}
                        className="dragwyb-control__color"
                        value={value}
                        onChange={(e) => this.updateControlHandler(id, e.target.value)}
                    />
                    <span
                        className="dragwyb-color__preview"
                        style={{ backgroundColor: value }}
                    />
                </div>
            </div>
        );
    }
}