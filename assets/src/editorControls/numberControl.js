export default class NumberControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'number';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        const min = settings.min ?? 0;
        const max = settings.max ?? 100;
        const step = settings.step ?? 1;

        return (
            <div className="dragwyb-control dragwyb-control--number" data-control="number" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}
                <input
                    type="number"
                    id={id}
                    name={id}
                    className="dragwyb-control__input"
                    min={min}
                    max={max}
                    step={step}
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, parseFloat(e.target.value))}
                />
            </div>
        );
    }
}