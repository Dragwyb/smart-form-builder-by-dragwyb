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
            'label' => __('Form Container', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_group_control('form_container_bg', [
            'type'      => Controls::GROUP_BACKGROUND,
            'label'     => __('Background', 'smart-form-builder-by-dragwyb'),
            'selector'  => '{{WRAPPER}}',
        ]);

        $this->add_group_control('form_css_filter', [
            'type'      => Controls::GROUP_CSS_FILTER,
            'label'     => __('CSS Filter', 'smart-form-builder-by-dragwyb'),
            'selector'  => '{{WRAPPER}}',
        ]);

        $this->add_group_control('form_border', [
            'type'      => Controls::GROUP_BORDER,
            'label'     => __('Border', 'smart-form-builder-by-dragwyb'),
            'selector'  => '{{WRAPPER}}',
        ]);

        $this->add_group_control('form_box_shadow', [
            'type'      => Controls::GROUP_BOX_SHADOW,
            'label'     => __('Box Shadow', 'smart-form-builder-by-dragwyb'),
            'selector'  => '{{WRAPPER}}',
        ]);

        $this->add_control('form_justify_content', [
            'type'      => Controls::CHOOSE,
            'label'     => __('Justify Content', 'smart-form-builder-by-dragwyb'),
            'default'   => 'left',
            'options' => [
                'left' => ['title' => 'Left',   'icon' => 'fa fa-align-left'],
                'center'     => ['title' => 'Center', 'icon' => 'fa fa-align-center'],
                'right'   => ['title' => 'Right',  'icon' => 'fa fa-align-right'],
            ],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-form-justify-content: {{VALUE}};',
            ],
        ]);

        $this->add_responsive_control('form_margin', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Margin', 'smart-form-builder-by-dragwyb'),
            'units'      => ['px', 'em', '%'],
            'selectors'  => [
                '{{WRAPPER}}' => '--dragwyb-form-mt: {{TOP}}{{UNIT}}; --dragwyb-form-mr: {{RIGHT}}{{UNIT}}; --dragwyb-form-mb: {{BOTTOM}}{{UNIT}}; --dragwyb-form-ml: {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('form_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'smart-form-builder-by-dragwyb'),
            'units'      => ['px', 'em', '%'],
            'default'    => ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20, 'unit' => 'px', 'linked' => false],
            'selectors'  => [
                '{{WRAPPER}}' => '--dragwyb-form-pt: {{TOP}}{{UNIT}}; --dragwyb-form-pr: {{RIGHT}}{{UNIT}}; --dragwyb-form-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-form-pl: {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_responsive_control('field_spacing', [
            'type'      => Controls::SLIDER,
            'label'     => __('Row Gap', 'smart-form-builder-by-dragwyb'),
            'default'   => ['size' => 15, 'unit' => 'px'],
            'range'     => ['px' => ['min' => 0, 'max' => 100]],
            'units'     => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-field-gap: {{VALUE}}{{UNIT}};',
            ],
        ]);

        $this->end_section();

        // SECTION 2: LABELS & HELP TEXT
        $this->start_section('section_label_style', [
            'label' => __('Labels & Help Text', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('label_position', [
            'type'    => Controls::SELECT,
            'label'   => __('Label Layout', 'smart-form-builder-by-dragwyb'),
            'options' => [
                'top'       => __('Top Aligned (Standard)', 'smart-form-builder-by-dragwyb'),
                'left'      => __('Left Aligned (Horizontal)', 'smart-form-builder-by-dragwyb'),
                'floating'  => __('Floating Label (Modern)', 'smart-form-builder-by-dragwyb'),
                'hidden'    => __('Hidden (Screen Reader Only)', 'smart-form-builder-by-dragwyb'),
            ],
            'default' => 'top',
            'label_inline' => true,
        ]);

        $this->add_control('floating_style', [
            'type'    => Controls::SELECT,
            'label'   => __('Floating Style', 'smart-form-builder-by-dragwyb'),
            'options' => [
                'outlined' => __('Outlined (On Border)', 'smart-form-builder-by-dragwyb'),
                'inside'   => __('Inside (Filled / Box)', 'smart-form-builder-by-dragwyb'),
            ],
            'default' => 'outlined',
            'label_inline' => true,
            'conditions' => [
                'label_position' => 'floating',
            ],
        ]);

        $this->add_control('floating_active_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Focus Border Color', 'smart-form-builder-by-dragwyb'),
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
            'label'     => __('Label Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#374151',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-label-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('required_asterisk_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Required Asterisk Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#ef4444',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-asterisk-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('help_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Help Text Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#6b7280',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-help-color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('label_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}}',
            'prefix'   => 'label',
        ]);

        $this->add_responsive_control('label_spacing', [
            'type'      => Controls::SLIDER,
            'label'     => __('Spacing (Bottom)', 'smart-form-builder-by-dragwyb'),
            'default'   => ['size' => 6, 'unit' => 'px'],
            'range'     => ['px' => ['min' => 0, 'max' => 50]],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-label-spacing: {{VALUE}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control('help_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Help Text Typography', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}}',
            'prefix'   => 'help-text',
        ]);

        $this->end_section();

        // SECTION 3: INPUT FIELDS
        $this->start_section('section_input_style', [
            'label' => __('Input Fields', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('input_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-input-bg: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#111827',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-input-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_placeholder_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Placeholder Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#9ca3af',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-input-placeholder: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('input_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}}',
            'prefix'   => 'input',
        ]);

        $this->add_responsive_control('input_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'smart-form-builder-by-dragwyb'),
            'units'      => ['px', 'em'],
            'selectors'  => [
                '{{WRAPPER}}' => '--dragwyb-input-pt: {{TOP}}{{UNIT}}; --dragwyb-input-pr: {{RIGHT}}{{UNIT}}; --dragwyb-input-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-input-pl: {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control('input_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}}',
            'prefix'   => 'input',
        ]);

        $this->start_tabs('tabs_input_states');

        $this->start_tab('tab_input_normal', ['label' => __('Normal', 'smart-form-builder-by-dragwyb')]);
        $this->end_tab();

        $this->start_tab('tab_input_focus', ['label' => __('Focus', 'smart-form-builder-by-dragwyb')]);

        $this->add_control('input_focus_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-input-focus-bg: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_focus_border_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Border Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-input-focus-border: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('input_focus_box_shadow', [
            'type'     => Controls::GROUP_BOX_SHADOW,
            'label'    => __('Box Shadow', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}}',
            'prefix'   => 'input',
        ]);

        $this->end_tab();
        $this->end_tabs();
        $this->end_section();

        // SECTION 4: BUTTON
        $this->start_section('section_button_style', [
            'label' => __('Button', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('button_width_type', [
            'type'    => Controls::SELECT,
            'label'   => __('Width Type', 'smart-form-builder-by-dragwyb'),
            'options' => [
                'auto' => 'Auto',
                'custom' => 'Custom',
            ],
            'default' => 'auto',
            'label_inline' => true,
        ]);

        $this->add_responsive_control('button_width', [
            'type'    => Controls::SLIDER,
            'label'   => __('Width', 'smart-form-builder-by-dragwyb'),
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
                '{{WRAPPER}}' => '--dragwyb-btn-width: {{VALUE}}{{UNIT}};',
            ],
        ]);

        $this->add_control('button_alignment', [
            'type'      => Controls::CHOOSE,
            'label'     => __('Alignment', 'smart-form-builder-by-dragwyb'),
            'options'   => [
                'left'    => ['title' => __('Left', 'smart-form-builder-by-dragwyb'), 'icon' => 'fa fa-align-left'],
                'center'  => ['title' => __('Center', 'smart-form-builder-by-dragwyb'), 'icon' => 'fa fa-align-center'],
                'right'   => ['title' => __('Right', 'smart-form-builder-by-dragwyb'), 'icon' => 'fa fa-align-right'],
            ],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-align: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('button_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}}',
            'prefix'   => 'btn',
        ]);

        $this->start_tabs('tabs_button_style');

        $this->start_tab('tab_button_normal', ['label' => __('Normal', 'smart-form-builder-by-dragwyb')]);

        $this->add_control('button_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#ffffff',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#1d4ed8',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-bg: {{VALUE}};',
            ],
        ]);

        $this->end_tab();

        $this->start_tab('tab_button_hover', ['label' => __('Hover', 'smart-form-builder-by-dragwyb')]);

        $this->add_control('button_hover_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-hover-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_hover_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#1e40af',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-hover-bg: {{VALUE}};',
            ],
        ]);

        $this->end_tab();
        $this->end_tabs();

        $this->add_responsive_control('button_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'smart-form-builder-by-dragwyb'),
            'units'      => ['px', 'em'],
            'selectors'  => [
                '{{WRAPPER}}' => '--dragwyb-btn-pt: {{TOP}}{{UNIT}}; --dragwyb-btn-pr: {{RIGHT}}{{UNIT}}; --dragwyb-btn-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-btn-pl: {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control('button_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}}',
            'prefix'   => 'btn',
        ]);

        $this->add_group_control('button_box_shadow', [
            'type'     => Controls::GROUP_BOX_SHADOW,
            'label'    => __('Box Shadow', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}}',
            'prefix'   => 'btn',
        ]);

        $this->end_section();

        // SECTION 5: MESSAGES & VALIDATION (Often ignored in free versions)
        $this->start_section('section_message_style', [
            'label' => __('Messages & Validation', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('success_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Success Text Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#15803d',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-success-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('success_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#dcfce7',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-success-bg: {{VALUE}};',
            ],
        ]);

        $this->add_control('error_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Error / Validation Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#b91c1c',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-error-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('error_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'smart-form-builder-by-dragwyb'),
            'default'   => '#fee2e2',
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-error-bg: {{VALUE}};',
            ],
        ]);

        $this->end_section();
    }
}
