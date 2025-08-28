const shouldRenderField=(field, fieldValues)=>{
    const conditions = field.conditions;

    // If no conditions, allow rendering
    if (!conditions || typeof conditions !== 'object') {
        return true;
    }

    // Check each condition
    for (const key in conditions) {
        if (
            !fieldValues.hasOwnProperty(key) ||  // Key missing in fieldValues
            fieldValues[key] !== conditions[key] // Value doesn't match
        ) {
            return false; // 🚫 Early exit on first mismatch
        }
    }

    return true; // ✅ All conditions matched
}

export default shouldRenderField;
