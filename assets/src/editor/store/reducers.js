import {
    ADD_FIELD,
    DUPLICATE_FIELD,
    DELETE_FIELD,
    DELETE_FIELD_ID,
    DELETE_STYLE_SELECTORS,
    ERROR_NOTICE,
    RESET_SECTION_SETTINGS,
    UPDATE_SAVE_STATE,
    HIDE_NOTICE,
    RESET_POPOVER_CONTROLS,
    SHOW_NOTICE,
    UPDATE_ACTIVE_POPOVER,
    UPDATE_FIELD,
    UPDATE_FIELD_ORDER,
    UPDATE_FIELD_VALUES,
    UPDATE_TOOLBAR_SETTINGS,
    UPDATE_SECTION_SETTINGS,
    UPDATE_POPOVER_INITIALIZE,
    UPDATE_POPOVER_CONTROLS,
    UPDATE_SELECTED_SETTING_ID,
    UPDATE_ACTIVE_TOOLBAR,
    UPDATE_THEME_MODE,
    UPDATE_IFRAME_NODE,
    UPDATE_RESPONSIVE_TYPE,
    UPDATE_FIELD_IDS,
    UPDATE_FIELD_ID,
    UPDATE_STYLE_SELECTORS,
    ADD_ROOT_CONTAINERS,
    DELETE_ROOT_CONTAINER,
    UPDATE_ACTIVE_ROOT_CONTAINER,
    RESET_ACTIVE_ROOT_CONTAINER
} from './actions';

const initialState = {
    form: {
        fields: [],
        settings: {},
        styles: {},
        notifications: [],
        confirmations: []
    },
    values: {},
    activePopoverKey: false,
    sectionSettings: {},
    popoverInitialize: false,
    popoverControls: [],
    updateSaveState: false,
    notices: [],
    fieldIds: [],
    selectedSettingId: false,
    activeToolbar: DragwybEditor?.EditorToolbars?.Default ?? false,
    themeMode: localStorage.getItem("DragwybEditorTheme") || 'dark',
    responsiveType: 1024,
    rootContainers: DragwybEditor?.formData?.fields?.rootContainers || []
};

const deleteFieldRecursive = (fields, fieldId) => {
    const field = fields[fieldId];
    if (!field) return;

    if (field.children && field.children.length > 0) {
        field.children.forEach(childId => {
            deleteFieldRecursive(fields, childId);
        });
    }

    delete fields[fieldId];

    return fields;
};

export default function reducer(state = initialState, action) {
    switch (action.type) {

        case UPDATE_THEME_MODE:
            localStorage.setItem("DragwybEditorTheme", action.payload.themeMode);
            return {
                ...state,
                themeMode: action.payload.themeMode
            }

        case UPDATE_IFRAME_NODE:
            return {
                ...state,
                iframeEle: action.payload.node
            }

        case UPDATE_RESPONSIVE_TYPE:
            return {
                ...state,
                responsiveType: action.payload.responsiveType
            }

        case UPDATE_ACTIVE_POPOVER:
            return {
                ...state,
                activePopoverKey: action.payload.activePopoverKey
            }

        case ADD_ROOT_CONTAINERS: {
            const { rootContainerId } = action.payload;
            return {
                ...state,
                form: {
                    ...state.form,
                    rootContainers: [...state.form.rootContainers, rootContainerId]
                }
            };
        }

        case DELETE_ROOT_CONTAINER: {
            const { rootContainerId } = action.payload;

            if (!rootContainerId) return state;

            if (!state.form.rootContainers.includes(rootContainerId)) {
                return state;
            }

            const childrens = state.form.fields[rootContainerId].children;
            const fields = state.form.fields;

            const deleteChildrens = (childId) => {
                const childrens = fields[childId].children;
                if (childrens.length > 0) {
                    childrens.forEach(childId => {
                        deleteChildrens(childId)
                    })
                }

                delete fields[childId];
            };

            childrens.forEach(childId => deleteChildrens(childId));

            delete fields[rootContainerId];

            return {
                ...state,
                form: {
                    ...state.form,
                    rootContainers: state.form.rootContainers.filter(rootContainer => rootContainer.id !== rootContainerId),
                    fields
                }
            };
        }

        case UPDATE_ACTIVE_ROOT_CONTAINER: {
            const { rootContainerId, activeColumnIndex } = action.payload;
            return {
                ...state,
                activeRootContainer: {
                    rootContainerId,
                    activeColumnIndex
                }
            };
        }

        case RESET_ACTIVE_ROOT_CONTAINER: {
            return {
                ...state,
                activeRootContainer: null
            };
        }

        case ADD_FIELD: {
            const { field, fieldIndex = null } = action.payload;
            if (!state?.form?.fields) {
                state.form.fields = {};
            }

            const index = null === fieldIndex ? (field.is_root_container ? Object.keys(state.form.fields).length : (state.form.fields[field.parentId].children ? Object.keys(state.form.fields[field.parentId].children).length : 0)) : fieldIndex;
            const fieldId = field._id;

            let childrens = [];
            if (field.parentId) {
                childrens = [...(state.form.fields[field.parentId].children ?? [])];

                if (childrens.length < index) {
                    for (let i = childrens.length; i < index; i++) {
                        if (!childrens[i]) {
                            childrens.push(null);
                        }
                    }
                }

                childrens[index] = fieldId;
            }

            const newFields = Object.entries(state.form.fields);
            const lastField = state.form.fields[Object.keys(state.form.fields)[Object.keys(state.form.fields).length - 1]];

            const rootContainers = state.form.rootContainers;

            if (lastField && lastField.type === 'button') {
                const rootContainer = lastField.parentId;
                const newFieldIndex = newFields.findIndex(([key]) => key === rootContainer);

                if (field.is_root_container && !rootContainers.includes(fieldId)) {
                    let rootContainerIndex = rootContainers.findIndex((key) => key === rootContainer);
                    if (null !== fieldIndex && fieldIndex >= 0 && fieldIndex < rootContainerIndex) {
                        rootContainerIndex = fieldIndex;
                    }

                    rootContainers.splice(rootContainerIndex, 0, fieldId);
                }

                // Add before button root container
                newFields.splice(newFieldIndex - 1, 0, [fieldId, field]);

            } else {
                newFields.push([fieldId, field]);
            }

            if (field.is_root_container && !rootContainers.includes(fieldId)) {
                if (null !== fieldIndex && fieldIndex >= 0) {
                    rootContainers.splice(fieldIndex, 0, fieldId);
                } else {
                    rootContainers.push(fieldId);
                }
            }

            const fields = Object.fromEntries(newFields);

            if (field.parentId) {
                fields[field.parentId] = {
                    ...fields[field.parentId],
                    children: childrens
                };
            }

            return {
                ...state,
                form: {
                    ...state.form,
                    fields,
                    rootContainers
                }
            };
        }

        case DUPLICATE_FIELD: {
            const { field, fieldIndex = null } = action.payload;

            if (!field) {
                return state;
            }

            if (!field._id || !field.type) {
                return state;
            }

            const duplicateId = state.form.fields[field._id];

            if (duplicateId) {
                console.error('Duplicate field id are not allowed');
                return state;
            }

            if (field.is_root_container) {
                const index = null === fieldIndex ? state.form.fields.length : fieldIndex;

                const rootContainers = [...state.form.rootContainers];
                rootContainers.splice(index, 0, field._id);

                return {
                    ...state,
                    form: {
                        ...state.form,
                        fields: {
                            ...state.form.fields,
                            [field._id]: field
                        },
                        rootContainers
                    }
                };
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

        case DELETE_FIELD: {
            let fields = { ...state.form.fields };
            let rootContainers = [...state.form.rootContainers];
            let parentField = null;
            if (fields[action.payload].is_root_container) {
                rootContainers = rootContainers.filter(rootContainer => rootContainer !== action.payload);
            } else if (fields[action.payload].parentId) {
                parentField = fields[action.payload].parentId;
                fields[parentField].children = fields[parentField].children.filter(childId => childId !== action.payload);
            }

            if (fields[action.payload].children && fields[action.payload].children.length > 0) {
                fields = deleteFieldRecursive(fields, action.payload);
            }

            delete fields[action.payload];

            return {
                ...state,
                form: {
                    ...state.form,
                    fields,
                    rootContainers
                }
            };
        }

        // AD changes pending
        case UPDATE_FIELD_ORDER: {
            const { currentId, targetId, index } = action.payload;
            const fields = { ...state.form.fields };

            if (targetId === 'root') {
                const rootContainers = [...state.form.rootContainers];
                const currentIndex = rootContainers.indexOf(currentId);

                if (currentIndex !== -1) {
                    rootContainers.splice(currentIndex, 1);
                }

                rootContainers.splice(index, 0, currentId);

                return {
                    ...state,
                    form: {
                        ...state.form,
                        fields,
                        rootContainers
                    }
                };
            }

            if (fields[targetId]) {
                const children = [...(fields[targetId].children || [])];
                const currentIndex = children.indexOf(currentId);

                if (currentIndex !== -1) {
                    children.splice(currentIndex, 1);
                }

                children.splice(index, 0, currentId);

                return {
                    ...state,
                    form: {
                        ...state.form,
                        fields: {
                            ...fields,
                            [targetId]: {
                                ...fields[targetId],
                                children
                            }
                        }
                    }
                };
            }

            return state;
        }

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
                if (!action.payload.id) return state;

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
                    popoverControls: [...(state.popoverControls || []), action.payload.id]
                }
            }

        case RESET_POPOVER_CONTROLS:
            {
                if (Object.keys(state.popoverControls).length < 1) return state;

                return {
                    ...state,
                    popoverControls: []
                }
            }

        case UPDATE_SELECTED_SETTING_ID:

            if (state.selectedSettingId === action.payload) return state;

            if (state?.activeRootContainer && state?.activeRootContainer.rootContainerId && state.form.fields[action.payload] && state.form.fields[action.payload].parentId && state.form.fields[action.payload].parentId !== state.activeRootContainer.rootContainerId) {
                return {
                    ...state,
                    selectedSettingId: action.payload,
                    activeRootContainer: null
                }
            }

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

            if (action.payload.responsiveType && 'desktop' !== action.payload.responsiveType) {
                return {
                    ...state,
                    styleSelectors: { ...state.styleSelectors || {}, [action.payload.responsiveType]: { ...state.styleSelectors[action.payload.responsiveType] || {}, [action.payload.key]: action.payload.value } }
                }
            }

            return {
                ...state,
                styleSelectors: { ...state.styleSelectors || {}, [action.payload.key]: { ...state.styleSelectors[action.payload.key] || {}, ...action.payload.value } }
            }

        case DELETE_STYLE_SELECTORS:
            if (!action.payload.key) return state;

            if (state.styleSelectors[action.payload.key]) {
                delete state.styleSelectors[action.payload.key];

                return {
                    ...state,
                    styleSelectors: { ...state.styleSelectors }
                }
            }

            return state;

        case DELETE_FIELD_ID:
            return {
                ...state,
                fieldIds: state.fieldIds.filter(id => id !== action.payload.id)
            }

        case UPDATE_SAVE_STATE:
            return {
                ...state,
                updateSaveState: action.payload.status
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