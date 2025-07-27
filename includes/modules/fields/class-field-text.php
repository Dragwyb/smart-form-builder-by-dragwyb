<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Field_Text extends Field_Base
{

    protected function register_scripts()
    {
        $scripts = array();

        if (defined('DRAGWYB_EDITOR')) {
            $scripts = array('dragwyb_editor_fields');
        }

        return $scripts;
    }

    protected function register_style()
    {
        return array();
    }

    public function __construct()
    {
        parent::__construct();
        wp_register_script('dragwyb_editor_fields', DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editorFields/editorFields.js', array(), DRAGWYB_FORM_BUILDER_VERSION, true);
    }

    protected function init(): void
    {
        $this->type = 'text';
        $this->name = __('Text Field', 'dragwyb-form-builder');
        $this->icon = 'dashicons-text';
    }

    protected function register_controls(): void
    {
        $this->start_section('text_form_settings', [
            'label' => 'Form Settings',
            'tab' => self::ContentTab
        ]);

        $this->start_tabs('text_tabs');

        $this->start_tab('text_normal', [
            'label' => 'Normal',
        ]);

        $this->add_control('text_color', [
            'type' => Controls::COLOR,
            'label' => __('Field Label Color', 'dragwyb-form-builder'),
            'default' => '',
        ]);

        $this->end_tab();

        $this->start_tab('text_hover', [
            'label' => 'Hover',
        ]);

        $this->add_control('text_hover_color', [
            'type' => Controls::COLOR,
            'label' => __('Field Label Color', 'dragwyb-form-builder'),
            'default' => '',
        ]);

        $this->end_tab();

        $this->end_tabs();

        $this->add_control('text_label', [
            'type' => Controls::TEXT,
            'label' => __('Field Label', 'dragwyb-form-builder'),
            'default' => 'Enter Your Label',
        ]);
        
        $this->add_control('text_placeholder', [
            'type' => Controls::TEXT,
            'label' => __('Placeholder', 'dragwyb-form-builder'),
            'default' => '',
            'conditions' => [
                'text_label' => 'aniket',
            ]
        ]);
        $this->add_control('text_required', [
            'type' => Controls::CHECKBOX,
            'label' => __('Required', 'dragwyb-form-builder'),
            'default' => false,
        ]);
        $this->add_control('text_css_class', [
            'type' => Controls::TEXT,
            'label' => __('CSS Class', 'dragwyb-form-builder'),
            'default' => '',
        ]);

        $this->end_section();

        $this->start_section('text_form_style', [
            'label' => 'Form Style',
            'tab' => self::ContentTab
        ]);
        $this->add_control('text_style', [
            'type' => Controls::TEXT,
            'label' => __('CSS Class', 'dragwyb-form-builder'),
            'default' => '',
        ]);
        $this->end_section();

        $this->start_section('text_form_style_tab', [
            'label' => 'Form Style',
            'tab' => self::StyleTab
        ]);
        $this->add_control('text_style_tab', [
            'type' => Controls::TEXT,
            'label' => __('CSS Class', 'dragwyb-form-builder'),
            'default' => '',
        ]);
        $this->end_section();
        $this->start_section('text_form_style_tab_two', [
            'label' => 'Form Style',
            'tab' => self::StyleTab
        ]);
        $this->add_control('text_style_tab_two', [
            'type' => Controls::TEXT,
            'label' => __('CSS Class', 'dragwyb-form-builder'),
            'default' => '',
        ]);
        $this->end_section();
    }

    public function render_frontend(array $field_data): string
    {
        $id = 'field_' . uniqid();
        $required = !empty($field_data['required']);

        ob_start();
?>
        <div class="dragwyb-field-wrapper <?php echo esc_attr($field_data['css_class'] ?? ''); ?>">
            <label for="<?php echo esc_attr($id); ?>">
                <?php echo esc_html($field_data['label']); ?>
                <?php if ($required): ?>
                    <span class="required">*</span>
                <?php endif; ?>
            </label>
            <input type="text"
                id="<?php echo esc_attr($id); ?>"
                name="<?php echo esc_attr($id); ?>"
                value="<?php echo esc_attr($field_data['default_value'] ?? ''); ?>"
                placeholder="<?php echo esc_attr($field_data['placeholder'] ?? ''); ?>"
                <?php echo $required ? 'required' : ''; ?>
                <?php if (!empty($field_data['min_length'])): ?>
                minlength="<?php echo esc_attr($field_data['min_length']); ?>"
                <?php endif; ?>
                <?php if (!empty($field_data['max_length'])): ?>
                maxlength="<?php echo esc_attr($field_data['max_length']); ?>"
                <?php endif; ?>>
        </div>
<?php
        return ob_get_clean();
    }

    public function validate($value): bool
    {
        if (empty($value) && !empty($this->settings['required']['value'])) {
            return false;
        }

        $min_length = (int) ($this->settings['min_length']['value'] ?? 0);
        $max_length = (int) ($this->settings['max_length']['value'] ?? 0);

        if ($min_length && strlen($value) < $min_length) {
            return false;
        }

        if ($max_length && strlen($value) > $max_length) {
            return false;
        }

        return true;
    }

    public function sanitize($value)
    {
        return sanitize_text_field($value);
    }
}
