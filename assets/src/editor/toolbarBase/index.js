
import React, { Component } from "react";

class DragwybToolbarBase extends Component {
    #updateValue = () => { }

    bind() { }

    toolBarName = (name) => { return name };

    constructor(args) {
        super();

        this.bind();
        this.toolBarName = this.toolBarName(args?.[1]);
        this.#setDisplaySetting(args);
    }

    #renderToolbar(args) {
        this.setState({ 'test': 'world' })
        if (!this.toolBarName) {
            return;
        }

        return this.renderToolbar(args)
    }

    renderToolbar(args) {
        this.#setDisplaySetting(args);
        return this;
    }

    #setDisplaySetting(args) {
        this.html = args[0];
        this.id = args[1];
        this.settingId = args[2];
        this.toolbarData = args[3] || {};
        this.settings = args[4];
        this.#updateValue = args[5];
        this.Utils = args[6];
    }

    render() {
        return this.html;
    }

    getToolbarSettings() {
        this.settings.id = this.id;
        this.settings.panelHeading = this.settings.label ?? this.toolBarName;
        return this.settings;
    }

    getToolbarValue() {
        return this.toolbarData;
    }

    updateToolbarHandler = (key, value) => {
        this.toolbarData[key] = value;

        if (typeof this.toolbarData === 'object') {
            this.toolbarData = { ...this.toolbarData };
        }

        if (value === undefined) {
            delete this.toolbarData[key];
        } else if (typeof this?.settings?.controls?.[key]?.default === 'object' && this.Utils.compareTwoObjects({ obj1: value || {}, obj2: this?.settings?.controls?.[key]?.default || {} })) {
            delete this.toolbarData[key];
        } else if (this?.settings?.controls?.[key]?.default === value) {
            delete this.toolbarData[key];
        }

        this.updateToolbar();
    }

    updateToolbar = () => {
        this.#triggerOnChange();
    }

    #triggerOnChange() {
        this.#updateValue({ key: this.id, value: this.toolbarData, selectedToolBarId: this.settingId, toolbarObj: this });
    }

    /**
     * ✅ Shared method: Check if this control should renfder based on settings.type
     */
    shouldRender() {
        return this.id === this.toolBarName;
    }
}

export default DragwybToolbarBase;