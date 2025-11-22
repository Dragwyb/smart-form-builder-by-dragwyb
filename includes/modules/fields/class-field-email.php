<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

class Field_Email extends Field_Base
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
        $this->type = 'email';
        $this->keywords = array('text');
        $this->name = __('Email Field', 'dragwyb-form-builder');
        $this->icon = 'fas fa-envelope';
    }

    protected function render_field()
    {
        $field_data = $this->get_field_settings();

        $id = 'field_' . $this->get_the_id();
        $required = $this->field_key_exist($field_data, 'required', false);
        $placeholder = $this->field_key_exist($field_data, 'placeholder', "");
?>
        <div class="dragwyb-field-wrapper dragwyb-email-field">
            <?php if (!empty($label)) : ?>
                <label for="<?php echo esc_attr($id); ?>" class="dragwyb-label">
                    <?php echo esc_html($label); ?>
                    <?php if ($required): ?><span class="required">*</span><?php endif; ?>
                </label>
            <?php endif; ?>

            <div class="dragwyb-input-wrapper <?php echo !empty($icon) ? 'has-icon' : ''; ?>">
                <?php if (!empty($icon)): ?>
                    <span class="dragwyb-input-icon"><i class="<?php echo esc_attr($icon); ?>"></i></span>
                <?php endif; ?>
                <input type="email" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($id); ?>"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    <?php echo $required ? 'required' : ''; ?>
                    class="dragwyb-input" />
            </div>
        </div>

<?php
    }

    public function validate($value): bool
    {
        if (empty($value) && !empty($this->settings['required']['value'])) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function sanitize($value)
    {
        return sanitize_email($value);
    }
}
