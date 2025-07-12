class Hooks {
    Hooks = {}
    Filters = {}

    addAction = (hook = false, callback = () => { }) => { }

    addFilter = (hook = false, callback = () => { }) => { }

    doAction = (hook) => { }

    applyFilter = () => { }
}

export default Hooks;