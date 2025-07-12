class  DragwybFieldBase {
    constructor(){
        this.fieldName=this.fieldName();
        this.#addFilter();
    }

    #addFilter(){

        if(!this.fieldName){
            return;
        }
        DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/fieldRender/'+this.fieldName,(args)=>{return this.renderComponent(args)});
    }

    renderComponent(args){
        this.#setDisplaySetting(args);
        return this.bind();
    }

    #setDisplaySetting(args){
        this.html=args[0];
        this.type=args[1];
        this.id=args[2];
        this.settings=args[3];
    }
}

export default DragwybFieldBase;