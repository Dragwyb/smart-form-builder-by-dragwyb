<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Repeater\Repeater;

class Field_Checkbox extends Field_Base
{
    protected function init(): void
    {
        $this->type = 'checkbox';
        $this->name = __('Checkbox Group', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-check-square';
    }

    protected function register_field_controls(): void
    {
        // --- Content Tab ---
        $this->start_section('section_content', [
            'label' => __('General Settings', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_control('label', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'smart-form-builder-by-dragwyb'),
            'default' => __('Select Options', 'smart-form-builder-by-dragwyb'),
        ]);

        // Repeater for Checkbox Options
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
                ['option_label' => 'Choice 1', 'option_value' => 'c1'],
                ['option_label' => 'Choice 2', 'option_value' => 'c2'],
            ],
            'title_field' => '{{{ option_label }}}',
        ]);

        $this->add_control('layout', [
            'type'    => Controls::SELECT,
            'label'   => __('Layout', 'smart-form-builder-by-dragwyb'),
            'options' => [
                'block'  => __('Vertical (List)', 'smart-form-builder-by-dragwyb'),
                'inline' => __('Horizontal (Inline)', 'smart-form-builder-by-dragwyb'),
            ],
            'default' => 'block',
        ]);

        $this->add_control('help_text', [
            'type'  => Controls::TEXTAREA,
            'label' => __('Help Text', 'smart-form-builder-by-dragwyb'),
            'rows'  => 3,
        ]);

        $this->add_control('required', [
            'type'  => Controls::SWITCHER,
            'label' => __('Required', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->end_section();

        // --- Style Tab ---
        $this->start_section('section_style_label', [
            'label' => __('Label', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('label_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}} .dragwyb-field-label' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('label_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}} .dragwyb-field-label',
        ]);

        $this->end_section();

        $this->start_section('section_style_options', [
            'label' => __('Checkbox Options', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('option_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}} .dragwyb-radio-label' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('option_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}} .dragwyb-radio-label',
        ]);

        $this->end_section();
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

        $layout_class = ($layout === 'inline') ? 'dragwyb-inline-options' : '';

?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?> dragwyb-no-float">
            <div class="dragwyb-input-group">
                <?php if (!empty($label)) : ?>
                    <div class="dragwyb-field-label"><?php echo esc_html($label); ?></div>
                <?php endif; ?>

                <div class="dragwyb-options-container <?php echo esc_attr($layout_class); ?>">
                    <?php foreach ($options as $index => $opt) : $opt_id = $field_id . '_' . $index; ?>
                        <label class="dragwyb-option-item" for="<?php echo esc_attr($opt_id); ?>">
                            <input type="checkbox" id="<?php echo esc_attr($opt_id); ?>" name="<?php echo esc_attr($field_id); ?>[]" value="<?php echo esc_attr($opt['option_value']); ?>">
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
        return true;
    } // Basic
    public function sanitize($value)
    {
        return $value;
    }
    protected function register_scripts()
    {
        return [];
    }
    protected function register_style()
    {
        return [];
    }
}
