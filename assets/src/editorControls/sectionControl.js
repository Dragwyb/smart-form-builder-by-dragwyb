import { RiArrowDownSLine } from "react-icons/ri";

export default class SectionControl extends DragwybEditor.editor.extends.ControlBase {
    controlName() {
        return 'section';
    }

    onRender(){
        if(!this.controlName){
            return;
        }
    }

    bind() {
        if (!this.shouldRender()) return <></>;

        const { settings, id } = this;
        const { value } = this.state;

        let sectionCls = 'dragwyb-control dragwyb-control--section';

        if (id === value) {
            sectionCls += ' section-active';
            
            DragwybBuilder.Hooks.removeFilter("Dragwyb/Editor/ControlSectionUpdate");
            DragwybBuilder.Hooks.addFilter("Dragwyb/Editor/ControlSectionUpdate", this.controlRenderKey.bind(this));
        }

        return (
            <div className={sectionCls} data-control="section" id={`control-${id}`} onClick={() => { this.updateControlHandler(id, !(id === value)) }}>
                <span className="dragwyb-section__title">{settings.label}</span>
                <RiArrowDownSLine />
            </div>
        );
    }

    controlRenderKey(type,key, value){
        if(type !== this.controlName || key === this.id || value !== true){
            return;
        }

        this.setState({value: ''});
    }

    updateControlHandler(key, value) {
        this.setState({ value: value ? key : '' })
        this.updateControls(key, value);

        if(this.id && value === true){
            DragwybBuilder.Hooks.applyFilter("Dragwyb/Editor/ControlSectionUpdate", this.controlName, key, value);
        }
    }
};