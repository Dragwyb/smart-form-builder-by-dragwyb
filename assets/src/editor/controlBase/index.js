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
        this.renderStyleSelector();
        this.onRender();
    }

    renderStyleSelector() {
        const controlType = this.controlName;
        const designControls = DragwybBuilder.Hooks.applyFilter('Dragwyb/Editor/DesignControls', ['section', 'tabs']);

        if (!designControls || !Array.isArray(designControls) || designControls.includes(controlType)) {
            return;
        }

        if (!this?.settings?.selectors || !this?.settings?.selectors_placeholders || Object.keys(this?.settings?.selectors).length === 0 || Object.keys(this?.settings?.selectors_placeholders).length === 0) {
            return;
        }

        if (!this.state.value && 0 !== this.state.value) {
            return;
        }

        const uniqueSelector = `${this.selectorKey}${this.selectedSetting && '' !== this.selectedSetting ? '_' + this.selectedSetting : ''}_${this.id}`;

        this.Utils.updateStyleSelectors({ key: uniqueSelector, value: this.state.value, selectors: this.settings.selectors, placeholders: this.getStyleSelectorPlaceholder(this.state.value, this.settings.selectors_placeholders), toolbarType: this.selectorKey, itemId: this.selectedSetting, initialRender: true });
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
            const uniqueSelector = `${this.selectorKey}${this.selectedSetting && '' !== this.selectedSetting ? '_' + this.selectedSetting : ''}_${key}`;

            this.Utils.updateStyleSelectors({ key: uniqueSelector, value: value, selectors: this.settings.selectors, placeholders: this.getStyleSelectorPlaceholder(value, this.settings.selectors_placeholders), toolbarType: this.selectorKey, itemId: this.selectedSetting });
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