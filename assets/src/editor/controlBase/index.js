class DragwybControlBase {
    #updateValue = () => { }

    constructor(args) {
        this.controlName = this.controlName();
        return this.#renderContent(args);
    }

    #renderContent(args) {
        if(!this.controlName){
            return;
        }

        return this.renderComponent(args)
    }

    renderComponent(args) {
        this.#setDisplaySetting(args);
        return this.bind({ id: this.id, html: this.html, settings: this.settings, value: this.value });
    }

    #setDisplaySetting(args) {
        this.html = args[0];
        this.id = args[1];
        this.settings = args[2];
        this.value = args[3];
        this.#updateValue = args[4];
        this.utils=args[5];
    }

    updateControls(key, value) {
        this.#triggerOnChange(key, value)
    }

    #triggerOnChange(key, value) {
        this.#updateValue(key, value, this.settings.type);
    }

    /**
     * ✅ Shared method: Check if this control should renfder based on settings.type
     */
    shouldRender() {
        return this.settings?.type === this.controlName && DragwybEditor.controlTypes[this.settings.type];
    }
}

export default DragwybControlBase;