
import React, { useRef, useEffect } from "react";
import { updateFieldId, addField, updateSelectedSettingId, updateActiveToolbar, updateFieldValues, updateToolbarSettings, updateSectionSettings, updateStyleSelectors as updateStyleSelectorsAction, deleteStyleSelectors as deleteStyleSelectorsAction, updateResponsiveType as updateResponsiveTypeAction, updateactivePopoverKey as updateactivePopoverKeyAction, updateActiveRootContainer as updateActiveRootContainerAction, resetActiveRootContainer as resetActiveRootContainerAction } from "../store/actions";
import PropTypes, { number } from "prop-types";
import { Placeholder } from "@wordpress/components";

/**
 * Generates a unique ID
 * @returns {string}
 */
export const generateId = ({ state, dispatch }) => {
    const existIds = state?.fieldIds || [];

    const letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    const createId = () => {
        let id = '';
        for (let i = 0; i < 9; i++) {
            id += letters.charAt(Math.floor(Math.random() * letters.length));
        }
        return id;
    };

    let id;

    id = createId();
    do {
        id = createId();
    } while (existIds.includes(id));

    dispatch(updateFieldId(id));

    return id;
};

export const updateResponsiveType = ({ state, dispatch, responsiveType }) => {
    try {
        const validatorResponsiveType = validateProp({
            key: "responsiveType",
            value: responsiveType, // invalid
            types: [number],
            required: true,
            functionName: "updateResponsiveType"
        });

        dispatch(updateResponsiveTypeAction(responsiveType));
    } catch (e) {
        console.error("Validation failed:", e.message);
    }
}

export const updateactivePopoverKey = ({ dispatch, activePopoverKey }) => {
    try {
        const validatorPopoverControls = validateProp({
            key: "activePopoverKey",
            value: activePopoverKey, // invalid
            types: ["string", "bool", "null"],
            required: true,
            functionName: "updateactivePopoverKey"
        });

        dispatch(updateactivePopoverKeyAction(activePopoverKey));
    } catch (e) {
        console.error("Validation failed:", e.message);
    }
}

export const PopoverControls = ({ state, dispatch }) => {
    return state.popoverControls;
}

export const updateActiveRootContainer = ({ state, dispatch, rootContainerId, activeColumnIndex = null }) => {
    try {
        const validatorRootContainerId = validateProp({
            key: "rootContainerId",
            value: rootContainerId, // invalid
            types: ["string"],
            required: true,
            functionName: "updateActiveRootContainer"
        });
        if (null !== activeColumnIndex) {
            const validatorActiveColumnIndex = validateProp({
                key: "activeColumnIndex",
                value: activeColumnIndex, // invalid
                types: ["number"],
                required: false,
                functionName: "updateActiveRootContainer"
            });
        }

        dispatch(updateActiveRootContainerAction(rootContainerId, activeColumnIndex));
    } catch (e) {
        console.error("Validation failed:", e.message);
    }
}

export const resetActiveRootContainer = ({ state, dispatch }) => {
    const activeRootContainer = state.activeRootContainer;

    if (!activeRootContainer || (!activeRootContainer.rootContainerId && !activeRootContainer.activeColumnIndex)) {
        return;
    }

    dispatch(resetActiveRootContainerAction());
}

export const AddField = ({ state, type, dispatch, Utils, index = null, parentContainer = null, attributes = {} }) => {
    let field = {
        _id: Utils.generateId(),
        type,
    };
    const activeRootContainer = parentContainer || state.activeRootContainer;

    const existingFields = state?.form?.fields || {};

    const fieldData = DragwybEditor.fields.fields[type] || {};

    if (fieldData.is_root_container === true) {
        field.is_root_container = true;
    }

    if (fieldData.controls) {
        const fieldControls = fieldData.controls;
        field.attributes = {};
        Object.keys(fieldControls).forEach(id => {
            if (!['tabs', 'tab', 'section'].includes(fieldControls[id].type)) {

                let defaultValue = fieldControls[id].default ? fieldControls[id].default : '';

                defaultValue = DragwybBuilder.Hooks.applyFilter(`Dragwyb/Editor/AddControl/${fieldControls[id].type}.defaultValue`, defaultValue, fieldControls[id], Utils);

                field.attributes[id] = defaultValue;
            }
        })

        field.attributes = { ...field.attributes, ...attributes };

        if (fieldControls.field_id) {
            field.attributes.field_id = `field_${field._id}`;
        }
    }

    if (!fieldData.is_root_container) {
        if (activeRootContainer) {
            const activeParentId = activeRootContainer.rootContainerId;
            const parentField = existingFields[activeParentId];
            const totalColumns = parentField.attributes.columns;
            const totalChildre = parentField.children?.length || 0;
            const lastIndex = Math.max(totalColumns, totalChildre);

            index = activeRootContainer.activeColumnIndex;
            field.parentId = activeParentId;

            let isResetActiveRootContainer = true;

            if (lastIndex && lastIndex > (index + 1)) {
                isResetActiveRootContainer = false;
            }

            if (isResetActiveRootContainer) {
                Utils.resetActiveRootContainer();
            } else {
                let activeColumnIndex = index + 1;

                if (parentField?.children) {
                    for (let i = activeColumnIndex; i < lastIndex; i++) {
                        if (!parentField.children[i]) {
                            activeColumnIndex = i;
                            break;
                        } else if (parentField.children[i]) {
                            activeColumnIndex = null;
                        }
                    }
                }

                if (activeColumnIndex !== null) {
                    Utils.updateActiveRootContainer({ rootContainerId: activeParentId, activeColumnIndex: activeColumnIndex });
                } else {
                    Utils.resetActiveRootContainer();
                }
            }
        } else {
            const buttonRootContainer = state.form.rootContainers[state.form.rootContainers.length - 1];
            const secondLastRootContainer = state.form.rootContainers[state.form.rootContainers.length - 2];

            let parentId = false;

            if (buttonRootContainer && existingFields[buttonRootContainer].is_root_container === true && (!existingFields[buttonRootContainer].children || existingFields[buttonRootContainer].children.length < 1)) {
                parentId = buttonRootContainer;
            } else if (secondLastRootContainer && existingFields[secondLastRootContainer].is_root_container === true && (!existingFields[secondLastRootContainer].children || existingFields[secondLastRootContainer].children.length < 1)) {
                parentId = secondLastRootContainer;
            }

            if (!parentId) {
                const rootContainerId = AddField({ state, type: 'row', dispatch, Utils, index: index });
                field.parentId = rootContainerId['_id'];
            } else {
                Utils.updateActiveRootContainer({ rootContainerId: parentId });
                field.parentId = parentId;
            }

            index = 0;
        }
    }

    field = DragwybBuilder.Hooks.applyFilter(`Dragwyb/Editor/AddField/${type}`, field, Utils);

    dispatch(addField({ field, index }));
    setSelectedSettingId({ dispatch, value: field._id });
    setActiveTab({ dispatch, value: 'fields' });

    if (!state.form.hasOwnProperty('fields')) {
        state.form.fields = {}
    }

    if (Object.keys(state.form.fields).length < 1) {
        state.form.fields[field._id] = field;
    }

    if (Object.keys(existingFields).length === 0 && type !== 'button') {
        const buttonAddStatus = DragwybEditor?.formData?.addSubmitButton;

        if (buttonAddStatus === true) {
            AddField({ state, type: 'button', dispatch, Utils, attributes: { text: 'Submit', field_id: 'submit' } });
            delete DragwybEditor.formData.addSubmitButton;

            setSelectedSettingId({ dispatch, value: field._id });
        }
    }

    return field;
};

export const updateFieldValue = ({ dispatch, id, value }) => {
    try {
        const validatorId = validateProp({
            key: "id",
            value: id, // invalid
            types: ["string"],
            required: true,
            functionName: "updateFieldValue"
        });
        const validatorValue = validateProp({
            key: "value",
            value: value, // invalid
            types: ["any"],
            required: true,
            functionName: "updateFieldValue"
        });
        dispatch(updateFieldValues(id, value));
    } catch (e) {
        console.error("Validation failed:", e.message);
    }
}

export const updateToolbarSetting = ({ id, value, dispatch }) => {

    if (!DragwybEditor.EditorToolbars || !DragwybEditor.EditorToolbars.toolbars || !DragwybEditor.EditorToolbars.toolbars[id]) {
        return;
    }

    try {
        const validatorId = validateProp({
            key: "id",
            value: id, // invalid
            types: ["string"],
            required: true,
            functionName: "updateFieldValue"
        });
        const validatorValue = validateProp({
            key: "value",
            value: value, // invalid
            types: ["any"],
            required: true,
            functionName: "updateFieldValue"
        });
        dispatch(updateToolbarSettings(id, value))
    } catch (e) {
        console.error("Validation failed:", e.message);
    }
}

export const setSelectedSettingId = ({ dispatch, value }) => {
    try {
        const validator = validateProp({
            key: "value",
            value: value, // invalid
            types: ["bool", "string"],
            required: true,
            functionName: "setSelectedSettingId"
        });
        dispatch(updateSelectedSettingId(value))
    } catch (e) {
        console.error("Validation failed:", e.message);
    }
}

export const setActiveTab = ({ dispatch, value }) => {
    try {
        validateProp({
            key: "value",
            value: value, // invalid
            types: ["bool", "string"],
            required: true,
            functionName: "setActiveTab"
        });

        dispatch(updateActiveToolbar(value))
    } catch (e) {
        console.error("Validation failed:", e.message);
    }
}

export const updateSectionSetting = ({ dispatch, key, value }) => {
    try {
        const validatorKey = validateProp({
            key: "key",
            value: key, // invalid
            types: ["string"],
            required: true,
            functionName: "updateSectionSetting"
        });
        const validatorValue = validateProp({
            key: "value",
            value: value, // invalid
            types: ["string"],
            required: true,
            functionName: "updateSectionSetting"
        });
        dispatch(updateSectionSettings(key, value))
    } catch (e) {
        console.error("Validation failed:", e.message);
    }
}

export const updateStyleSelectors = ({ state, dispatch, key, value, selectors, placeholders, toolbarType, itemId, currentItemId, responsiveType = 'desktop', initialRender = false }) => {

    try {
        const validatorKey = validateProp({
            key: "key",
            value: key, // invalid
            types: ["string"],
            required: true,
            functionName: "updateStyleSelectors"
        });
        const validatorValue = validateProp({
            key: "value",
            value: value, // invalid
            types: ["any"],
            required: true,
            functionName: "updateStyleSelectors"
        });
        const validatorSelectors = validateProp({
            key: "selectors",
            value: selectors, // invalid
            types: ["object"],
            required: true,
            functionName: "updateStyleSelectors"
        });
        const validatorPlaceholders = validateProp({
            key: "placeholders",
            value: placeholders, // invalid
            types: ["object"],
            required: true,
            functionName: "updateStyleSelectors"
        });

        const existSelectors = state.styleSelectors;
        const formId = state.form.id;

        if (initialRender && existSelectors[key]) {
            return;
        }

        const cssCache = {};
        Object.keys(selectors).forEach((selector) => {
            let wrapperId = formId;

            if (toolbarType === 'fields' && itemId && itemId !== '') {
                const fieldData = state.form.fields[itemId];

                if (fieldData.type === 'row') {
                    wrapperId += ' #dragwyb-row-' + itemId;
                } else {
                    wrapperId += ' #dragwyb-field-wrapper-' + itemId;
                }
            }

            const targetSelector = selector.replaceAll("{{WRAPPER}}", `#dragwyb-form-wrapper-${wrapperId}`);

            cssCache[targetSelector] = selectors[selector];

            if (currentItemId && '' !== currentItemId && targetSelector.includes('{{CURRENT_ITEM}}')) {
                cssCache[targetSelector] = cssCache[targetSelector].replaceAll('{{CURRENT_ITEM}}', currentItemId);
            }


            Object.keys(placeholders).forEach((placeholder) => {
                if (placeholder === 'VALUE' && placeholders[placeholder] === true && ['string', 'number', 'BigInt'].includes(typeof value)) {
                    cssCache[targetSelector] = cssCache[targetSelector].replaceAll("{{VALUE}}", value);
                } else {
                    cssCache[targetSelector] = cssCache[targetSelector].replaceAll("{{" + placeholder + "}}", value[placeholders[placeholder]]);
                }
            });

        });

        dispatch(updateStyleSelectorsAction(key, cssCache, responsiveType))
    } catch (e) {
        console.error("Validation failed:", e.message);
    }
}

export const duplicateStyleSelectors = ({ cloneId, currentId, state, dispatch }) => {
    try {
        validateProp({
            key: "cloneId",
            value: cloneId, // invalid
            types: ["string"],
            required: true,
            functionName: "duplicateStyleSelectors"
        });

        validateProp({
            key: "currentId",
            value: currentId, // invalid
            types: ["string"],
            required: true,
            functionName: "duplicateStyleSelectors"
        });

        const refStyles = { ...state.styleSelectors };

        Object.keys(refStyles).forEach((key) => {
            if (key.startsWith(`fields_${currentId}`)) {
                let newKey = key.replaceAll(currentId, cloneId);
                let newStyleSelectors = JSON.stringify(refStyles[key]);
                newStyleSelectors = newStyleSelectors.replaceAll(currentId, cloneId);
                dispatch(updateStyleSelectorsAction(newKey, JSON.parse(newStyleSelectors)))
            }
        });

        const responsiveDevices = ['desktop', 'tablet', 'mobile'];

        responsiveDevices.forEach((device) => {
            if (refStyles[device]) {
                Object.keys(refStyles[device]).forEach((key) => {
                    if (key.startsWith(`fields_${currentId}`)) {
                        let newKey = key.replaceAll(currentId, cloneId);
                        let newStyleSelectors = JSON.stringify(refStyles[device][key]);
                        newStyleSelectors = newStyleSelectors.replaceAll(currentId, cloneId);
                        dispatch(updateStyleSelectorsAction(newKey, JSON.parse(newStyleSelectors), device))
                    }
                });
            }
        });
    } catch (e) {
        console.error("Validation failed:", e.message);
    }
}

export const deleteStyleSelectors = ({ dispatch, state, key }) => {
    try {
        const validatorKey = validateProp({
            key: "key",
            value: key, // invalid
            types: ["string"],
            required: true,
            functionName: "deleteStyleSelectors"
        });
        const currentStyles = state.styleSelectors;

        dispatch(deleteStyleSelectorsAction(key))
    } catch (e) {
        console.error("Validation failed:", e.message);
    }
}

export const compareTwoObjects = ({ obj1, obj2 }) => {
    const keys = Object.keys(obj1);
    let isvalueChanged = true;
    for (let i = 0; i < keys.length; i++) {
        if (obj1[keys[i]] !== obj2[keys[i]]) {
            isvalueChanged = false;
        }
    }
    return isvalueChanged;
}

/**
 * Deep clones an object
 * @param {Object} obj - The object to clone
 * @returns {Object}
 */
export const deepClone = (obj) => {
    return JSON.parse(JSON.stringify(obj));
};

/**
 * Formats a date string
 * @param {string} date - The date to format
 * @returns {string}
 */
export const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

/**
 * Validates an email address
 * @param {string} email - The email to validate
 * @returns {boolean}
 */
export const isValidEmail = (email) => {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
};

/**
 * Sanitizes a string for use as a slug
 * @param {string} text - The text to slugify
 * @returns {string}
 */
export const slugify = (text) => {
    return text
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/(^-|-$)/g, '');
};

/**
 * Debounces a function
 * @param {Function} func - The function to debounce
 * @param {number} wait - The debounce delay in milliseconds
 * @returns {Function}
 */
export const debounce = (func, wait) => {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
};

export const useDebouncedCallback = (callback, delay = 300) => {
    const timeoutRef = useRef(null);

    useEffect(() => {
        return () => {
            if (timeoutRef.current) {
                clearTimeout(timeoutRef.current);
            }
        };
    }, []);

    return (...args) => {
        if (timeoutRef.current) {
            clearTimeout(timeoutRef.current);
        }
        timeoutRef.current = setTimeout(() => {
            callback(...args);
        }, delay);
    };
};

/**
 * Formats file size
 * @param {number} bytes - The size in bytes
 * @returns {string}
 */
export const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

/**
 * Checks if an object is empty
 * @param {Object} obj - The object to check
 * @returns {boolean}
 */
export const isEmpty = (obj) => {
    return Object.keys(obj).length === 0;
};

/**
 * Gets a nested object value using a path string
 * @param {Object} obj - The object to traverse
 * @param {string} path - The path to the value (e.g., 'settings.notifications.email')
 * @param {*} defaultValue - The default value if path doesn't exist
 * @returns {*}
 */
export const getNestedValue = (obj, path, defaultValue = undefined) => {
    return path.split('.').reduce((current, key) => {
        return current && current[key] !== undefined ? current[key] : defaultValue;
    }, obj);
};

/**
 * Sets a nested object value using a path string
 * @param {Object} obj - The object to modify
 * @param {string} path - The path to set
 * @param {*} value - The value to set
 * @returns {Object}
 */
export const setNestedValue = (obj, path, value) => {
    const clone = { ...obj };
    const keys = path.split('.');
    const lastKey = keys.pop();
    const lastObj = keys.reduce((current, key) => {
        if (!(key in current)) current[key] = {};
        return current[key];
    }, clone);
    lastObj[lastKey] = value;
    return clone;
};

/**
 * Validates a form field based on its type and rules
 * @param {Object} field - The field configuration
 * @param {*} value - The field value
 * @returns {boolean}
 */
export const validateField = (field, value) => {
    if (field.required && !value) return false;

    switch (field.type) {
        case 'email':
            return !value || isValidEmail(value);
        case 'number':
            const num = parseFloat(value);
            return !value || (
                !isNaN(num) &&
                (field.min === undefined || num >= field.min) &&
                (field.max === undefined || num <= field.max)
            );
        case 'file':
            if (!value) return true;
            const file = value instanceof File ? value : { size: 0 };
            return file.size <= (field.maxSize || Infinity);
        default:
            return true;
    }
};

export const validateProp = ({
    key,
    value,
    types = [],
    required = false,
    functionName = "AnonymousFunction"
}) => {
    const typeMap = {
        string: PropTypes.string,
        bool: PropTypes.bool,
        object: PropTypes.object,
        number: PropTypes.number,
        array: PropTypes.array,
        func: PropTypes.func,
        node: PropTypes.node,
        element: PropTypes.element,
        any: PropTypes.any
    };

    let validator = PropTypes.oneOfType(types.map(t => typeMap[t] || PropTypes.any));
    if (required) validator = validator.isRequired;

    const props = { [key]: value };

    // 🚀 Direct validator call (avoids caching)
    const error = validator(props, key, functionName, "prop", null, "SECRET_DO_NOT_PASS_THIS_OR_YOU_WILL_BE_FIRED");

    if (error) {
        throw error; // always throws if invalid
    }

    return true;
};

