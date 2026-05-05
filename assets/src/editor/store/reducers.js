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

/**
 * Collects all IDs that need to be deleted (field + all nested children).
 * Returns a Set of field IDs — pure function, no mutation.
 */
const collectFieldIdsToDelete = (fields, fieldId) => {
    const idsToDelete = new Set();

    const collect = (id) => {
        const field = fields[id];
        if (!field) return;
        idsToDelete.add(id);
        if (field.children && field.children.length > 0) {
            field.children.forEach(childId => collect(childId));
        }
    };

    collect(fieldId);
    return idsToDelete;
};

/**
 * Immutably removes a set of field IDs from the fields object.
 */
const removeFieldsById = (fields, idsToDelete) => {
    const newFields = {};
    for (const [key, value] of Object.entries(fields)) {
        if (!idsToDelete.has(key)) {
            newFields[key] = value;
        }
    }
    return newFields;
};

export default function reducer(state, action) {
    switch (action.type) {

        case UPDATE_THEME_MODE:
            localStorage.setItem("DragwybEditorTheme", action.payload.themeMode);
            return {
                ...state,
                themeMode: action.payload.themeMode
            };

        case UPDATE_IFRAME_NODE:
            return {
                ...state,
                iframeEle: action.payload.node
            };

        case UPDATE_RESPONSIVE_TYPE:
            return {
                ...state,
                responsiveType: action.payload.responsiveType
            };

        case UPDATE_ACTIVE_POPOVER:
            return {
                ...state,
                activePopoverKey: action.payload.activePopoverKey
            };

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
            if (!state.form.rootContainers.includes(rootContainerId)) return state;

            // Collect all IDs to delete (root container + all nested children)
            const idsToDelete = collectFieldIdsToDelete(state.form.fields, rootContainerId);

            // Immutably remove all collected IDs
            const newFields = removeFieldsById(state.form.fields, idsToDelete);

            return {
                ...state,
                form: {
                    ...state.form,
                    rootContainers: state.form.rootContainers.filter(
                        rc => rc !== rootContainerId
                    ),
                    fields: newFields
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

            // Ensure fields object exists (immutably)
            const existingFields = state?.form?.fields || {};

            const index = null === fieldIndex
                ? (field.is_root_container
                    ? Object.keys(existingFields).length
                    : (existingFields[field.parentId]?.children
                        ? Object.keys(existingFields[field.parentId].children).length
                        : 0))
                : fieldIndex;

            const fieldId = field._id;

            let childrens = [];
            if (field.parentId) {
                childrens = [...(existingFields[field.parentId]?.children ?? [])];

                if (childrens.length < index) {
                    for (let i = childrens.length; i < index; i++) {
                        if (!childrens[i]) {
                            childrens.push(null);
                        }
                    }
                }

                childrens[index] = fieldId;
            }

            const newFields = Object.entries(existingFields);
            const lastField = existingFields[Object.keys(existingFields)[Object.keys(existingFields).length - 1]];

            // Clone rootContainers — never mutate the original
            const rootContainers = [...state.form.rootContainers];

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

            if (!field) return state;
            if (!field._id || !field.type) return state;

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

            return state;
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
            const fieldToDelete = state.form.fields[action.payload];
            if (!fieldToDelete) return state;

            // Collect all IDs to delete (the field + all its children recursively)
            const idsToDelete = collectFieldIdsToDelete(state.form.fields, action.payload);

            // Immutably remove fields
            let newFields = removeFieldsById(state.form.fields, idsToDelete);

            // Update rootContainers
            let rootContainers = [...state.form.rootContainers];
            if (fieldToDelete.is_root_container) {
                rootContainers = rootContainers.filter(rc => rc !== action.payload);
            }

            // Immutably update parent's children array
            if (fieldToDelete.parentId && newFields[fieldToDelete.parentId]) {
                newFields = {
                    ...newFields,
                    [fieldToDelete.parentId]: {
                        ...newFields[fieldToDelete.parentId],
                        children: newFields[fieldToDelete.parentId].children.filter(
                            childId => childId !== action.payload
                        )
                    }
                };
            }

            return {
                ...state,
                form: {
                    ...state.form,
                    fields: newFields,
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

        case UPDATE_TOOLBAR_SETTINGS: {
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
        }

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

                // Bounded array — keep max 50 entries to prevent unbounded growth
                const existingControls = state.popoverControls || [];
                const newControls = existingControls.length >= 50
                    ? [...existingControls.slice(-49), action.payload.id]
                    : [...existingControls, action.payload.id];

                return {
                    ...state,
                    popoverInitialize,
                    popoverControls: newControls
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
                    styleSelectors: {
                        ...state.styleSelectors || {},
                        [action.payload.responsiveType]: {
                            ...(state.styleSelectors?.[action.payload.responsiveType] || {}),
                            [action.payload.key]: action.payload.value
                        }
                    }
                }
            }

            return {
                ...state,
                styleSelectors: {
                    ...state.styleSelectors || {},
                    [action.payload.key]: {
                        ...(state.styleSelectors?.[action.payload.key] || {}),
                        ...action.payload.value
                    }
                }
            }

        case DELETE_STYLE_SELECTORS: {
            if (!action.payload.key) return state;

            // Immutable delete — clone first, then omit the key
            if (action.payload.responsiveType && 'desktop' !== action.payload.responsiveType) {
                const responsiveGroup = state.styleSelectors?.[action.payload.responsiveType];
                if (!responsiveGroup || !responsiveGroup[action.payload.key]) return state;

                const { [action.payload.key]: _removed, ...restResponsive } = responsiveGroup;
                return {
                    ...state,
                    styleSelectors: {
                        ...state.styleSelectors,
                        [action.payload.responsiveType]: restResponsive
                    }
                };
            }

            if (!state.styleSelectors?.[action.payload.key]) return state;

            const { [action.payload.key]: _removed, ...restSelectors } = state.styleSelectors;
            return {
                ...state,
                styleSelectors: restSelectors
            };
        }

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
                errors: [...(state.errors || []), action.payload]
            };

        default:
            return state;
    }
}