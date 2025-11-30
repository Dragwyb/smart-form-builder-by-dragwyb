import React, { Component } from "react";

class DragwybControlBase extends Component {
    #updateValue = () => { }

    constructor(props) {
        super();
        this.state = {
            value: props.value
        }
        this.controlName = this.controlName() || props.settings.type;
        this.onInit();
        this.#renderContent(props);
    }

    controlName = () => { return null };

    bind() {
        return <div>Unsupported control type: {this.props.settings?.type || 'unknown'}</div>;
    }

    componentDidMount = () => {
        this.onRender();
    }

    componentDidUpdate = (prevProps, prevState) => {
        this.onUpdate(prevProps, prevState);
        if (prevProps.settings !== this.props.settings) {
            this.setState({ settings: this.props.settings });
        }

        if (prevProps.value !== this.props.value) {
            this.setState({ value: this.props.value });
        }
    }

    componentWillUnmount = () => {
        this.onDestroy();
    }

    #renderContent(props) {
        if (!this.controlName) {
            return;
        }

        return this.renderComponent(props)
    }

    renderComponent(props) {
        this.#setDisplaySetting(props);
    }

    render() {
        return this.bind();
    }

    #setDisplaySetting(props) {
        this.id = props.id;
        this.settings = props.settings;
        this.#updateValue = props.handleChange;
        this.Utils = props.Utils;
    }

    updateControlHandler(key, value) {
        this.setState({ value })
        this.updateControls(key, value);
    }

    updateControls(key, value) {
        this.#triggerOnChange(key, value)
    }

    #triggerOnChange(key, value) {
        this.#updateValue(key, value, this.settings.type, this);
        this.onValueUpdated(key, value);
    }

    /**
     * ✅ Shared method: Check if this control should renfder based on settings.type
     */
    shouldRender() {
        return this.settings?.type === this.controlName && DragwybEditor.controlTypes[this.settings.type];
    }

    onInit() { }

    onRender() { }

    onDestroy() { }

    onUpdate() { }

    onValueUpdated() { }
}

export default DragwybControlBase;