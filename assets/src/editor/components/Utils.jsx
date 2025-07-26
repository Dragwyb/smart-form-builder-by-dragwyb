import * as Helpers from '../utils/helpers';

const Utils=(state, dispatch)=>{
    const HelperFunctions=Object.keys(Helpers);
    const Utils={};

    HelperFunctions.forEach(funName=>{
        Utils[funName]=()=>{return Helpers[funName](state, dispatch)};
    })

    return Utils;
}

export default Utils;