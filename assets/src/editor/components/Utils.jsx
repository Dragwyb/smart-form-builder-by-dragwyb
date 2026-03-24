import * as Helpers from '../utils/helpers';

export const Utils = (state, dispatch, helpersTypes = []) => {
    const HelperFunctions = Object.keys(Helpers);
    const Utils = {};

    if (helpersTypes.length > 0) {
        helpersTypes.forEach(funName => {
            if (HelperFunctions.includes(funName)) {
                Utils[funName] = (args = {}) => { return Helpers[funName]({ state, dispatch, ...args }) };
            }
        })
    } else {
        HelperFunctions.forEach(funName => {
            Utils[funName] = (args = {}) => { return Helpers[funName]({ state, dispatch, ...args }) };
        })
    }


    return Object.freeze(Utils);
}