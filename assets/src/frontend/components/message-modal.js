export default class MessageModal {
    constructor(data, type = 'success') {
        this.data = data;
        this.type = type;

        // Handle case where data is a simple string (fallback for old logic)
        if (typeof data === 'string') {
            this.data = {
                title: type === 'success' ? 'Success!' : 'Error',
                message: data,
                icon: type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>',
                bg_color: '#ffffff',
                text_color: type === 'success' ? '#15803d' : '#b91c1c'
            };
        }

        this.init();
    }

    init() {
        this.buildModal();
        this.bindEvents();
        this.show();
    }

    buildModal() {
        // Remove existing if any to avoid stacking
        jQuery('.dragwyb-message-modal-overlay').remove();

        this.$overlay = jQuery('<div class="dragwyb-message-modal-overlay"></div>');

        const $modal = jQuery('<div class="dragwyb-message-modal"></div>');
        $modal.css({
            'background-color': this.data.bg_color,
        });

        const $closeBtn = jQuery('<button type="button" class="dragwyb-message-modal-close" aria-label="Close modal">&times;</button>');

        const $icon = jQuery(`<div class="dragwyb-message-modal-icon" style="color: ${this.data.text_color};">${this.data.icon}</div>`);
        const $title = jQuery(`<h3 class="dragwyb-message-modal-title" style="color: ${this.data.text_color};">${this.data.title}</h3>`);
        const $message = jQuery(`<div class="dragwyb-message-modal-text" style="color: ${this.data.text_color};">${this.data.message}</div>`);

        $modal.append($closeBtn, $icon, $title, $message);
        this.$overlay.append($modal);

        jQuery('body').append(this.$overlay);
    }

    bindEvents() {
        this.$overlay.on('click', '.dragwyb-message-modal-close', (e) => {
            e.preventDefault();
            this.close();
        });

        // Close on overlay click
        this.$overlay.on('click', (e) => {
            if (e.target === this.$overlay[0]) {
                this.close();
            }
        });

        // Close on ESC key
        jQuery(document).on('keydown.dragwybMessageModal', (e) => {
            if (e.key === 'Escape') {
                this.close();
            }
        });
    }

    show() {
        // Trigger reflow to allow CSS transition
        this.$overlay[0].offsetHeight;
        this.$overlay.addClass('dragwyb-message-modal-show');
    }

    close() {
        this.$overlay.removeClass('dragwyb-message-modal-show');

        // Remove from DOM after transition
        setTimeout(() => {
            this.$overlay.remove();
            jQuery(document).off('keydown.dragwybMessageModal');
        }, 300); // match css transition duration
    }
}
