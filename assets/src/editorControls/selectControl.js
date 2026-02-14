import Select from "../editor/components/Common/Select";

export default class SelectControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'select';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value = settings.default } = this.state;
        const options = settings.options || {};
        const labelInline = settings.label_inline || false;

        let wrapperCls = 'dragwyb-control dragwyb-control--select';
        if (labelInline) {
            wrapperCls += ' dragwyb-label-inline';
        }

        return (
            <div className={wrapperCls} data-control="select" id={`control-${id}`}>
                <this.RenderLabel
                    attr={
                        { htmlFor: id }
                    }
                />
                <Select
                    options={options}
                    value={value}
                    onChange={(value) => this.updateControlHandler(id, value)}
                />
            </div>
        );
    }
}