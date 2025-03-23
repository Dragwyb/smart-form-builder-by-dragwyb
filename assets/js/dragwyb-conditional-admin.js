class DragwybConditionalAdmin {
    constructor() {
        this.ruleTemplate = this.createRuleTemplate();
        this.groupTemplate = this.createGroupTemplate();
        
        this.initialize();
    }

    initialize() {
        // Add rule group button
        document.getElementById('add-rule-group')
            .addEventListener('click', () => this.addRuleGroup());

        // Initialize existing rule groups
        document.querySelectorAll('.dragwyb-rule-group').forEach(group => {
            this.initializeRuleGroup(group);
        });

        // Initialize sortable
        this.initializeSortable();
    }

    createRuleTemplate() {
        return `
            <div class="dragwyb-rule">
                <select class="rule-field">
                    ${this.getFieldOptions()}
                </select>
                <select class="rule-operator">
                    ${this.getOperatorOptions()}
                </select>
                <div class="rule-value-container">
                    <input type="text" class="rule-value">
                </div>
                <button type="button" class="remove-rule">
                    ${dragwybConditionalAdmin.i18n.remove}
                </button>
            </div>
        `;
    }

    createGroupTemplate() {
        return `
            <div class="dragwyb-rule-group">
                <div class="group-header">
                    <span class="group-title">IF</span>
                    <button type="button" class="remove-group">
                        ${dragwybConditionalAdmin.i18n.remove}
                    </button>
                </div>
                <div class="group-rules"></div>
                <button type="button" class="add-rule">
                    ${dragwybConditionalAdmin.i18n.addRule}
                </button>
                <div class="group-operator">
                    ${dragwybConditionalAdmin.i18n.or}
                </div>
            </div>
        `;
    }

    getFieldOptions() {
        const fields = this.getFormFields();
        return fields.map(field => `
            <option value="${field.id}">${field.label}</option>
        `).join('');
    }

    getOperatorOptions(fieldType = 'text') {
        const operators = dragwybConditionalAdmin.fieldTypes[fieldType]?.operators || [];
        return operators.map(operator => `
            <option value="${operator}">${this.getOperatorLabel(operator)}</option>
        `).join('');
    }

    getOperatorLabel(operator) {
        const labels = {
            equals: 'Equals',
            not_equals: 'Does not equal',
            contains: 'Contains',
            not_contains: 'Does not contain',
            starts_with: 'Starts with',
            ends_with: 'Ends with',
            greater_than: 'Greater than',
            less_than: 'Less than',
            between: 'Between',
            checked: 'Is checked',
            unchecked: 'Is unchecked',
            is_empty: 'Is empty',
            is_not_empty: 'Is not empty'
        };
        return labels[operator] || operator;
    }

    addRuleGroup() {
        const container = document.querySelector('.dragwyb-conditional-rules');
        const group = this.createElementFromHTML(this.groupTemplate);
        container.appendChild(group);
        this.initializeRuleGroup(group);
    }

    initializeRuleGroup(group) {
        // Add rule button
        group.querySelector('.add-rule').addEventListener('click', () => {
            const rule = this.createElementFromHTML(this.ruleTemplate);
            group.querySelector('.group-rules').appendChild(rule);
            this.initializeRule(rule);
        });

        // Remove group button
        group.querySelector('.remove-group').addEventListener('click', () => {
            group.remove();
        });

        // Initialize existing rules
        group.querySelectorAll('.dragwyb-rule').forEach(rule => {
            this.initializeRule(rule);
        });
    }

    initializeRule(rule) {
        // Field change handler
        rule.querySelector('.rule-field').addEventListener('change', (e) => {
            const fieldType = this.getFieldType(e.target.value);
            this.updateOperators(rule, fieldType);
            this.updateValueField(rule, fieldType);
        });

        // Operator change handler
        rule.querySelector('.rule-operator').addEventListener('change', (e) => {
            const fieldType = this.getFieldType(
                rule.querySelector('.rule-field').value
            );
            this.updateValueField(rule, fieldType, e.target.value);
        });

        // Remove rule button
        rule.querySelector('.remove-rule').addEventListener('click', () => {
            rule.remove();
        });
    }

    updateOperators(rule, fieldType) {
        const operatorSelect = rule.querySelector('.rule-operator');
        operatorSelect.innerHTML = this.getOperatorOptions(fieldType);
    }

    updateValueField(rule, fieldType, operator) {
        const container = rule.querySelector('.rule-value-container');
        const currentValue = this.getValueFieldValue(container);

        container.innerHTML = this.getValueFieldHTML(fieldType, operator);
        this.setValueFieldValue(container, currentValue);
    }

    getValueFieldHTML(fieldType, operator) {
        switch (fieldType) {
            case 'select':
            case 'radio':
                return this.getOptionsFieldHTML(fieldType);

            case 'checkbox':
                return operator === 'contains' ? 
                    this.getOptionsFieldHTML(fieldType) : '';

            case 'number':
                return operator === 'between' ?
                    this.getRangeFieldHTML() :
                    '<input type="number" class="rule-value">';

            case 'date':
                return operator === 'between' ?
                    this.getDateRangeFieldHTML() :
                    '<input type="date" class="rule-value">';

            default:
                return '<input type="text" class="rule-value">';
        }
    }

    getOptionsFieldHTML(fieldType) {
        const field = document.querySelector(`[data-field-type="${fieldType}"]`);
        if (!field) return '<input type="text" class="rule-value">';

        return `
            <select class="rule-value">
                ${Array.from(field.options).map(option => `
                    <option value="${option.value}">${option.text}</option>
                `).join('')}
            </select>
        `;
    }

    getRangeFieldHTML() {
        return `
            <div class="rule-value-range">
                <input type="number" class="rule-value-min">
                <span class="range-separator">to</span>
                <input type="number" class="rule-value-max">
            </div>
        `;
    }

    getDateRangeFieldHTML() {
        return `
            <div class="rule-value-range">
                <input type="date" class="rule-value-min">
                <span class="range-separator">to</span>
                <input type="date" class="rule-value-max">
            </div>
        `;
    }

    getValueFieldValue(container) {
        const rangeMin = container.querySelector('.rule-value-min');
        const rangeMax = container.querySelector('.rule-value-max');
        
        if (rangeMin && rangeMax) {
            return [rangeMin.value, rangeMax.value];
        }

        const input = container.querySelector('.rule-value');
        return input ? input.value : '';
    }

    setValueFieldValue(container, value) {
        const rangeMin = container.querySelector('.rule-value-min');
        const rangeMax = container.querySelector('.rule-value-max');
        
        if (rangeMin && rangeMax && Array.isArray(value)) {
            rangeMin.value = value[0] || '';
            rangeMax.value = value[1] || '';
            return;
        }

        const input = container.querySelector('.rule-value');
        if (input) {
            input.value = value || '';
        }
    }

    initializeSortable() {
        jQuery('.dragwyb-conditional-rules').sortable({
            items: '.dragwyb-rule-group',
            handle: '.group-header',
            axis: 'y',
            opacity: 0.7,
        });

        jQuery('.group-rules').sortable({
            items: '.dragwyb-rule',
            axis: 'y',
            opacity: 0.7,
        });
    }

    createElementFromHTML(html) {
        const div = document.createElement('div');
        div.innerHTML = html.trim();
        return div.firstChild;
    }

    getFormFields() {
        // Implementation depends on how form fields are stored
        return window.dragwybForm?.fields || [];
    }

    getFieldType(fieldId) {
        const field = this.getFormFields().find(f => f.id === fieldId);
        return field?.type || 'text';
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', () => {
    new DragwybConditionalAdmin();
}); 