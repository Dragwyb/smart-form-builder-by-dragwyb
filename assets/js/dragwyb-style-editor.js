class DragwybStyleEditor {
    constructor() {
        this.currentStyles = { ...dragwybStyler.defaultStyles };
        this.formId = document.getElementById('post_ID').value;
        this.initialize();
    }

    initialize() {
        this.initializeColorPickers();
        this.initializePanels();
        this.initializeThemeSelector();
        this.initializeSaveButtons();
        this.initializePreview();
        this.loadCurrentStyles();
    }

    initializeColorPickers() {
        jQuery('.color-picker').wpColorPicker({
            change: (event, ui) => {
                const input = jQuery(event.target);
                const stylePath = input.data('style');
                this.updateStyle(stylePath, ui.color.toString());
            }
        });
    }

    initializePanels() {
        const panels = document.querySelectorAll('.style-editor-sections button');
        panels.forEach(button => {
            button.addEventListener('click', () => {
                this.switchPanel(button.dataset.section);
            });
        });

        // Initialize other inputs
        document.querySelectorAll('.style-panel input:not(.color-picker)').forEach(input => {
            input.addEventListener('change', () => {
                const stylePath = input.dataset.style;
                this.updateStyle(stylePath, input.value);
            });
        });
    }

    switchPanel(section) {
        document.querySelectorAll('.style-editor-sections button').forEach(button => {
            button.classList.toggle('active', button.dataset.section === section);
        });

        document.querySelectorAll('.style-panel').forEach(panel => {
            panel.style.display = panel.dataset.panel === section ? 'block' : 'none';
        });
    }

    initializeThemeSelector() {
        const selector = document.getElementById('theme-selector');
        selector.addEventListener('change', () => {
            const themeName = selector.value;
            if (themeName) {
                this.loadTheme(themeName);
            }
        });
    }

    initializeSaveButtons() {
        document.getElementById('save-styles').addEventListener('click', () => {
            this.saveStyles();
        });

        document.getElementById('save-as-theme').addEventListener('click', () => {
            this.saveAsTheme();
        });

        document.getElementById('reset-preview').addEventListener('click', () => {
            this.resetStyles();
        });
    }

    initializePreview() {
        this.updatePreview();
    }

    loadCurrentStyles() {
        jQuery.ajax({
            url: dragwybStyler.ajaxUrl,
            type: 'POST',
            data: {
                action: 'dragwyb_get_form_styles',
                form_id: this.formId,
                nonce: dragwybStyler.nonce
            },
            success: (response) => {
                if (response.success) {
                    this.currentStyles = response.data.styles;
                    this.updateAllInputs();
                    this.updatePreview();
                }
            }
        });
    }

    loadTheme(themeName) {
        const theme = dragwybStyler.themes[themeName];
        if (theme) {
            this.currentStyles = { ...theme.styles };
            this.updateAllInputs();
            this.updatePreview();
        }
    }

    updateStyle(path, value) {
        const parts = path.split('.');
        let current = this.currentStyles;

        for (let i = 0; i < parts.length - 1; i++) {
            if (!current[parts[i]]) {
                current[parts[i]] = {};
            }
            current = current[parts[i]];
        }

        current[parts[parts.length - 1]] = value;
        this.updatePreview();
    }

    updateAllInputs() {
        document.querySelectorAll('.style-panel input').forEach(input => {
            const stylePath = input.dataset.style;
            if (!stylePath) return;

            const value = this.getStyleValue(stylePath);
            if (input.classList.contains('color-picker')) {
                jQuery(input).wpColorPicker('color', value);
            } else {
                input.value = value;
            }
        });
    }

    getStyleValue(path) {
        const parts = path.split('.');
        let value = this.currentStyles;

        for (const part of parts) {
            if (!value[part]) return '';
            value = value[part];
        }

        return value;
    }

    updatePreview() {
        const previewContainer = document.querySelector('.preview-container');
        const css = this.generatePreviewCSS();

        // Update preview styles
        let styleElement = document.getElementById('dragwyb-preview-styles');
        if (!styleElement) {
            styleElement = document.createElement('style');
            styleElement.id = 'dragwyb-preview-styles';
            document.head.appendChild(styleElement);
        }
        styleElement.textContent = css;

        // Update preview content if needed
        if (!previewContainer.querySelector('.dragwyb-form')) {
            this.loadPreviewForm();
        }
    }

    generatePreviewCSS() {
        return this.generateFormCSS(this.currentStyles);
    }

    loadPreviewForm() {
        jQuery.ajax({
            url: dragwybStyler.ajaxUrl,
            type: 'POST',
            data: {
                action: 'dragwyb_get_form_preview',
                form_id: this.formId,
                nonce: dragwybStyler.nonce
            },
            success: (response) => {
                if (response.success) {
                    document.querySelector('.preview-container').innerHTML = response.data.html;
                }
            }
        });
    }

    saveStyles() {
        jQuery.ajax({
            url: dragwybStyler.ajaxUrl,
            type: 'POST',
            data: {
                action: 'dragwyb_save_form_style',
                form_id: this.formId,
                styles: JSON.stringify(this.currentStyles),
                nonce: dragwybStyler.nonce
            },
            success: (response) => {
                if (response.success) {
                    this.showNotice('success', 'Styles saved successfully.');
                } else {
                    this.showNotice('error', response.data.message);
                }
            }
        });
    }

    saveAsTheme() {
        const themeName = prompt('Enter theme name:');
        if (!themeName) return;

        jQuery.ajax({
            url: dragwybStyler.ajaxUrl,
            type: 'POST',
            data: {
                action: 'dragwyb_save_theme',
                theme: JSON.stringify({
                    name: themeName,
                    styles: this.currentStyles
                }),
                nonce: dragwybStyler.nonce
            },
            success: (response) => {
                if (response.success) {
                    this.showNotice('success', 'Theme saved successfully.');
                    this.updateThemeSelector(themeName);
                } else {
                    this.showNotice('error', response.data.message);
                }
            }
        });
    }

    resetStyles() {
        this.currentStyles = { ...dragwybStyler.defaultStyles };
        this.updateAllInputs();
        this.updatePreview();
    }

    updateThemeSelector(newThemeName) {
        const selector = document.getElementById('theme-selector');
        const option = document.createElement('option');
        option.value = newThemeName;
        option.textContent = newThemeName;
        selector.appendChild(option);
    }

    showNotice(type, message) {
        const notice = document.createElement('div');
        notice.className = `notice notice-${type} is-dismissible`;
        notice.innerHTML = `<p>${message}</p>`;
        const wrapper = document.querySelector('.dragwyb-style-editor');
        wrapper.insertBefore(notice, wrapper.firstChild);

        // Auto-dismiss after 3 seconds
        setTimeout(() => {
            notice.remove();
        }, 3000);

        generateFormCSS(styles) {
            return `
        .dragwyb-form-preview {
            background: ${styles.form.background};
            padding: ${styles.form.padding};
            border-radius: ${styles.form.border_radius};
            border: ${styles.form.border_width} ${styles.form.border_style} ${styles.form.border_color};
            box-shadow: ${styles.form.box_shadow};
            max-width: ${styles.form.max_width};
            margin: ${styles.form.margin};
        }

        .dragwyb-form-preview .dragwyb-field {
            margin-bottom: ${styles.fields.spacing};
        }

        .dragwyb-form-preview .dragwyb-field label {
            color: ${styles.fields.label_color};
            font-size: ${styles.fields.label_font_size};
            font-weight: ${styles.fields.label_font_weight};
            margin: ${styles.fields.label_margin};
            display: block;
        }

        .dragwyb-form-preview .dragwyb-field input,
        .dragwyb-form-preview .dragwyb-field select,
        .dragwyb-form-preview .dragwyb-field textarea {
            background: ${styles.fields.input_background};
            color: ${styles.fields.input_color};
            font-size: ${styles.fields.input_font_size};
            padding: ${styles.fields.input_padding};
            border-radius: ${styles.fields.input_border_radius};
            border: ${styles.fields.input_border_width} ${styles.fields.input_border_style} ${styles.fields.input_border_color};
            box-shadow: ${styles.fields.input_box_shadow};
            width: 100%;
            transition: all ${styles.animations.transition_duration} ${styles.animations.transition_timing};
        }

        .dragwyb-form-preview .dragwyb-field input:focus,
        .dragwyb-form-preview .dragwyb-field select:focus,
        .dragwyb-form-preview .dragwyb-field textarea:focus {
            border-color: ${styles.fields.input_focus_border_color};
            box-shadow: ${styles.fields.input_focus_box_shadow};
            outline: none;
        }

        .dragwyb-form-preview .dragwyb-field input::placeholder,
        .dragwyb-form-preview .dragwyb-field select::placeholder,
        .dragwyb-form-preview .dragwyb-field textarea::placeholder {
            color: ${styles.fields.placeholder_color};
        }

        .dragwyb-form-preview .dragwyb-submit-button {
            background: ${styles.buttons.background};
            color: ${styles.buttons.color};
            font-size: ${styles.buttons.font_size};
            font-weight: ${styles.buttons.font_weight};
            padding: ${styles.buttons.padding};
            border-radius: ${styles.buttons.border_radius};
            border: ${styles.buttons.border_width} ${styles.buttons.border_style} ${styles.buttons.border_color};
            cursor: pointer;
            transition: all ${styles.animations.transition_duration} ${styles.animations.transition_timing};
        }

        .dragwyb-form-preview .dragwyb-submit-button:hover {
            background: ${styles.buttons.hover_background};
            color: ${styles.buttons.hover_color};
            border-color: ${styles.buttons.hover_border_color};
            transform: ${styles.animations.hover_transform};
        }

        .dragwyb-form-preview .dragwyb-submit-button:active {
            background: ${styles.buttons.active_background};
            border-color: ${styles.buttons.active_border_color};
            transform: ${styles.animations.active_transform};
        }

        .dragwyb-form-preview .dragwyb-error-message {
            color: ${styles.validation.error_color};
            background: ${styles.validation.error_background};
            border: 1px solid ${styles.validation.error_border_color};
            padding: ${styles.validation.error_padding};
            border-radius: ${styles.validation.error_border_radius};
            font-size: ${styles.validation.error_font_size};
            margin-top: 5px;
        }

        .dragwyb-form-preview .dragwyb-success-message {
            color: ${styles.validation.success_color};
            background: ${styles.validation.success_background};
            border: 1px solid ${styles.validation.success_border_color};
            padding: ${styles.validation.success_padding};
            border-radius: ${styles.validation.success_border_radius};
            font-size: ${styles.validation.success_font_size};
            margin-top: 5px;
        }

        @media (max-width: ${styles.responsive.breakpoint_mobile}) {
            .dragwyb-form-preview {
                padding: ${styles.responsive.mobile_padding};
            }

            .dragwyb-form-preview .dragwyb-field input,
            .dragwyb-form-preview .dragwyb-field select,
            .dragwyb-form-preview .dragwyb-field textarea {
                font-size: ${styles.responsive.mobile_font_size};
            }

            .dragwyb-form-preview .dragwyb-submit-button {
                padding: ${styles.responsive.mobile_button_padding};
            }
        }
    `;
        }

        // Initialize when document is ready
        document.addEventListener('DOMContentLoaded', () => {
            new DragwybStyleEditor();
        }); 