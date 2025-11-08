(function($) {
    'use strict';

    const DragwybFormBuilder = {
        init: function() {
            this.bindEvents();
            this.initSortable();
            this.loadExistingFields();
        },

        bindEvents: function() {
            // Field type buttons
            $('.dragwyb-add-field').on('click', this.addField.bind(this));

            // Field actions
            $(document).on('click', '.dragwyb-field-settings', this.toggleFieldSettings);
            $(document).on('click', '.dragwyb-field-remove', this.removeField);

            // Form actions
            $('.dragwyb-save-form').on('click', this.saveForm.bind(this));
            $('.dragwyb-preview-form').on('click', this.previewForm.bind(this));

            // Modal close
            $('.dragwyb-modal-close').on('click', this.closePreview);
        },

        initSortable: function() {
            $('#dragwyb-form-fields').sortable({
                placeholder: 'dragwyb-drag-placeholder',
                handle: '.dragwyb-field-header',
                cursor: 'move',
                start: function(e, ui) {
                    ui.item.addClass('dragwyb-dragging');
                },
                stop: function(e, ui) {
                    ui.item.removeClass('dragwyb-dragging');
                }
            });
        },

        addField: function(e) {
            const $button = $(e.currentTarget);
            const fieldType = $button.data('type');
            const fieldTemplate = this.getFieldTemplate(fieldType);
            
            const $field = $(fieldTemplate({
                type: fieldType,
                label: this.getDefaultLabel(fieldType),
                required: false,
                placeholder: ''
            }));

            $('#dragwyb-form-fields').append($field);
            $('.dragwyb-form-empty-state').addClass('hidden');
        },

        getFieldTemplate: function(type) {
            return wp.template('dragwyb-field-' + type);
        },

        getDefaultLabel: function(type) {
            const labels = {
                text: 'Text',
                textarea: 'Paragraph Field',
                email: 'Email Field',
                number: 'Number Field',
                radio: 'Radio Buttons',
                checkbox: 'Checkboxes',
                select: 'Dropdown'
            };
            return labels[type] || 'New Field';
        },

        toggleFieldSettings: function(e) {
            const $field = $(e.currentTarget).closest('.dragwyb-field');
            $field.find('.dragwyb-field-settings-panel').slideToggle();
        },

        removeField: function(e) {
            if (confirm(dragwybFormBuilder.strings.confirmDelete)) {
                const $field = $(e.currentTarget).closest('.dragwyb-field');
                $field.remove();

                if ($('#dragwyb-form-fields').children().length === 0) {
                    $('.dragwyb-form-empty-state').removeClass('hidden');
                }
            }
        },

        saveForm: function() {
            const formData = this.getFormData();
            
            $.ajax({
                url: c.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dragwyb_save_form',
                    nonce: dragwybFormBuilder.nonce,
                    post_id: $('#post_ID').val(),
                    form_data: JSON.stringify(formData)
                },
                beforeSend: function() {
                    $('.dragwyb-save-form').prop('disabled', true)
                        .text(dragwybFormBuilder.strings.savingForm);
                },
                success: function(response) {
                    if (response.success) {
                        alert(dragwybFormBuilder.strings.formSaved);
                    } else {
                        alert(dragwybFormBuilder.strings.error);
                    }
                },
                error: function() {
                    alert(dragwybFormBuilder.strings.error);
                },
                complete: function() {
                    $('.dragwyb-save-form').prop('disabled', false)
                        .text('Save Form');
                }
            });
        },

        getFormData: function() {
            const fields = [];
            
            $('#dragwyb-form-fields .dragwyb-field').each(function() {
                const $field = $(this);
                fields.push({
                    type: $field.data('type'),
                    label: $field.find('.dragwyb-field-label').val(),
                    required: $field.find('.dragwyb-field-required').is(':checked'),
                    placeholder: $field.find('.dragwyb-field-placeholder').val(),
                    options: $field.find('.dragwyb-field-options').val()
                });
            });

            return {
                fields: fields
            };
        },

        previewForm: function() {
            const formData = this.getFormData();
            
            $.ajax({
                url: dragwybFormBuilder.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dragwyb_preview_form',
                    nonce: dragwybFormBuilder.nonce,
                    form_data: JSON.stringify(formData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#dragwyb-preview-frame').attr('src', response.data.preview_url);
                        $('#dragwyb-preview-modal').show();
                    }
                }
            });
        },

        closePreview: function() {
            $('#dragwyb-preview-modal').hide();
        },

        loadExistingFields: function() {
            // Load existing fields from hidden input if available
            const $existingData = $('#dragwyb-form-data');
            if ($existingData.length) {
                try {
                    const formData = JSON.parse($existingData.val());
                    if (formData.fields && formData.fields.length) {
                        formData.fields.forEach(field => {
                            const template = this.getFieldTemplate(field.type);
                            const $field = $(template(field));
                            $('#dragwyb-form-fields').append($field);
                        });
                        $('.dragwyb-form-empty-state').addClass('hidden');
                    }
                } catch (e) {
                    console.error('Error loading existing fields:', e);
                }
            }
        },

        getFieldSettings: function(fieldType) {
            return $.ajax({
                url: dragwybFormBuilder.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'dragwyb_get_field_settings',
                    nonce: dragwybFormBuilder.nonce,
                    field_type: fieldType
                }
            });
        },

        renderFieldSettings: function($field, settings) {
            const $settingsPanel = $field.find('.dragwyb-field-settings-panel');
            $settingsPanel.empty();

            Object.entries(settings).forEach(([key, setting]) => {
                const $setting = this.createSettingField(key, setting);
                $settingsPanel.append($setting);
            });
        },

        createSettingField: function(key, setting) {
            const $wrapper = $('<div>', {
                class: 'dragwyb-field-setting'
            });

            const $label = $('<label>', {
                for: `setting-${key}`,
                text: setting.label
            });

            let $input;

            switch (setting.type) {
                case 'text':
                    $input = $('<input>', {
                        type: 'text',
                        id: `setting-${key}`,
                        class: 'dragwyb-setting-input',
                        'data-setting': key,
                        value: setting.default
                    });
                    break;

                case 'checkbox':
                    $input = $('<input>', {
                        type: 'checkbox',
                        id: `setting-${key}`,
                        class: 'dragwyb-setting-input',
                        'data-setting': key,
                        checked: setting.default
                    });
                    break;

                case 'number':
                    $input = $('<input>', {
                        type: 'number',
                        id: `setting-${key}`,
                        class: 'dragwyb-setting-input',
                        'data-setting': key,
                        value: setting.default
                    });
                    break;
            }

            $wrapper.append($label, $input);
            return $wrapper;
        }
    };

    $(document).ready(function() {
        DragwybFormBuilder.init();
    });

})(jQuery); 