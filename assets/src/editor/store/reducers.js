import {
    ADD_FIELD,
    UPDATE_FIELD,
    DELETE_FIELD,
    UPDATE_FIELD_ORDER,
    UPDATE_FIELD_VALUES,
    UPDATE_FORM_SETTINGS,
    UPDATE_TEMP_SETTINGS,
    SHOW_NOTICE,
    HIDE_NOTICE,
    ERROR_NOTICE
} from './actions';

const initialState = {
    form: {
        title: '',
        fields: [],
        settings: {},
        styles: {},
        notifications: [],
        confirmations: []
    },
    tempSettings:{

    },
    notices: []
};

export default function reducer(state = initialState, action) {
    switch (action.type) {
        case ADD_FIELD:
            return {
                ...state,
                form: {
                    ...state.form,
                    fields: [...state.form.fields, action.payload]
                }
            };

        case UPDATE_FIELD:
            return {
                ...state,
                form: {
                    ...state.form,
                    fields: state.form.fields.map(field =>
                        field.id === action.payload.fieldId
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
                        field => field.id !== action.payload
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
                form: {
                    ...state.form,
                    fields: state.form.fields.map(field => field.id === action.payload.fieldId ? { ...field, values: action.payload.values } : field)
                }
            };

        case UPDATE_FORM_SETTINGS:
            return {
                ...state,
                form: {
                    ...state.form,
                    settings: action.payload
                }
            };

        case UPDATE_TEMP_SETTINGS:
            return {
                ...state,
                tempSettings: {
                    ...state.tempSettings,
                    [action.payload.fieldId]:action.payload.field 
                }
            };

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