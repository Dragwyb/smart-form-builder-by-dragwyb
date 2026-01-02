<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Style_Settings;

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Settings extends Register_Controls_Base
{
    private static $instance = null;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function init(): void {}

    protected function register_controls(): void
    {
        // ==============================================================
        // 🔹 SECTION 1: FORM CONTAINER (Global Wrapper)
        // ==============================================================
        $this->start_section('section_form_container', [
            'label' => __('Form Container', 'dragwyb-form-builder'),
        ]);

        $this->add_control('form_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            // "selectors": Array format. Key = Selector, Value = CSS Property with {{VALUE}}
            'selectors' => [
                '{{WRAPPER}}' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('form_border', [
            'type'      => Controls::GROUP_BORDER,
            'label'     => __('Border', 'dragwyb-form-builder'),
            // "selector": String format. Just the target element. Group handles properties.
            'selector'  => '{{WRAPPER}}',
        ]);

        $this->add_group_control('form_box_shadow', [
            'type'      => Controls::GROUP_BOX_SHADOW,
            'label'     => __('Box Shadow', 'dragwyb-form-builder'),
            'selector'  => '{{WRAPPER}}',
        ]);

        $this->add_control('form_padding', [
            'type'      => Controls::DIMENSIONS,
            'label'     => __('Padding', 'dragwyb-form-builder'),
            'units'     => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_control('form_margin', [
            'type'      => Controls::DIMENSIONS,
            'label'     => __('Margin', 'dragwyb-form-builder'),
            'units'     => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_control('field_spacing', [
            'type'      => Controls::SLIDER,
            'label'     => __('Rows Gap', 'dragwyb-form-builder'),
            'default'   => ['size' => 15, 'unit' => 'px'],
            'range'     => [
                'px' => ['min' => 0, 'max' => 100],
            ],
            'selectors' => [
                '{{WRAPPER}} .dragwyb-field-group' => 'margin-bottom: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_section();

        // ==============================================================
        // 🔹 SECTION 2: LABELS
        // ==============================================================
        $this->start_section('section_label_style', [
            'label' => __('Labels', 'dragwyb-form-builder'),
        ]);

        $this->add_control('label_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'default'   => '#333333',
            'selectors' => [
                '{{WRAPPER}} label' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('label_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} label',
        ]);

        $this->add_control('label_spacing', [
            'type'      => Controls::SLIDER,
            'label'     => __('Spacing (Bottom)', 'dragwyb-form-builder'),
            'default'   => ['size' => 5, 'unit' => 'px'],
            'range'     => ['px' => ['min' => 0, 'max' => 50]],
            'selectors' => [
                '{{WRAPPER}} label' => 'margin-bottom: {{SIZE}}{{UNIT}}; display: inline-block;',
            ],
        ]);

        $this->end_section();

        // ==============================================================
        // 🔹 SECTION 3: INPUT FIELDS
        // ==============================================================
        $this->start_section('section_input_style', [
            'label' => __('Input Fields', 'dragwyb-form-builder'),
        ]);

        // Centralized selector variable for cleaner code
        $input_selector = '{{WRAPPER}} input:not([type="button"]):not([type="submit"]), {{WRAPPER}} textarea, {{WRAPPER}} select';

        $this->add_control('input_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'default'   => '#555555',
            'selectors' => [
                $input_selector => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            'default'   => '#ffffff',
            'selectors' => [
                $input_selector => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_placeholder_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Placeholder Color', 'dragwyb-form-builder'),
            'selectors' => [
                "$input_selector::placeholder" => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('input_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => $input_selector,
        ]);

        $this->add_group_control('input_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'dragwyb-form-builder'),
            'selector' => $input_selector,
        ]);

        $this->add_control('input_padding', [
            'type'      => Controls::DIMENSIONS,
            'label'     => __('Padding', 'dragwyb-form-builder'),
            'units'     => ['px', 'em'],
            'selectors' => [
                $input_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->end_section();

        // ==============================================================
        // 🔹 SECTION 4: SUBMIT BUTTON
        // ==============================================================
        $this->start_section('section_button_style', [
            'label' => __('Submit Button', 'dragwyb-form-builder'),
        ]);

        $btn_selector = '{{WRAPPER}} button[type="submit"], {{WRAPPER}} input[type="submit"]';

        $this->add_group_control('button_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => $btn_selector,
        ]);

        $this->start_tabs('tabs_button_style');

        // --- Normal State ---
        $this->start_tab('tab_button_normal', [
            'label' => __('Normal', 'dragwyb-form-builder'),
        ]);

        $this->add_control('button_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'default'   => '#ffffff',
            'selectors' => [
                $btn_selector => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            'default'   => '#0073e6',
            'selectors' => [
                $btn_selector => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->end_tab();

        // --- Hover State ---
        $this->start_tab('tab_button_hover', [
            'label' => __('Hover', 'dragwyb-form-builder'),
        ]);

        $this->add_control('button_hover_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'selectors' => [
                "$btn_selector:hover" => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_hover_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            'default'   => '#005bb5',
            'selectors' => [
                "$btn_selector:hover" => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_hover_border_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Border Color', 'dragwyb-form-builder'),
            'selectors' => [
                "$btn_selector:hover" => 'border-color: {{VALUE}};',
            ],
        ]);

        $this->end_tab();
        $this->end_tabs();

        // --- Global Button Settings (Padding/Border) ---

        $this->add_group_control('button_border', [
            'type'      => Controls::GROUP_BORDER,
            'label'     => __('Border', 'dragwyb-form-builder'),
            'selector'  => $btn_selector,
            'separator' => 'before',
        ]);

        $this->add_control('button_padding', [
            'type'      => Controls::DIMENSIONS,
            'label'     => __('Padding', 'dragwyb-form-builder'),
            'units'     => ['px', 'em'],
            'selectors' => [
                $btn_selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->end_section();
    }
}
