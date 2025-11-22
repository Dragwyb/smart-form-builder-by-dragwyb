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
        $this->name = __('Textarea', 'dragwyb-form-builder');
        $this->icon = 'fas fa-align-left';
        $this->keywords = array('text', 'wyswing');
    }

    protected function render_field()
    {
        $field_data = $this->get_field_settings();

        $id = 'field_' . $this->get_the_id();
        $required = !empty($field_data['required']);
?>
        <div class="dragwyb-field-wrapper dragwyb-textarea-field">
            <?php if (!empty($label)) : ?>
                <label for="<?php echo esc_attr($id); ?>" class="dragwyb-label">
                    <?php echo esc_html($label); ?>
                    <?php if ($required): ?><span class="required">*</span><?php endif; ?>
                </label>
            <?php endif; ?>

            <div class="dragwyb-input-wrapper">
                <textarea id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($id); ?>"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    <?php echo $required ? 'required' : ''; ?>
                    class="dragwyb-input"></textarea>
            </div>
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
