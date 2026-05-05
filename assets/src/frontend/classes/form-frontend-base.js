/**
 * Individual Form Handler Class
 */
class DragwybFormFrontendBase {
    constructor($container, formId) {
        this.$container = $container;
        this.formId = formId;
        this.elements = {};

        this.bindElements();
        this.init();
    }

    bindElements() { }

    init() { }

    getContainer() {
        return this.$container;
    }

    getElements(element) {
        if (element) {
            return this.elements[element] || null;
        }
        return this.elements;
    }

    getFormId() {
        return this.formId;
    }
}

DragwybBuilder.DragwybFormFrontendBase = DragwybFormFrontendBase;
