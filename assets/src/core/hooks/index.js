class Hooks {
    constructor() {
        this.Actions = {};
        this.Filters = {};
    }

    addAction = (handle = false, callback = false) => {
        if (!handle) {
            new Error('Do not call addAction without handle name');
            return;
        }

        if (!callback) {
            new Error('Do not call addAction without callback function');
            return;
        }

        this.#addUserCallback(handle, callback, this.Actions);
    }

    addFilter = (handle = false, callback = () => { }) => {
        if (!handle) {
            new Error('Do not call addFilter without handle name');
            return;
        }

        if (!callback) {
            new Error('Do not call addFilter without callback function');
            return;
        }

        this.#addUserCallback(handle, callback, this.Filters);
    }

    doAction = (handle = false, ...args) => {
        if (!handle) {
            new Error('Do not call doAction without handle name');
            return;
        }

        if (args.length < 0) {
            args = false;
        }

        this.#usercallBack(handle, this.Actions, args);
    }

    applyFilter = (handle = false, ...args) => {
        if (args.length < 0) {
            new Error('Do not call doAction without arguments');
        }

        if (!handle) {
            new Error('Do not call doAction without handle name');
            return;
        }

        const data = this.#usercallBack(handle, this.Filters, args);

        if (!data.status) {
            return args[0];
        }

        return data.found;
    }

    hasAction=(handle)=>{
        return this.#handleExists(handle, this.Actions);
    }
    
    hasFilter=(handle)=>{
        return this.#handleExists(handle, this.Filters);
    }

    #handleExists=(handle, object)=>{
        const handleKeys = handle.split('/');
        const lastKey=handleKeys[handleKeys.length - 1];

        if (handleKeys[0] === 'Dragwyb') {
            handleKeys.shift();
        }

        handleKeys.pop();

        let currentObject = object;

        for (const key of handleKeys) {
            if (currentObject?.[key]) {
                currentObject = currentObject[key];
            } else {
                currentObject = {};
                break;
            }
        }

        return currentObject.hasOwnProperty(lastKey);
    }

    removeAction=(handle)=>{
        if(this.Actions && this.Actions[handle]) delete this.Actions[handle];
    }
    removeFilter=(handle)=>{
        if(this.Filters && this.Filters[handle]) delete this.Filters[handle];
    }

    #addUserCallback = (handle = '', callback = () => { }, object) => {
        let handleKeys = handle.split('/');

        if (handleKeys[0] === 'Dragwyb') {
            handleKeys.shift();
        }

        let currentHookObj = object;

        handleKeys.forEach((key, index) => {
            // If it's the last key, assign the callback
            if (index === handleKeys.length - 1) {
                currentHookObj[key] = [...currentHookObj[key] || [], callback];
            } else {
                // If key doesn't exist or is not an object, initialize as an object
                if (typeof currentHookObj[key] !== 'object' || currentHookObj[key] === null) {
                    currentHookObj[key] = {};
                }

                // Traverse deeper
                currentHookObj = currentHookObj[key];
            }
        });
    };

    #usercallBack = (handle, object, args = false) => {
        const handleKeys = handle.split('/');

        if (handleKeys[0] === 'Dragwyb') {
            handleKeys.shift();
        }
        let callbacks = object;

        for (const key of handleKeys) {
            if (callbacks?.[key]) {
                callbacks = callbacks[key];
            } else {
                callbacks = [];
                break;
            }
        }

        let data = {status: false};

        if (callbacks.length > 0) {
            data.status=true;
            data.found = true;

            callbacks.forEach((callback) => {
                data.found = callback(...args)
            });
        }

        return data;
    }
}

export default Hooks;