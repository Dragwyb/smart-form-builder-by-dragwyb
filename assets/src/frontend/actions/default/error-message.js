import DragwybActionBase from '../action-base';

export default class ErrorMessageAction extends DragwybActionBase {
    getActionName() {
        return 'error_message';
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
            .addClass('dragwyb-error')
            .html(actionData)
            .show();
    }
}
