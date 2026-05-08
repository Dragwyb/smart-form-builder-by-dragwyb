import './handler/form-frontend-base';
import DragwybFrontend from './frontend';
import DragwybFormHandler from './handler/form-handler';

jQuery(document).on('Dragwyb:frontendInit', () => {
    DragwybBuilder.Hooks.addAction('dragwyb/frontend/form_ready', (container, formId) => {
        window.dragwybFormHandler = new DragwybFormHandler(container, formId);
    });
});

jQuery(document).on('Dragwyb:init', () => {
    new DragwybFrontend();
});