<?php
declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;

class Field_Textarea extends Field_Base {
    protected function register_scripts(){

        wp_register_script('dragwyb_editor_fields', DRAGWYB_FORM_BUILDER_URL. 'assets/dist/editorFields/editorFields.js', array(), DRAGWYB_FORM_BUILDER_VERSION, true);

        $scripts=array();   

        if(defined('DRAGWYB_EDITOR')){
            $scripts=array('dragwyb_editor_fields');
        }

        return $scripts;
    }
    
    protected function register_style(){
        return array();
    }

    protected function init(): void {
        $this->type = 'textarea';
        $this->name = __('Paragraph Field', 'dragwyb-form-builder');
        $this->icon = 'dashicons-editor-paragraph';
        $this->settings = array_merge(
            $this->get_default_settings(),
            [
                'rows' => [
                    'type' => 'number',
                    'label' => __('Rows', 'dragwyb-form-builder'),
                    'default' => 4,
                ],
                'max_length' => [
                    'type' => 'number',
                    'label' => __('Maximum Length', 'dragwyb-form-builder'),
                    'default' => 0,
                ],
            ]
        );
    }

    public function render_frontend(array $field_data): string {
        $id = 'field_' . uniqid();
        $required = !empty($field_data['required']);
        
        ob_start();
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
                placeholder="<?php echo esc_attr($field_data['placeholder'] ?? ''); ?>"
            ><?php echo esc_textarea($field_data['default_value'] ?? ''); ?></textarea>
        </div>
        <?php
        return ob_get_clean();
    }

    public function validate($value): bool {
        if (empty($value) && !empty($this->settings['required']['value'])) {
            return false;
        }

        $max_length = (int) ($this->settings['max_length']['value'] ?? 0);
        if ($max_length && strlen($value) > $max_length) {
            return false;
        }

        return true;
    }

    public function sanitize($value) {
        return sanitize_textarea_field($value);
    }
} 