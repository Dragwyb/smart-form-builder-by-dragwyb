import React, { Component } from "react";
import ResponsiveDevices from "../../editor/components/Common/ResponsiveDevices";
import { FaUndo, FaDatabase } from "react-icons/fa";
import { __ } from '@wordpress/i18n';

class DragwybControlBase extends Component {
    #updateValue = () => { }

    constructor(props) {
        super();
        this.state = {
            value: props.value,
            showDynamicMenu: false
        }
        this.styleRender = props.styleRender || false;

        this.controlName = this.controlName() || props.settings.type;
        this.onInit();
        this.RenderLabel = this.RenderLabel.bind(this);
        this.RenderDescription = this.RenderDescription.bind(this);
        this.#renderContent(props);
    }

    controlName = () => { return null };

    bind() {
        return <div>Unsupported control type: {this.props.settings?.type || 'unknown'}</div>;
    }

    componentDidMount = () => {
        this.renderStyleSelector();
        this.onRender();
        document.addEventListener('click', this.handleOutsideClick);
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

        if (this.currentItemId) {
            styleSelectorsData.currentItemId = this.currentItemId;
        }

        if (this?.settings?.responsive_control && this?.settings?.responsive_type) {
            styleSelectorsData.responsiveType = this.settings.responsive_type;
        }

        return this.Utils.updateStyleSelectors(styleSelectorsData);
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
        document.removeEventListener('click', this.handleOutsideClick);
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

    handleOutsideClick = (e) => {
        if (this.state.showDynamicMenu) {
            const button = document.getElementById(`dynamic-btn-${this.id}`);
            const dropdown = document.getElementById(`dynamic-dropdown-${this.id}`);

            if (button && !button.contains(e.target) && dropdown && !dropdown.contains(e.target)) {
                this.setState({ showDynamicMenu: false });
            }
        }
    }

    handleInsertDynamicTag = (tag) => {
        const currentValue = this.state.value || '';
        let newValue;
        if (typeof currentValue === 'object' && currentValue !== null) {
            if (this.controlName === 'url') {
                newValue = {
                    ...currentValue,
                    url: tag
                };
            } else {
                newValue = currentValue;
            }
        } else {
            newValue = tag;
        }

        this.setState({ showDynamicMenu: false });
        this.updateControlHandler(this.id, newValue);
    }

    getDynamicTagsOptions() {
        const options = [];

        const isFieldIdEnabled = this.settings.field_id_tags === true;

        if (isFieldIdEnabled) {
            // 2. Fetch all field IDs currently in the form Redux store
            const storeState = window.DragwybStore?.getState();
            const fields = storeState?.form?.fields || {};

            Object.keys(fields).forEach(key => {
                const field = fields[key];
                if (field && field.type !== 'row' && field.type !== 'button') {
                    const idVal = field.attributes?.field_id || field._id;
                    const labelVal = field.attributes?.label || '';
                    if (idVal) {
                        options.push({
                            tag: idVal,
                            label: labelVal
                        });
                    }
                }
            });

            return options;
        }

        // 1. Fetch registered PHP dynamic tags
        const phpTags = DragwybEditor?.dynamicTags || [];
        const isFieldIdTagsEnabled = this.settings.dynamic_tag?.field_ids === true;
        phpTags.forEach(item => {
            options.push({
                tag: item.tag,
                label: item.label
            });
        });


        if (isFieldIdTagsEnabled) {
            // 2. Fetch all field IDs currently in the form Redux store
            const storeState = window.DragwybStore?.getState();
            const fields = storeState?.form?.fields || {};

            Object.keys(fields).forEach(key => {
                const field = fields[key];
                if (field && field.type !== 'row' && field.type !== 'button') {
                    const idVal = field.attributes?.field_id || field._id;
                    const labelVal = field.attributes?.label || '';
                    if (idVal) {
                        options.push({
                            tag: `field:${idVal}`,
                            label: `${__('Field:', 'smart-form-builder-by-dragwyb')} ${labelVal}`
                        });
                    }
                }
            });
        }

        return options;
    }

    RenderLabel({ label = null, className = '', attr = {}, children = null }) {
        label = label || this.settings.label;
        const defaultValue = this.settings.default || '';

        if (!label) {
            return null;
        }

        const isFieldTagEnable = this.settings.field_id_tags === true;
        const isDynamicSupported = this.settings.dynamic_tag?.active === true || isFieldTagEnable;

        return (
            <>
                <label className={`dragwyb-control__label${className !== '' ? ' ' + className : ''}`} {...attr}>
                    {label}
                    {this.settings.responsive_control && this.settings.responsive_type && <ResponsiveDevices Utils={this.Utils} style='dropdown' />}
                    {children}
                    {isDynamicSupported && (
                        <span
                            id={`dynamic-btn-${this.id}`}
                            className={`dragwyb-control__dynamic${this.state.value ? ' has-reset' : ''}`}
                            onClick={(e) => {
                                e.preventDefault();
                                this.setState(prev => ({ showDynamicMenu: !prev.showDynamicMenu }));
                            }}
                            title={__('Dynamic Tags', 'smart-form-builder-by-dragwyb')}
                        >
                            <FaDatabase size={10} />
                        </span>
                    )}
                    {this.state.value && this.state.value !== defaultValue && <span className="dragwyb-control__reset" onClick={(e) => { e.preventDefault(); e.stopPropagation(); this.resetControl(); }}>
                        <FaUndo size={12} title={__('Reset to Default', 'smart-form-builder-by-dragwyb')} />
                    </span>}
                    {isDynamicSupported && this.state.showDynamicMenu && (
                        <div id={`dynamic-dropdown-${this.id}`} className="dragwyb-dynamic-dropdown">
                            <div className="dragwyb-dynamic-dropdown__header">
                                {__('Dynamic Tags', 'smart-form-builder-by-dragwyb')}
                            </div>
                            <ul className="dragwyb-dynamic-dropdown__list">
                                {this.getDynamicTagsOptions().map((opt, i) => (
                                    <li key={i} className="dragwyb-dynamic-dropdown__item" onClick={(e) => {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        this.handleInsertDynamicTag(`${isFieldTagEnable ? opt.tag : `{${opt.tag}}`}`);
                                    }}>
                                        <FaDatabase size={10} style={{ marginRight: '6px' }} />
                                        <span>{opt.label}({opt.tag})</span>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </label>
            </>
        );
    }

    RenderDescription() {
        if (!this.settings.description) return null;
        return <div className="dragwyb-control__description">{this.settings.description}</div>;
    }

    #setDisplaySetting(props) {
        this.id = props.id;
        this.settings = props.settings;
        this.selectedSetting = props.selectedSetting;
        this.#updateValue = props.handleChange;
        this.Utils = props.Utils;
        this.selectorKey = props.toolbarId;
        this.fieldValue = props.fieldValue || {};
        this.currentItemId = props.currentItemId || false;

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
                this.Utils.deleteStyleSelectors({ key: uniqueSelector });
            } else {

                const styleSelectorsData = { key: uniqueSelector, value: value, selectors: this.settings.selectors, placeholders: this.getStyleSelectorPlaceholder(value, this.settings.selectors_placeholders), toolbarType: this.selectorKey, itemId: this.selectedSetting };

                if (this.currentItemId) {
                    styleSelectorsData.currentItemId = this.currentItemId;
                }

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