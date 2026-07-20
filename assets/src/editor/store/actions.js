import api from '../utils/api';
import { generateId, validateField, debounce } from '../utils/helpers';

export const ADD_FIELD = 'ADD_FIELD';
export const UPDATE_FIELD = 'UPDATE_FIELD';
export const DELETE_FIELD = 'DELETE_FIELD';
export const UPDATE_ACTIVE_POPOVER = 'UPDATE_ACTIVE_POPOVER';
export const UPDATE_FIELD_ORDER = 'UPDATE_FIELD_ORDER';
export const UPDATE_TOOLBAR_SETTINGS = 'UPDATE_TOOLBAR_SETTINGS';
export const UPDATE_FORM_TITLE = 'UPDATE_FORM_TITLE';
export const UPDATE_SECTION_SETTINGS = 'UPDATE_SECTION_SETTINGS';
export const RESET_SECTION_SETTINGS = 'RESET_SECTION_SETTINGS';
export const UPDATE_POPOVER_INITIALIZE = 'UPDATE_POPOVER_INITIALIZE';
export const UPDATE_POPOVER_CONTROLS = 'UPDATE_POPOVER_CONTROLS';
export const RESET_POPOVER_CONTROLS = 'RESET_POPOVER_CONTROLS';
export const UPDATE_SAVE_STATE = 'UPDATE_SAVE_STATE';
export const SHOW_NOTICE = 'SHOW_NOTICE';
export const HIDE_NOTICE = 'HIDE_NOTICE';
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
export const UPDATE_THEME_MODE = 'UPDATE_THEME_MODE';
export const UPDATE_IFRAME_NODE = 'UPDATE_IFRAME_NODE';
export const UPDATE_RESPONSIVE_TYPE = 'UPDATE_RESPONSIVE_TYPE';
export const ADD_ROOT_CONTAINERS = 'ADD_ROOT_CONTAINERS';
export const DELETE_ROOT_CONTAINER = 'DELETE_ROOT_CONTAINER';
export const UPDATE_ACTIVE_ROOT_CONTAINER = 'UPDATE_ACTIVE_ROOT_CONTAINER';
export const RESET_ACTIVE_ROOT_CONTAINER = 'RESET_ACTIVE_ROOT_CONTAINER';
export const HISTORY_REVERT = 'HISTORY_REVERT';
export const ADD_HISTORY_SNAPSHOT = 'ADD_HISTORY_SNAPSHOT';

export const revertToHistory = (index) => ({
    type: HISTORY_REVERT,
    payload: { index }
});

export const addHistorySnapshot = (label) => ({
    type: ADD_HISTORY_SNAPSHOT,
    payload: { label }
});

export const updateThemeMode = (themeMode) => ({
    type: UPDATE_THEME_MODE,
    payload: { themeMode }
})

export const updateIframeNode = (node) => ({
    type: UPDATE_IFRAME_NODE,
    payload: { node }
})

export const updateResponsiveType = (responsiveType) => ({
    type: UPDATE_RESPONSIVE_TYPE,
    payload: { responsiveType }
})

export const addRootContainers = (rootContainerId) => ({
    type: ADD_ROOT_CONTAINERS,
    payload: { rootContainerId }
})

export const deleteRootContainer = (rootContainerId) => ({
    type: DELETE_ROOT_CONTAINER,
    payload: { rootContainerId }
})

export const updateActiveRootContainer = (rootContainerId, activeColumnIndex = null) => ({
    type: UPDATE_ACTIVE_ROOT_CONTAINER,
    payload: { rootContainerId, activeColumnIndex }
})

export const resetActiveRootContainer = () => ({
    type: RESET_ACTIVE_ROOT_CONTAINER
})

export const addField = ({ field, index = null }) => ({
    type: ADD_FIELD,
    payload: { field: field, fieldIndex: index }
});

export const duplicateField = (field, index) => ({
    type: DUPLICATE_FIELD,
    payload: { field, fieldIndex: index }
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

export const updateactivePopoverKey = (value) => ({
    type: UPDATE_ACTIVE_POPOVER,
    payload: { activePopoverKey: value }
})

export const updatePopoverInitStatus = (status) => ({
    type: UPDATE_POPOVER_INITIALIZE,
    payload: { status }
})

export const updatePopoverControls = (id, status) => ({
    type: UPDATE_POPOVER_CONTROLS,
    payload: { id, status }
})

export const resetPopoverControls = () => ({
    type: RESET_POPOVER_CONTROLS
})

export const deleteField = (fieldId) => ({
    type: DELETE_FIELD,
    payload: fieldId
});

export const updateFieldOrder = (currentId, targetId, index) => ({
    type: UPDATE_FIELD_ORDER,
    payload: { currentId, targetId, index }
});

export const updateToolbarSettings = (id, value, selectedToolBarId) => {
    return {
        type: UPDATE_TOOLBAR_SETTINGS,
        payload: { id, value, selectedToolBarId }
    }
};

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

export const updateStyleSelectors = (key, value, responsiveType = 'desktop') => ({
    type: UPDATE_STYLE_SELECTORS,
    payload: { key, value, responsiveType }
})

export const deleteStyleSelectors = (key, responsiveType = 'desktop') => ({
    type: DELETE_STYLE_SELECTORS,
    payload: { key, responsiveType }
})

export const updateSaveState = (status) => ({
    type: UPDATE_SAVE_STATE,
    payload: { status }
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
        dispatch(updateSaveState(true));
        await api.saveForm(formData);
        dispatch(showNotice(DragwybBuilder.i18n.save));
    } catch (error) {
        dispatch(showNotice(error.message, 'error'));
        console.error(error);
    }
    dispatch(updateSaveState(false));
};

export const addError = (message) => ({
    type: ERROR_NOTICE,
    payload: { message, type: 'error' }
});
