import DragwybActionBase from '../action-base';

export default class RedirectAction extends DragwybActionBase {
    getActionName() {
        return 'redirect';
    }

    run(actionData, response, formHandler) {
        if (!actionData) return;

        if (typeof actionData === 'string' && actionData.trim() !== '') {
            window.location.href = actionData;
        }
    }
}
