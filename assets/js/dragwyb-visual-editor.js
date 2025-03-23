(function($) {
    'use strict';

    const DragwybVisualEditor = {
        init: function() {
            this.bindEvents();
            this.initColorPickers();
        },

        bindEvents: function() {
            // Tab switching
            $('.dragwyb-visual-editor-tabs button').on('click', this.switchTab);

            // Style inputs
            $('.dragwyb-visual-editor-content input, .dragwyb-visual-editor-content select')
                .on('input change', this.updateStyles.bind(this));

            // Target selection
            $('#dragwyb-style-target').on('change', this.updateTarget.bind(this));
        },

        initColorPickers: function() {
            $('.dragwyb-color-picker').wpColorPicker({
                change: this.updateStyles.bind(this)
            });
        },

        switchTab: function(e) {
            const $button = $(e.currentTarget);
            const tab = $button.data('tab');

            // Update active states
            $('.dragwyb-visual-editor-tabs button').removeClass('active');
            $button.addClass('active');

            // Show selected tab
            $('.dragwyb-visual-editor-tab').removeClass('active');
            $(`.dragwyb-visual-editor-tab[data-tab="${tab}"]`).addClass('active');
        },

        updateStyles: function() {
            const target = $('#dragwyb-style-target').val();
            const styles = this.collectStyles(target);
            this.applyStyles(target, styles);
            this.updateCustomCSS(target, styles);
        },

        collectStyles: function(target) {
            const styles = {};

            // Collect spacing styles
            $('.dragwyb-spacing-inputs input').each((i, input) => {
                const $input = $(input);
                const property = $input.data('spacing');
                const value = $input.val();
                if (value) {
                    styles[property] = `${value}px`;
                }
            });

            // Collect typography styles
            $('.dragwyb-typography-control input, .dragwyb-typography-control select').each((i, input) => {
                const $input = $(input);
                const property = $input.data('typography');
                const value = $input.val();
                if (value && property === 'font-size') {
                    const unit = $('[data-typography="font-size-unit"]').val();
                    styles[property] = `${value}${unit}`;
                } else if (value) {
                    styles[property] = value;
                }
            });

            // Collect color styles
            $('.dragwyb-color-picker').each((i, input) => {
                const $input = $(input);
                const property = $input.data('color');
                const value = $input.val();
                if (value) {
                    styles[property] = value;
                }
            });

            // Collect effects styles
            $('.dragwyb-effects-control input').each((i, input) => {
                const $input = $(input);
                const property = $input.data('effects');
                const value = $input.val();
                if (value) {
                    if (property === 'border-radius') {
                        styles[property] = `${value}px`;
                    } else if (property === 'opacity') {
                        styles[property] = value / 100;
                    }
                }
            });

            // Build box-shadow
            const shadow = {
                h: $('[data-shadow="h-offset"]').val() || '0',
                v: $('[data-shadow="v-offset"]').val() || '0',
                blur: $('[data-shadow="blur"]').val() || '0',
                spread: $('[data-shadow="spread"]').val() || '0',
                color: $('[data-shadow="color"]').val() || 'rgba(0,0,0,0.1)'
            };
            if (shadow.h || shadow.v || shadow.blur || shadow.spread) {
                styles['box-shadow'] = `${shadow.h}px ${shadow.v}px ${shadow.blur}px ${shadow.spread}px ${shadow.color}`;
            }

            return styles;
        },

        applyStyles: function(target, styles) {
            let selector;
            switch (target) {
                case 'form':
                    selector = '.dragwyb-form-preview';
                    break;
                case 'labels':
                    selector = '.dragwyb-form-preview label';
                    break;
                case 'inputs':
                    selector = '.dragwyb-form-preview input, .dragwyb-form-preview textarea, .dragwyb-form-preview select';
                    break;
                case 'button':
                    selector = '.dragwyb-form-preview button[type="submit"]';
                    break;
            }

            if (selector) {
                const styleString = Object.entries(styles)
                    .map(([property, value]) => `${property}: ${value};`)
                    .join(' ');
                
                this.updatePreviewStyles(selector, styleString);
            }
        },

        updatePreviewStyles: function(selector, styles) {
            const styleId = 'dragwyb-preview-' + selector.replace(/[^a-z0-9]/g, '-');
            let $style = $('#' + styleId);
            
            if (!$style.length) {
                $style = $('<style>', { id: styleId }).appendTo('head');
            }
            
            $style.text(`${selector} { ${styles} }`);
        },

        updateCustomCSS: function(target, styles) {
            const cssString = this.generateCSS(target, styles);
            $('#dragwyb-custom-css').val(function(i, val) {
                // Update or add the styles for this target
                const regex = new RegExp(`\\/\\* ${target} styles \\*\\/[^]*?\\/\\* end ${target} styles \\*\\/`);
                const replacement = `/* ${target} styles */\n${cssString}\n/* end ${target} styles */`;
                
                if (val.match(regex)) {
                    return val.replace(regex, replacement);
                } else {
                    return val + '\n\n' + replacement;
                }
            });
        },

        generateCSS: function(target, styles) {
            let selector;
            switch (target) {
                case 'form':
                    selector = '.dragwyb-form';
                    break;
                case 'labels':
                    selector = '.dragwyb-form label';
                    break;
                case 'inputs':
                    selector = '.dragwyb-form input, .dragwyb-form textarea, .dragwyb-form select';
                    break;
                case 'button':
                    selector = '.dragwyb-form button[type="submit"]';
                    break;
            }

            return `${selector} {\n${Object.entries(styles)
                .map(([property, value]) => `    ${property}: ${value};`)
                .join('\n')}\n}`;
        }
    };

    $(document).ready(function() {
        DragwybVisualEditor.init();
    });

})(jQuery); 