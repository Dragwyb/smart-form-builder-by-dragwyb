import {
    ADD_FIELD,
    DUPLICATE_FIELD,
    UPDATE_FIELD,
    DELETE_FIELD,
    UPDATE_FIELD_ORDER,
    UPDATE_FIELD_VALUES,
    UPDATE_TOOLBAR_SETTINGS,
    UPDATE_FORM_TITLE,
    UPDATE_SECTION_SETTINGS,
    RESET_SECTION_SETTINGS,
    UPDATE_SELECTED_SETTING_ID,
    UPDATE_ACTIVE_TOOLBAR,
    UPDATE_PREVIEW_MODE,
    UPDATE_FIELD_IDS,
    UPDATE_FIELD_ID,
    DELETE_FIELD_ID,
    SHOW_NOTICE,
    HIDE_NOTICE,
    ERROR_NOTICE
} from './actions';

import Helper from '../components/Utils'

import { useDispatch } from 'react-redux';
import { act } from 'react';

const initialState = {
    form: {
        title: '',
        fields: [],
        settings: {},
        styles: {},
        notifications: [],
        confirmations: []
    },
    sectionSettings: {},
    notices: [],
    fieldIds:[],
    formStatus:DragwybEditor?.formData?.status ?? 'draft',
    selectedSettingId: false,
    activeToolbar: DragwybEditor?.EditorToolbars?.Default ?? false,
    previewMode: false,
};

export default function reducer(state = initialState, action) {
    switch (action.type) {
        case ADD_FIELD:
            const { field, fieldIndex = null } = action.payload;
            if(!state?.form?.fields){
                state.form.fields=[];
            }

            const index= null === fieldIndex ? state.form.fields.length : fieldIndex;

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
            if(!action.payload.field){
                return state;
            }

            if(!action.payload.field._id || !action.payload.field.type){
                return state;
            }

            const duplicateId=state.form.fields.filter(field => field._id === action.payload.field._id);

            if(duplicateId.length > 0){
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
            const toolbarId=action.payload.id;

            if(!DragwybEditor.EditorToolbars || !DragwybEditor.EditorToolbars.toolbars || !DragwybEditor.EditorToolbars.toolbars[toolbarId]){
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
            return {
                ...state,
                sectionSettings: {
                    ...state.sectionSettings,
                    [action.payload.Id]: action.payload.value
                }
            };

        case UPDATE_FORM_TITLE:
            return {
                ...state,
                form: {
                    ...state.form,
                    title: action.payload
                }
            }

        case RESET_SECTION_SETTINGS:
            return {
                ...state,
                sectionSettings: {}
            };

        case UPDATE_SELECTED_SETTING_ID:
            return {
                ...state,
                selectedSettingId: action.payload
            }

        case UPDATE_ACTIVE_TOOLBAR:
            return {
                ...state,
                activeToolbar: action.payload
            }

        case UPDATE_PREVIEW_MODE:
            return {
                ...state,
                previewMode: action.payload
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