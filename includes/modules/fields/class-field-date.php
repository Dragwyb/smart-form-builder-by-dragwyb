<?php
declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

class Field_Date extends Field_Base {
    protected function register_scripts(){
        return array();
    }
    
    protected function register_style(){
        return array();
    }

    protected function register_controls(): void{}
    
    protected function init(): void {
        $this->type = 'date';
        $this->name = __('Date Field', 'dragwyb-form-builder');
        $this->icon = 'dashicons-calendar-alt';
    }

    protected function render_field(): string {
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
            <input type="date"
                   id="<?php echo esc_attr($id); ?>"
                   name="<?php echo esc_attr($id); ?>"
                   value="<?php echo esc_attr($field_data['default_value'] ?? ''); ?>"
                   <?php if (!empty($field_data['min_date'])): ?>
                   min="<?php echo esc_attr($field_data['min_date']); ?>"
                   <?php endif; ?>
                   <?php if (!empty($field_data['max_date'])): ?>
                   max="<?php echo esc_attr($field_data['max_date']); ?>"
                   <?php endif; ?>
                   <?php echo $required ? 'required' : ''; ?>>
        </div>
        <?php
        return ob_get_clean();
    }

    public function validate($value): bool {
        if (empty($value) && !empty($this->settings['required']['value'])) {
            return false;
        }

        if (!empty($value)) {
            $date = DateTime::createFromFormat('Y-m-d', $value);
            if (!$date || $date->format('Y-m-d') !== $value) {
                return false;
            }

            // Check min date
            if (!empty($this->settings['min_date']['value'])) {
                $min_date = new DateTime($this->settings['min_date']['value']);
                if ($date < $min_date) {
                    return false;
                }
            }

            // Check max date
            if (!empty($this->settings['max_date']['value'])) {
                $max_date = new DateTime($this->settings['max_date']['value']);
                if ($date > $max_date) {
                    return false;
                }
            }
        }

        return true;
    }

    public function sanitize($value) {
        if (empty($value)) {
            return '';
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);
        return $date ? $date->format($this->settings['date_format']['value']) : '';
    }
} 