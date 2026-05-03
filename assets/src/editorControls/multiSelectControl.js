import MultiSelect from "../editor/components/Common/MultiSelect";

export default class MultiSelectControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'multiselect';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value = settings.default || [] } = this.state;
        const options = settings.options || {};



        let wrapperCls = 'dragwyb-control dragwyb-control--multiselect';

        return (
            <div className={wrapperCls} data-control="multiselect" id={`control-${id}`}>
                <this.RenderLabel
                    attr={
                        { htmlFor: id }
                    }
                />
                <MultiSelect
                    options={options}
                    value={value}
                    onChange={(newValue) => this.updateControlHandler(id, newValue)}
                />
            </div>
        );
    }
}
