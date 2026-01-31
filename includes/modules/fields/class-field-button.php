<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Field_Button extends Field_Base
{
    protected function init(): void
    {
        $this->type = 'button';
        $this->name = __('Button', 'dragwyb-form-builder');
        $this->icon = 'fa fa-mouse-pointer';
    }

    protected function register_scripts(): void {}
    protected function register_style(): void {}

    /**
     * Define settings specific to this button
     */
    protected function register_controls(): void
    {
        // 🔹 CONTENT TAB
        $this->start_section('section_button_content', [
            'label' => __('Button Settings', 'dragwyb-form-builder'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_control('text', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'dragwyb-form-builder'),
            'default' => __('Submit', 'dragwyb-form-builder'),
        ]);

        // 🟢 UPDATED: Only Submit and Reset options
        $this->add_control('button_action', [
            'type'    => Controls::SELECT,
            'label'   => __('Action Type', 'dragwyb-form-builder'),
            'options' => [
                'submit' => __('Submit Form', 'dragwyb-form-builder'),
                'reset'  => __('Clear / Reset Form', 'dragwyb-form-builder'),
            ],
            'default' => 'submit',
            'label_inline' => true,
        ]);

        $this->add_control('button_align', [
            'type'    => Controls::CHOOSE,
            'label'   => __('Alignment', 'dragwyb-form-builder'),
            'options' => [
                'left'   => ['title' => 'Left', 'icon' => 'fa fa-align-left'],
                'center' => ['title' => 'Center', 'icon' => 'fa fa-align-center'],
                'right'  => ['title' => 'Right', 'icon' => 'fa fa-align-right'],
            ],
            'default' => 'left',
        ]);

        $this->add_control('button_width_type', [
            'type'    => Controls::SELECT,
            'label'   => __('Width Type', 'dragwyb-form-builder'),
            'options' => [
                'auto' => 'Auto',
                'custom' => 'Custom',
            ],
            'default' => 'auto',
            'label_inline' => true,
        ]);

        $this->add_control('button_width', [
            'type'    => Controls::SLIDER,
            'label'   => __('Width', 'dragwyb-form-builder'),
            'unit'    => '%',
            'range' => [
                '%' => [
                    'min' => 0,
                    'max' => 100,
                ],
            ],
            'default' => ['size' => '100', 'unit' => '%'],
            'label_inline' => true,
            'conditions' => [
                'button_width_type' => 'custom',
            ],
            'selectors' => [
                '{{WRAPPER}} button' => 'width: {{VALUE}}{{UNIT}};',
            ],
        ]);

        $this->end_section();

        // 🔹 STYLE TAB
        $this->start_section('section_button_style', [
            'label' => __('Button Style', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('custom_style', [
            'type'  => Controls::SWITCHER,
            'label' => __('Override Global Styles', 'dragwyb-form-builder'),
        ]);

        $this->add_control('bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            'conditions' => ['custom_style' => 'yes'],
            'selectors' => [
                '{{WRAPPER}} button' => 'background-color: {{VALUE}}; border-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'conditions' => ['custom_style' => 'yes'],
            'selectors' => [
                '{{WRAPPER}} button' => 'color: {{VALUE}};',
            ],
        ]);

        // Add more style controls here (Border radius, padding, etc.) if needed

        $this->end_section();
    }

    /**
     * Render the button on frontend
     */
    public function render_field(): void
    {
        $settings = $this->get_field_settings();
        $action = $settings['button_action'] ?? 'submit';
        $text   = $settings['text'] ?? __('Submit', 'dragwyb-form-builder');
        $width  = $settings['width'] ?? 'auto';
        $align  = $settings['button_align'] ?? 'left';

        // 1. Determine HTML Type
        // 'submit' triggers form submission
        // 'reset' triggers browser's native clear form functionality
        $html_type = ($action === 'reset') ? 'reset' : 'submit';

        // 2. Base Classes
        $classes = 'dragwyb-btn dragwyb-btn-' . esc_attr($action);

        if ($width === '100%') {
            $classes .= ' dragwyb-btn-block';
        }

        // 3. Wrapper Style
        $wrapper_style = "text-align: {$align};";

        echo '<div class="dragwyb-field-button-wrapper" style="' . esc_attr($wrapper_style) . '">';

        printf(
            '<button type="%s" class="%s" id="%s" name="%s">%s</button>',
            esc_attr($html_type),
            esc_attr($classes),
            esc_attr('dragwyb_btn_' . $this->get_the_id()),
            esc_attr('dragwyb_field_' . $this->get_the_id()),
            esc_html($text)
        );

        echo '</div>';
    }

    public function validate($value): bool
    {
        return true;
    }
}
