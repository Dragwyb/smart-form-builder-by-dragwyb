export default class TextareaControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'textarea';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--textarea" data-control="textarea" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}
                <textarea
                    id={id}
                    name={id}
                    className="dragwyb-control__textarea"
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, e.target.value)}
                />
            </div>
        );
    }
}