<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

class Field_Textarea extends Field_Base
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
        $this->type = 'textarea';
        $this->name = __('Paragraph Field', 'dragwyb-form-builder');
        $this->icon = 'dashicons-editor-paragraph';
    }

    protected function render_field()
    {
        $field_data = $this->get_field_settings();

        $id = 'field_' . uniqid();
        $required = !empty($field_data['required']);
?>
        <div class="dragwyb-field-wrapper">
            <label for="<?php echo esc_attr($id); ?>">
                <?php echo esc_html($field_data['label']); ?>
                <?php if ($required): ?>
                    <span class="required">*</span>
                <?php endif; ?>
            </label>
            <textarea
                id="<?php echo esc_attr($id); ?>"
                name="<?php echo esc_attr($id); ?>"
                rows="<?php echo esc_attr($field_data['rows'] ?? 4); ?>"
                <?php echo $required ? 'required' : ''; ?>
                <?php if (!empty($field_data['max_length'])): ?>
                maxlength="<?php echo esc_attr($field_data['max_length']); ?>"
                <?php endif; ?>
                placeholder="<?php echo esc_attr($field_data['placeholder'] ?? ''); ?>"><?php echo esc_textarea($field_data['default_value'] ?? ''); ?></textarea>
        </div>
<?php
    }

    public function validate($value): bool
    {
        if (empty($value) && !empty($this->settings['required']['value'])) {
            return false;
        }

        $max_length = (int) ($this->settings['max_length']['value'] ?? 0);
        if ($max_length && strlen($value) > $max_length) {
            return false;
        }

        return true;
    }
}
