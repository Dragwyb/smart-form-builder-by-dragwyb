(function($) {
    'use strict';

    const DragwybStylePresets = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            $('#dragwyb-apply-preset').on('click', this.applyPreset.bind(this));
            $('#dragwyb-save-preset').on('click', this.savePreset.bind(this));
        },

        applyPreset: function() {
            const presetSlug = $('#dragwyb-style-preset').val();
            if (!presetSlug) return;

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dragwyb_get_preset',
                    nonce: dragwybFormBuilder.nonce,
                    preset: presetSlug
                },
                success: (response) => {
                    if (response.success) {
                        this.updateFormSettings(response.data.settings);
                        // Trigger preview update
                        $(document).trigger('dragwyb-style-updated');
                    }
                }
            });
        },

        savePreset: function() {
            const name = prompt(dragwybFormBuilder.strings.enterPresetName);
            if (!name) return;

            const settings = this.getCurrentSettings();

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'dragwyb_save_preset',
                    nonce: dragwybFormBuilder.nonce,
                    name: name,
                    settings: settings
                },
                success: (response) => {
                    if (response.success) {
                        this.addPresetOption(response.data.slug, name);
                        alert(dragwybFormBuilder.strings.presetSaved);
                    }
                }
            });
        },

        getCurrentSettings: function() {
            const settings = {};
            $('.dragwyb-style-field input, .dragwyb-style-field select').each(function() {
                const $input = $(this);
                const name = $input.attr('name').match(/\[(.*?)\]/)[1];
                settings[name] = $input.val();
            });
            return settings;
        },

        updateFormSettings: function(settings) {
            Object.entries(settings).forEach(([key, value]) => {
                const $input = $(`[name="form_style_settings[${key}]"]`);
                if ($input.length) {
                    if ($input.hasClass('wp-color-picker')) {
                        $input.wpColorPicker('color', value);
                    } else {
                        $input.val(value);
                    }
                }
            });
        },

        addPresetOption: function(slug, name) {
            const $option = $('<option>', {
                value: slug,
                text: name
            });
            $('#dragwyb-style-preset').append($option);
        }
    };

    $(document).ready(function() {
        DragwybStylePresets.init();
    });

})(jQuery); 