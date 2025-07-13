<?php
declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;

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
        $this->settings = array_merge(
            $this->get_default_settings(),
            [
                'min_date' => [
                    'type' => 'date',
                    'label' => __('Minimum Date', 'dragwyb-form-builder'),
                    'default' => '',
                ],
                'max_date' => [
                    'type' => 'date',
                    'label' => __('Maximum Date', 'dragwyb-form-builder'),
                    'default' => '',
                ],
                'date_format' => [
                    'type' => 'select',
                    'label' => __('Date Format', 'dragwyb-form-builder'),
                    'default' => 'Y-m-d',
                    'options' => [
                        'Y-m-d' => __('YYYY-MM-DD', 'dragwyb-form-builder'),
                        'd/m/Y' => __('DD/MM/YYYY', 'dragwyb-form-builder'),
                        'm/d/Y' => __('MM/DD/YYYY', 'dragwyb-form-builder'),
                    ],
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