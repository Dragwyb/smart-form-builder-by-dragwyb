class Hooks {
    constructor() {
        this.Action = {};
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

        this.#addUserCallback(handle, callback, this.Action);
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

        this.#usercallBack(handle, this.Action, args);
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

        if (!data) {
            return args[0];
        }

        return data;
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

        let data = false;

        if (callbacks.length > 0) {
            data = true;

            callbacks.forEach((callback) => {
                data = callback(...args)
            });
        }

        return data;
    }
}

export default Hooks;