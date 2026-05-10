<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Repeater\Repeater;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Radio extends Field_Base
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
            'default' => __('Choose Option', 'smart-form-builder-by-dragwyb'),
        ]);

        $repeater = new Repeater();

        $repeater->add_control('option_label', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'smart-form-builder-by-dragwyb'),
            'default' => 'Option 1',
        ]);

        $repeater->add_control('option_value', [
            'type'    => Controls::TEXT,
            'label'   => __('Value', 'smart-form-builder-by-dragwyb'),
            'default' => 'val_1',
        ]);

        $this->add_control('options_list', [
            'type'        => Controls::REPEATER,
            'label'       => __('Options', 'smart-form-builder-by-dragwyb'),
            'items'      => $repeater->get_settings(),
            'default'     => [
                ['option_label' => 'Yes', 'option_value' => 'yes'],
                ['option_label' => 'No', 'option_value' => 'no'],
            ],
            'item_label' => 'option_label',
        ]);

        // Choose layout: stacked vertically or side-by-side
        $this->add_control('layout', [
            'type'    => Controls::SELECT,
            'label'   => __('Layout', 'smart-form-builder-by-dragwyb'),
            'options' => [
                'block'  => __('Vertical (List)', 'smart-form-builder-by-dragwyb'),
                'inline' => __('Horizontal (Inline)', 'smart-form-builder-by-dragwyb'),
            ],
            'label_inline' => true,
            'default' => 'block',
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

        // Style the individual option text
        $this->start_section('section_style_options', [
            'label' => __('Options', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('option_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-input-color: {{VALUE}};'],
        ]);

        $this->add_group_control('option_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}}',
            'prefix'   => 'option',
        ]);

        $this->end_section();

        $this->start_section('section_style_toggle', [
            'label' => __('Radio Appearance', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('toggle_size', [
            'type'      => Controls::SLIDER,
            'label'     => __('Size', 'smart-form-builder-by-dragwyb'),
            'range'     => ['px' => ['min' => 10, 'max' => 50]],
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-toggle-size: {{VALUE}}{{UNIT}};'],
        ]);

        $this->add_control('toggle_primary_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Primary Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-toggle-primary-color: {{VALUE}};'],
        ]);

        $this->add_control('toggle_border_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Border Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-toggle-border-color: {{VALUE}};'],
        ]);

        $this->add_control('toggle_spacing', [
            'type'      => Controls::SLIDER,
            'label'     => __('Spacing', 'smart-form-builder-by-dragwyb'),
            'range'     => ['px' => ['min' => 0, 'max' => 50]],
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-toggle-spacing: {{VALUE}}{{UNIT}};'],
        ]);

        $this->end_section();
    }

    protected function init(): void
    {
        $this->type = 'radio';
        $this->name = __('Radio Button', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-dot-circle';
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();

        $id = $this->get_the_id();
        $field_id       = $this->field_key_exist($settings, 'field_id', uniqid('field_'));
        $label    = $this->field_key_exist($settings, 'label', '');
        $options  = $this->field_key_exist($settings, 'options_list', []);
        $layout   = $this->field_key_exist($settings, 'layout', 'block');
        $help     = $this->field_key_exist($settings, 'help_text', '');
        $classes  = $this->field_key_exist($settings, 'css_classes', '');

        $layout_class = ($layout === 'inline') ? 'dragwyb-inline' : '';

?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?> dragwyb-no-float">
            <div class="dragwyb-input-group">
                <?php if (!empty($label)) : ?>
                    <div class="dragwyb-field-label"><?php echo esc_html($label); ?></div>
                <?php endif; ?>

                <div class="dragwyb-options-container <?php echo esc_attr($layout_class); ?>">
                    <?php foreach ($options as $index => $opt) : $opt_id = $field_id . '_' . $index;
                        $opt = $this->field_key_exist($opt, 'attributes', $opt);
                    ?>
                        <label class="dragwyb-option-item" for="<?php echo esc_attr($opt_id); ?>">
                            <input type="radio" id="<?php echo esc_attr($opt_id); ?>" name="<?php echo esc_attr($field_id); ?>" value="<?php echo esc_attr($opt['option_value']); ?>">
                            <span class="dragwyb-radio-label"><?php echo esc_html($opt['option_label']); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
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
        if ($value) {
            if (!is_array($value)) {
                $value = [$value];
            }
            return array_map('sanitize_text_field', $value);
        }

        return sanitize_text_field($default);
    }
}
