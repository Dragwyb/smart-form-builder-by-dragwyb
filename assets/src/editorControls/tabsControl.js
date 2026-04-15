export default class TabsControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'tabs';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;
        const options = settings.tabs || [];

        if (!value && value === '' && Object.keys(options).length > 0) {
            this.updateControlHandler(id, Object.keys(options)[0]);
        }

        return (
            <div className="dragwyb-control dragwyb-control--tabs" data-control="tabs" id={`control-${id}`}>
                <this.RenderLabel />
                <div className="dragwyb-tabs__nav">
                    {Object.keys(options)?.map((option) => (
                        <button
                            key={option}
                            type="button"
                            className={`dragwyb-tabs__nav-item ${value === option ? 'is-active' : ''}`}
                            onClick={() => this.updateControlHandler(id, option)}
                        >
                            {options[option].label}
                        </button>
                    ))}
                </div>
            </div>
        );
    }
}