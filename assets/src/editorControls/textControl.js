export default class TextControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'text';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value = settings.default } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--text" data-control="text" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}
                <input
                    type="text"
                    className="dragwyb-control__input"
                    id={id}
                    name={id}
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, e.target.value)}
                />
            </div>
        );
    }
}