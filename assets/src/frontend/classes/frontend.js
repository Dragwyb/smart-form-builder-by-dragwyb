/**
 * Main Frontend Application Class
 */
class DragwybFrontend {
    constructor() {
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        this.onDocumentReady();
    }

    onDocumentReady() {
        jQuery(document).trigger('Dragwyb:frontendInit');
        this.initForms();
    }

    initForms() {
        const $forms = jQuery('.dragwyb-form-wrapper');

        $forms.each((index, element) => {
            const $element = jQuery(element);
            this.initForm($element);
        });
    }

    initForm($container) {
        let formId = $container.attr('id');


        if (!formId.startsWith('dragwyb-form-wrapper-')) {
            return;
        }

        formId = formId.substring(21);

        if (!formId || !formId.match(/^[1-9][0-9]*$/)) {
            return;
        }

        formId = parseInt(formId, 10);

        if (!$container || !$container.length) return;

        // Action when a specific form is ready
        DragwybBuilder.Hooks.doAction(`dragwyb/frontend/form_ready`, $container, formId);
    }
}

export default DragwybFrontend;
