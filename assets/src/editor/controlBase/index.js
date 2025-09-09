import React, {Component} from "react";

class DragwybControlBase extends Component {
    #updateValue = () => {}

    constructor(props) {
        super();
        this.state={
            settings: props.settings,
            value: props.value
        }
        this.controlName = this.controlName() || props.settings.type;
        this.#renderContent(props);
    }
    
    controlName=()=>{return null};
    
    bind(){
        return <div>Unsupported Controller type: {this.settings.type}</div>
    }

    componentDidUpdate=(prevProps)=>{
        if(prevProps.settings !== this.props.settings){
            this.setState({settings: this.props.settings});
        }

        if(prevProps.value !== this.props.value){
            this.setState({value: this.props.value});
        }
    }

    #renderContent(props) {
        if(!this.controlName){
            return;
        }

        return this.renderComponent(props)
    }

    renderComponent(props) {
        this.#setDisplaySetting(props);
    }

    render(){
        return this.bind();
    }

    #setDisplaySetting(props) {
        this.id = props.id;
        this.settings = props.settings;
        this.#updateValue = props.handleChange;
        this.Utils=props.Utils;
    }

    updateControlHandler(key, value){
        this.setState({value})
        this.updateControls(key, value);
    }

    updateControls(key, value) {
        this.#triggerOnChange(key, value)
    }

    #triggerOnChange(key, value) {
        this.#updateValue(key, value, this.settings.type, this);
    }

    /**
     * ✅ Shared method: Check if this control should renfder based on settings.type
     */
    shouldRender() {
        return this.settings?.type === this.controlName && DragwybEditor.controlTypes[this.settings.type];
    }
}

export default DragwybControlBase;