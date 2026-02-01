<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Repeater\Repeater;

class Field_Select extends Field_Base
{
    protected function register_scripts()
    {
        return array(); // If needed, you can enqueue custom scripts here
    }

    protected function register_style()
    {
        return array(); // If needed, you can enqueue custom styles here
    }

    protected function register_controls(): void
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
            'default' => __('Select Option', 'dragwyb-form-builder'),
        ]);

        // Use a Repeater control to let users add unlimited options
        $repeater = new Repeater();

        $repeater->add_control('option_label', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'dragwyb-form-builder'),
            'default' => 'Option 1',
        ]);

        $repeater->add_control('option_value', [
            'type'    => Controls::TEXT,
            'label'   => __('Value', 'dragwyb-form-builder'),
            'default' => 'value_1',
        ]);

        $this->add_control('options_list', [
            'type'        => Controls::REPEATER,
            'label'       => __('Options', 'dragwyb-form-builder'),
            'fields'      => $repeater->get_settings(),
            'default'     => [
                ['option_label' => 'Option 1', 'option_value' => 'val_1'],
                ['option_label' => 'Option 2', 'option_value' => 'val_2'],
                ['option_label' => 'Option 3', 'option_value' => 'val_3'],
            ],
            'title_field' => '{{{ option_label }}}',
        ]);

        $this->add_control('multiple', [
            'type'  => Controls::SWITCHER,
            'label' => __('Allow Multiple Selection', 'dragwyb-form-builder'),
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

        $this->add_control('label_spacing', [
            'type'      => Controls::SLIDER,
            'label'     => __('Spacing (Bottom)', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-field-label' => 'margin-bottom: {{SIZE}}{{UNIT}};'],
        ]);

        $this->end_section();

        // Style the dropdown input box
        $this->start_section('section_style_input', [
            'label' => __('Select Field', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('input_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} select.dragwyb-field-input' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('input_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} select.dragwyb-field-input' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('input_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} select.dragwyb-field-input',
        ]);

        $this->add_control('input_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'dragwyb-form-builder'),
            'selectors'  => ['{{WRAPPER}} select.dragwyb-field-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
            'separator'  => 'before',
        ]);

        $this->add_control('input_radius', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Border Radius', 'dragwyb-form-builder'),
            'selectors'  => ['{{WRAPPER}} select.dragwyb-field-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_section();

        // ==============================================================
        // ADVANCE TAB
        // ==============================================================

        $this->start_section('section_advance_layout', [
            'label' => __('Layout & ID', 'dragwyb-form-builder'),
            'tab'   => self::AdvanceTab,
        ]);

        $this->add_control('field_id', [
            'type'        => Controls::TEXT,
            'label'       => __('Field ID', 'dragwyb-form-builder'),
            'default'     => uniqid('field_'),
            'dynamic'     => ['active' => false],
        ]);

        $this->add_control('width', [
            'type'         => Controls::SELECT,
            'label'        => __('Field Width', 'dragwyb-form-builder'),
            'label_inline' => true,
            'options'      => [
                '100%' => '100%',
                '50%'  => '50%',
                '33%'  => '33%',
                '25%'  => '25%',
                'auto' => 'Auto',
            ],
            'default'   => '100%',
            'selectors' => [
                '{{WRAPPER}}' => 'width: {{VALUE}};',
            ],
        ]);

        $this->add_control('css_classes', [
            'type'        => Controls::TEXT,
            'label'       => __('Custom CSS Classes', 'dragwyb-form-builder'),
        ]);

        $this->end_section();

        $this->start_section('section_advance_logic', [
            'label' => __('Conditional Logic', 'dragwyb-form-builder'),
            'tab'   => self::AdvanceTab,
        ]);

        $this->add_control('enable_logic', [
            'type'         => Controls::SWITCHER,
            'label'        => __('Enable Logic', 'dragwyb-form-builder'),
            'default'      => '',
            'return_value' => '',
            'disabled'     => true,
        ]);

        $this->add_control('logic_msg', [
            'type' => Controls::RAW_HTML,
            'raw'  => '<div style="color: hsl(var(--dragwyb-sidebar-foreground)/var(--dragwyb-text-opacity, 1)); font-size: 12px; padding: 10px 0;">' . sprintf(__('%1$sComing Soon%2$s: Advanced Conditional Logic is in development. This feature will allow you to dynamically show or hide fields based on user input.', 'dragwyb-form-builder'), '<strong>', '</strong>') . '</div>',
            'condition' => [
                'enable_logic' => 'yes',
            ],
        ]);

        $this->end_section();
    }

    protected function init(): void
    {
        $this->type = 'select';
        $this->name = __('Select Dropdown', 'dragwyb-form-builder');
        $this->icon = 'fas fa-caret-down'; // Choose a different icon if needed
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id       = $this->field_key_exist($settings, 'field_id', uniqid('field_'));
        $label    = $this->field_key_exist($settings, 'label', '');
        $options  = $this->field_key_exist($settings, 'options_list', []);
        $help     = $this->field_key_exist($settings, 'help_text', '');
        $required = $this->field_key_exist($settings, 'required', '') === 'yes';
        $multiple = $this->field_key_exist($settings, 'multiple', '') === 'yes';
        $classes  = $this->field_key_exist($settings, 'css_classes', '');

?>
        <div class="dragwyb-field-wrapper <?php echo esc_attr($classes); ?>">
            <div class="dragwyb-input-group">
                <select
                    id="<?php echo esc_attr($id); ?>"
                    name="<?php echo esc_attr($id); ?>"
                    class="dragwyb-field-input"
                    <?php echo $required ? 'required' : ''; ?>
                    <?php echo $multiple ? 'multiple' : ''; ?>>
                    <?php foreach ($options as $opt) : ?>
                        <option value="<?php echo esc_attr($opt['option_value']); ?>">
                            <?php echo esc_html($opt['option_label']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (!empty($label)) : ?>
                    <label for="<?php echo esc_attr($id); ?>" class="dragwyb-field-label">
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

    public function validate($value): bool
    {
        if (empty($value) && !empty($this->settings['required']['value'])) {
            return false;
        }

        // Validate against defined options
        $valid_values = array_column($this->settings['options']['value'] ?? [], 'value');
        return in_array($value, $valid_values, true);
    }

    public function sanitize($value)
    {
        return sanitize_text_field($value);
    }
}
