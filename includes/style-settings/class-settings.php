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
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-form-bg: {{VALUE}};',
            ],
        ]);

        // Note: For Group Controls, we typically target the element directly 
        // because they generate multiple complex properties (border-style, width, color).
        // If you strictly want variables, your Group Control Class needs to support variable generation.
        // For now, I will target the wrapper directly for borders/shadows so they affect the container.

        $this->add_group_control('form_border', [
            'type'      => Controls::GROUP_BORDER,
            'label'     => __('Border', 'dragwyb-form-builder'),
            'settings' => [
                'radius' => [
                    'unit' => 'px',
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                    'isLinked' => true,
                ]
            ],
            'selector'  => '{{WRAPPER}}', // Applied directly to wrapper class
        ]);

        $this->add_group_control('form_box_shadow', [
            'type'      => Controls::GROUP_BOX_SHADOW,
            'label'     => __('Box Shadow', 'dragwyb-form-builder'),
            'settings' => [
                'color' => '#0000001a',
                'horizontal' => ['size' => 0, 'unit' => 'px'],
                'vertical'   => ['size' => 10, 'unit' => 'px'],
                'blur'       => ['size' => 40, 'unit' => 'px'],
                'spread'     => ['size' => -5, 'unit' => 'px'],
            ],
            'selector'  => '{{WRAPPER}}',
        ]);

        $this->add_control('form_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'dragwyb-form-builder'),
            'units'      => ['px', 'em', '%'],
            'default'    => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20, 'unit' => 'px', 'linked' => false],
            'selectors'  => [
                // Mapping single control to 4 separate CSS variables
                '{{WRAPPER}}' => '--dragwyb-form-pt: {{TOP}}{{UNIT}}; --dragwyb-form-pr: {{RIGHT}}{{UNIT}}; --dragwyb-form-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-form-pl: {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_control('field_spacing', [
            'type'      => Controls::SLIDER,
            'label'     => __('Rows Gap', 'dragwyb-form-builder'),
            'default'   => ['size' => 20, 'unit' => 'px'],
            'range'     => ['px' => ['min' => 0, 'max' => 100]],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-row-gap: {{VALUE}}{{UNIT}};',
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
            'default'   => '#374151',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-label-color: {{VALUE}};',
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
            'default'   => ['size' => 6, 'unit' => 'px'],
            'range'     => ['px' => ['min' => 0, 'max' => 50]],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-label-spacing: {{VALUE}}{{UNIT}};',
            ],
        ]);

        $this->end_section();

        // ==============================================================
        // 🔹 SECTION 3: INPUT FIELDS
        // ==============================================================
        $this->start_section('section_input_style', [
            'label' => __('Input Fields', 'dragwyb-form-builder'),
        ]);

        // --- Normal State ---
        $this->add_control('input_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-input-bg: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'default'   => '#111827',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-input-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_placeholder_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Placeholder Color', 'dragwyb-form-builder'),
            'default'   => '#9ca3af',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-input-placeholder: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('input_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} input, {{WRAPPER}} textarea, {{WRAPPER}} select',
        ]);

        $this->add_control('input_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'dragwyb-form-builder'),
            'units'      => ['px', 'em'],
            'selectors'  => [
                '{{WRAPPER}}' => '--dragwyb-input-pt: {{TOP}}{{UNIT}}; --dragwyb-input-pr: {{RIGHT}}{{UNIT}}; --dragwyb-input-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-input-pl: {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control('input_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} input, {{WRAPPER}} textarea, {{WRAPPER}} select',
        ]);

        $this->add_group_control('input_box_shadow', [
            'type'     => Controls::GROUP_BOX_SHADOW,
            'label'    => __('Box Shadow', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} input, {{WRAPPER}} textarea, {{WRAPPER}} select',
        ]);

        // --- Focus State ---
        $this->start_tabs('tabs_input_states');

        $this->start_tab('tab_input_normal', ['label' => __('Normal', 'dragwyb-form-builder')]);
        $this->end_tab();

        $this->start_tab('tab_input_focus', ['label' => __('Focus', 'dragwyb-form-builder')]);

        $this->add_control('input_focus_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-input-focus-bg: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_focus_border_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Border Color', 'dragwyb-form-builder'),
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-input-focus-border: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('input_focus_box_shadow', [
            'type'     => Controls::GROUP_BOX_SHADOW,
            'label'    => __('Box Shadow', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} input:focus, {{WRAPPER}} textarea:focus, {{WRAPPER}} select:focus',
        ]);

        $this->end_tab();
        $this->end_tabs();
        $this->end_section();

        // ==============================================================
        // 🔹 SECTION 4: SUBMIT BUTTON
        // ==============================================================
        $this->start_section('section_button_style', [
            'label' => __('Submit Button', 'dragwyb-form-builder'),
        ]);

        $this->add_control('button_width', [
            'type'    => Controls::SELECT,
            'label'   => __('Width', 'dragwyb-form-builder'),
            'options' => [
                'auto' => __('Auto', 'dragwyb-form-builder'),
                '100%' => __('Full Width', 'dragwyb-form-builder'),
            ],
            'default' => '100%',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-width: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_alignment', [
            'type'      => Controls::CHOOSE,
            'label'     => __('Alignment', 'dragwyb-form-builder'),
            'options'   => [
                'left'    => ['title' => __('Left', 'dragwyb-form-builder'), 'icon' => 'fa fa-align-left'],
                'center'  => ['title' => __('Center', 'dragwyb-form-builder'), 'icon' => 'fa fa-align-center'],
                'right'   => ['title' => __('Right', 'dragwyb-form-builder'), 'icon' => 'fa fa-align-right'],
            ],
            'default'   => 'center',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-align: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('button_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} button[type="submit"]',
        ]);

        $this->start_tabs('tabs_button_style');

        // --- Normal State ---
        $this->start_tab('tab_button_normal', ['label' => __('Normal', 'dragwyb-form-builder')]);

        $this->add_control('button_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            'default'   => '#1d4ed8',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-bg: {{VALUE}};',
            ],
        ]);

        $this->end_tab();

        // --- Hover State ---
        $this->start_tab('tab_button_hover', ['label' => __('Hover', 'dragwyb-form-builder')]);

        $this->add_control('button_hover_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-hover-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_hover_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            'default'   => '#1e40af',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-hover-bg: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_hover_border_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Border Color', 'dragwyb-form-builder'),
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-hover-border: {{VALUE}};',
            ],
        ]);

        $this->end_tab();
        $this->end_tabs();

        // --- Button Dimensions ---
        $this->add_control('button_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'dragwyb-form-builder'),
            'units'      => ['px', 'em'],
            'selectors'  => [
                '{{WRAPPER}}' => '--dragwyb-btn-pt: {{TOP}}{{UNIT}}; --dragwyb-btn-pr: {{RIGHT}}{{UNIT}}; --dragwyb-btn-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-btn-pl: {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control('button_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} button[type="submit"]',
        ]);

        $this->add_group_control('button_box_shadow', [
            'type'     => Controls::GROUP_BOX_SHADOW,
            'label'    => __('Box Shadow', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} button[type="submit"]',
        ]);

        $this->end_section();
    }
}
