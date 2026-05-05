import store from "../store";

const shouldRenderField = (field, fieldValues, settings, skipSections = false) => {

    if (field?.responsive_control === true && field?.responsive_type) {
        const getResponsiveDevice = (size) => (
            size < 768 ? 'mobile' : size < 1024 ? 'tablet' : 'desktop'
        );

        const controlResponsiveType = field.responsive_type;

        if (controlResponsiveType !== getResponsiveDevice(store.getState().responsiveType)) {
            return false;
        }
    }


    const conditions = field.conditions;
    fieldValues = JSON.parse(JSON.stringify(fieldValues));

    // If no conditions, allow rendering
    if (!conditions || typeof conditions !== 'object') {
        return true;
    }


    const getStatus = (expected, actual, isNot) => {
        let status = null;

        if (isNot) {
            if (Array.isArray(actual)) {
                if (typeof expected === 'object') {
                    let skipLoop = null;
                    for (const exp of expected) {
                        if (actual.includes(exp)) {
                            skipLoop = true;
                            break;
                        }
                    }

                    if (skipLoop !== true) {
                        status = true;
                    }

                } else if (actual.includes(expected)) {
                    status = false;
                }

            } else {
                if (typeof expected === 'object') {
                    if (expected.includes(actual)) {
                        status = false;
                    }
                } else if (actual === expected) {
                    status = false; // 🚫 fail if not equal
                }
            }
        } else {
            if (Array.isArray(actual)) {
                if (typeof expected === 'object') {
                    let skipLoop = null;
                    for (const exp of expected) {
                        if (actual.includes(exp)) {
                            skipLoop = true;
                            break;
                        }
                    }

                    if (skipLoop !== true) {
                        status = false;
                    }

                } else if (!actual.includes(expected)) {
                    status = false;
                }

            } else {
                if (typeof expected === 'object') {
                    if (!expected.includes(actual)) {
                        status = false;
                    }
                } else if (actual !== expected) {
                    status = false; // 🚫 fail if not equal
                }
            }
        }

        return status;
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


        const status = getStatus(expected, actual, isNot);

        if (null !== status) {
            return status;
        }
    }

    return true; // ✅ All conditions matched
}

export default shouldRenderField;
