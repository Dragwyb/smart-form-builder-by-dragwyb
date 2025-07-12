<?php
declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;

class Field_Email extends Field_Base {
    protected function register_scripts(){
        return array();
    }
    
    protected function register_style(){
        return array();
    }
    
    protected function init(): void {
        $this->type = 'email';
        $this->name = __('Email Field', 'dragwyb-form-builder');
        $this->icon = 'dashicons-email';
        $this->settings = array_merge(
            $this->get_default_settings(),
            [
                'confirmation' => [
                    'type' => 'checkbox',
                    'label' => __('Require Confirmation', 'dragwyb-form-builder'),
                    'default' => false,
                ],
            ]
        );
    }

    public function render_admin(): string {
        ob_start();
        ?>
        <div class="dragwyb-field" data-type="<?php echo esc_attr($this->type); ?>">
            <div class="dragwyb-field-preview">
                <span class="dragwyb-field-icon dashicons <?php echo esc_attr($this->icon); ?>"></span>
                <span class="dragwyb-field-label"><?php echo esc_html($this->name); ?></span>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function render_frontend(array $field_data): string {
        $id = 'field_' . uniqid();
        $required = !empty($field_data['required']);
        
        ob_start();
        ?>
        <div class="dragwyb-field-wrapper <?php echo esc_attr($field_data['css_class'] ?? ''); ?>">
            <label for="<?php echo esc_attr($id); ?>">
                <?php echo esc_html($field_data['label']); ?>
                <?php if ($required): ?>
                    <span class="required">*</span>
                <?php endif; ?>
            </label>
            <input type="email"
                   id="<?php echo esc_attr($id); ?>"
                   name="<?php echo esc_attr($id); ?>"
                   value="<?php echo esc_attr($field_data['default_value'] ?? ''); ?>"
                   placeholder="<?php echo esc_attr($field_data['placeholder'] ?? ''); ?>"
                   <?php echo $required ? 'required' : ''; ?>
            >
            <?php if (!empty($field_data['confirmation'])): ?>
                <label for="<?php echo esc_attr($id . '_confirm'); ?>">
                    <?php echo esc_html__('Confirm Email', 'dragwyb-form-builder'); ?>
                    <?php if ($required): ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </label>
                <input type="email"
                       id="<?php echo esc_attr($id . '_confirm'); ?>"
                       name="<?php echo esc_attr($id . '_confirm'); ?>"
                       placeholder="<?php echo esc_attr__('Confirm your email', 'dragwyb-form-builder'); ?>"
                       <?php echo $required ? 'required' : ''; ?>
                >
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    public function validate($value): bool {
        if (empty($value) && !empty($this->settings['required']['value'])) {
            return false;
        }

        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function sanitize($value) {
        return sanitize_email($value);
    }
} 