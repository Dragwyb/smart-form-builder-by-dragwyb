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
        this.selectedSetting = props.selectedSetting;
        this.#updateValue = props.handleChange;
        this.Utils = props.Utils;
        this.selectorKey = props.toolbarId;

        if (this.settings.popover) {
            props.resetControlEventLifting(this.resetControl.bind(this));
            props.valueChangedCheckLifting(this.valueChanged.bind(this));
        }

        if (props.selectedSetting && '' !== props.selectedSetting) {
            this.selectorKey += '_' + props.selectedSetting;
        }
    }

    resetControl() {
        const value = this.settings && [undefined, null].includes(this.settings.default) ? '' : this.settings.default;
        this.setState({ value: value });
        this.updateControls(this.id, value);
    }

    valueChanged() {
        const { default: defaultValue } = this.settings;
        const currentValue = this.state.value || '';

        return currentValue !== defaultValue;
    }

    updateControlHandler(key, value) {
        this.setState({ value })
        this.updateControls(key, value);
    }

    updateControls(key, value) {
        this.#triggerOnChange(key, value)
    }

    getStyleSelectorPlaceholder(value, placeholder) {
        return placeholder;
    }

    #triggerOnChange(key, value) {
        this.#updateValue(key, value, this.settings.type, this);
        this.onValueUpdated(key, value);

        this.#updateStyleSelector(key, value);
    }

    #updateStyleSelector(key, value) {
        if (this.settings && this.settings.type && this.settings.selectors && this.settings.selectors_placeholders) {
            const uniqueSelector = this.selectorKey + '_' + key;
            this.Utils.updateStyleSelectors({ key: uniqueSelector, value: value, selectors: this.settings.selectors, placeholders: this.getStyleSelectorPlaceholder(value, this.settings.selectors_placeholders) });
        }
    }

    /**
     * ✅ Shared method: Check if this control should renfder based on settings.type
     */
    shouldRender() {
        return this.settings?.type === this.controlName && DragwybEditor.controlTypes[this.settings.type];
    }

    getValidValue(...args) {
        let validValue = args[args.length - 1];
        // Loop through all arguments passed to the function
        for (const value of args) {

            if (value || value === 0 || value === "" || value === false) {
                validValue = value;
                break;
            };
        }

        return validValue;
    }

    onInit() { }

    onRender() { }

    onDestroy() { }

    onUpdate() { }

    onValueUpdated() { }
}

export default DragwybControlBase;