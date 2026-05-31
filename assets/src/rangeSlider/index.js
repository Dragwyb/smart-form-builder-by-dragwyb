import DragwybRangeSlider from './range-slider';

jQuery(document).on('Dragwyb:frontendInit', () => {
    DragwybBuilder.Hooks.addAction('dragwyb/frontend/form_ready', (container, formId) => {
        new DragwybRangeSlider(container);
    });
});

jQuery(document).on('Dragwyb:editorAppLoaded', () => {
    DragwybBuilder.Hooks.addAction('dragwyb/editorPreview/form_ready', (container, formId) => {
        new DragwybRangeSlider(container);
    });
});