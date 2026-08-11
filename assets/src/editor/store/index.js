import { legacy_createStore as createStore, applyMiddleware } from 'redux';
import { thunk } from 'redux-thunk';
import reducer from './reducers';

let fieldIds = [];

if (DragwybEditor?.formData?.fields) {
    const existingIds = JSON.stringify(DragwybEditor?.formData?.fields);
    fieldIds = [...existingIds.matchAll(/"_id"\s*:\s*"([^"]+)"/g)].map(match => match[1]);
}

const initialState = {
    form: DragwybEditor && DragwybEditor.formData ? JSON.parse(JSON.stringify(DragwybEditor.formData)) : {},
    notices: [],
    errors: [],
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
    activeRootContainer: null,
    history: {
        past: [],
        currentIndex: -1
    }
};

if (window?.DragwybEditor?.isInitialLoad) {
    if (!initialState.form) {
        initialState.form = {};
    }

    if (!initialState.form.advance) {
        initialState.form.advance = {};
    }

    if (!initialState.form.advance.form_status) {
        initialState.form.advance.form_status = "publish";
        DragwybEditor.formData.status = "publish";
    }
}

const store = createStore(reducer, initialState, applyMiddleware(thunk));
window.DragwybStore = store;

export default store; 