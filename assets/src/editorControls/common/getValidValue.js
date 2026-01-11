/**
 * Iterates through arguments and returns the first "valid" value.
 * Valid = Not null, not undefined, and not false.
 * * @param  {...any} args - List of values to check
 * @returns {any} - The first valid value found
 */
const getValidValue = (...args) => {
    let validValue = null;
    // Loop through all arguments passed to the function
    for (const value of args) {

        if (value || value === 0 || value === "" || value === false) {
            validValue = value;
            break;
        };
    }

    return validValue;
};

export default getValidValue;
