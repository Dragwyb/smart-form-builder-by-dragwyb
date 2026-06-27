/**
 * Frontend Conditional Fields logic
 */
class DragwybConditionalFields {
    constructor($container, formId) {
        this.$container = $container;
        this.formId = formId;
        this.$form = $container.is('form') ? $container : $container.find('form.dragwyb-form');
        this.conditions = window.DragwybFrontendData && window.DragwybFrontendData[`form_${formId}`] && window.DragwybFrontendData[`form_${formId}`].conditions ? window.DragwybFrontendData[`form_${formId}`].conditions : {};
        this.init();
    }

    init() {
        if (!this.conditions || Object.keys(this.conditions).length === 0) {
            return;
        }
        this.bindEvents();
        this.evaluateAllConditions();
    }

    bindEvents() {
        // Listen to change and input events on all form inputs.
        // The 'input' event captures rangeSlider movements in real-time as it bubbles up.
        this.$form.on('change input', 'input, select, textarea', () => {
            this.evaluateAllConditions();
        });
    }

    evaluateAllConditions() {
        Object.entries(this.conditions).forEach(([targetFieldId, conditionsList]) => {
            if (!conditionsList || !conditionsList.length) return;

            let conditionsMet = true;
            let action = 'yes'; // 'yes' = Show, 'no' = Hide

            conditionsList.forEach(condition => {
                action = condition.action;
                const fieldValue = this.getFieldValue(condition.field_id);
                const isConditionMet = this.evaluateCondition(condition.operator, fieldValue, condition.value);

                if (!isConditionMet) {
                    conditionsMet = false;
                }
            });

            // Find target element wrapper
            const $targetInput = this.$form.find(`[name="${targetFieldId}"], [name="${targetFieldId}[]"]`);
            const $wrapper = $targetInput.first().closest('.dragwyb-field-wrapper');

            if ($wrapper.length) {
                // If action is 'yes' (Show): show if met, hide if not met
                // If action is 'no' (Hide): hide if met, show if not met
                const shouldShow = (action === 'yes') ? conditionsMet : !conditionsMet;

                if (shouldShow) {
                    $wrapper.show();
                } else {
                    $wrapper.hide();
                    this.clearFieldError($targetInput);
                }
            }
        });
    }

    getFieldValue(fieldId) {
        let $field = this.$form.find(`[name="${fieldId}"], [name="${fieldId}[]"]`);
        if (!$field.length) {
            return '';
        }

        if ($field.is(':radio')) {
            return this.$form.find(`[name="${fieldId}"]:checked, [name="${fieldId}[]"]:checked`).val() || '';
        } else if ($field.is(':checkbox')) {
            const checkedValues = this.$form.find(`[name="${fieldId}"]:checked, [name="${fieldId}[]"]:checked`).map((_, el) => jQuery(el).val()).get();
            if ($field.length > 1) {
                return checkedValues;
            }
            return checkedValues.length ? checkedValues[0] : '';
        }
        return $field.val();
    }

    evaluateCondition(operator, fieldValue, conditionValue) {
        if (Array.isArray(fieldValue)) {
            const isCondEmpty = conditionValue === undefined || conditionValue === null || String(conditionValue).trim() === '';
            if (operator === 'equal') {
                if (isCondEmpty) {
                    return fieldValue.length === 0;
                }
                return fieldValue.includes(conditionValue);
            }
            if (operator === 'not_equal') {
                if (isCondEmpty) {
                    return fieldValue.length > 0;
                }
                return !fieldValue.includes(conditionValue);
            }
            if (operator === 'contains') {
                if (isCondEmpty) {
                    return false;
                }
                return fieldValue.some(val => String(val).includes(conditionValue));
            }
            if (operator === 'not_contains') {
                if (isCondEmpty) {
                    return true;
                }
                return !fieldValue.some(val => String(val).includes(conditionValue));
            }
        }

        const strFieldVal = (fieldValue !== undefined && fieldValue !== null) ? String(fieldValue).trim() : '';
        const strCondVal = (conditionValue !== undefined && conditionValue !== null) ? String(conditionValue).trim() : '';

        switch (operator) {
            case 'equal':
                return strFieldVal === strCondVal;
            case 'not_equal':
                return strFieldVal !== strCondVal;
            case 'contains':
                return strFieldVal.includes(strCondVal);
            case 'not_contains':
                return !strFieldVal.includes(strCondVal);
            case 'greater_than':
                return parseFloat(strFieldVal) > parseFloat(strCondVal);
            case 'less_than':
                return parseFloat(strFieldVal) < parseFloat(strCondVal);
            default:
                return false;
        }
    }

    clearFieldError($field) {
        $field.removeClass('dragwyb-error');
        const $wrapper = $field.first().closest('.dragwyb-field-wrapper');
        if ($wrapper.length) {
            $wrapper.find('.dragwyb-error').removeClass('dragwyb-error');
            $wrapper.find('.dragwyb-field-validation-error, .dragwyb-field-error-message').remove();
        }
    }
}

// Hook into frontend form ready
jQuery(document).on('Dragwyb:frontendInit', () => {
    DragwybBuilder.Hooks.addAction('dragwyb/frontend/form_ready', (container, formId) => {
        new DragwybConditionalFields(container, formId);
    });
});
