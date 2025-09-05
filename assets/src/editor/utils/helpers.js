
import React, { useRef, useEffect } from "react";
import { updateFieldId, addField, updateSelectedSettingId, updateActiveToolbar, updatePreviewMode, updateFieldValues,updateToolbarSettings, updateSectionSettings } from "../store/actions";
import PropTypes from "prop-types";

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

export const PopoverControls = ({state, dispatch})=>{
    return state.popoverControls;
}

export const AddField = ({ type, dispatch, Utils, index = null }) => {
    const field = {
        _id: Utils.generateId(),
        type,
    };

    if (DragwybEditor.fields[type] && DragwybEditor.fields[type].controls) {
        const fieldControls = DragwybEditor.fields[type].controls;
        field.attributes = {};
        Object.keys(fieldControls).forEach(id => {
            if (!['tabs', 'tab', 'section'].includes(fieldControls[id].type)) {

                let defaultValue = fieldControls[id].default ? fieldControls[id].default : '';

                defaultValue = DragwybBuilder.Hooks.applyFilter(`Dragwyb/Editor/AddControl/${fieldControls[id].type}.defaultValue`, defaultValue, fieldControls[id], Utils);

                field.attributes[id] = defaultValue;
            }
        })
    }

    dispatch(addField({ field, index }));
    setSelectedSettingId({ dispatch, value: field._id });
    setActiveTab({ dispatch, value: 'fields' });

    return field;
};

export const updateFieldValue=({dispatch, id, value})=>{
    try {
        const validatorId=validateProp({
            key: "id",
            value: id, // invalid
            types: ["string"],
            required: true,
            functionName: "updateFieldValue"
        });
        const validatorValue=validateProp({
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

export const updateToolbarSetting=({id, value, dispatch})=>{

    if(!DragwybEditor.EditorToolbars || !DragwybEditor.EditorToolbars.toolbars || !DragwybEditor.EditorToolbars.toolbars[id]){
        return;
    }

    try {
        const validatorId=validateProp({
            key: "id",
            value: id, // invalid
            types: ["string"],
            required: true,
            functionName: "updateFieldValue"
        });
        const validatorValue=validateProp({
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
        const validator=validateProp({
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

export const updateSectionSetting = ({dispatch, key, value})=>{
    try {
        const validatorKey=validateProp({
            key: "key",
            value: key, // invalid
            types: ["string"],
            required: true,
            functionName: "updateSectionSetting"
        });
        const validatorValue=validateProp({
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

export const setPreviewMode = ({ dispatch, value }) => {
      try {
        validateProp({
            key: "value",
            value: value, // invalid
            types: ["bool"],
            required: true,
            functionName: "setPreviewMode"
        });

        dispatch(updatePreviewMode(value))
    } catch (e) {
        console.error("Validation failed:", e.message);
    }
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

