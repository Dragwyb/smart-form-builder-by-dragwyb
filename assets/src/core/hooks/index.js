class Hooks {
    constructor() {
        this.Actions = {};
        this.Filters = {};
    }

    addAction = (handle = false, callback = false, priority = 10) => {
        if (!handle || !callback) {
            console.error('Error: addAction requires a handle and a callback.');
            return;
        }
        this.#addUserCallback(handle, callback, priority, this.Actions);
    }

    addFilter = (handle = false, callback = () => { }, priority = 10) => {
        if (!handle || !callback) {
            console.error('Error: addFilter requires a handle and a callback.');
            return;
        }
        this.#addUserCallback(handle, callback, priority, this.Filters);
    }

    doAction = (handle = false, ...args) => {
        if (!handle) return;
        this.#usercallBack(handle, this.Actions, args, false);
    }

    applyFilter = (handle = false, ...args) => {
        if (!handle) return args[0];

        // Pass true to indicate this is a filter chain
        const result = this.#usercallBack(handle, this.Filters, args, true);

        return result.value;
    }

    hasAction = (handle, callback = false) => {
        return this.#handleExists(handle, this.Actions, callback);
    }

    hasFilter = (handle, callback = false) => {
        return this.#handleExists(handle, this.Filters, callback);
    }

    removeAction = (handle, callback = false) => {
        return this.#removeHandle(handle, this.Actions, callback);
    }

    removeFilter = (handle, callback = false) => {
        return this.#removeHandle(handle, this.Filters, callback);
    }

    /**
     * ---------------------------------------------------------
     * PRIVATE METHODS
     * ---------------------------------------------------------
     */

    #getHandle = (handle) => {
        let normalized = handle;
        // Keep the namespace logic but simplify it for a flat object
        if (normalized.startsWith('Dragwyb/')) {
            normalized = normalized.substring(8);
        }
        return normalized;
    }

    #handleExists = (handle, object, callbackToCheck = false) => {
        const normalized = this.#getHandle(handle);
        if (!object[normalized]) return false;

        if (!callbackToCheck) return true;

        return object[normalized].some(item => item.callback === callbackToCheck);
    }

    #removeHandle = (handle, object, callbackToRemove = false) => {
        const normalized = this.#getHandle(handle);
        if (!object[normalized]) return false;

        if (callbackToRemove) {
            const initialLength = object[normalized].length;
            object[normalized] = object[normalized].filter(item => item.callback !== callbackToRemove);

            if (object[normalized].length === 0) {
                delete object[normalized];
            }
            return object[normalized]?.length < initialLength;
        } else {
            delete object[normalized];
            return true;
        }
    }

    #addUserCallback = (handle, callback, priority, object) => {
        const normalized = this.#getHandle(handle);
        if (!object[normalized]) {
            object[normalized] = [];
        }

        object[normalized].push({ callback, priority });
        // Sort by priority (ascending)
        object[normalized].sort((a, b) => a.priority - b.priority);
    };

    #usercallBack = (handle, object, args = [], isFilter = false) => {
        const normalized = this.#getHandle(handle);
        let value = args[0];

        if (!object[normalized]) {
            return { status: false, value: value };
        }

        object[normalized].forEach((item) => {
            const callback = item.callback;
            if (typeof callback === 'function') {
                if (isFilter) {
                    value = callback(value, ...args.slice(1));
                } else {
                    callback(...args);
                }
            }
            else if (typeof callback === 'string' && typeof window[callback] === 'function') {
                if (isFilter) {
                    value = window[callback](value, ...args.slice(1));
                } else {
                    window[callback](...args);
                }
            }
        });

        return { status: true, value: value };
    }
}

export default Hooks;