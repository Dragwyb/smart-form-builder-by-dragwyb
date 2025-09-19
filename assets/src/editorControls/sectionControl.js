import { RiArrowDownSLine } from "react-icons/ri";

export default class SectionControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'section';
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        let sectionCls = 'dragwyb-control dragwyb-control--section';

        if (id === value) {
            sectionCls += ' section-active';
        }

        return (
            <div className={sectionCls} data-control="section" id={`control-${id}`} onClick={() => { this.updateControlHandler(id, !(id === value)) }}>
                <span className="dragwyb-section__title">{settings.label}</span>
                <RiArrowDownSLine />
            </div>
        );
    }

    updateControlHandler(key, value) {
        this.setState({ value: value ? key : '' })
        this.updateControls(key, value);
    }
};