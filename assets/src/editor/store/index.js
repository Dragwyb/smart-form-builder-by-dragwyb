import { createStore, applyMiddleware } from 'redux';
import thunk from 'redux-thunk';
import reducer from './reducers';

let fieldIds = [];

if (DragwybEditor?.formData?.fields) {
    const existingIds = JSON.stringify(DragwybEditor?.formData?.fields);
    fieldIds = [...existingIds.matchAll(/"_id"\s*:\s*"([^"]+)"/g)].map(match => match[1]);
}

const initialState = {
    form: DragwybEditor.formData || [],
    notices: [],
    errors: [],
    values: {},
    sectionSettings: {},
    activePopoverKey: false,
    fieldIds,
    selectedSettingId: null,
    activeToolbar: DragwybEditor?.EditorToolbars?.Default ?? false,
    popoverInitialize: false,
    popoverControls: [],
    styleSelectors: DragwybEditor?.frontendInitialData?.css && typeof DragwybEditor?.frontendInitialData?.css === 'object' ? DragwybEditor?.frontendInitialData?.css : {},
    iframeEle: null,
    themeMode: localStorage.getItem("DragwybEditorTheme") || 'dark',
    responsiveType: 1024,
    rootContainers: DragwybEditor?.formData?.fields?.rootContainers || [],
    activeRootContainer: null
};

const store = createStore(reducer, initialState, applyMiddleware(thunk));

export default store; 