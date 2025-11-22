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
            'option_label',
            array(
                'type'    => Controls::TEXT,
                'label'   => __('Label', 'dragwyb-form-builder'),
                'default' => __('Enter Text One', 'dragwyb-form-builder'),
            )
        );

        $repeater->add_control(
            'option_value',
            array(
                'type'       => Controls::TEXT,
                'label'      => __('Text Two', 'dragwyb-form-builder'),
                'default'    => __('Enter Text Two', 'dragwyb-form-builder'),
            )
        );

        // You can define select field-specific controls in the editor here
        $this->start_section('select_form_settings', [
            'label' => 'Form Settings',
            'tab' => self::ContentTab
        ]);

        $this->add_control('label', [
            'type' => Controls::TEXT,
            'label' => __('Field Label', 'dragwyb-form-builder'),
            'default' => 'Label',
        ]);

        $this->add_control('options', [
            'type' => Controls::REPEATER,
            'label' => __('Required', 'dragwyb-form-builder'),
            'add_item' => __('Add Options', 'dragwyb-form-builder'),
            'item_label' => 'option_label',
            'default' => [
                [
                    'option_label' => 'Select Default',
                    'option_value' => 'text',
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

        $id = 'field_' . $this->get_the_id();
        $required = !empty($this->field_key_exist($field_data, 'required', ''));
        $options = $this->field_key_exist($field_data, 'options', []);
?>
        <div class="dragwyb-field-wrapper dragwyb-select-field">
            <?php if (!empty($label)) : ?>
                <label for="<?php echo esc_attr($id); ?>" class="dragwyb-label">
                    <?php echo esc_html($label); ?>
                    <?php if ($required): ?><span class="required">*</span><?php endif; ?>
                </label>
            <?php endif; ?>

            <div class="dragwyb-input-wrapper">
                <select id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($id); ?>"
                    <?php echo $required ? 'required' : ''; ?>
                    class="dragwyb-input">
                    <?php if (!empty($options)) : ?>
                        <?php foreach ($options as $option) :
                            $attribute = $option['attributes'];
                            $val = $this->field_key_exist($attribute, 'option_label', '');
                            $text = $this->field_key_exist($attribute, 'option_value', $val);

                            if ($val && !empty($val)):
                        ?>
                                <option value="<?php echo esc_attr($val); ?>"><?php echo esc_html($text); ?></option>
                        <?php
                            endif;
                        endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
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
