export default class SwitcherControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'switcher';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value = settings.default } = this.state;
        const returnValue = settings.return_value;

        const changeHandler = () => {
            const updatedValue = value === returnValue ? null : returnValue;
            this.updateControlHandler(id, updatedValue)
        }

        return (
            <div
                className="dragwyb-control dragwyb-control--switcher"
                data-control="switcher"
                id={`control-${id}`}
            >
                {settings.label && (
                    <label
                        className="dragwyb-control__label"
                        htmlFor={id}
                    >
                        {settings.label}
                    </label>
                )}

                <label className="dragwyb-switcher">
                    <input
                        type="checkbox"
                        id={id}
                        name={id}
                        checked={value === returnValue}
                        onChange={changeHandler}
                    />
                    <span className="dragwyb-switcher__slider">
                        {settings.show_label === true && (value === returnValue ? settings.on_label : settings.off_label)}
                    </span>
                </label>
            </div>
        );
    }
}