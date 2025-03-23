(function($) {
    'use strict';

    class DragwybConditionalLogic {
        constructor(form) {
            this.form = form;
            this.formId = form.dataset.formId;
            this.rules = JSON.parse(form.dataset.conditionalRules || '{}');
            this.fields = {};
            this.dependentFields = {};
            
            this.initialize();
        }

        initialize() {
            // Initialize field references
            this.form.querySelectorAll('[data-field-id]').forEach(field => {
                const fieldId = field.dataset.fieldId;
                this.fields[fieldId] = field;
                
                // Set up event listeners
                field.addEventListener('change', () => this.handleFieldChange(fieldId));
                field.addEventListener('input', () => this.handleFieldChange(fieldId));
            });

            // Build dependency map
            this.buildDependencyMap();

            // Initial evaluation
            this.evaluateAllRules();
        }

        buildDependencyMap() {
            Object.entries(this.rules).forEach(([targetField, ruleGroups]) => {
                ruleGroups.forEach(group => {
                    group.forEach(rule => {
                        const sourceField = rule.field;
                        if (!this.dependentFields[sourceField]) {
                            this.dependentFields[sourceField] = new Set();
                        }
                        this.dependentFields[sourceField].add(targetField);
                    });
                });
            });
        }

        handleFieldChange(fieldId) {
            if (!this.dependentFields[fieldId]) {
                return;
            }

            // Evaluate rules for all dependent fields
            this.dependentFields[fieldId].forEach(targetField => {
                this.evaluateFieldRules(targetField);
            });
        }

        evaluateAllRules() {
            Object.keys(this.rules).forEach(fieldId => {
                this.evaluateFieldRules(fieldId);
            });
        }

        evaluateFieldRules(fieldId) {
            const field = this.fields[fieldId];
            if (!field) return;

            const ruleGroups = this.rules[fieldId];
            const action = field.dataset.conditionalAction || 'show';
            const result = this.evaluateRuleGroups(ruleGroups);

            // Apply visibility
            this.setFieldVisibility(field, action === 'show' ? result : !result);
        }

        evaluateRuleGroups(groups) {
            // OR logic between groups
            return groups.some(group => this.evaluateRuleGroup(group));
        }

        evaluateRuleGroup(group) {
            // AND logic within group
            return group.every(rule => this.evaluateRule(rule));
        }

        evaluateRule(rule) {
            const field = this.fields[rule.field];
            if (!field) return false;

            const fieldValue = this.getFieldValue(field);
            const compareValue = rule.value;

            switch (rule.operator) {
                case 'equals':
                    return fieldValue == compareValue;

                case 'not_equals':
                    return fieldValue != compareValue;

                case 'contains':
                    return String(fieldValue).toLowerCase()
                        .includes(String(compareValue).toLowerCase());

                case 'not_contains':
                    return !String(fieldValue).toLowerCase()
                        .includes(String(compareValue).toLowerCase());

                case 'starts_with':
                    return String(fieldValue).toLowerCase()
                        .startsWith(String(compareValue).toLowerCase());

                case 'ends_with':
                    return String(fieldValue).toLowerCase()
                        .endsWith(String(compareValue).toLowerCase());

                case 'greater_than':
                    return parseFloat(fieldValue) > parseFloat(compareValue);

                case 'less_than':
                    return parseFloat(fieldValue) < parseFloat(compareValue);

                case 'between':
                    if (!Array.isArray(compareValue)) return false;
                    const value = parseFloat(fieldValue);
                    return value >= parseFloat(compareValue[0]) && 
                           value <= parseFloat(compareValue[1]);

                case 'checked':
                    return !!fieldValue;

                case 'unchecked':
                    return !fieldValue;

                case 'is_empty':
                    return !fieldValue || fieldValue.length === 0;

                case 'is_not_empty':
                    return !!fieldValue && fieldValue.length > 0;

                default:
                    return false;
            }
        }

        getFieldValue(field) {
            const type = field.type || field.dataset.fieldType;

            switch (type) {
                case 'checkbox':
                    return field.checked;

                case 'radio':
                    const radioGroup = this.form.querySelectorAll(
                        `input[name="${field.name}"]:checked`
                    );
                    return radioGroup.length ? radioGroup[0].value : '';

                case 'select-multiple':
                    return Array.from(field.selectedOptions).map(option => option.value);

                default:
                    return field.value;
            }
        }

        setFieldVisibility(field, visible) {
            const container = field.closest('.dragwyb-field-container');
            if (!container) return;

            if (visible) {
                container.style.display = '';
                container.classList.remove('dragwyb-field-hidden');
                field.disabled = false;
            } else {
                container.style.display = 'none';
                container.classList.add('dragwyb-field-hidden');
                field.disabled = true;
            }

            // Trigger custom event
            container.dispatchEvent(new CustomEvent('dragwybFieldVisibilityChange', {
                detail: { visible, fieldId: field.dataset.fieldId }
            }));
        }
    }

    // Initialize conditional logic for all forms
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.dragwyb-form').forEach(form => {
            new DragwybConditionalLogic(form);
        });
    });

})(jQuery); 