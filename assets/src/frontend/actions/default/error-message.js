import DragwybActionBase from '../action-base';
import MessageModal from '../../components/message-modal';

export default class ErrorMessageAction extends DragwybActionBase {
    getActionName() {
        return 'error_message';
    }

    run(actionData, response, formHandler) {
        if (!actionData) return;

        // Instantiate and show the new MessageModal component
        new MessageModal(actionData, 'error');
    }
}
