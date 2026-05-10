import DefaultActions from './default';

// Initialize action instances
export const initActions = () => {

    const formFronentendData = window.DragwybFrontendData;

    if (!formFronentendData) return;

    const renderForms = formFronentendData?.render_forms;

    if (!renderForms || renderForms.length === 0) return;

    renderForms.forEach((formId) => {
        const formData = formFronentendData?.[`form_${formId}`];

        if (!formData) return;

        const selectedActions = formData?.actions;

        if (!selectedActions || selectedActions.length === 0) {
            return;
        }


        const actionMap = {
            'redirect': DefaultActions.RedirectAction,
            'success_message': DefaultActions.SuccessMessageAction,
            'error_message': DefaultActions.ErrorMessageAction,
        };

        selectedActions.forEach((actionName) => {
            if (actionMap[actionName]) {
                new actionMap[actionName](formId);
            }
        });
    });

};
