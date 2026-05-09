import DragwybActionBase from '../action-base';

export default class SuccessMessageAction extends DragwybActionBase {
    getActionName() {
        return 'success_message';
    }

    run(actionData, response, formHandler) {
        if (!actionData) return;

        let $message = formHandler.elements.$message;

        if (!$message || !$message.length) {
            $message = jQuery('<div class="dragwyb-form-message"></div>');
            formHandler.elements.$form.append($message);
            formHandler.elements.$message = $message;
        }

        $message
            .removeClass('dragwyb-success dragwyb-error')
            .addClass('dragwyb-success')
            .html(actionData.message)
            .show();
    }
}
