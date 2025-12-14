import Select from "../editor/components/Common/Select";

export default class SelectControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'select';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;
        const options = settings.options || {};
        const labelInline = settings.label_inline || false;

        let wrapperCls = 'dragwyb-control dragwyb-control--select';
        if (labelInline) {
            wrapperCls += ' dragwyb-label-inline';
        }

        return (
            <div className={wrapperCls} data-control="select" id={`control-${id}`}>
                {settings.label && (
                    <label className="dragwyb-control__label" htmlFor={id}>
                        {settings.label}
                    </label>
                )}
                {/* <select
                    id={id}
                    name={id}
                    className="dragwyb-control__select"
                    value={value}
                    onChange={(e) => this.updateControlHandler(id, e.target.value)}
                >
                    {Object.keys(options).map((key) => (
                        <option key={key} value={key}>
                            {options[key]}
                        </option>
                    ))}
                </select> */}
                <Select
                    options={options}
                    value={value}
                    onChange={(value) => this.updateControlHandler(id, value)}
                />
            </div>
        );
    }
}