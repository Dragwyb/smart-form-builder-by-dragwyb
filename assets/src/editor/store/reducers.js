import {
    ADD_FIELD,
    DUPLICATE_FIELD,
    UPDATE_FIELD,
    DELETE_FIELD,
    UPDATE_FIELD_ORDER,
    UPDATE_FIELD_VALUES,
    UPDATE_TOOLBAR_SETTINGS,
    UPDATE_SECTION_SETTINGS,
    RESET_SECTION_SETTINGS,
    UPDATE_POPOVER_INITIALIZE,
    UPDATE_POPOVER_CONTROLS,
    RESET_POPOVER_CONTROLS,
    UPDATE_SELECTED_SETTING_ID,
    UPDATE_ACTIVE_TOOLBAR,
    UPDATE_PREVIEW_MODE,
    UPDATE_FIELD_IDS,
    UPDATE_FIELD_ID,
    DELETE_FIELD_ID,
    UPDATE_STYLE_SELECTORS,
    DELETE_STYLE_SELECTORS,
    SHOW_NOTICE,
    HIDE_NOTICE,
    ERROR_NOTICE
} from './actions';

const initialState = {
    form: {
        fields: [],
        settings: {},
        styles: {},
        notifications: [],
        confirmations: []
    },
    sectionSettings: {},
    popoverInitialize: false,
    popoverControls: {},
    notices: [],
    fieldIds: [],
    selectedSettingId: false,
    activeToolbar: DragwybEditor?.EditorToolbars?.Default ?? false,
};

export default function reducer(state = initialState, action) {
    switch (action.type) {
        case ADD_FIELD:
            const { field, fieldIndex = null } = action.payload;
            if (!state?.form?.fields) {
                state.form.fields = [];
            }

            const index = null === fieldIndex ? state.form.fields.length : fieldIndex;

            return {
                ...state,
                form: {
                    ...state.form,
                    fields: [
                        ...state.form.fields.slice(0, index),
                        field,
                        ...state.form.fields.slice(index)
                    ]
                }
            };

        case DUPLICATE_FIELD:
            if (!action.payload.field) {
                return state;
            }

            if (!action.payload.field._id || !action.payload.field.type) {
                return state;
            }

            const duplicateId = state.form.fields.filter(field => field._id === action.payload.field._id);

            if (duplicateId.length > 0) {
                return state;
            }

            return {
                ...state,
                form: {
                    ...state.form,
                    fields: [...state.form.fields, action.payload.field],
                }
            }

        case UPDATE_FIELD:
            return {
                ...state,
                form: {
                    ...state.form,
                    fields: state.form.fields.map(field =>
                        field._id === action.payload.fieldId
                            ? action.payload.field
                            : field
                    )
                }
            };

        case DELETE_FIELD:
            return {
                ...state,
                form: {
                    ...state.form,
                    fields: state.form.fields.filter(
                        field => field._id !== action.payload
                    )
                }
            };

        case UPDATE_FIELD_ORDER:
            const fields = [...state.form.fields];
            const [removed] = fields.splice(action.payload.oldIndex, 1);
            fields.splice(action.payload.newIndex, 0, removed);

            return {
                ...state,
                form: {
                    ...state.form,
                    fields
                }
            };

        case UPDATE_FIELD_VALUES:
            return {
                ...state,
                values: {
                    ...state.values || {},
                    [action.payload.fieldId]: action.payload.value
                }
            };

        case UPDATE_TOOLBAR_SETTINGS:
            const toolbarId = action.payload.id;

            if (!DragwybEditor.EditorToolbars || !DragwybEditor.EditorToolbars.toolbars || !DragwybEditor.EditorToolbars.toolbars[toolbarId]) {
                return state;
            }

            return {
                ...state,
                form: {
                    ...state.form,
                    [toolbarId]: action.payload.value
                }
            };

        case UPDATE_SECTION_SETTINGS:
            if (state.sectionSettings && state.sectionSettings[action.payload.Id] && state.sectionSettings[action.payload.Id] === action.payload.value) {
                return state;
            }

            return {
                ...state,
                sectionSettings: {
                    ...state.sectionSettings,
                    [action.payload.Id]: action.payload.value
                }
            };

        case RESET_SECTION_SETTINGS:

            if (Object.keys(state.sectionSettings || {}).length < 1) {
                return state;
            }

            return {
                ...state,
                sectionSettings: {}
            };

        case UPDATE_POPOVER_INITIALIZE:
            {
                return {
                    ...state,
                    popoverInitialize: action.payload.status
                }
            }

        case UPDATE_POPOVER_CONTROLS:
            {
                if (!action.payload.id || !action.payload.control) return state;

                const status = action.payload.status;
                const popoverInit = state.popoverInitialize;
                let popoverInitialize = true;

                if (status && true === status.start) {
                    if (true === popoverInit) {
                        console.error(
                            `[Popover] Attempt to start a new popover before closing the previous one. Key: ${action.payload.id}`
                        );

                        return state;
                    }
                }

                if (status && true === status.end) {
                    if (!popoverInit) {
                        console.error(
                            `[Popover] Attempt to close a popover that was never opened. Key: ${action.payload.id}`
                        );

                        return state;
                    }
                }

                return {
                    ...state,
                    popoverInitialize,
                    popoverControls: { ...state.popoverControls || {}, [action.payload.id]: { ...state.popoverControls[action.payload.id] || {}, control: action.payload.control, resetControlEvent: action.payload.resetControlEvent, valueChangedCheck: action.payload.valueChangedCheck } }
                }
            }

        case RESET_POPOVER_CONTROLS:
            {
                if (Object.keys(state.popoverControls).length < 1) return state;

                return {
                    ...state,
                    popoverControls: {}
                }
            }

        case UPDATE_SELECTED_SETTING_ID:

            if (state.selectedSettingId === action.payload) return state;

            return {
                ...state,
                selectedSettingId: action.payload
            }

        case UPDATE_ACTIVE_TOOLBAR:

            if (state.activeToolbar === action.payload) return state;

            return {
                ...state,
                activeToolbar: action.payload
            }

        case UPDATE_FIELD_IDS:
            return {
                ...state,
                fieldIds: [...state.fieldIds, ...action.payload.ids]
            }

        case UPDATE_FIELD_ID:
            return {
                ...state,
                fieldIds: [...state.fieldIds, action.payload.id]
            }

        case UPDATE_STYLE_SELECTORS:
            if (!action.payload.key || !action.payload.value) return state;
            return {
                ...state,
                styleSelectors: { ...state.styleSelectors || {}, [action.payload.key]: { ...state.styleSelectors[action.payload.key] || {}, ...action.payload.value } }
            }

        case DELETE_STYLE_SELECTORS:
            if (!action.payload.key) return state;

            if (state.styleSelectors[action.payload.key]) {
                delete state.styleSelectors[action.payload.key];
            }

            return state;

        case DELETE_FIELD_ID:
            return {
                ...state,
                fieldIds: state.fieldIds.filter(id => id !== action.payload.id)
            }

        case SHOW_NOTICE:
            return {
                ...state,
                notices: [...state.notices, {
                    id: Date.now(),
                    ...action.payload
                }]
            };

        case HIDE_NOTICE:
            return {
                ...state,
                notices: state.notices.filter(notice => notice.id !== action.payload)
            };

        case ERROR_NOTICE:
            return {
                ...state,
                errors: [...state.errors, action.payload]
            };

        default:
            return state;
    }
} 