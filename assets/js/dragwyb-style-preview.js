(function($) {
    'use strict';

    const DragwybStylePreview = {
        styleSheet: null,
        previewFrame: null,

        init: function() {
            this.createPreviewFrame();
            this.bindEvents();
            this.initColorPickers();
        },

        createPreviewFrame: function() {
            // Create preview frame
            const $previewContainer = $('<div>', {
                class: 'dragwyb-style-preview-container'
            });

            const $previewFrame = $('<iframe>', {
                id: 'dragwyb-style-preview-frame',
                class: 'dragwyb-style-preview-frame'
            });

            $previewContainer.append($previewFrame);
            $('.dragwyb-style-settings').after($previewContainer);

            this.previewFrame = $previewFrame[0];
            
            // Initialize preview content
            this.previewFrame.addEventListener('load', () => {
                this.initializePreviewContent();
            });

            // Set preview frame source
            this.previewFrame.src = 'about:blank';
        },

        initializePreviewContent: function() {
            const doc = this.previewFrame.contentDocument;
            const formHtml = this.getPreviewFormHtml();

            // Write preview content
            doc.open();
            doc.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <style id="dragwyb-preview-styles"></style>
                </head>
                <body>
                    <div class="dragwyb-preview-wrapper">
                        ${formHtml}
                    </div>
                </body>
                </html>
            `);
            doc.close();

            this.styleSheet = doc.getElementById('dragwyb-preview-styles');
            this.updatePreviewStyles();
        },

        getPreviewFormHtml: function() {
            return `
                <form class="dragwyb-form dragwyb-form-preview">
                    <div class="dragwyb-field-wrapper">
                        <label>Name</label>
                        <input type="text" placeholder="Enter your name">
                    </div>
                    <div class="dragwyb-field-wrapper">
                        <label>Email</label>
                        <input type="email" placeholder="Enter your email">
                    </div>
                    <div class="dragwyb-field-wrapper">
                        <label>Message</label>
                        <textarea placeholder="Enter your message"></textarea>
                    </div>
                    <div class="dragwyb-field-wrapper">
                        <button type="submit">Submit Form</button>
                    </div>
                </form>
            `;
        },

        bindEvents: function() {
            // Handle style input changes
            $('.dragwyb-style-field input, .dragwyb-style-field select').on('input change', 
                this.debounce(this.updatePreviewStyles.bind(this), 300)
            );

            // Handle device preview switches
            $('.dragwyb-preview-device').on('click', this.switchPreviewDevice.bind(this));
        },

        initColorPickers: function() {
            if ($.fn.wpColorPicker) {
                $('.dragwyb-style-field input[type="color"]').wpColorPicker({
                    change: this.debounce(this.updatePreviewStyles.bind(this), 300)
                });
            }
        },

        updatePreviewStyles: function() {
            const styles = this.generatePreviewCSS();
            if (this.styleSheet) {
                this.styleSheet.textContent = styles;
            }
        },

        generatePreviewCSS: function() {
            const settings = this.getStyleSettings();
            let css = `
                .dragwyb-form-preview {
                    width: ${settings.form_width};
                    max-width: 100%;
                    padding: ${settings.form_padding};
                    background: ${settings.form_background};
                }

                .dragwyb-form-preview .dragwyb-field-wrapper {
                    margin: ${settings.field_margin};
                }

                .dragwyb-form-preview label {
                    display: block;
                    color: ${settings.label_color};
                    font-size: ${settings.label_font_size};
                    font-weight: ${settings.label_font_weight};
                    margin-bottom: ${settings.label_margin};
                }

                .dragwyb-form-preview input[type="text"],
                .dragwyb-form-preview input[type="email"],
                .dragwyb-form-preview textarea,
                .dragwyb-form-preview select {
                    width: 100%;
                    padding: ${settings.input_padding};
                    color: ${settings.input_color};
                    background: ${settings.input_background};
                    border: ${settings.input_border_width} solid ${settings.input_border_color};
                    border-radius: ${settings.input_border_radius};
                    font-size: ${settings.input_font_size};
                }

                .dragwyb-form-preview button[type="submit"] {
                    background: ${settings.button_background};
                    color: ${settings.button_color};
                    padding: ${settings.button_padding};
                    border: none;
                    border-radius: ${settings.button_border_radius};
                    font-size: ${settings.button_font_size};
                    cursor: pointer;
                }

                .dragwyb-form-preview button[type="submit"]:hover {
                    background: ${this.adjustColor(settings.button_background, -10)};
                }

                /* Responsive Styles */
                @media (max-width: 768px) {
                    .dragwyb-form-preview {
                        width: ${settings.tablet_form_width};
                        padding: ${settings.tablet_form_padding};
                    }

                    .dragwyb-form-preview label {
                        font-size: ${settings.tablet_label_font_size};
                    }

                    .dragwyb-form-preview input[type="text"],
                    .dragwyb-form-preview input[type="email"],
                    .dragwyb-form-preview textarea,
                    .dragwyb-form-preview select {
                        font-size: ${settings.tablet_input_font_size};
                        padding: ${settings.tablet_input_padding};
                    }
                }

                @media (max-width: 480px) {
                    .dragwyb-form-preview {
                        width: ${settings.mobile_form_width};
                        padding: ${settings.mobile_form_padding};
                    }

                    .dragwyb-form-preview label {
                        font-size: ${settings.mobile_label_font_size};
                    }

                    .dragwyb-form-preview input[type="text"],
                    .dragwyb-form-preview input[type="email"],
                    .dragwyb-form-preview textarea,
                    .dragwyb-form-preview select {
                        font-size: ${settings.mobile_input_font_size};
                        padding: ${settings.mobile_input_padding};
                    }
                }
            `;

            return css;
        },

        getStyleSettings: function() {
            const settings = {};
            $('.dragwyb-style-field input, .dragwyb-style-field select').each(function() {
                const $input = $(this);
                const name = $input.attr('name').match(/\[(.*?)\]/)[1];
                settings[name] = $input.val();
            });
            return settings;
        },

        switchPreviewDevice: function(e) {
            e.preventDefault();
            const $button = $(e.currentTarget);
            const device = $button.data('device');

            // Update active device button
            $('.dragwyb-preview-device').removeClass('active');
            $button.addClass('active');

            // Update preview frame size
            const sizes = {
                desktop: '100%',
                tablet: '768px',
                mobile: '480px'
            };

            $('.dragwyb-style-preview-frame').css('width', sizes[device]);
        },

        adjustColor: function(hex, percent) {
            // Convert hex to RGB
            let r = parseInt(hex.substring(1,3), 16);
            let g = parseInt(hex.substring(3,5), 16);
            let b = parseInt(hex.substring(5,7), 16);

            // Adjust color
            r = Math.round(r * (100 + percent) / 100);
            g = Math.round(g * (100 + percent) / 100);
            b = Math.round(b * (100 + percent) / 100);

            // Ensure values are within valid range
            r = Math.min(255, Math.max(0, r));
            g = Math.min(255, Math.max(0, g));
            b = Math.min(255, Math.max(0, b));

            // Convert back to hex
            return `#${((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1)}`;
        },

        debounce: function(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
    };

    $(document).ready(function() {
        DragwybStylePreview.init();
    });

})(jQuery); 