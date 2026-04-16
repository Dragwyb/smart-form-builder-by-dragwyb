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
            'default' => __('Message', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('placeholder', [
            'type'    => Controls::TEXT,
            'label'   => __('Placeholder', 'smart-form-builder-by-dragwyb'),
            'default' => __('Type your message here...', 'smart-form-builder-by-dragwyb'),
        ]);

        // Set the height of the box in rows
        $this->add_control('rows', [
            'type'    => Controls::NUMBER,
            'label'   => __('Rows (Height)', 'smart-form-builder-by-dragwyb'),
            'default' => 4,
            'min'     => 1,
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
        // STYLE TAB
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
            'selectors' => ['{{WRAPPER}} textarea.dragwyb-field-input' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('input_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}} textarea.dragwyb-field-input' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('input_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}} textarea.dragwyb-field-input',
        ]);

        $this->add_control('input_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'smart-form-builder-by-dragwyb'),
            'selectors'  => ['{{WRAPPER}} textarea.dragwyb-field-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_section();
    }

    protected function init(): void
    {
        $this->type = 'textarea';
        $this->name = __('Textarea', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-align-left';
        $this->keywords = array('text', 'wyswing');
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id = $this->get_the_id();
        $field_id       = $this->field_key_exist($settings, 'field_id', uniqid('field_'));
        $label    = $this->field_key_exist($settings, 'label', 'Message');
        $placeholder = $this->field_key_exist($settings, 'placeholder', ' ');
        $rows     = $this->field_key_exist($settings, 'rows', 4);
        $help     = $this->field_key_exist($settings, 'help_text', '');
        $required = $this->field_key_exist($settings, 'required', '') === 'yes';
        $classes  = $this->field_key_exist($settings, 'css_classes', '');

?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?>">
            <div class="dragwyb-input-group">
                <textarea
                    id="<?php echo esc_attr($field_id); ?>"
                    name="<?php echo esc_attr($field_id); ?>"
                    rows="<?php echo esc_attr($rows); ?>"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    class="dragwyb-field-input"
                    <?php echo $required ? 'required' : ''; ?>></textarea>
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

        $max_length = (int) ($this->settings['max_length']['value'] ?? 0);
        if ($max_length && strlen($value) > $max_length) {
            return false;
        }

        return true;
    }
}
