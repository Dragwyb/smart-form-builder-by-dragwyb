<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Repeater\Repeater;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Select extends Field_Base
{
    protected function register_field_controls(): void
    {
        // ==============================================================
        // CONTENT TAB
        // ==============================================================

        $this->start_section('section_content_general', [
            'label' => __('General Settings', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_control('label', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'smart-form-builder-by-dragwyb'),
            'default' => __('Select Option', 'smart-form-builder-by-dragwyb'),
        ]);

        // Use a Repeater control to let users add unlimited options
        $repeater = new Repeater();

        $repeater->add_control('option_label', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'smart-form-builder-by-dragwyb'),
            'default' => 'Option 1',
        ]);

        $repeater->add_control('option_value', [
            'type'    => Controls::TEXT,
            'label'   => __('Value', 'smart-form-builder-by-dragwyb'),
            'default' => 'value_1',
        ]);

        $this->add_control('options_list', [
            'type'        => Controls::REPEATER,
            'label'       => __('Options', 'smart-form-builder-by-dragwyb'),
            'items'      => $repeater->get_settings(),
            'default'     => [
                ['option_label' => 'Option 1', 'option_value' => 'val_1'],
                ['option_label' => 'Option 2', 'option_value' => 'val_2'],
                ['option_label' => 'Option 3', 'option_value' => 'val_3'],
            ],
            'title_field' => '{{{ option_label }}}',
        ]);

        $this->add_control('multiple', [
            'type'  => Controls::SWITCHER,
            'label' => __('Allow Multiple Selection', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('help_text', [
            'type'        => Controls::TEXTAREA,
            'label'       => __('Help Text', 'smart-form-builder-by-dragwyb'),
            'rows'        => 3,
        ]);

        $this->add_control('required', [
            'type'  => Controls::SWITCHER,
            'label' => __('Required', 'smart-form-builder-by-dragwyb'),
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

    protected function init(): void
    {
        $this->type = 'select';
        $this->name = __('Select Dropdown', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-caret-down'; // Choose a different icon if needed
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id = $this->get_the_id();
        $field_id       = $this->field_key_exist($settings, 'field_id', uniqid('field_'));
        $label    = $this->field_key_exist($settings, 'label', 'Select Option');
        $repeater_options  = $this->field_key_exist($settings, 'options_list', [
            ['option_label' => 'Option 1', 'option_value' => 'val_1'],
            ['option_label' => 'Option 2', 'option_value' => 'val_2'],
            ['option_label' => 'Option 3', 'option_value' => 'val_3'],
        ]);
        $help     = $this->field_key_exist($settings, 'help_text', '');
        $required = $this->field_key_exist($settings, 'required', '') === 'yes';
        $multiple = $this->field_key_exist($settings, 'multiple', '') === 'yes';
        $classes  = $this->field_key_exist($settings, 'css_classes', '');

?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?> dragwyb-no-float">
            <div class="dragwyb-input-group">
                <?php if (!empty($label)) : ?>
                    <label for="<?php echo esc_attr($field_id); ?>" class="dragwyb-field-label">
                        <?php echo esc_html($label); ?>
                        <?php if ($required) : ?><span class="dragwyb-required">*</span><?php endif; ?>
                    </label>
                <?php endif; ?>
                <select
                    id="<?php echo esc_attr($field_id); ?>"
                    name="<?php echo esc_attr($field_id); ?>"
                    class="dragwyb-field-input"
                    <?php echo $required ? 'required' : ''; ?>
                    <?php echo $multiple ? 'multiple' : ''; ?>>
                    <?php foreach ($repeater_options as $option) :
                        $option =  $this->field_key_exist($option, 'attributes', []);
                    ?>
                        <option value="<?php echo esc_attr($option['option_value']); ?>">
                            <?php echo esc_html($option['option_label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if (!empty($help)) : ?>
                <div class="dragwyb-field-help"><?php echo esc_html($help); ?></div>
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

        if (empty($value) && isset($field_attr['required']) && 'yes' == $field_attr['required']) {
            $error_handler->add_error($field_id, __('This field is required', 'smart-form-builder-by-dragwyb'));
            return;
        }

        if (empty($value)) {
            return;
        }

        $option_values = array();

        $field_options = isset($field_attr['options_list']) ? $field_attr['options_list'] : array();

        if (is_array($field_options)) {
            foreach ($field_options as $field_option) {
                if (isset($field_option['attributes']['option_value'])) {
                    $option_values[] = $field_option['attributes']['option_value'];
                }
            }
        }

        if (!in_array($value, $option_values, true)) {
            $error_handler->add_error($field_id, __('Invalid option selected', 'smart-form-builder-by-dragwyb'));
        }
    }

    /**
     * Sanitize the field value.
     *
     * @param string $default The default value.
     * @param mixed $value The value to sanitize.
     * @return mixed Sanitized value.
     */
    public function sanitize($default = '', $value = null)
    {
        if ($value && is_string($value)) {
            return sanitize_text_field($value);
        }

        return sanitize_text_field($default);
    }
}
