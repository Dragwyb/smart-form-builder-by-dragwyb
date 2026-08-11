/**
 * Frontend Conditional Fields logic
 */
class DragwybConditionalFields extends DragwybBuilder.DragwybFormFrontendBase {
    bindElements() {
        this.elements.$form = this.$container.is('form') ? this.$container : this.$container.find('form.dragwyb-form');
    }

    init() {
        const formId = this.getFormId();
        this.conditions = window.DragwybFrontendData && window.DragwybFrontendData[`form_${formId}`] && window.DragwybFrontendData[`form_${formId}`].conditions ? window.DragwybFrontendData[`form_${formId}`].conditions : {};

        if (!this.conditions || Object.keys(this.conditions).length === 0) {
            return;
        }
        this.bindEvents();
        this.evaluateAllConditions();
    }

    bindEvents() {
        // Listen to change and input events on all form inputs.
        // The 'input' event captures rangeSlider movements in real-time as it bubbles up.
        this.getElements('$form').on('change input', 'input, select, textarea', () => {
            this.evaluateAllConditions();
        });
    }

    evaluateAllConditions() {
        Object.entries(this.conditions).forEach(([targetFieldId, conditionsList]) => {
            if (!conditionsList || !conditionsList.length) return;

            let conditionsMet = true;
            let action = 'show'; // 'yes' = Show, 'no' = Hide

            conditionsList.forEach(condition => {
                action = condition.action;
                const fieldValue = this.getFieldValue(condition.field_id);
                const isConditionMet = this.evaluateCondition(condition.operator, fieldValue, condition.value);

                if (!isConditionMet) {
                    conditionsMet = false;
                }
            });

            // Find target element wrapper
            const $form = this.getElements('$form');
            const $targetInput = $form.find(`[name="${targetFieldId}"], [name="${targetFieldId}[]"]`);
            const $wrapper = $targetInput.first().closest('.dragwyb-field-wrapper');

            if ($wrapper.length) {
                // If action is 'yes' (Show): show if met, hide if not met
                // If action is 'no' (Hide): hide if met, show if not met
                const shouldShow = (action === 'show') ? conditionsMet : !conditionsMet;

                if (shouldShow) {
                    $wrapper.show();
                    // Restore required attribute if it was originally required
                    $targetInput.each((_, el) => {
                        const $el = jQuery(el);
                        if ($el.data('dragwyb-required') === true || $el.attr('data-dragwyb-required') === 'true') {
                            $el.prop('required', true);
                            $el.attr('required', 'required');
                        }
                    });
                } else {
                    $wrapper.hide();
                    this.clearFieldError($targetInput);
                    // Store original required attribute and remove it
                    $targetInput.each((_, el) => {
                        const $el = jQuery(el);
                        const isRequired = el.hasAttribute('required') || $el.prop('required') || $el.data('dragwyb-required') === true || $el.attr('data-dragwyb-required') === 'true';
                        if (isRequired) {
                            $el.data('dragwyb-required', true);
                            $el.attr('data-dragwyb-required', 'true');
                            $el.prop('required', false);
                            $el.removeAttr('required');
                        }
                    });
                }
            }
        });
    }

    getFieldValue(fieldId) {
        const $form = this.getElements('$form');
        let $field = $form.find(`[name="${fieldId}"], [name="${fieldId}[]"]`);
        if (!$field.length) {
            return '';
        }

        if ($field.is(':radio')) {
            return $form.find(`[name="${fieldId}"]:checked, [name="${fieldId}[]"]:checked`).val() || '';
        } else if ($field.is(':checkbox')) {
            const checkedValues = $form.find(`[name="${fieldId}"]:checked, [name="${fieldId}[]"]:checked`).map((_, el) => jQuery(el).val()).get();
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
        }
    }
}

// Hook into frontend form ready
jQuery(document).on('Dragwyb:frontendInit', () => {
    DragwybBuilder.Hooks.addAction('dragwyb/frontend/form_ready', (container, formId) => {
        new DragwybConditionalFields(container, formId);
    });
});
