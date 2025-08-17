import { createStore, applyMiddleware } from 'redux';
import thunk from 'redux-thunk';
import reducer from './reducers';

let fieldIds=[];

if(DragwybEditor?.formData?.fields){
    const existingIds=JSON.stringify(DragwybEditor?.formData?.fields);
    fieldIds = [...existingIds.matchAll(/"_id"\s*:\s*"([^"]+)"/g)].map(match => match[1]);
}

const initialState = {
    form: DragwybEditor.formData || [],
    notices: [],
    errors: [],
    values: {},
    fieldIds,
    formStatus:DragwybEditor?.formData?.status ?? 'draft',
    selectedField: null,
    activeToolbar: DragwybEditor?.EditorToolbars?.Default ?? false,
    previewMode: false,
};

const store = createStore(reducer, initialState, applyMiddleware(thunk));

export default store; 