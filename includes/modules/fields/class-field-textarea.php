<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

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

    protected function register_controls(): void
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
            'default' => __('Message', 'dragwyb-form-builder'),
        ]);

        $this->add_control('placeholder', [
            'type'    => Controls::TEXT,
            'label'   => __('Placeholder', 'dragwyb-form-builder'),
            'default' => __('Type your message here...', 'dragwyb-form-builder'),
        ]);

        // Set the height of the box in rows
        $this->add_control('rows', [
            'type'    => Controls::NUMBER,
            'label'   => __('Rows (Height)', 'dragwyb-form-builder'),
            'default' => 4,
            'min'     => 1,
        ]);

        $this->add_control('help_text', [
            'type'        => Controls::TEXTAREA,
            'label'       => __('Help Text', 'dragwyb-form-builder'),
            'rows'        => 3,
        ]);

        $this->add_control('required', [
            'type'  => Controls::SWITCHER,
            'label' => __('Required', 'dragwyb-form-builder'),
        ]);

        $this->end_section();

        // ==============================================================
        // STYLE TAB
        // ==============================================================

        $this->start_section('section_style_label', [
            'label' => __('Label', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

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

        $this->end_section();

        $this->start_section('section_style_input', [
            'label' => __('Input Field', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('input_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} textarea.dragwyb-field-input' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('input_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} textarea.dragwyb-field-input' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('input_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} textarea.dragwyb-field-input',
        ]);

        $this->add_control('input_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'dragwyb-form-builder'),
            'selectors'  => ['{{WRAPPER}} textarea.dragwyb-field-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_section();

        // ==============================================================
        // ADVANCE TAB
        // ==============================================================

        $this->start_section('section_advance_layout', [
            'label' => __('Layout & ID', 'dragwyb-form-builder'),
            'tab'   => self::AdvanceTab,
        ]);

        $this->add_control('field_id', [
            'type'        => Controls::TEXT,
            'label'       => __('Field ID', 'dragwyb-form-builder'),
            'default'     => uniqid('field_'),
            'dynamic'     => ['active' => false],
        ]);

        $this->add_control('width', [
            'type'         => Controls::SELECT,
            'label'        => __('Field Width', 'dragwyb-form-builder'),
            'label_inline' => true,
            'options'      => [
                '100%' => '100%',
                '50%'  => '50%',
                '33%'  => '33%',
                '25%'  => '25%',
                'auto' => 'Auto',
            ],
            'default'   => '100%',
            'selectors' => [
                '{{WRAPPER}}' => 'width: {{VALUE}};',
            ],
        ]);

        $this->add_control('css_classes', [
            'type'        => Controls::TEXT,
            'label'       => __('Custom CSS Classes', 'dragwyb-form-builder'),
        ]);

        $this->end_section();

        $this->start_section('section_advance_logic', [
            'label' => __('Conditional Logic', 'dragwyb-form-builder'),
            'tab'   => self::AdvanceTab,
        ]);

        $this->add_control('enable_logic', [
            'type'         => Controls::SWITCHER,
            'label'        => __('Enable Logic', 'dragwyb-form-builder'),
            'default'      => '',
            'return_value' => '',
            'disabled'     => true,
        ]);

        $this->add_control('logic_msg', [
            'type' => Controls::RAW_HTML,
            'raw'  => '<div style="color: hsl(var(--dragwyb-sidebar-foreground)/var(--dragwyb-text-opacity, 1)); font-size: 12px; padding: 10px 0;">' . sprintf(__('%1$sComing Soon%2$s: Advanced Conditional Logic is in development. This feature will allow you to dynamically show or hide fields based on user input.', 'dragwyb-form-builder'), '<strong>', '</strong>') . '</div>',
            'condition' => [
                'enable_logic' => 'yes',
            ],
        ]);

        $this->end_section();
    }

    protected function init(): void
    {
        $this->type = 'textarea';
        $this->name = __('Textarea', 'dragwyb-form-builder');
        $this->icon = 'fas fa-align-left';
        $this->keywords = array('text', 'wyswing');
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id       = $this->field_key_exist($settings, 'field_id', uniqid('field_'));
        $label    = $this->field_key_exist($settings, 'label', 'Message');
        $placeholder = $this->field_key_exist($settings, 'placeholder', ' ');
        $rows     = $this->field_key_exist($settings, 'rows', 4);
        $help     = $this->field_key_exist($settings, 'help_text', '');
        $required = $this->field_key_exist($settings, 'required', '') === 'yes';
        $classes  = $this->field_key_exist($settings, 'css_classes', '');

?>
        <div class="dragwyb-field-wrapper <?php echo esc_attr($classes); ?>">
            <div class="dragwyb-input-group">
                <textarea
                    id="<?php echo esc_attr($id); ?>"
                    name="<?php echo esc_attr($id); ?>"
                    rows="<?php echo esc_attr($rows); ?>"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    class="dragwyb-field-input"
                    <?php echo $required ? 'required' : ''; ?>></textarea>
                <?php if (!empty($label)) : ?>
                    <label for="<?php echo esc_attr($id); ?>" class="dragwyb-field-label">
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

        $max_length = (int) ($this->settings['max_length']['value'] ?? 0);
        if ($max_length && strlen($value) > $max_length) {
            return false;
        }

        return true;
    }
}
