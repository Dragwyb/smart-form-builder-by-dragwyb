export default class RadioControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'radio';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        return (
            <div className="dragwyb-control dragwyb-control--radio" data-control="radio" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label">{settings.label}</label>
                )}
                <div className="dragwyb-control__options">
                    {options.map((opt) => (
                        <label key={opt.value} className="dragwyb-radio">
                            <input
                                type="radio"
                                name={id}
                                value={opt.value}
                                checked={value === opt.value}
                                onChange={(e) => this.updateControlHandler(id, e.target.value)}
                            />
                            <span className="dragwyb-radio__custom" />
                            <span className="dragwyb-radio__label">{opt.label}</span>
                        </label>
                    ))}
                </div>
            </div>
        );
    }
}