import * as Helpers from '../utils/helpers';
import { addField } from '../store/actions';

export const Utils=(state, dispatch)=>{
    const HelperFunctions=Object.keys(Helpers);
    const Utils={};

    HelperFunctions.forEach(funName=>{
        Utils[funName]=()=>{return Helpers[funName](state, dispatch)};
    })

    return Utils;
}

export const AddField = ({type, dispatch, Utils, index=null} ) => {
    const field = {
        _id: Utils.generateId(),
        type,
    };

    if (DragwybEditor.fieldTypes[type] && DragwybEditor.fieldTypes[type].controls) {
        const fieldControls = DragwybEditor.fieldTypes[type].controls;
        field.attributes = {};
        Object.keys(fieldControls).forEach(id => {
            if (!['tabs', 'tab', 'section'].includes(fieldControls[id].type)) {

                let defaultValue = fieldControls[id].default ? fieldControls[id].default : '';

                defaultValue = DragwybBuilder.Hooks.applyFilter(`Dragwyb/Editor/AddControl/${fieldControls[id].type}.defaultValue`, defaultValue, Utils);
                
                field.attributes[id] = defaultValue;
            }
        })
    }

    dispatch(addField({field, index}));

    return field;
};