<?php
declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;

class Field_File extends Field_Base {
    protected function register_scripts(){
        return array();
    }
    
    protected function register_style(){
        return array();
    }

    protected function register_controls(): void{}
    
    protected function init(): void {
        $this->type = 'file';
        $this->name = __('File Upload', 'dragwyb-form-builder');
        $this->icon = 'dashicons-upload';
        $this->settings = array_merge(
            $this->get_default_settings(),
            [
                'allowed_types' => [
                    'type' => 'text',
                    'label' => __('Allowed File Types', 'dragwyb-form-builder'),
                    'default' => 'jpg,jpeg,png,pdf',
                    'description' => __('Comma-separated list of file extensions', 'dragwyb-form-builder'),
                ],
                'max_size' => [
                    'type' => 'number',
                    'label' => __('Maximum File Size (MB)', 'dragwyb-form-builder'),
                    'default' => 2,
                ],
                'multiple' => [
                    'type' => 'checkbox',
                    'label' => __('Allow Multiple Files', 'dragwyb-form-builder'),
                    'default' => false,
                ],
            ]
        );
    }

    public function render_frontend(array $field_data): string {
        $id = 'field_' . uniqid();
        $required = !empty($field_data['required']);
        $multiple = !empty($field_data['multiple']);
        $allowed_types = array_map('trim', explode(',', $field_data['allowed_types'] ?? ''));
        $accept = '.' . implode(',.', $allowed_types);
        
        ob_start();
        ?>
        <div class="dragwyb-field-wrapper dragwyb-file-upload">
            <label for="<?php echo esc_attr($id); ?>">
                <?php echo esc_html($field_data['label']); ?>
                <?php if ($required): ?>
                    <span class="required">*</span>
                <?php endif; ?>
            </label>
            <div class="dragwyb-file-upload-wrapper">
                <input type="file"
                       id="<?php echo esc_attr($id); ?>"
                       name="<?php echo esc_attr($id . ($multiple ? '[]' : '')); ?>"
                       accept="<?php echo esc_attr($accept); ?>"
                       <?php echo $multiple ? 'multiple' : ''; ?>
                       <?php echo $required ? 'required' : ''; ?>
                       data-max-size="<?php echo esc_attr($field_data['max_size'] ?? 2); ?>">
                <div class="dragwyb-file-upload-info">
                    <?php
                    printf(
                        __('Maximum file size: %s MB', 'dragwyb-form-builder'),
                        esc_html($field_data['max_size'] ?? 2)
                    );
                    ?>
                    <br>
                    <?php
                    printf(
                        __('Allowed types: %s', 'dragwyb-form-builder'),
                        esc_html(implode(', ', $allowed_types))
                    );
                    ?>
                </div>
                <div class="dragwyb-file-preview"></div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function validate($value): bool {
        if (empty($_FILES)) {
            return !$this->settings['required']['value'];
        }

        $files = $this->get_uploaded_files();
        if (empty($files)) {
            return !$this->settings['required']['value'];
        }

        foreach ($files as $file) {
            // Check file size
            $max_size = (int) ($this->settings['max_size']['value'] ?? 2) * 1024 * 1024; // Convert MB to bytes
            if ($file['size'] > $max_size) {
                return false;
            }

            // Check file type
            $allowed_types = array_map('trim', explode(',', $this->settings['allowed_types']['value']));
            $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_types)) {
                return false;
            }
        }

        return true;
    }

    public function sanitize($value) {
        $files = $this->get_uploaded_files();
        if (empty($files)) {
            return [];
        }

        $uploaded_files = [];
        foreach ($files as $file) {
            $upload = wp_handle_upload($file, ['test_form' => false]);
            if (!empty($upload['url'])) {
                $uploaded_files[] = $upload;
            }
        }

        return $uploaded_files;
    }

    private function get_uploaded_files(): array {
        $files = [];
        $field_name = $this->get_field_name();

        if (!isset($_FILES[$field_name])) {
            return [];
        }

        if (is_array($_FILES[$field_name]['name'])) {
            // Multiple files
            $file_count = count($_FILES[$field_name]['name']);
            for ($i = 0; $i < $file_count; $i++) {
                $files[] = [
                    'name' => $_FILES[$field_name]['name'][$i],
                    'type' => $_FILES[$field_name]['type'][$i],
                    'tmp_name' => $_FILES[$field_name]['tmp_name'][$i],
                    'error' => $_FILES[$field_name]['error'][$i],
                    'size' => $_FILES[$field_name]['size'][$i],
                ];
            }
        } else {
            // Single file
            $files[] = $_FILES[$field_name];
        }

        return $files;
    }
} 