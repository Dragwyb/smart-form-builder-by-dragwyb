import api from '../utils/api';
import { generateId, validateField, debounce } from '../utils/helpers';

export const ADD_FIELD = 'ADD_FIELD';
export const UPDATE_FIELD = 'UPDATE_FIELD';
export const DELETE_FIELD = 'DELETE_FIELD';
export const UPDATE_FIELD_ORDER = 'UPDATE_FIELD_ORDER';
export const UPDATE_TOOLBAR_SETTINGS = 'UPDATE_TOOLBAR_SETTINGS';
export const UPDATE_FORM_TITLE = 'UPDATE_FORM_TITLE';
export const UPDATE_SECTION_SETTINGS = 'UPDATE_SECTION_SETTINGS';
export const RESET_SECTION_SETTINGS = 'RESET_SECTION_SETTINGS';
export const UPDATE_POPOVER_INITIALIZE = 'UPDATE_POPOVER_INITIALIZE';
export const UPDATE_POPOVER_CONTROLS = 'UPDATE_POPOVER_CONTROLS';
export const RESET_POPOVER_CONTROLS = 'RESET_POPOVER_CONTROLS';
export const SHOW_NOTICE = 'SHOW_NOTICE';
export const HIDE_NOTICE = 'HIDE_NOTICE';
export const UPDATE_FIELD_VALUES = 'UPDATE_FIELD_VALUES';
export const ERROR_NOTICE = 'ERROR_NOTICE';
export const DUPLICATE_FIELD = 'DUPLICATE_FIELD';
export const UPDATE_SELECTED_SETTING_ID = 'UPDATE_SELECTED_SETTING_ID';
export const UPDATE_ACTIVE_TOOLBAR = 'UPDATE_ACTIVE_TOOLBAR';
export const UPDATE_PREVIEW_MODE = 'UPDATE_PREVIEW_MODE';
export const UPDATE_FIELD_IDS = 'UPDATE_FIELD_IDS';
export const UPDATE_FIELD_ID = 'UPDATE_FIELD_ID';
export const DELETE_FIELD_ID = 'DELETE_FIELD_ID';
export const UPDATE_STYLE_SELECTORS = 'UPDATE_STYLE_SELECTORS';
export const DELETE_STYLE_SELECTORS = 'DELETE_STYLE_SELECTORS';

export const addField = ({ field, index = null }) => ({
    type: ADD_FIELD,
    payload: { field: field, fieldIndex: index }
});

export const duplicateField = (field, index, dispatch) => ({
    type: DUPLICATE_FIELD,
    payload: { field, index, dispatch }
})

export const updateField = (fieldId, field) => ({
    type: UPDATE_FIELD,
    payload: { fieldId, field }
});

export const updateSectionSettings = (Id, value) => ({
    type: UPDATE_SECTION_SETTINGS,
    payload: { Id, value }
})

export const resetSectionSettings = () => ({
    type: RESET_SECTION_SETTINGS
})

export const updatePopoverInitStatus = (status) => ({
    type: UPDATE_POPOVER_INITIALIZE,
    payload: { status }
})

export const updatePopoverControls = (id, control, status) => ({
    type: UPDATE_POPOVER_CONTROLS,
    payload: { id, control, status }
})


export const resetPopoverControls = () => ({
    type: RESET_POPOVER_CONTROLS
})

export const deleteField = (fieldId) => ({
    type: DELETE_FIELD,
    payload: fieldId
});

export const updateFieldOrder = (oldIndex, newIndex) => ({
    type: UPDATE_FIELD_ORDER,
    payload: { oldIndex, newIndex }
});

export const updateFieldValues = (fieldId, value) => ({
    type: UPDATE_FIELD_VALUES,
    payload: { fieldId, value }
});

export const updateToolbarSettings = (id, value) => ({
    type: UPDATE_TOOLBAR_SETTINGS,
    payload: { id, value }
});

export const updateSelectedSettingId = (value) => ({
    type: 'UPDATE_SELECTED_SETTING_ID',
    payload: value
})

export const updateActiveToolbar = (value) => ({
    type: 'UPDATE_ACTIVE_TOOLBAR',
    payload: value
})

export const updateFieldIds = (ids) => ({
    type: UPDATE_FIELD_IDS,
    payload: { ids }
})

export const updateFieldId = (id) => ({
    type: UPDATE_FIELD_ID,
    payload: { id }
})

export const deleteFieldIds = (id) => ({
    type: DELETE_FIELD_ID,
    payload: { id }
})

export const updateStyleSelectors = (key, value) => ({
    type: UPDATE_STYLE_SELECTORS,
    payload: { key, value }
})

export const deleteStyleSelectors = (key) => ({
    type: DELETE_STYLE_SELECTORS,
    payload: { key }
})

export const showNotice = (message, type = 'success') => ({
    type: SHOW_NOTICE,
    payload: { message, type }
});

export const hideNotice = (id) => ({
    type: HIDE_NOTICE,
    payload: id
});

export const saveForm = (formData) => async (dispatch) => {
    try {
        await api.saveForm(formData);
        dispatch(showNotice(DragwybBuilder.i18n.save));
    } catch (error) {
        dispatch(showNotice(error.message, 'error'));
        throw error;
    }
};

export const addError = (message) => ({
    type: ERROR_NOTICE,
    payload: { message, type: 'error' }
});

// Example usage in a component
const handleSave = async () => {
    try {
        await api.saveForm(formData);
        // Handle success
    } catch (error) {
        // Handle error
    }
};