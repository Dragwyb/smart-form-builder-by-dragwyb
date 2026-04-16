<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Field_Number extends Field_Base
{
    protected function init(): void
    {
        $this->type = 'number';
        $this->name = __('Number', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-sort-numeric-up';
    }

    protected function register_field_controls(): void
    {
        // --- Content Tab ---
        $this->start_section('section_content', ['label' => 'General Settings', 'tab' => self::ContentTab]);

        $this->add_control('label', [
            'type' => Controls::TEXT,
            'label' => __('Label', 'smart-form-builder-by-dragwyb'),
            'default' => __('Number', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('placeholder', [
            'type' => Controls::TEXT,
            'label' => __('Placeholder', 'smart-form-builder-by-dragwyb'),
            'default' => '',
        ]);

        $this->add_control('min_val', [
            'type' => Controls::NUMBER,
            'label' => __('Min Value', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('max_val', [
            'type' => Controls::NUMBER,
            'label' => __('Max Value', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('step', [
            'type' => Controls::NUMBER,
            'label' => __('Step', 'smart-form-builder-by-dragwyb'),
            'default' => 1,
        ]);

        $this->add_control('required', [
            'type' => Controls::SWITCHER,
            'label' => __('Required', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->end_section();
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id = $this->get_the_id();
        $field_id       = $this->field_key_exist($settings, 'field_id', uniqid('field_'));
        $label    = $this->field_key_exist($settings, 'label', 'Number');
        $placeholder = $this->field_key_exist($settings, 'placeholder', ' ');
        $min      = $this->field_key_exist($settings, 'min_val', '');
        $max      = $this->field_key_exist($settings, 'max_val', '');
        $step     = $this->field_key_exist($settings, 'step', '1');
        $required = $this->field_key_exist($settings, 'required', '') === 'yes';
        $classes  = $this->field_key_exist($settings, 'css_classes', '');

?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?>">
            <div class="dragwyb-input-group">
                <input
                    type="number"
                    id="<?php echo esc_attr($field_id); ?>"
                    name="<?php echo esc_attr($field_id); ?>"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    min="<?php echo esc_attr($min); ?>"
                    max="<?php echo esc_attr($max); ?>"
                    step="<?php echo esc_attr($step); ?>"
                    class="dragwyb-field-input"
                    <?php echo $required ? 'required' : ''; ?> />

                <?php if (!empty($label)) : ?>
                    <label for="<?php echo esc_attr($field_id); ?>" class="dragwyb-field-label">
                        <?php echo esc_html($label); ?>
                        <?php if ($required) : ?><span class="dragwyb-required">*</span><?php endif; ?>
                    </label>
                <?php endif; ?>
            </div>
        </div>
<?php
    }

    public function validate($value): bool
    {
        return true;
    }
    public function sanitize($value)
    {
        return floatval($value);
    }
    protected function register_scripts()
    {
        return [];
    }
    protected function register_style()
    {
        return [];
    }
}
