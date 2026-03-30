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
        // SECTION 1: FORM CONTAINER
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

        $this->add_group_control('form_border', [
            'type'      => Controls::GROUP_BORDER,
            'label'     => __('Border', 'dragwyb-form-builder'),
            'selector'  => '{{WRAPPER}}',
        ]);

        $this->add_group_control('form_box_shadow', [
            'type'      => Controls::GROUP_BOX_SHADOW,
            'label'     => __('Box Shadow', 'dragwyb-form-builder'),
            'selector'  => '{{WRAPPER}}',
        ]);

        $this->add_control('form_justify_content', [
            'type'      => Controls::CHOOSE,
            'label'     => __('Justify Content', 'dragwyb-form-builder'),
            'default'   => 'center',
            'options' => [
                'left'   => ['title' => 'Left',   'icon' => 'fa fa-align-left'],
                'center' => ['title' => 'Center', 'icon' => 'fa fa-align-center'],
                'right'  => ['title' => 'Right',  'icon' => 'fa fa-align-right'],
                'space-between' => ['title' => 'Space Between', 'icon' => 'fa fa-align-justify'],
            ],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-form-justify-content: {{VALUE}};',
            ],
        ]);

        $this->add_responsive_control('form_margin', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Margin', 'dragwyb-form-builder'),
            'units'      => ['px', 'em', '%'],
            'selectors'  => [
                '{{WRAPPER}}' => '--dragwyb-form-mt: {{TOP}}{{UNIT}}; --dragwyb-form-mr: {{RIGHT}}{{UNIT}}; --dragwyb-form-mb: {{BOTTOM}}{{UNIT}}; --dragwyb-form-ml: {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('form_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'dragwyb-form-builder'),
            'units'      => ['px', 'em', '%'],
            'default'    => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20, 'unit' => 'px', 'linked' => false],
            'selectors'  => [
                '{{WRAPPER}}' => '--dragwyb-form-pt: {{TOP}}{{UNIT}}; --dragwyb-form-pr: {{RIGHT}}{{UNIT}}; --dragwyb-form-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-form-pl: {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('field_spacing', [
            'type'      => Controls::SLIDER,
            'label'     => __('Rows Gap', 'dragwyb-form-builder'),
            'default'   => ['size' => 20, 'unit' => 'px'],
            'range'     => ['px' => ['min' => 0, 'max' => 100]],
            'units'     => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-field-gap: {{VALUE}}{{UNIT}};',
            ],
        ]);

        $this->end_section();

        // SECTION 2: LABELS & HELP TEXT
        $this->start_section('section_label_style', [
            'label' => __('Labels & Help Text', 'dragwyb-form-builder'),
        ]);

        $this->add_control('label_position', [
            'type'    => Controls::SELECT,
            'label'   => __('Label Layout', 'dragwyb-form-builder'),
            'options' => [
                'top'       => __('Top Aligned (Standard)', 'dragwyb-form-builder'),
                'left'      => __('Left Aligned (Horizontal)', 'dragwyb-form-builder'),
                'floating'  => __('Floating Label (Modern)', 'dragwyb-form-builder'),
                'hidden'    => __('Hidden (Screen Reader Only)', 'dragwyb-form-builder'),
            ],
            'default' => 'top',
            'label_inline' => true,
        ]);

        $this->add_control('floating_style', [
            'type'    => Controls::SELECT,
            'label'   => __('Floating Style', 'dragwyb-form-builder'),
            'options' => [
                'outlined' => __('Outlined (On Border)', 'dragwyb-form-builder'),
                'inside'   => __('Inside (Filled / Box)', 'dragwyb-form-builder'),
            ],
            'default' => 'outlined',
            'label_inline' => true,
            'conditions' => [
                'label_position' => 'floating',
            ],
        ]);

        $this->add_control('floating_active_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Focus Border Color', 'dragwyb-form-builder'),
            'default'   => '#1d4ed8', // Default blue
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-float-active: {{VALUE}};',
            ],
            'conditions' => [
                'label_position' => 'floating',
                'floating_style' => 'inside',
            ],
        ]);

        $this->add_control('label_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Label Color', 'dragwyb-form-builder'),
            'default'   => '#374151',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-label-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('required_asterisk_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Required Asterisk Color', 'dragwyb-form-builder'),
            'default'   => '#ef4444',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-asterisk-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('help_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Help Text Color', 'dragwyb-form-builder'),
            'default'   => '#6b7280',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-help-color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('label_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} label',
            'prefix'   => 'label',
        ]);

        $this->add_responsive_control('label_spacing', [
            'type'      => Controls::SLIDER,
            'label'     => __('Spacing (Bottom)', 'dragwyb-form-builder'),
            'default'   => ['size' => 6, 'unit' => 'px'],
            'range'     => ['px' => ['min' => 0, 'max' => 50]],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-label-spacing: {{VALUE}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control('help_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Help Text Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} .dragwyb-field-description',
            'prefix'   => 'help-text',
        ]);

        $this->end_section();

        // SECTION 3: INPUT FIELDS
        $this->start_section('section_input_style', [
            'label' => __('Input Fields', 'dragwyb-form-builder'),
        ]);

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
            'prefix'   => 'input',
        ]);

        $this->add_responsive_control('input_padding', [
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
            'prefix'   => 'input',
        ]);

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
            'prefix'   => 'input',
        ]);

        $this->end_tab();
        $this->end_tabs();
        $this->end_section();

        // SECTION 4: BUTTON
        $this->start_section('section_button_style', [
            'label' => __('Button', 'dragwyb-form-builder'),
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

        $this->add_responsive_control('button_width', [
            'type'    => Controls::SLIDER,
            'label'   => __('Width', 'dragwyb-form-builder'),
            'units' => ['px', '%'],
            'range' => [
                'px' => [
                    'min' => 0,
                    'max' => 200,
                    'step' => 5,
                ],
                '%' => [
                    'min' => 0,
                    'max' => 100,
                    'step' => 5
                ],
            ],
            'default' => ['size' => '100', 'unit' => '%'],
            'label_inline' => true,
            'conditions' => [
                'button_width_type' => 'custom',
            ],
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
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-align: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('button_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} button[type="submit"]',
            'prefix'   => 'btn',
        ]);

        $this->start_tabs('tabs_button_style');

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

        $this->end_tab();
        $this->end_tabs();

        $this->add_responsive_control('button_padding', [
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
            'prefix'   => 'btn',
        ]);

        $this->add_group_control('button_box_shadow', [
            'type'     => Controls::GROUP_BOX_SHADOW,
            'label'    => __('Box Shadow', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} button[type="submit"]',
            'prefix'   => 'btn',
        ]);

        $this->end_section();

        // SECTION 5: MESSAGES & VALIDATION (Often ignored in free versions)
        $this->start_section('section_message_style', [
            'label' => __('Messages & Validation', 'dragwyb-form-builder'),
        ]);

        $this->add_control('success_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Success Text Color', 'dragwyb-form-builder'),
            'default'   => '#15803d',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-success-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('success_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            'default'   => '#dcfce7',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-success-bg: {{VALUE}};',
            ],
        ]);

        $this->add_control('error_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Error / Validation Color', 'dragwyb-form-builder'),
            'default'   => '#b91c1c',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-error-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('error_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            'default'   => '#fee2e2',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-error-bg: {{VALUE}};',
            ],
        ]);

        $this->end_section();
    }
}
