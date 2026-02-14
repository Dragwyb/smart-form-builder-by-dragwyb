<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

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

    protected function register_field_controls(): void
    {
        // ==============================================================
        // CONTENT TAB
        // ==============================================================

        $this->start_section('section_content_general', [
            'label' => __('General Settings', 'dragwyb-form-builder'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_control('label', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'dragwyb-form-builder'),
            'default' => __('Email Address', 'dragwyb-form-builder'),
        ]);

        $this->add_control('placeholder', [
            'type'    => Controls::TEXT,
            'label'   => __('Placeholder', 'dragwyb-form-builder'),
            'default' => __('name@example.com', 'dragwyb-form-builder'),
        ]);

        $this->add_control('default_value', [
            'type'    => Controls::TEXT,
            'label'   => __('Default Value', 'dragwyb-form-builder'),
            'default' => '',
        ]);

        $this->add_control('help_text', [
            'type'        => Controls::TEXTAREA,
            'label'       => __('Help Text', 'dragwyb-form-builder'),
            'rows'        => 3,
            'description' => __('Text that appears below the field to guide the user.', 'dragwyb-form-builder'),
        ]);

        $this->add_control('required', [
            'type'         => Controls::SWITCHER,
            'label'        => __('Required Field', 'dragwyb-form-builder'),
            'return_value' => 'yes',
            'default'      => 'no',
        ]);

        $this->end_section();

        // ==============================================================
        // STYLE TAB
        // ==============================================================

        $this->start_section('section_style_label', [
            'label' => __('Label', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        // Standard Label Styling Controls
        $this->add_control('label_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-field-label' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('label_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} .dragwyb-field-label',
        ]);

        $this->add_control('label_spacing', [
            'type'      => Controls::SLIDER,
            'label'     => __('Spacing (Bottom)', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-field-label' => 'margin-bottom: {{SIZE}}{{UNIT}};'],
        ]);

        $this->end_section();

        $this->start_section('section_style_input', [
            'label' => __('Input Field', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        // Use tabs for Normal and Focus states of the input
        $this->start_tabs('tabs_input_style');

        $this->start_tab('tab_input_normal', ['label' => __('Normal', 'dragwyb-form-builder')]);

        $this->add_control('input_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} input.dragwyb-field-input' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('input_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} input.dragwyb-field-input' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('input_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} input.dragwyb-field-input',
        ]);

        $this->end_tab();

        $this->start_tab('tab_input_focus', ['label' => __('Focus', 'dragwyb-form-builder')]);

        $this->add_control('input_focus_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} input.dragwyb-field-input:focus' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('input_focus_border_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Border Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} input.dragwyb-field-input:focus' => 'border-color: {{VALUE}};'],
        ]);

        $this->end_tab();
        $this->end_tabs();

        $this->add_control('input_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'dragwyb-form-builder'),
            'selectors'  => ['{{WRAPPER}} input.dragwyb-field-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
            'separator'  => 'before',
        ]);

        $this->add_control('input_radius', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Border Radius', 'dragwyb-form-builder'),
            'selectors'  => ['{{WRAPPER}} input.dragwyb-field-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_section();

        $this->start_section('section_style_help', [
            'label' => __('Help Text', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('help_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-field-help' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('help_text_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} .dragwyb-field-help',
        ]);

        $this->end_section();
    }

    protected function init(): void
    {
        $this->type = 'email';
        $this->keywords = array('text');
        $this->name = __('Email Field', 'dragwyb-form-builder');
        $this->icon = 'fas fa-envelope';
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id = $this->get_the_id();
        $field_id       = $this->field_key_exist($settings, 'field_id', uniqid('field_'));
        $label    = $this->field_key_exist($settings, 'label', 'Email Address');
        $placeholder = $this->field_key_exist($settings, 'placeholder', ' ');
        $value    = $this->field_key_exist($settings, 'default_value', '');
        $help     = $this->field_key_exist($settings, 'help_text', '');
        $required = $this->field_key_exist($settings, 'required', '') === 'yes';
        $classes  = $this->field_key_exist($settings, 'css_classes', '');

?>
        <div id="dragwyb-field-wrapper-<?php echo esc_attr($id); ?>" class="dragwyb-field-wrapper <?php echo esc_attr($classes); ?>">
            <div class="dragwyb-input-group">
                <input
                    type="email"
                    id="<?php echo esc_attr($field_id); ?>"
                    name="<?php echo esc_attr($field_id); ?>"
                    value="<?php echo esc_attr($value); ?>"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    class="dragwyb-field-input"
                    <?php echo $required ? 'required' : ''; ?> />
                <?php if (!empty($label)) : ?>
                    <label for="<?php echo esc_attr($field_id); ?>" class="dragwyb-field-label">
                        <?php echo esc_html($label); ?>
                        <?php if ($required) : ?><span class="dragwyb-required">*</span><?php endif; ?>
                    </label>
                <?php endif; ?>
            </div>
            <?php if (!empty($help)) : ?>
                <div class="dragwyb-field-help"><?php echo esc_html($help); ?></div>
            <?php endif; ?>
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
