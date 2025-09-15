<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Field_Text extends Field_Base
{

    protected function register_scripts()
    {
        $scripts = array();

        if (defined('DRAGWYB_EDITOR')) {
            $scripts = array('dragwyb_editor_fields');
        }

        return $scripts;
    }

    protected function register_style()
    {
        return array();
    }

    public function __construct()
    {
        parent::__construct();
        wp_register_script('dragwyb_editor_fields', DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editorFields/editorFields.js', array(), DRAGWYB_FORM_BUILDER_VERSION, true);
    }

    protected function init(): void
    {
        $this->type = 'text';
        $this->name = __('Text Field', 'dragwyb-form-builder');
        $this->icon = 'fas fa-font';
    }

    protected function register_controls(): void
    {
        $this->start_section('text_form_settings', [
            'label' => 'Form Settings',
            'tab' => self::ContentTab
        ]);

        $this->start_tabs('text_tabs');

        $this->start_tab('text_normal', [
            'label' => 'Normal',
        ]);

        $this->add_control('text_color', [
            'type' => Controls::COLOR,
            'label' => __('Field Label Color', 'dragwyb-form-builder'),
            'default' => '',
        ]);

        $this->end_tab();

        $this->start_tab('text_hover', [
            'label' => 'Hover',
        ]);

        $this->add_control('text_hover_color', [
            'type' => Controls::COLOR,
            'label' => __('Field Label Color', 'dragwyb-form-builder'),
            'default' => '',
            'selectoR' => array(
                '{{WRAPPER}} .form-text input: {color: {{VALUE}}}',
            )
        ]);

        $this->end_tab();

        $this->end_tabs();

        $this->add_control('text_label', [
            'type' => Controls::TEXT,
            'label' => __('Field Label', 'dragwyb-form-builder'),
            'default' => 'Enter Your Label',
        ]);

        $this->add_control('text_placeholder', [
            'type' => Controls::TEXT,
            'label' => __('Placeholder', 'dragwyb-form-builder'),
            'default' => '',
            'conditions' => [
                'text_label' => 'aniket',
            ]
        ]);
        $this->add_control('text_required', [
            'type' => Controls::SWITCHER,
            'label' => __('Required', 'dragwyb-form-builder'),
            'default' => 'no',
        ]);
        $this->add_control('text_spacing', [
            'type' => Controls::SLIDER,
            'label' => __('Spacing', 'dragwyb-form-builder'),
            'units' => ['px', '%'],
            'range' => [
                'px' => [
                    'min' => 0,
                    'max' => 1000,
                    'step' => 5,
                ],
                '%' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 5
                ],
            ],
            'default' => [
                'unit' => '%',
                'size' => 50,
            ],
        ]);
        $this->add_control('text_spacing_without_unit', [
            'type' => Controls::SLIDER,
            'label' => __('Spacing', 'dragwyb-form-builder'),
            'default' => [
                'unit' => '%',
                'size' => 50,
            ],
        ]);
        $this->add_control('text_spacing_unit', [
            'type' => Controls::DIMENSIONS,
            'label' => __('Spacing', 'dragwyb-form-builder'),
        ]);
        $this->add_control('text_css_class', [
            'type' => Controls::TEXT,
            'label' => __('CSS Class', 'dragwyb-form-builder'),
            'default' => '',
        ]);

        $this->end_section();

        $this->start_section('text_form_style', [
            'label' => 'Form Style',
            'tab' => self::ContentTab
        ]);
        $this->add_control('text_style', [
            'type' => Controls::TEXT,
            'label' => __('CSS Class', 'dragwyb-form-builder'),
            'default' => '',
        ]);
        $this->end_section();

        $this->start_section('text_form_style_tab', [
            'label' => 'Form Style',
            'tab' => self::StyleTab
        ]);
        $this->add_control('text_style_tab', [
            'type' => Controls::TEXT,
            'label' => __('CSS Class', 'dragwyb-form-builder'),
            'default' => '',
        ]);
        $this->end_section();
        $this->start_section('text_form_style_tab_two', [
            'label' => 'Form Style',
            'tab' => self::StyleTab
        ]);
        $this->add_control('text_style_tab_two', [
            'type' => Controls::TEXT,
            'label' => __('CSS Class', 'dragwyb-form-builder'),
            'default' => '',
        ]);

        $this->add_control('text_style_popover_toggle', [
            'type' => Controls::POPOVER_TOGGLE,
            'label' => __('Popover Toggle', 'dragwyb-form-builder'),
            'icon' => 'fa-solid fa-pen'
        ]);

        $this->start_popover();

        $this->add_control('text_popover_hover_color', [
            'type' => Controls::COLOR,
            'label' => __('Field Label Color', 'dragwyb-form-builder'),
            'default' => '',
            'selectoR' => array(
                '{{WRAPPER}} .form-text input: {color: {{VALUE}}}',
            )
        ]);
        $this->add_control('text_popover_hover_color_one', [
            'type' => Controls::COLOR,
            'label' => __('Field Label Color', 'dragwyb-form-builder'),
            'default' => '',
            'selectoR' => array(
                '{{WRAPPER}} .form-text input: {color: {{VALUE}}}',
            )
        ]);
        $this->add_control('text_popover_hover_color_two', [
            'type' => Controls::COLOR,
            'label' => __('Field Label Color', 'dragwyb-form-builder'),
            'default' => '',
            'selectoR' => array(
                '{{WRAPPER}} .form-text input: {color: {{VALUE}}}',
            )
        ]);
        $this->add_control('text_popover_hover_color_three', [
            'type' => Controls::COLOR,
            'label' => __('Field Label Color', 'dragwyb-form-builder'),
            'default' => '',
            'selectoR' => array(
                '{{WRAPPER}} .form-text input: {color: {{VALUE}}}',
            )
        ]);

        $this->end_popover();
        $this->end_section();
    }

    protected function render_field()
    {
        $field_data = $this->get_field_settings();

        $id       = 'field_' . uniqid();
        $required = !empty($this->field_key_exist($field_data, 'required', ''));
        $icon     = $this->field_key_exist($field_data, 'text_icon', '');
        $label    = $this->field_key_exist($field_data, 'text_label', '');
        $placeholder = $this->field_key_exist($field_data, 'text_placeholder', '');

?>
        <div class="dragwyb-field-wrapper dragwyb-text-field">
            <?php if (!empty($label)) : ?>
                <label for="<?php echo esc_attr($id); ?>" class="dragwyb-label">
                    <?php echo esc_html($label); ?>
                    <?php if ($required): ?><span class="required">*</span><?php endif; ?>
                </label>
            <?php endif; ?>

            <div class="dragwyb-input-wrapper <?php echo !empty($icon) ? 'has-icon' : ''; ?>">
                <?php if (!empty($icon)): ?>
                    <span class="dragwyb-input-icon"><i class="<?php echo esc_attr($icon); ?>"></i></span>
                <?php endif; ?>
                <input type="text" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($id); ?>"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    <?php echo $required ? 'required' : ''; ?>
                    class="dragwyb-input" />
            </div>
        </div>
<?php
    }

    public function validate($value): bool
    {
        if (empty($value) && !empty($this->settings['required']['value'])) {
            return false;
        }

        $min_length = (int) ($this->settings['min_length']['value'] ?? 0);
        $max_length = (int) ($this->settings['max_length']['value'] ?? 0);

        if ($min_length && strlen($value) < $min_length) {
            return false;
        }

        if ($max_length && strlen($value) > $max_length) {
            return false;
        }

        return true;
    }

    public function sanitize($value)
    {
        return sanitize_text_field($value);
    }
}
