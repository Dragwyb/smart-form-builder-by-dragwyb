<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Error_Handler;

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
        wp_register_script(
            'dragwyb_editor_fields',
            esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editorFields/editorFields.js'),
            array(),
            esc_attr(DRAGWYB_FORM_BUILDER_VERSION),
            true
        );
    }

    protected function init(): void
    {
        $this->type = 'text';
        $this->name = __('Text Field', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-font';
    }

    protected function register_field_controls(): void
    {
        /**
         * TAB: CONTENT
         */
        $this->start_section('section_content_general', [
            'label' => __('Basic Settings', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_control('label', [
            'type'    => Controls::TEXT,
            'label'   => __('Field Label', 'smart-form-builder-by-dragwyb'),
            'default' => __('Text Field', 'smart-form-builder-by-dragwyb'),
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('placeholder', [
            'type'    => Controls::TEXT,
            'label'   => __('Placeholder Text', 'smart-form-builder-by-dragwyb'),
            'default' => __('Enter text...', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('default_value', [
            'type'    => Controls::TEXT,
            'label'   => __('Default Value', 'smart-form-builder-by-dragwyb'),
            'description' => __('Pre-fill the field for the user.', 'smart-form-builder-by-dragwyb'),
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

        $this->add_control('min_length', [
            'type'  => Controls::NUMBER,
            'label' => __('Minimum Length', 'smart-form-builder-by-dragwyb'),
            'min'   => 0,
        ]);

        $this->add_control('max_length', [
            'type'  => Controls::NUMBER,
            'label' => __('Maximum Length', 'smart-form-builder-by-dragwyb'),
            'min'   => 1,
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

        $this->add_control('input_placeholder_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Placeholder Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-input-placeholder-color: {{VALUE}};'],
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

        $this->end_tab();

        $this->start_tab('tab_input_focus', ['label' => __('Focus', 'smart-form-builder-by-dragwyb')]);

        $this->add_control('input_focus_border_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Active Border Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-input-focus-border: {{VALUE}};'],
        ]);

        $this->end_tab();

        $this->end_tabs();

        $this->add_control('input_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Inner Padding', 'smart-form-builder-by-dragwyb'),
            'size_units' => ['px', 'em', '%'],
            'selectors'  => ['{{WRAPPER}}' => '--dragwyb-input-pt: {{TOP}}{{UNIT}}; --dragwyb-input-pr: {{RIGHT}}{{UNIT}}; --dragwyb-input-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-input-pl: {{LEFT}}{{UNIT}};'],
            'separator'  => 'before',
        ]);

        $this->end_section();
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id = $this->get_the_id();
        $field_id       = $this->field_key_exist($settings, 'field_id', uniqid('field_'));
        $label    = $this->field_key_exist($settings, 'label', 'Text Field');
        $placeholder = $this->field_key_exist($settings, 'placeholder', ' '); // Space for float logic
        $value    = $this->field_key_exist($settings, 'default_value', '');
        $help     = $this->field_key_exist($settings, 'help_text', '');
        $required = $this->field_key_exist($settings, 'required', '') === 'yes';
        $classes  = $this->field_key_exist($settings, 'css_classes', '');
?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?>">
            <div class="dragwyb-input-group">
                <input
                    type="text"
                    id="<?php echo esc_attr($field_id); ?>"
                    name="<?php echo esc_attr($field_id); ?>"
                    value="<?php echo esc_attr($value); ?>"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    class="dragwyb-field-input"
                    <?php echo $required ? 'required' : ''; ?> />
                <?php if (!empty($label)) : ?>
                    <label for="<?php echo esc_attr($field_id); ?>" class="dragwyb-field-label">
                        <?php echo esc_html($label); ?>
                        <?php if ($required) : ?><span class="dragwyb-required">*</span><?php endif; ?>
                    </label>
                <?php endif; ?>
            </div>
            <?php if (!empty($help)) : ?>
                <div class="dragwyb-field-help"><?php echo esc_html($help); ?></div>
            <?php endif; ?>
        </div>
<?php
    }

    public function validate($value, $field_id, $settings, Form_Error_Handler $error_handler): void
    {
        if (empty($value) && !empty($this->settings['required']['value'])) {
            $error_handler->add_error($field_id, __('This field is required.', 'smart-form-builder-by-dragwyb'));
            return;
        }

        $min_length = (int) ($this->settings['min_length']['value'] ?? 0);
        $max_length = (int) ($this->settings['max_length']['value'] ?? 0);

        if ($min_length && strlen($value) < $min_length) {
            $error_handler->add_error($field_id, sprintf(__('This field requires at least %d characters.', 'smart-form-builder-by-dragwyb'), $min_length));
            return;
        }

        if ($max_length && strlen($value) > $max_length) {
            $error_handler->add_error($field_id, sprintf(__('This field requires at most %d characters.', 'smart-form-builder-by-dragwyb'), $max_length));
            return;
        }
    }

    public function sanitize($value)
    {
        return sanitize_text_field($value);
    }
}
