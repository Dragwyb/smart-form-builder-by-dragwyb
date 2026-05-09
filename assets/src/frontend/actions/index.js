import DefaultActions from './default';

// Initialize action instances
export const initActions = () => {
    const selectedActions = window.DragwybFrontendData?.actions;

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
            new actionMap[actionName]();
        }
    });
};
