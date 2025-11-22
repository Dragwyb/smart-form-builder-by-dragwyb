class Hooks {
    constructor() {
        this.Actions = {};
        this.Filters = {};
    }

    addAction = (handle = false, callback = false) => {
        if (!handle || !callback) {
            console.error('Error: addAction requires a handle and a callback.');
            return;
        }
        this.#addUserCallback(handle, callback, this.Actions);
    }

    addFilter = (handle = false, callback = () => {}) => {
        if (!handle || !callback) {
            console.error('Error: addFilter requires a handle and a callback.');
            return;
        }
        this.#addUserCallback(handle, callback, this.Filters);
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

    // Updated: callback is optional
    hasAction = (handle, callback = false) => {
        return this.#handleExists(handle, this.Actions, callback);
    }

    // Updated: callback is optional
    hasFilter = (handle, callback = false) => {
        return this.#handleExists(handle, this.Filters, callback);
    }

    // Updated: callback is optional
    removeAction = (handle, callback = false) => {
        return this.#removeHandle(handle, this.Actions, callback);
    }

    // Updated: callback is optional
    removeFilter = (handle, callback = false) => {
        return this.#removeHandle(handle, this.Filters, callback);
    }

    /**
     * ---------------------------------------------------------
     * PRIVATE METHODS
     * ---------------------------------------------------------
     */

    #handleExists = (handle, object, callbackToCheck = false) => {
        const keys = this.#getHandleKeys(handle);
        const lastKey = keys.pop();
        
        let currentContext = object;
        // Traverse to the parent object
        for (const key of keys) {
            if (currentContext?.[key]) {
                currentContext = currentContext[key];
            } else {
                return false;
            }
        }

        // 1. Check if the handle array exists
        if (!currentContext || !currentContext.hasOwnProperty(lastKey)) {
            return false;
        }

        // 2. If NO callback was passed, just return true (because the handle exists)
        if (!callbackToCheck) {
            return true;
        }

        // 3. If callback WAS passed, check for specific existence
        const callbacksArray = currentContext[lastKey];

        const found = callbacksArray.some(storedCallback => {
            // Strict Type Check (String vs Function) && Value Check
            return (typeof storedCallback === typeof callbackToCheck) && (storedCallback === callbackToCheck);
        });

        return found;
    }

    /**
     * Remove Handle
     * - If callback provided: Remove specific callback.
     * - If NO callback provided: Remove the entire handle.
     */
    #removeHandle = (handle, object, callbackToRemove = false) => {
        const keys = this.#getHandleKeys(handle);
        const lastKey = keys.pop(); 

        let currentContext = object;

        // Traverse to the parent object
        for (const key of keys) {
            if (currentContext?.[key]) {
                currentContext = currentContext[key];
            } else {
                return false; // Path not found
            }
        }

        // Check if the specific event array exists
        if (currentContext && Array.isArray(currentContext[lastKey])) {
            
            // CASE 1: Remove Specific Callback
            if (callbackToRemove) {
                const callbacksArray = currentContext[lastKey];
                
                const index = callbacksArray.findIndex(storedCallback => {
                    // Strict Type Check && Reference Check
                    return (typeof storedCallback === typeof callbackToRemove) && (storedCallback === callbackToRemove);
                });

                if (index > -1) {
                    callbacksArray.splice(index, 1);
                    
                    // Optional: Clean up empty array if you want
                    if (callbacksArray.length === 0) {
                        delete currentContext[lastKey];
                    }
                    return true; 
                }
            } 
            // CASE 2: No callback provided -> Remove the WHOLE handle
            else {
                delete currentContext[lastKey];
                return true;
            }
        }

        return false; // Handle not found
    }

    #addUserCallback = (handle, callback, object) => {
        const keys = this.#getHandleKeys(handle);
        let currentContext = object;

        keys.forEach((key, index) => {
            if (index === keys.length - 1) {
                currentContext[key] = [...(currentContext[key] || []), callback];
            } else {
                if (!currentContext[key] || typeof currentContext[key] !== 'object') {
                    currentContext[key] = {};
                }
                currentContext = currentContext[key];
            }
        });
    };

    #usercallBack = (handle, object, args = [], isFilter = false) => {
        const keys = this.#getHandleKeys(handle);
        let currentContext = object;

        for (const key of keys) {
            if (currentContext?.[key]) {
                currentContext = currentContext[key];
            } else {
                return { status: false, value: isFilter ? args[0] : null };
            }
        }

        const callbacks = Array.isArray(currentContext) ? currentContext : [];
        let value = args[0]; 

        if (callbacks.length > 0) {
            callbacks.forEach((callback) => {
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
        }

        return { status: true, value: value };
    }

    #getHandleKeys = (handle) => {
        const keys = handle.split('/');
        if (keys[0] === 'Dragwyb') {
            keys.shift();
        }
        return keys;
    }
}

export default Hooks;