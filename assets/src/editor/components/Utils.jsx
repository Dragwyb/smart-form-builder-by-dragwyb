import * as Helpers from '../utils/helpers';
import { addField } from '../store/actions';

export const Utils=(state, dispatch)=>{
    const HelperFunctions=Object.keys(Helpers);
    const Utils={};

    HelperFunctions.forEach(funName=>{
        Utils[funName]=(args = {})=>{return Helpers[funName]({state, dispatch, ...args})};
    })

    return Object.freeze(Utils);
}