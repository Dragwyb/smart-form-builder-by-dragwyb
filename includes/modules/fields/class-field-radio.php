<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

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

    protected function register_controls(): void {}

    protected function init(): void
    {
        $this->type = 'radio';
        $this->name = __('Radio Buttons', 'dragwyb-form-builder');
        $this->icon = 'fas fa-dot-circle';
    }

    protected function render_field()
    {
        $field_data = $this->get_field_settings();

        $id = 'field_' . uniqid();
        $required = !empty($field_data['required']);
        $inline = !empty($field_data['inline']);
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
