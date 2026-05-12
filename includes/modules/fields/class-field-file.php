<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_File extends Field_Base
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function init(): void
    {
        $this->type = 'file';
        $this->name = __('File Upload', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-upload';
        $this->category = 'advanced-fields';
        $this->keywords = array('upload', 'document', 'image', 'attachment');
    }

    protected function register_field_controls(): void
    {
        $this->start_section('section_content_general', [
            'label' => __('Basic Settings', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_control('label', [
            'type'    => Controls::TEXT,
            'label'   => __('Field Label', 'smart-form-builder-by-dragwyb'),
            'default' => __('Upload File', 'smart-form-builder-by-dragwyb'),
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('help_text', [
            'type'        => Controls::TEXTAREA,
            'label'       => __('Instructional Text', 'smart-form-builder-by-dragwyb'),
            'rows'        => 3,
            'description' => __('A short hint displayed below the input.', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->end_section();

        $this->start_section('section_content_validation', [
            'label' => __('Validation Rules', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_control('required', [
            'type'         => Controls::SWITCHER,
            'label'        => __('Is Required?', 'smart-form-builder-by-dragwyb'),
            'return_value' => 'yes',
            'default'      => 'no',
        ]);

        $this->add_control('free_limit_notice', [
            'type' => Controls::RAW_HTML,
            'raw'  => '<div style="background-color: #f0f9ff; border: 1px solid #bae6fd; padding: 10px; border-radius: 4px; font-size: 12px; margin-top: 10px;">' .
                '<strong>Free Version Limits:</strong><br/>' .
                '- Max File Size: 2MB<br/>' .
                '- Allowed Extensions: jpg, jpeg, png, pdf, doc, docx, txt<br/>' .
                '- Max Files: 1' .
                '</div>',
        ]);

        $this->end_section();

        // Label Style
        $this->start_section('section_style_label', [
            'label' => __('Label Appearance', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('label_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-label-color: {{VALUE}};'],
        ]);

        $this->add_control('label_spacing', [
            'type'      => Controls::SLIDER,
            'label'     => __('Bottom Margin', 'smart-form-builder-by-dragwyb'),
            'range'     => ['px' => ['min' => 0, 'max' => 50]],
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-label-spacing: {{VALUE}}{{UNIT}};'],
        ]);

        $this->end_section();

        $this->start_section('section_style_input', [
            'label' => __('Input Box Style', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->start_tabs('tabs_input_style');

        $this->start_tab('tab_input_normal', ['label' => __('Normal', 'smart-form-builder-by-dragwyb')]);

        $this->add_control('input_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-input-bg: {{VALUE}};'],
        ]);

        $this->add_control('input_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-input-color: {{VALUE}};'],
        ]);

        $this->add_group_control('input_border', [
            'type'     => Controls::GROUP_BORDER,
            'selector' => '{{WRAPPER}}',
            'prefix'   => 'input',
        ]);

        $this->add_control('input_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Inner Padding', 'smart-form-builder-by-dragwyb'),
            'size_units' => ['px', 'em', '%'],
            'selectors'  => ['{{WRAPPER}}' => '--dragwyb-input-pt: {{TOP}}{{UNIT}}; --dragwyb-input-pr: {{RIGHT}}{{UNIT}}; --dragwyb-input-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-input-pl: {{LEFT}}{{UNIT}};'],
            'separator'  => 'before',
        ]);

        $this->end_tab();

        $this->start_tab('tab_input_focus', ['label' => __('Focus', 'smart-form-builder-by-dragwyb')]);

        $this->add_control('input_focus_border_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Active Border Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-input-focus-border: {{VALUE}};'],
        ]);

        $this->end_tab();

        $this->end_tabs();

        $this->end_section();
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id = $this->get_the_id();
        $field_id = $this->field_key_exist($settings, 'field_id', uniqid('field_'));
        $label    = $this->field_key_exist($settings, 'label', 'Upload File');
        $help     = $this->field_key_exist($settings, 'help_text', '');
        $required = $this->field_key_exist($settings, 'required', '') === 'yes';
        $classes  = $this->field_key_exist($settings, 'css_classes', '');
?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?>">
            <div class="dragwyb-input-group dragwyb-file-upload-group">
                <?php if (!empty($label)) : ?>
                    <label for="<?php echo esc_attr($field_id); ?>" class="dragwyb-field-label">
                        <?php echo esc_html($label); ?>
                        <?php if ($required) : ?><span class="dragwyb-required">*</span><?php endif; ?>
                    </label>
                <?php endif; ?>
                <input
                    type="file"
                    id="<?php echo esc_attr($field_id); ?>"
                    name="<?php echo esc_attr($field_id); ?>"
                    class="dragwyb-field-input dragwyb-file-input"
                    accept=".jpg,.jpeg,.png,.pdf,.doc,.docx,.txt"
                    <?php echo $required ? 'required' : ''; ?> />
            </div>
            <?php if (!empty($help)) : ?>
                <div class="dragwyb-field-help"><?php echo wp_kses_post($help); ?></div>
            <?php endif; ?>
        </div>
<?php
    }

    public function validate($value, $field_id, $form_config, Form_Submission_Handler $error_handler): void
    {
        if (!isset($form_config['fields'][$field_id])) {
            $error_handler->add_error($field_id, __('Invalid field.', 'smart-form-builder-by-dragwyb'));
            return;
        }
        $field_attr = isset($form_config['fields'][$field_id]['attributes']) ? $form_config['fields'][$field_id]['attributes'] : array();

        // Check if file was uploaded
        $file_exists = isset($_FILES[$field_id]) && $_FILES[$field_id]['error'] !== UPLOAD_ERR_NO_FILE;

        if (!$file_exists && (isset($field_attr['required']) && $field_attr['required'] == 'yes')) {
            $error_handler->add_error($field_id, __('This field is required.', 'smart-form-builder-by-dragwyb'));
            return;
        }

        if (!$file_exists) {
            return;
        }

        $file = $_FILES[$field_id];

        // 1. Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_handler->add_error($field_id, __('Error uploading file.', 'smart-form-builder-by-dragwyb'));
            return;
        }

        // 2. Max File Size: 1MB (1048576 bytes)
        $max_size = 1 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            $error_handler->add_error($field_id, __('File size exceeds the 1MB limit for the free version.', 'smart-form-builder-by-dragwyb'));
            return;
        }

        // 3. Allowed Extensions
        $allowed_exts = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'txt'];
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_exts, true)) {
            $error_handler->add_error($field_id, __('Invalid file type. Allowed types: jpg, jpeg, png, pdf, doc, docx, txt.', 'smart-form-builder-by-dragwyb'));
            return;
        }

        // Mime type check for added security
        $mime_type = mime_content_type($file['tmp_name']);
        $allowed_mimes = [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'text/plain'
        ];

        if (!in_array($mime_type, $allowed_mimes, true)) {
            $error_handler->add_error($field_id, __('Invalid file mime type.', 'smart-form-builder-by-dragwyb'));
            return;
        }
    }

    public function sanitize($default = '', $value = null)
    {
        // For files, the sanitization and actual saving/moving usually happen during a dedicated submission step
        // Returning the file path or name or an empty string for now
        if (isset($value['name'])) {
            return sanitize_file_name($value['name']);
        }
        return '';
    }
}
