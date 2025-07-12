<?php
declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;

class Field_Radio extends Field_Base {
    protected function register_scripts(){
        return array();
    }
    
    protected function register_style(){
        return array();
    }
    
    protected function init(): void {
        $this->type = 'radio';
        $this->name = __('Radio Buttons', 'dragwyb-form-builder');
        $this->icon = 'dashicons-marker';
        $this->settings = array_merge(
            $this->get_default_settings(),
            [
                'options' => [
                    'type' => 'repeater',
                    'label' => __('Options', 'dragwyb-form-builder'),
                    'default' => [],
                    'fields' => [
                        'label' => [
                            'type' => 'text',
                            'label' => __('Option Label', 'dragwyb-form-builder'),
                        ],
                        'value' => [
                            'type' => 'text',
                            'label' => __('Option Value', 'dragwyb-form-builder'),
                        ],
                    ],
                ],
                'inline' => [
                    'type' => 'checkbox',
                    'label' => __('Display Inline', 'dragwyb-form-builder'),
                    'default' => false,
                ],
            ]
        );
    }

    public function render_frontend(array $field_data): string {
        $id = 'field_' . uniqid();
        $required = !empty($field_data['required']);
        $inline = !empty($field_data['inline']);
        
        ob_start();
        ?>
        <div class="dragwyb-field-wrapper">
            <fieldset>
                <legend>
                    <?php echo esc_html($field_data['label']); ?>
                    <?php if ($required): ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </legend>
                <div class="dragwyb-radio-options <?php echo $inline ? 'dragwyb-inline-options' : ''; ?>">
                    <?php foreach ($field_data['options'] as $option): ?>
                        <label class="dragwyb-radio-option">
                            <input type="radio"
                                   name="<?php echo esc_attr($id); ?>"
                                   value="<?php echo esc_attr($option['value']); ?>"
                                   <?php echo $required ? 'required' : ''; ?>
                                   <?php checked($option['value'], $field_data['default_value'] ?? ''); ?>>
                            <?php echo esc_html($option['label']); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </fieldset>
        </div>
        <?php
        return ob_get_clean();
    }

    public function validate($value): bool {
        if (empty($value) && !empty($this->settings['required']['value'])) {
            return false;
        }

        // Check if value exists in options
        $valid_values = array_column($this->settings['options']['value'] ?? [], 'value');
        return in_array($value, $valid_values, true);
    }

    public function sanitize($value) {
        return sanitize_text_field($value);
    }
} 