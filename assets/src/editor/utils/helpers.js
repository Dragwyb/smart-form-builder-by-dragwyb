import { updateFieldId } from "../store/actions";


/**
 * Generates a unique ID
 * @returns {string}
 */
export const generateId = (state, dispatch) => {
    const existIds=state?.fieldIds || [];
        
    const letters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    
    const createId = () => {
        let id = '';
        for (let i = 0; i < 9; i++) {
            id += letters.charAt(Math.floor(Math.random() * letters.length));
        }
        return id;
    };

    let id;

    id=createId();
    do {
        id = createId();
    } while (existIds.includes(id));

    dispatch(updateFieldId(id));

    return id;
};

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