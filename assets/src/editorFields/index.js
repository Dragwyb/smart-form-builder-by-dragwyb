
class textField extends DragwybEditor.FieldBase {
    fieldName (){
        return 'text';
    }

    bind(){
        const settings=this.settings;

        return <div>Testing Completed</div>;
    }
}

jQuery(document).on('Dragwyb:editorInit', () => {   
    const text=()=>{
        return new textField();
    }

    DragwybBuilder.Hooks.addAction('Dragwyb/Editor/fieldBase',text);
});