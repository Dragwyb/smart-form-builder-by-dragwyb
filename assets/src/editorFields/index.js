
class textField extends DragwybEditor.FieldBase {
    fieldName (){
        return 'text';
    }

    bind(){
        if (!this.shouldRender()) return <></>;

        return <input type={this.fieldName} onChange={(e)=>this.updateField(this.id, e.target.value)}/>;
    }
}

const initializeFields=()=>{
    const defaultFields={
        'text': (args)=>new textField(args),
    }

    Object.keys(defaultFields).map(key => DragwybBuilder.Hooks.addFilter('Dragwyb/Editor/FieldRender/'+key,(args)=>{return defaultFields[key](args)}))
}


jQuery(document).on('Dragwyb:editorInit', () => {   

    DragwybBuilder.Hooks.addAction('Dragwyb/Editor/FieldBase',initializeFields);
});