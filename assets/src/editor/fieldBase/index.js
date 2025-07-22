class  DragwybFieldBase {
    #updateValue = () => { }

    constructor(args){
        this.fieldName=this.fieldName();
        return this.#renderContent(args);
    }

    #renderContent(args){

        if(!this.fieldName){
            return;
        }

        return this.renderComponent(args)
    }

    renderComponent(args){
        this.#setDisplaySetting(args);
        return this.bind();
    }

    #setDisplaySetting(args){
        this.html=args[0];
        this.type=args[1];
        this.id=args[2];
        this.value=args[3];
        this.field=args[4];
        this.#updateValue = args[5];
    }

    updateField(key, value) {
        this.#triggerOnChange(key, value)
    }

    #triggerOnChange(key, value) {
        this.#updateValue({fieldId: key, value});
    }

    /**
     * ✅ Shared method: Check if this control should render based on settings.type
     */
    shouldRender() {
        return this.field?.type === this.fieldName;
    }
}

export default DragwybFieldBase;