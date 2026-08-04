export default class SwitcherControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'switcher';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value = settings.default } = this.state;
        const returnValue = settings.return_value;
        const disabled = settings.disabled || false;

        const changeHandler = () => {
            const updatedValue = value === returnValue ? '' : returnValue;
            this.updateControlHandler(id, updatedValue)
        }

        return (
            <div
                className="dragwyb-control dragwyb-control--switcher"
                data-control="switcher"
                id={`control-${id}`}
            >
                <this.RenderLabel
                    attr={
                        { htmlFor: id }
                    }
                />

                <label className="dragwyb-switcher">
                    <input
                        type="checkbox"
                        id={id}
                        name={id}
                        checked={value === returnValue}
                        onChange={changeHandler}
                        disabled={disabled}
                    />
                    <div className="dragwyb-switcher__label" {...{ 'data-show-label': settings.on_label, 'data-hide-label': settings.off_label }}>
                        <span className="dragwyb-switcher__slider">
                        </span>
                    </div>
                </label >
            </div >
        );
    }
}