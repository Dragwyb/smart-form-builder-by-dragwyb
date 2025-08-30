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
        $repeater = new Repeater();

        $repeater->add_control(
            'repeater_text',
            array(
                'type'    => Controls::TEXT,
                'label'   => __('Text One', 'dragwyb-form-builder'),
                'default' => __('Enter Text One', 'dragwyb-form-builder'),
            )
        );

        $repeater->add_control(
            'repeater_text_two',
            array(
                'type'       => Controls::TEXT,
                'label'      => __('Text Two', 'dragwyb-form-builder'),
                'default'    => __('Enter Text Two', 'dragwyb-form-builder'),
                'conditions' => array('repeater_text' => 'dogra'),
            )
        );

        $repeater->start_tabs('text_tabs');

        $repeater->start_tab('text_normal', [
            'label' => 'Normal',
        ]);

        $repeater->add_control('text_color', [
            'type' => Controls::COLOR,
            'label' => __('Field Label Color', 'dragwyb-form-builder'),
            'default' => '',
        ]);

        $repeater->end_tab();

        $repeater->start_tab('text_hover', [
            'label' => 'Hover',
        ]);

        $repeater->add_control('text_hover_color', [
            'type' => Controls::COLOR,
            'label' => __('Field Label Color', 'dragwyb-form-builder'),
            'default' => '',
            'selectoR' => array(
                '{{WRAPPER}} .form-text input: {color: {{VALUE}}}',
            )
        ]);

        $repeater->end_tab();

        $repeater->end_tabs();

        $repeater->add_control(
            'repeater_text_three',
            array(
                'type'    => Controls::TEXT,
                'label'   => __('Text Three', 'dragwyb-form-builder'),
                'default' => __('Enter Text Three', 'dragwyb-form-builder'),
            )
        );

        // You can define select field-specific controls in the editor here
        $this->start_section('select_form_settings', [
            'label' => 'Form Settings',
            'tab' => self::ContentTab
        ]);

        $this->add_control('select_label', [
            'type' => Controls::TEXT,
            'label' => __('Field Label', 'dragwyb-form-builder'),
            'default' => 'Label',
        ]);

        $this->add_control('select_options', [
            'type' => Controls::REPEATER,
            'label' => __('Required', 'dragwyb-form-builder'),
            'add_item' => __('Add Options', 'dragwyb-form-builder'),
            'item_label' => 'repeater_text',
            'default' => [
                [
                    'repeater_text' => 'Aniket Dogra',
                    'repeater_text_two' => 'Aniket Hello World',
                ],
                [
                    'repeater_text' => 'Aniket Dogra Two',
                    'repeater_text_two' => 'Aniket Hello World Two',
                ]
            ],
            'items' => $repeater->get_settings()
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
        $field_data = $this->get_field_settings();

        $id = 'field_' . uniqid();
        $required = !empty($this->field_key_exist($field_data, 'required', ''));

?>
        <div class="dragwyb-field-wrapper">
            <label for="<?php echo esc_attr($id); ?>">
                <?php echo esc_html($field_data['select_label']); ?>
                <?php if ($required): ?>
                    <span class="required">*</span>
                <?php endif; ?>
            </label>
            <select
                id="<?php echo esc_attr($id); ?>"
                name="<?php echo esc_attr($id); ?>"
                <?php echo $required ? 'required' : ''; ?>
                class="dragwyb-select-field">
                <?php foreach ($field_data['select_options'] as $option):
                    $item_data = $option['attributes'];
                ?>
                    <option
                        value="<?php echo esc_attr($item_data['repeater_text']); ?>"
                        <?php selected($item_data['repeater_text'], $item_data['default_value'] ?? ''); ?>>
                        <?php echo esc_html($item_data['label'] ?? $item_data['repeater_text']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
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
