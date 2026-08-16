import DragwybActionBase from '../action-base';
import MessageModal from '../../components/message-modal';

export default class SuccessMessageAction extends DragwybActionBase {
    getActionName() {
        return 'success_message';
    }

    run(actionData, response, formHandler) {
        if (!actionData) return;

        // Instantiate and show MessageModal/MessageDisplay component
        new MessageModal(actionData, 'success', formHandler);
    }
}
