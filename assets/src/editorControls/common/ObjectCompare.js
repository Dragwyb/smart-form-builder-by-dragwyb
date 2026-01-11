const ObjectCompare = (obj1, obj2) => {
    const keys = Object.keys(obj1);
    let isvalueChanged = true;
    for (let i = 0; i < keys.length; i++) {
        if (obj1[keys[i]] !== obj2[keys[i]]) {
            isvalueChanged = false;
        }
    }
    return isvalueChanged;
}

export default ObjectCompare;