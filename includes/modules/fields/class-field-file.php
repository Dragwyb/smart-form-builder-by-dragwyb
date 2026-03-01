<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Field_File extends Field_Base
{
    protected function register_scripts()
    {
        return array();
    }

    protected function register_style()
    {
        return array();
    }

    protected function register_field_controls(): void
    {
        // ==============================================================
        // CONTENT TAB
        // ==============================================================

        $this->start_section('section_content_general', [
            'label' => __('General Settings', 'dragwyb-form-builder'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_control('label', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'dragwyb-form-builder'),
            'default' => __('Upload File', 'dragwyb-form-builder'),
        ]);

        // Allow user to upload more than one file
        $this->add_control('multiple', [
            'type'  => Controls::SWITCHER,
            'label' => __('Multiple Files', 'dragwyb-form-builder'),
        ]);

        // Restrict the file types users can select
        $this->add_control('accepted_types', [
            'type'        => Controls::TEXT,
            'label'       => __('Accepted Types', 'dragwyb-form-builder'),
            'placeholder' => '.jpg, .png, .pdf',
            'description' => __('Comma separated list (e.g., .jpg, .pdf).', 'dragwyb-form-builder'),
        ]);

        $this->add_control('max_size', [
            'type'        => Controls::NUMBER,
            'label'       => __('Max Size (MB)', 'dragwyb-form-builder'),
            'default'     => 5,
            'description' => __('Maximum file size allowed in megabytes.', 'dragwyb-form-builder'),
        ]);

        $this->add_control('help_text', [
            'type'        => Controls::TEXTAREA,
            'label'       => __('Help Text', 'dragwyb-form-builder'),
            'rows'        => 3,
        ]);

        $this->add_control('required', [
            'type'  => Controls::SWITCHER,
            'label' => __('Required', 'dragwyb-form-builder'),
        ]);

        $this->end_section();

        // ==============================================================
        // STYLE TAB
        // ==============================================================

        $this->start_section('section_style_label', [
            'label' => __('Label', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('label_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-field-label' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('label_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} .dragwyb-field-label',
        ]);

        $this->end_section();

        // Style the upload container area
        $this->start_section('section_style_input', [
            'label' => __('Upload Area', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('input_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-file-upload' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_group_control('input_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} .dragwyb-file-upload',
        ]);

        $this->add_control('input_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'dragwyb-form-builder'),
            'selectors'  => ['{{WRAPPER}} .dragwyb-file-upload' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_section();
    }

    protected function init(): void
    {
        $this->type = 'file';
        $this->name = __('File Upload', 'dragwyb-form-builder');
        $this->icon = 'fas fa-upload';
        $this->category = 'advanced-fields';
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id = $this->get_the_id();
        $field_id      = $this->field_key_exist($settings, 'field_id', uniqid('field_'));
        $label    = $this->field_key_exist($settings, 'label', '');
        $multiple = $this->field_key_exist($settings, 'multiple', '') === 'yes';
        $accept   = $this->field_key_exist($settings, 'accepted_types', '');
        $classes  = $this->field_key_exist($settings, 'css_classes', '');
        $help     = $this->field_key_exist($settings, 'help_text', '');

?>
        <div id="dragwyb-field-wrapper-<?php echo esc_attr($id); ?>" class="dragwyb-field-wrapper dragwyb-no-float <?php echo esc_attr($classes); ?>">
            <?php if (!empty($label)) : ?>
                <label for="<?php echo esc_attr($field_id); ?>" class="dragwyb-field-label">
                    <?php echo esc_html($label); ?>
                </label>
            <?php endif; ?>

            <div class="dragwyb-file-upload-container">
                <input
                    type="file"
                    id="<?php echo esc_attr($field_id); ?>"
                    name="<?php echo esc_attr($field_id); ?>"
                    class="dragwyb-field-input"
                    accept="<?php echo esc_attr($accept); ?>"
                    <?php echo $multiple ? 'multiple' : ''; ?>>
            </div>
            <?php if (!empty($help)) : ?>
                <div class="dragwyb-field-help"><?php echo esc_html($help); ?></div>
            <?php endif; ?>
        </div>
<?php
    }

    public function validate($value): bool
    {
        if (empty($_FILES)) {
            return !$this->settings['required']['value'];
        }

        $files = $this->get_uploaded_files();
        if (empty($files)) {
            return !$this->settings['required']['value'];
        }

        foreach ($files as $file) {
            // Check file size
            $max_size = (int) ($this->settings['max_size']['value'] ?? 2) * 1024 * 1024; // Convert MB to bytes
            if ($file['size'] > $max_size) {
                return false;
            }

            // Check file type
            $allowed_types = array_map('trim', explode(',', $this->settings['allowed_types']['value']));
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_types)) {
                return false;
            }
        }

        return true;
    }

    public function sanitize($value)
    {
        $files = $this->get_uploaded_files();
        if (empty($files)) {
            return [];
        }

        $uploaded_files = [];
        foreach ($files as $file) {
            $upload = wp_handle_upload($file, ['test_form' => false]);
            if (!empty($upload['url'])) {
                $uploaded_files[] = $upload;
            }
        }

        return $uploaded_files;
    }

    private function get_uploaded_files(): array
    {
        $files = [];
        $field_name = $this->get_field_name();

        if (!isset($_FILES[$field_name])) {
            return [];
        }

        if (is_array($_FILES[$field_name]['name'])) {
            // Multiple files
            $file_count = count($_FILES[$field_name]['name']);
            for ($i = 0; $i < $file_count; $i++) {
                $files[] = [
                    'name' => $_FILES[$field_name]['name'][$i],
                    'type' => $_FILES[$field_name]['type'][$i],
                    'tmp_name' => $_FILES[$field_name]['tmp_name'][$i],
                    'error' => $_FILES[$field_name]['error'][$i],
                    'size' => $_FILES[$field_name]['size'][$i],
                ];
            }
        } else {
            // Single file
            $files[] = $_FILES[$field_name];
        }

        return $files;
    }
}
