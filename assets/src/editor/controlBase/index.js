import React, { Component } from "react";
import { Field } from "../Editor/Fields";
import ResponsiveDevices from "../../editor/components/Common/ResponsiveDevices";
import { Value } from "sass";

class DragwybControlBase extends Component {
    #updateValue = () => { }

    constructor(props) {
        super();
        this.state = {
            value: props.value
        }
        this.styleRender = props.styleRender || false;

        this.controlName = this.controlName() || props.settings.type;
        this.onInit();
        this.RenderLabel = this.RenderLabel.bind(this);
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

        if (!this.state.value && 0 !== this.state.value && !this.settings.default && this.settings.default !== 0) {
            return;
        }

        const selectedSetting = this.selectedSetting && '' !== this.selectedSetting && this.selectedSetting !== this.selectorKey ? this.selectedSetting : false;
        const uniqueSelector = `${this.selectorKey}${selectedSetting ? '_' + selectedSetting : ''}_${this.id}`;

        const styleSelectorsData = { key: uniqueSelector, value: this.state.value || this.settings.default, selectors: this.settings.selectors, placeholders: this.getStyleSelectorPlaceholder(this.state.value || this.settings.default, this.settings.selectors_placeholders), toolbarType: this.selectorKey, itemId: this.selectedSetting, initialRender: true };

        if (this?.settings?.responsive_control && this?.settings?.responsive_type) {
            styleSelectorsData.responsiveType = this.settings.responsive_type;
        }

        this.Utils.updateStyleSelectors(styleSelectorsData);
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

    RenderLabel({ label = null, className = '', attr = {}, children = null }) {
        label = label || this.settings.label;

        if (!label) {
            return null;
        }

        return (
            <label className={`dragwyb-control__label${className !== '' ? ' ' + className : ''}`} {...attr}>
                {label}
                {this.settings.responsive_control && this.settings.responsive_type && <ResponsiveDevices Utils={this.Utils} style='dropdown' />}
                {children}
            </label>
        );
    }

    #setDisplaySetting(props) {
        this.id = props.id;
        this.settings = props.settings;
        this.selectedSetting = props.selectedSetting;
        this.#updateValue = props.handleChange;
        this.Utils = props.Utils;
        this.selectorKey = props.toolbarId;

        if (this.settings.popover) {
            props?.resetControlEventLifting?.(this.resetControl.bind(this));
            props?.valueChangedCheckLifting?.(this.valueChanged.bind(this));
        }
    }

    resetControl() {
        this.setState({ value: undefined });
        this.updateControls(this.id, undefined);
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
            const selectedSetting = this.selectedSetting && '' !== this.selectedSetting && this.selectedSetting !== this.selectorKey ? this.selectedSetting : false;
            const uniqueSelector = `${this.selectorKey}${selectedSetting ? '_' + selectedSetting : ''}_${key}`;

            if (value === undefined || value === null || value === '') {
                const defaultValue = this?.settings?.default;

                this.Utils.deleteStyleSelectors({ key: uniqueSelector });
            } else {

                const styleSelectorsData = { key: uniqueSelector, value: value, selectors: this.settings.selectors, placeholders: this.getStyleSelectorPlaceholder(value, this.settings.selectors_placeholders), toolbarType: this.selectorKey, itemId: this.selectedSetting };

                if (this?.settings?.responsive_control && this?.settings?.responsive_type) {
                    styleSelectorsData.responsiveType = this.settings.responsive_type;
                }

                this.Utils.updateStyleSelectors(styleSelectorsData);
            }
        }
    }

    /**
     * ✅ Shared method: Check if this control should renfder based on settings.type
     */
    shouldRender() {
        return (this.settings?.type === this.controlName && DragwybEditor.controlTypes[this.settings.type]) || this.styleRender;
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