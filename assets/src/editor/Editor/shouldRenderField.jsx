const shouldRenderField=(field, fieldValues)=>{
    const conditions = field.conditions;

    // If no conditions, allow rendering
    if (!conditions || typeof conditions !== 'object') {
        return true;
    }

    for (const key in conditions) {
        const expected = conditions[key];
    
        // If key ends with "!" → treat as "not equal"
        const isNot = key.endsWith("!");
        const cleanKey = isNot ? key.slice(0, -1) : key;
    
        // Missing key in fieldValues → fail immediately
        if (!fieldValues.hasOwnProperty(cleanKey)) {
            return false;
        }
    
        const actual = fieldValues[cleanKey];
    
        if (isNot) {
            if (actual === expected) {
                return false; // 🚫 fail if equal
            }
        } else {
            if (actual !== expected) {
                return false; // 🚫 fail if not equal
            }
        }
    }

    return true; // ✅ All conditions matched
}

export default shouldRenderField;
