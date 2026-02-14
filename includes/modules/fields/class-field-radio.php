<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Repeater\Repeater;

class Field_Radio extends Field_Base
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
            'default' => __('Choose Option', 'dragwyb-form-builder'),
        ]);

        $repeater = new Repeater();

        $repeater->add_control('option_label', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'dragwyb-form-builder'),
            'default' => 'Option 1',
        ]);

        $repeater->add_control('option_value', [
            'type'    => Controls::TEXT,
            'label'   => __('Value', 'dragwyb-form-builder'),
            'default' => 'val_1',
        ]);

        $this->add_control('options_list', [
            'type'        => Controls::REPEATER,
            'label'       => __('Options', 'dragwyb-form-builder'),
            'items'      => $repeater->get_settings(),
            'default'     => [
                ['option_label' => 'Yes', 'option_value' => 'yes'],
                ['option_label' => 'No', 'option_value' => 'no'],
            ],
            'title_field' => '{{{ option_label }}}',
        ]);

        // Choose layout: stacked vertically or side-by-side
        $this->add_control('layout', [
            'type'    => Controls::SELECT,
            'label'   => __('Layout', 'dragwyb-form-builder'),
            'options' => [
                'block'  => __('Vertical (List)', 'dragwyb-form-builder'),
                'inline' => __('Horizontal (Inline)', 'dragwyb-form-builder'),
            ],
            'label_inline' => true,
            'default' => 'block',
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
            'label'     => __('Label Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-field-label' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('label_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} .dragwyb-field-label',
        ]);

        $this->end_section();

        // Style the individual option text
        $this->start_section('section_style_options', [
            'label' => __('Options', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('option_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-radio-label' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('option_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} .dragwyb-radio-label',
        ]);

        $this->end_section();
    }

    protected function init(): void
    {
        $this->type = 'radio';
        $this->name = __('Radio Button', 'dragwyb-form-builder');
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
        <div id="dragwyb-field-wrapper-<?php echo esc_attr($id); ?>" class="dragwyb-field-wrapper dragwyb-no-float <?php echo esc_attr($classes); ?>">
            <div class="dragwyb-input-group">
                <?php if (!empty($label)) : ?>
                    <div class="dragwyb-field-label"><?php echo esc_html($label); ?></div>
                <?php endif; ?>

                <div class="dragwyb-options-container <?php echo esc_attr($layout_class); ?>">
                    <?php foreach ($options as $index => $opt) : $opt_id = $field_id . '_' . $index; ?>
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

    public function validate($value): bool
    {
        if (empty($value) && !empty($this->settings['required']['value'])) {
            return false;
        }

        // Check if value exists in options
        $valid_values = array_column($this->settings['options']['value'] ?? [], 'value');
        return in_array($value, $valid_values, true);
    }

    public function sanitize($value)
    {
        return sanitize_text_field($value);
    }
}
