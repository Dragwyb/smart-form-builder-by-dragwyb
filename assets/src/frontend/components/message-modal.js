export default class MessageModal {
    constructor(data, type = 'success', formHandler = null) {
        this.data = data;
        this.type = type;
        this.formHandler = formHandler;

        // Handle case where data is a simple string (fallback for old logic)
        if (typeof data === 'string') {
            this.data = {
                title: type === 'success' ? 'Success!' : 'Error',
                message: data,
                icon: type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>',
                bg_color: '#ffffff',
                text_color: type === 'success' ? '#15803d' : '#b91c1c',
                position: 'form_bottom'
            };
        }

        this.init();
    }

    init() {
        const position = this.data?.position || 'form_bottom';

        if (position === 'form_place') {
            this.showInFormPlace();
        } else if (position === 'modal') {
            this.showModal();
        } else {
            this.showInFormBottom();
        }
    }

    createInlineBox() {
        const bgColor = this.data.bg_color || '#ffffff';
        const textColor = this.data.text_color || (this.type === 'success' ? '#15803d' : '#b91c1c');

        const $box = jQuery('<div class="dragwyb-inline-message-box"></div>');
        $box.css({
            'background-color': bgColor,
            'border-left': `5px solid ${textColor}`,
            'border-radius': '0',
            'padding': '20px 24px',
            'margin': '15px 0',
            'box-shadow': '0 4px 16px rgba(0, 0, 0, 0.08)',
            'display': 'none'
        });

        const $content = jQuery('<div class="dragwyb-inline-message-content"></div>');
        const $icon = jQuery(`<div class="dragwyb-inline-message-icon" style="color: ${textColor};">${this.data.icon || ''}</div>`);
        const $body = jQuery('<div class="dragwyb-inline-message-body"></div>');

        if (this.data.title) {
            const $title = jQuery(`<h4 class="dragwyb-inline-message-title" style="color: ${textColor};">${this.data.title}</h4>`);
            $body.append($title);
        }

        if (this.data.message) {
            const $text = jQuery(`<div class="dragwyb-inline-message-text" style="color: ${textColor};">${this.data.message}</div>`);
            $body.append($text);
        }

        $content.append($icon, $body);
        $box.append($content);
        return $box;
    }

    createBottomBox() {
        const bgColor = this.data.bg_color || '#ffffff';
        const textColor = this.data.text_color || (this.type === 'success' ? '#15803d' : '#b91c1c');

        const $box = jQuery('<div class="dragwyb-bottom-message-box dragwyb-inline-message-box"></div>');
        const $content = jQuery('<div class="dragwyb-inline-message-content" style="display: flex; align-items: center; gap: 12px;"></div>');

        if (this.data.icon) {
            const $icon = jQuery(`<div class="dragwyb-inline-message-icon" style="font-size: 20px; color: ${textColor}; line-height: 1; flex-shrink: 0;">${this.data.icon}</div>`);
            $content.append($icon);
        }

        if (this.data.message) {
            const $text = jQuery(`<div class="dragwyb-inline-message-text" style="font-size: 14px; font-weight: 500; line-height: 1.4; color: ${textColor}; margin: 0;">${this.data.message}</div>`);
            $content.append($text);
        }

        $box.append($content);
        return $box;
    }

    showInFormPlace() {
        const $form = this.formHandler?.elements?.$form?.closest('.dragwyb-form-wrapper');
        if (!$form || !$form.length) {
            this.showModal();
            return;
        }

        const $box = this.createInlineBox();

        // Remove any existing inline messages in container
        $form.parent().find('.dragwyb-inline-message-box').remove();

        $form.after($box);
        $form.slideUp(400, () => {
            $box.slideDown(400);
        });
    }

    showInFormBottom() {
        const $form = this.formHandler?.elements?.$form;
        if (!$form || !$form.length) {
            this.showModal();
            return;
        }

        const $box = this.createBottomBox();

        // Remove any existing inline/bottom messages inside form container
        $form.find('.dragwyb-inline-message-box, .dragwyb-bottom-message-box').remove();

        $form.append($box);
        $box.slideDown(300);
    }

    showModal() {
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
        if (this.$overlay && this.$overlay[0]) {
            this.$overlay[0].offsetHeight;
            this.$overlay.addClass('dragwyb-message-modal-show');
        }
    }

    close() {
        if (!this.$overlay) return;
        this.$overlay.removeClass('dragwyb-message-modal-show');

        // Remove from DOM after transition
        setTimeout(() => {
            this.$overlay.remove();
            jQuery(document).off('keydown.dragwybMessageModal');
        }, 300); // match css transition duration
    }
}
