/**
 * Frontend Radio Fields Handler Class
 */
class DragwybRadioFields extends DragwybBuilder.DragwybFormFrontendBase {
    bindElements() {
        this.elements.$form = this.$container.is('form') ? this.$container : this.$container.find('form.dragwyb-form');
        this.elements.$radioInputs = this.elements.$form.find('input[type="radio"]');
    }

    init() {
        if (this.elements.$radioInputs.length === 0) {
            return;
        }

        this.initCheckedStates();
        this.bindEvents();
    }

    initCheckedStates() {
        // Apply is-checked class on initial page load / render for checked radio inputs
        this.elements.$form.find('input[type="radio"]:checked').each((_, el) => {
            jQuery(el).closest('.dragwyb-option-item').addClass('is-checked');
        });
    }

    bindEvents() {
        this.elements.$form.on('change', 'input[type="radio"]', (e) => {
            const radio = e.target;
            if (radio.name) {
                this.elements.$form.find(`input[type="radio"][name="${radio.name}"]`).closest('.dragwyb-option-item').removeClass('is-checked');
                jQuery(radio).closest('.dragwyb-option-item').addClass('is-checked');
            }
        });
    }
}

const DragwybRadioFieldHandler = (container, formId) => {
    new DragwybRadioFields(container, formId);
};

jQuery(document).on('Dragwyb:frontendInit', () => {
    DragwybBuilder.Hooks.addAction('dragwyb/frontend/form_ready', DragwybRadioFieldHandler);
});

jQuery(document).on('Dragwyb:editorAppLoaded', () => {
    DragwybBuilder.Hooks.addAction('dragwyb/editorPreview/form_ready', DragwybRadioFieldHandler);
});
