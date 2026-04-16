<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Field_Date extends Field_Base
{

    protected function register_scripts()
    {
        return array();
    }

    protected function register_style()
    {
        return array();
    }

    protected function register_field_controls(): void
    {
        // ==============================================================
        // CONTENT TAB
        // ==============================================================

        $this->start_section('section_content_general', [
            'label' => __('General Settings', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_control('label', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'smart-form-builder-by-dragwyb'),
            'default' => __('Select Date', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('placeholder', [
            'type'    => Controls::TEXT,
            'label'   => __('Placeholder', 'smart-form-builder-by-dragwyb'),
            'default' => 'YYYY-MM-DD',
        ]);

        // Set a date range limit
        $this->add_control('min_date', [
            'type'        => Controls::TEXT,
            'label'       => __('Min Date', 'smart-form-builder-by-dragwyb'),
            'description' => __('Earliest allowed date.', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('max_date', [
            'type'        => Controls::TEXT,
            'label'       => __('Max Date', 'smart-form-builder-by-dragwyb'),
            'description' => __('Latest allowed date.', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('help_text', [
            'type'        => Controls::TEXTAREA,
            'label'       => __('Help Text', 'smart-form-builder-by-dragwyb'),
            'rows'        => 3,
        ]);

        $this->add_control('required', [
            'type'  => Controls::SWITCHER,
            'label' => __('Required', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->end_section();

        // ==============================================================
        // STYLE TAB (Same as Text/Email)
        // ==============================================================

        $this->start_section('section_style_label', [
            'label' => __('Label', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('label_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}} .dragwyb-field-label' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('label_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}} .dragwyb-field-label',
        ]);

        $this->end_section();

        $this->start_section('section_style_input', [
            'label' => __('Input Field', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('input_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}} input.dragwyb-field-input' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('input_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}} input.dragwyb-field-input' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('input_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}} input.dragwyb-field-input',
        ]);

        $this->add_control('input_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'smart-form-builder-by-dragwyb'),
            'selectors'  => ['{{WRAPPER}} input.dragwyb-field-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_section();
    }

    protected function init(): void
    {
        $this->type = 'date';
        $this->name = __('Date Field', 'smart-form-builder-by-dragwyb');
        $this->icon = 'far fa-calendar';
        $this->category = 'advanced-fields';
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $field_data = $this->get_field_settings();
        $id = $this->get_the_id();
        $field_id      = $this->field_key_exist($settings, 'field_id', uniqid('date_'));
        $required = !empty($field_data['required']);
?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?> dragwyb-no-float">
            <?php if (!empty($label)) : ?>
                <label for="<?php echo esc_attr($field_id); ?>" class="dragwyb-label">
                    <?php echo esc_html($label); ?>
                    <?php if ($required): ?><span class="required">*</span><?php endif; ?>
                </label>
            <?php endif; ?>

            <input type="date" id="<?php echo esc_attr($field_id); ?>" name="<?php echo esc_attr($field_id); ?>"
                placeholder="<?php echo esc_attr($placeholder); ?>"
                <?php echo $required ? 'required' : ''; ?>
                class="dragwyb-input" />
        </div>

<?php
    }

    public function validate($value): bool
    {
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

    public function sanitize($value)
    {
        if (empty($value)) {
            return '';
        }

        $date = DateTime::createFromFormat('Y-m-d', $value);
        return $date ? $date->format($this->settings['date_format']['value']) : '';
    }
}
