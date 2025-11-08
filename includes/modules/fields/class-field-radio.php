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
        $this->name = __('Radio Button', 'dragwyb-form-builder');
        $this->icon = 'fas fa-dot-circle';
    }

    protected function render_field()
    {
        $field_data = $this->get_field_settings();

        $id = 'field_' . uniqid();
        $required = !empty($field_data['required']);
        $inline = !empty($field_data['inline']);
        $options = $this->field_key_exist($field_data, 'options', []);
        $label = $this->field_key_exist($field_data, 'label', "");

?>
        <div class="dragwyb-field-wrapper dragwyb-radio-field">
            <?php if (!empty($label)) : ?>
                <span class="dragwyb-label">
                    <?php echo esc_html($label); ?>
                    <?php if ($required): ?><span class="required">*</span><?php endif; ?>
                </span>
            <?php endif; ?>

            <div class="dragwyb-radio-options">
                <?php if (!empty($field_data['options'])) : ?>
                    <?php foreach ($field_data['options'] as $val => $text) : ?>
                        <label class="dragwyb-radio-option">
                            <input type="radio" name="<?php echo esc_attr($id); ?>"
                                value="<?php echo esc_attr($val); ?>"
                                <?php echo $required ? 'required' : ''; ?> />
                            <span><?php echo esc_html($text); ?></span>
                        </label>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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
