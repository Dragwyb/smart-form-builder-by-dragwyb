import {
    ADD_FIELD,
    DUPLICATE_FIELD,
    UPDATE_FIELD,
    DELETE_FIELD,
    UPDATE_FIELD_ORDER,
    UPDATE_FIELD_VALUES,
    UPDATE_FORM_SETTINGS,
    UPDATE_FORM_TITLE,
    UPDATE_SECTION_SETTINGS,
    RESET_SECTION_SETTINGS,
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
    sectionSettings:{},
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

        case DUPLICATE_FIELD:
            const referenceField = state.form.fields.find(
                field => field.id === action.payload.ReferenceField
            );

            if (!referenceField) return state;

            const { id, ...rest } = referenceField;

            const duplicatedField = {
                id: action.payload.activeField, // New unique ID
                ...rest
            };


            return {
                ...state,
                form: {
                    ...state.form,
                    fields: [...state.form.fields, duplicatedField]
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
                values: {
                    ...state.values || {},
                    [action.payload.fieldId]: action.payload.value
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

        case UPDATE_SECTION_SETTINGS:
            return {
                ...state,
                sectionSettings: {
                    ...state.sectionSettings,
                    [action.payload.Id]:action.payload.value 
                }
            };

        case UPDATE_FORM_TITLE:
            return {
                ...state,
                form:{
                    ...state.form,
                    title: action.payload
                }
            }

        case RESET_SECTION_SETTINGS:
            return {
                ...state,
                sectionSettings: {}
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