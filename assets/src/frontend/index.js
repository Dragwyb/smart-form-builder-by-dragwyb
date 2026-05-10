import './handler/form-frontend-base';
import DragwybFrontend from './frontend';
import DragwybFormHandler from './handler/form-handler';
import actionBase from './actions/action-base';
import { initActions } from './actions/index';

jQuery(document).on('Dragwyb:frontendInit', () => {
    window.DragwybFrontendAction = {};
    DragwybFrontendAction.Base = actionBase;
    initActions();
    DragwybBuilder.Hooks.addAction('dragwyb/frontend/form_ready', (container, formId) => {
        new DragwybFormHandler(container, formId);
    });
});

jQuery(document).on('Dragwyb:init', () => {
    new DragwybFrontend();
});