export default class DragwybActionBase {
    constructor(formId) {
        this.formId = parseInt(formId);
        this.actionName = this.getActionName();
        this.init();
    }

    /**
     * Define the unique action name (e.g. 'redirect')
     */
    getActionName() {
        return '';
    }

    /**
     * Initialization method called automatically
     */
    init() {
        if (!this.actionName) {
            return;
        }

        const formData = window.DragwybFrontendData?.[`form_${this.formId}`];

        if (!formData) {
            return;
        }

        const selectedActions = formData?.actions || [];

        if (!selectedActions || selectedActions.length === 0) {
            return;
        }

        // Register action filter/hook specifically for this action type
        DragwybBuilder.Hooks.addAction(`dragwyb/frontend/action/${this.actionName}/${this.formId}`, (actionData, response, formHandler) => {
            this.run(actionData, response, formHandler);
        });
    }

    /**
     * Core execution method to override in subclasses
     * 
     * @param {Object} actionData  Settings/Options for this specific action returned from backend
     * @param {Object} response    The full JSON response object from the server
     * @param {Object} formHandler The Form Handler instance containing the form element references
     */
    run(actionData, response, formHandler) {
        // Implement logic in child class
    }
}
