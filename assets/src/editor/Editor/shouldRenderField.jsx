const shouldRenderField = (field, fieldValues, settings, skipSections = false) => {
    const conditions = field.conditions;
    fieldValues = JSON.parse(JSON.stringify(fieldValues));

    // If no conditions, allow rendering
    if (!conditions || typeof conditions !== 'object') {
        return true;
    }

    for (const key in conditions) {
        const expected = conditions[key];

        // If key ends with "!" → treat as "not equal"
        const isNot = key.endsWith("!");
        const cleanKey = isNot ? key.slice(0, -1) : key;

        if (!skipSections && !fieldValues.hasOwnProperty(cleanKey) && settings.hasOwnProperty(cleanKey)) {
            if (settings[cleanKey].hasOwnProperty("default")) {
                fieldValues[cleanKey] = settings[cleanKey].default;
            }
        }

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
