<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Style_Settings;

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Settings extends Register_Controls_Base {

	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function init(): void {}

	protected function register_controls(): void {
		// SECTION 1: FORM CONTAINER
		$this->start_section(
			'section_form_container',
			array(
				'label' => __( 'Form Container', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_group_control(
			'form_container_bg',
			array(
				'type'     => Controls::GROUP_BACKGROUND,
				'label'    => __( 'Background', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}}',
			)
		);

		$this->add_group_control(
			'form_css_filter',
			array(
				'type'     => Controls::GROUP_CSS_FILTER,
				'label'    => __( 'CSS Filter', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}}',
				'backdrop' => true,
			)
		);

		$this->add_group_control(
			'form_border',
			array(
				'type'     => Controls::GROUP_BORDER,
				'label'    => __( 'Border', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}}',
			)
		);

		$this->add_group_control(
			'form_box_shadow',
			array(
				'type'     => Controls::GROUP_BOX_SHADOW,
				'label'    => __( 'Box Shadow', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}}',
			)
		);

		$this->add_control(
			'form_justify_content',
			array(
				'type'      => Controls::CHOOSE,
				'label'     => __( 'Justify Content', 'smart-form-builder-by-dragwyb' ),
				'default'   => 'left',
				'options'   => array(
					'left'   => array(
						'title' => 'Left',
						'icon'  => 'fa fa-align-left',
					),
					'center' => array(
						'title' => 'Center',
						'icon'  => 'fa fa-align-center',
					),
					'right'  => array(
						'title' => 'Right',
						'icon'  => 'fa fa-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-form-justify-content: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'form_width',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Width', 'smart-form-builder-by-dragwyb' ),
				'units'     => array( 'px', 'em', '%' ),
				'range'     => array(
					'px' => array(
						'min'  => 0,
						'max'  => 2000,
						'step' => 5,
					),
					'%'  => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 5,
					),
					'em' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 5,
					),
				),
				'selectors' => array(
					'{{WRAPPER}}' => 'width: {{VALUE}}{{UNIT}}; --dragwyb-form-width: {{VALUE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'form_margin',
			array(
				'type'      => Controls::DIMENSIONS,
				'label'     => __( 'Margin', 'smart-form-builder-by-dragwyb' ),
				'units'     => array( 'px', 'em', '%' ),
				'selectors' => array(
					'{{WRAPPER}}' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --dragwyb-form-mt: {{TOP}}{{UNIT}}; --dragwyb-form-mr: {{RIGHT}}{{UNIT}}; --dragwyb-form-mb: {{BOTTOM}}{{UNIT}}; --dragwyb-form-ml: {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'form_padding',
			array(
				'type'      => Controls::DIMENSIONS,
				'label'     => __( 'Padding', 'smart-form-builder-by-dragwyb' ),
				'units'     => array( 'px', 'em', '%' ),
				'selectors' => array(
					'{{WRAPPER}}' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --dragwyb-form-pt: {{TOP}}{{UNIT}}; --dragwyb-form-pr: {{RIGHT}}{{UNIT}}; --dragwyb-form-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-form-pl: {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'field_spacing',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Row Gap', 'smart-form-builder-by-dragwyb' ),
				'default'   => array(
					'size' => 15,
					'unit' => 'px',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'units'     => array( 'px', '%' ),
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-field-gap: {{VALUE}}{{UNIT}};',
				),
			)
		);

		$this->end_section();

		// SECTION 2: LABELS & HELP TEXT
		$this->start_section(
			'section_label_style',
			array(
				'label' => __( 'Labels & Help Text', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'label_position',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Label Style', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'top'      => __( 'Top Aligned', 'smart-form-builder-by-dragwyb' ),
					'left'     => __( 'Left Aligned', 'smart-form-builder-by-dragwyb' ),
					'floating' => __( 'Floating Label', 'smart-form-builder-by-dragwyb' ),
					'hidden'   => __( 'Hidden', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'top',
				'label_inline' => true,
			)
		);

		$this->add_control(
			'floating_style',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Floating Style', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'outlined' => __( 'Outlined (On Border)', 'smart-form-builder-by-dragwyb' ),
					'inside'   => __( 'Inside (Filled / Box)', 'smart-form-builder-by-dragwyb' ),
					'hide'     => __( 'Inside & Hide on Type', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'outlined',
				'label_inline' => true,
				'conditions'   => array(
					'label_position' => 'floating',
				),
			)
		);

		$this->add_control(
			'label_icon_position',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Icon Position', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'before' => __( 'Before Label', 'smart-form-builder-by-dragwyb' ),
					'after'  => __( 'After Label', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'before',
				'label_inline' => true,
				'conditions'   => array(
					'label_position!' => 'hidden',
				),
			)
		);

		$this->add_responsive_control(
			'label_icon_size',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Icon Size', 'smart-form-builder-by-dragwyb' ),
				'units'     => array( 'px', 'em', '%' ),
				'range'     => array(
					'px' => array(
						'min' => 5,
						'max' => 100,
					),
					'em' => array(
						'min'  => 0.1,
						'max'  => 10,
						'step' => 0.1,
					),
					'%'  => array(
						'min'  => 10,
						'max'  => 200,
						'step' => 5,
					),
				),
				'default'   => array(
					'size' => 18,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-label-icon-size: {{VALUE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'label_icon_spacing',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Icon Spacing', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-label-icon-spacing: {{VALUE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'floating_active_color',
			array(
				'type'       => Controls::COLOR,
				'label'      => __( 'Focus Border Color', 'smart-form-builder-by-dragwyb' ),
				'selectors'  => array(
					'{{WRAPPER}}' => '--dragwyb-float-active: {{VALUE}};',
				),
				'conditions' => array(
					'label_position'  => 'floating',
					'floating_style!' => 'hide',
				),
			)
		);

		$this->add_control(
			'label_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Label Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-label' => 'color: {{VALUE}};',
					'{{WRAPPER}}'                      => '--dragwyb-label-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'required_asterisk_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Required Asterisk Color', 'smart-form-builder-by-dragwyb' ),
				'default'   => '#ef4444',
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-required' => 'color: {{VALUE}};',
					'{{WRAPPER}}'                   => '--dragwyb-asterisk-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'help_text_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Help Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-help' => 'color: {{VALUE}};',
					'{{WRAPPER}}'                     => '--dragwyb-help-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			'label_typography',
			array(
				'type'     => Controls::GROUP_TYPOGRAPHY,
				'label'    => __( 'Typography', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}} .dragwyb-field-label',
				'prefix'   => 'label',
			)
		);

		$this->add_responsive_control(
			'label_spacing',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Spacing (Bottom)', 'smart-form-builder-by-dragwyb' ),
				'default'   => array(
					'size' => 6,
					'unit' => 'px',
				),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-label' => 'margin-bottom: {{VALUE}}{{UNIT}};',
					'{{WRAPPER}}'                      => '--dragwyb-label-spacing: {{VALUE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			'help_typography',
			array(
				'type'     => Controls::GROUP_TYPOGRAPHY,
				'label'    => __( 'Help Text Typography', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}} .dragwyb-field-help',
				'prefix'   => 'help-text',
			)
		);

		$this->end_section();

		// SECTION 3: INPUT FIELDS
		$this->start_section(
			'section_input_style',
			array(
				'label' => __( 'Input Fields', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->start_tabs( 'tabs_input_states' );

		$this->start_tab( 'tab_input_normal', array( 'label' => __( 'Normal', 'smart-form-builder-by-dragwyb' ) ) );

		$this->add_control(
			'input_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-input' => 'background: {{VALUE}};',
					'{{WRAPPER}}'                      => '--dragwyb-input-bg: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'input_text_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-input' => 'color: {{VALUE}};',
					'{{WRAPPER}}'                      => '--dragwyb-input-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'input_placeholder_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Placeholder Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-input::placeholder' => 'color: {{VALUE}};',
					'{{WRAPPER}}' => '--dragwyb-input-placeholder-color: {{VALUE}};',
				),
			)
		);

		$this->end_tab();

		$this->start_tab( 'tab_input_focus', array( 'label' => __( 'Focus', 'smart-form-builder-by-dragwyb' ) ) );

		$this->add_control(
			'input_focus_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-input:focus' => 'background: {{VALUE}};',
					'{{WRAPPER}}' => '--dragwyb-input-focus-bg: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'input_focus_border_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Border Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-input:focus' => 'border-color: {{VALUE}};',
					'{{WRAPPER}}' => '--dragwyb-input-focus-border: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			'input_focus_box_shadow',
			array(
				'type'     => Controls::GROUP_BOX_SHADOW,
				'label'    => __( 'Box Shadow', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}} .dragwyb-field-input:focus',
				'prefix'   => 'input',
			)
		);

		$this->end_tab();
		$this->end_tabs();

		$this->add_group_control(
			'input_typography',
			array(
				'type'     => Controls::GROUP_TYPOGRAPHY,
				'label'    => __( 'Typography', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}} .dragwyb-field-input',
				'prefix'   => 'input',
			)
		);

		$this->add_responsive_control(
			'input_padding',
			array(
				'type'      => Controls::DIMENSIONS,
				'label'     => __( 'Padding', 'smart-form-builder-by-dragwyb' ),
				'units'     => array( 'px', 'em' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-input' => 'padding-top: {{TOP}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}}; padding-left: {{LEFT}}{{UNIT}};',
					'{{WRAPPER}}'                      => '--dragwyb-input-pt: {{TOP}}{{UNIT}}; --dragwyb-input-pr: {{RIGHT}}{{UNIT}}; --dragwyb-input-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-input-pl: {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			'input_border',
			array(
				'type'     => Controls::GROUP_BORDER,
				'label'    => __( 'Border', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}} .dragwyb-field-input',
				'prefix'   => 'input',
			)
		);

		$this->end_section();

		// SECTION: RADIO & CHECKBOX FIELDS
		$this->start_section(
			'section_radio_style',
			array(
				'label' => __( 'Radio & Checkbox Fields', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'option_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-radio-label' => 'color: {{VALUE}};',
					'{{WRAPPER}}'                      => '--dragwyb-option-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			'option_typography',
			array(
				'type'     => Controls::GROUP_TYPOGRAPHY,
				'label'    => __( 'Typography', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}} .dragwyb-radio-label',
				'prefix'   => 'option',
			)
		);

		$this->add_responsive_control(
			'radio_size',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Size', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 10,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-size: {{VALUE}}{{UNIT}};' ),
			)
		);

		$this->add_control(
			'radio_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Radio Background Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-bg-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'radio_border_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Border Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-border-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'radio_active_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Active / Checked Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-primary-color: {{VALUE}};' ),
			)
		);

		$this->add_responsive_control(
			'radio_option_spacing',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Option Spacing', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-toggle-spacing: {{VALUE}}{{UNIT}};' ),
			)
		);

		$this->end_section();

		// SECTION 4: STEPS STYLING
		$this->start_section(
			'section_steps_style',
			array(
				'label' => __( 'Steps Field', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'step_indicator_type',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Indicator Type', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'numbers'  => __( 'Numbers & Labels', 'smart-form-builder-by-dragwyb' ),
					'progress' => __( 'Progress Bar', 'smart-form-builder-by-dragwyb' ),
					'dots'     => __( 'Dots Only', 'smart-form-builder-by-dragwyb' ),
					'none'     => __( 'None', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'numbers',
				'label_inline' => true,
			)
		);

		$this->add_control(
			'step_divider_style',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Line Style', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'solid'  => __( 'Solid', 'smart-form-builder-by-dragwyb' ),
					'dashed' => __( 'Dashed', 'smart-form-builder-by-dragwyb' ),
					'dotted' => __( 'Dotted', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'solid',
				'label_inline' => true,
				'conditions'   => array(
					'step_indicator_type' => array( 'numbers', 'dots' ),
				),
				'selectors'    => array(
					'{{WRAPPER}}' => '--dragwyb-step-divider-style: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'step_dot_size',
			array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Dot Size', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 100,
					),
				),
				'conditions' => array(
					'step_indicator_type' => array( 'numbers', 'dots' ),
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--dragwyb-step-dot-size: {{VALUE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'step_divider_thickness',
			array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Line Thickness', 'smart-form-builder-by-dragwyb' ),
				'default'    => array(
					'size' => 2,
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 1,
						'max' => 10,
					),
				),
				'conditions' => array(
					'step_indicator_type!' => 'none',
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--dragwyb-step-divider-thickness: {{VALUE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'step_bottom_spacing',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Step Bottom Spacing', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'units'     => array( 'px', '%' ),
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-step-bottom-spacing: {{VALUE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			'step_typography',
			array(
				'type'       => Controls::GROUP_TYPOGRAPHY,
				'label'      => __( 'Step Typography', 'smart-form-builder-by-dragwyb' ),
				'selector'   => '{{WRAPPER}}',
				'prefix'     => 'step',
				'conditions' => array(
					'step_indicator_type!' => 'none',
				),
			)
		);

		$this->add_control(
			'step_active_color',
			array(
				'type'       => Controls::COLOR,
				'label'      => __( 'Active Color', 'smart-form-builder-by-dragwyb' ),
				'default'    => '#1d4ed8',
				'conditions' => array(
					'step_indicator_type!' => 'none',
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--dragwyb-step-active-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'step_inactive_color',
			array(
				'type'       => Controls::COLOR,
				'label'      => __( 'Inactive Color', 'smart-form-builder-by-dragwyb' ),
				'default'    => '#9ca3af',
				'conditions' => array(
					'step_indicator_type' => array( 'numbers', 'dots' ),
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--dragwyb-step-inactive-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'step_text_color',
			array(
				'type'       => Controls::COLOR,
				'label'      => __( 'Divider Text Color', 'smart-form-builder-by-dragwyb' ),
				'default'    => '#374151',
				'conditions' => array(
					'step_indicator_type!' => 'none',
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--dragwyb-step-text-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'step_line_color',
			array(
				'type'       => Controls::COLOR,
				'label'      => __( 'Line/Connector Color', 'smart-form-builder-by-dragwyb' ),
				'default'    => '#e5e7eb',
				'conditions' => array(
					'step_indicator_type!' => 'none',
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--dragwyb-step-line-color: {{VALUE}};',
				),
			)
		);

		$this->end_section();

		// SECTION 5: BUTTON
		$this->start_section(
			'section_button_style',
			array(
				'label' => __( 'Button', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'button_width_type',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Width Type', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'auto'   => 'Auto',
					'custom' => 'Custom',
				),
				'default'      => 'custom',
				'label_inline' => true,
			)
		);

		$this->add_responsive_control(
			'button_width',
			array(
				'type'         => Controls::SLIDER,
				'label'        => __( 'Width', 'smart-form-builder-by-dragwyb' ),
				'units'        => array( 'px', '%', 'em', 'rem' ),
				'range'        => array(
					'px'  => array(
						'min'  => 0,
						'max'  => 1000,
						'step' => 5,
					),
					'%'   => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 5,
					),
					'em'  => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 5,
					),
					'rem' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 5,
					),
				),
				'default'      => array(
					'size' => '100',
					'unit' => '%',
				),
				'label_inline' => true,
				'conditions'   => array(
					'button_width_type' => 'custom',
				),
				'selectors'    => array(
					'{{WRAPPER}} button' => 'width: {{VALUE}}{{UNIT}};',
					'{{WRAPPER}}'        => '--dragwyb-btn-width: {{VALUE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'button_alignment',
			array(
				'type'      => Controls::CHOOSE,
				'label'     => __( 'Alignment', 'smart-form-builder-by-dragwyb' ),
				'options'   => array(
					'left'   => array(
						'title' => __( 'Left', 'smart-form-builder-by-dragwyb' ),
						'icon'  => 'fa fa-align-left',
					),
					'center' => array(
						'title' => __( 'Center', 'smart-form-builder-by-dragwyb' ),
						'icon'  => 'fa fa-align-center',
					),
					'right'  => array(
						'title' => __( 'Right', 'smart-form-builder-by-dragwyb' ),
						'icon'  => 'fa fa-align-right',
					),
				),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-button-field' => 'text-align: {{VALUE}};',
					'{{WRAPPER}}'                       => '--dragwyb-btn-align: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			'button_typography',
			array(
				'type'     => Controls::GROUP_TYPOGRAPHY,
				'label'    => __( 'Typography', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}} button',
				'prefix'   => 'btn',
			)
		);
		$this->add_control(
			'submit_button_heading',
			array(
				'type'       => Controls::HEADING,
				'label'      => __( 'Submit / Next Button', 'smart-form-builder-by-dragwyb' ),
				'conditions' => array(
					'step_indicator_type!' => 'none',
				),
			)
		);

		$this->start_tabs( 'tabs_button_style' );

		$this->start_tab( 'tab_button_normal', array( 'label' => __( 'Normal', 'smart-form-builder-by-dragwyb' ) ) );

		$this->add_control(
			'button_text_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} button' => 'color: {{VALUE}};',
					'{{WRAPPER}}'        => '--dragwyb-btn-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} button' => 'background: {{VALUE}};',
					'{{WRAPPER}}'        => '--dragwyb-btn-bg: {{VALUE}};',
				),
			)
		);

		$this->end_tab();

		$this->start_tab( 'tab_button_hover', array( 'label' => __( 'Hover', 'smart-form-builder-by-dragwyb' ) ) );

		$this->add_control(
			'button_hover_text_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} button:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}}'              => '--dragwyb-btn-hover-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'button_hover_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} button:hover' => 'background: {{VALUE}};',
					'{{WRAPPER}}'              => '--dragwyb-btn-hover-bg: {{VALUE}};',
				),
			)
		);

		$this->end_tab();
		$this->end_tabs();

		$this->add_control(
			'heading_prev_button',
			array(
				'type'       => Controls::HEADING,
				'label'      => __( 'Previous Button', 'smart-form-builder-by-dragwyb' ),
				'conditions' => array(
					'step_indicator_type!' => 'none',
				),
			)
		);

		$this->start_tabs(
			'tabs_prev_button_style',
			array(
				'conditions' => array(
					'step_indicator_type!' => 'none',
				),

			)
		);

		$this->start_tab(
			'tab_prev_button_normal',
			array(
				'label' => __( 'Normal', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'prev_button_text_color',
			array(
				'type'       => Controls::COLOR,
				'label'      => __( 'Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors'  => array(
					'{{WRAPPER}} button.dragwyb-button-prev' => 'color: {{VALUE}};',
					'{{WRAPPER}}' => '--dragwyb-prev-btn-color: {{VALUE}};',
				),
				'conditions' => array(
					'step_indicator_type!' => 'none',
				),
			)
		);

		$this->add_control(
			'prev_button_bg_color',
			array(
				'type'       => Controls::COLOR,
				'label'      => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'selectors'  => array(
					'{{WRAPPER}} button.dragwyb-button-prev' => 'background: {{VALUE}};',
					'{{WRAPPER}}' => '--dragwyb-prev-btn-bg: {{VALUE}};',
				),
				'conditions' => array(
					'step_indicator_type!' => 'none',
				),
			)
		);

		$this->end_tab();

		$this->start_tab(
			'tab_prev_button_hover',
			array(
				'label' => __( 'Hover', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'prev_button_hover_text_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} button.dragwyb-button-prev:hover' => 'color: {{VALUE}};',
					'{{WRAPPER}}' => '--dragwyb-prev-btn-hover-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'prev_button_hover_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} button.dragwyb-button-prev:hover' => 'background: {{VALUE}};',
					'{{WRAPPER}}' => '--dragwyb-prev-btn-hover-bg: {{VALUE}};',
				),
			)
		);

		$this->end_tab();
		$this->end_tabs();

		$this->add_responsive_control(
			'button_padding',
			array(
				'type'      => Controls::DIMENSIONS,
				'label'     => __( 'Padding', 'smart-form-builder-by-dragwyb' ),
				'units'     => array( 'px', 'em' ),
				'selectors' => array(
					'{{WRAPPER}} button' => 'padding-top: {{TOP}}{{UNIT}}; padding-right: {{RIGHT}}{{UNIT}}; padding-bottom: {{BOTTOM}}{{UNIT}}; padding-left: {{LEFT}}{{UNIT}};',
					'{{WRAPPER}}'        => '--dragwyb-btn-pt: {{TOP}}{{UNIT}}; --dragwyb-btn-pr: {{RIGHT}}{{UNIT}}; --dragwyb-btn-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-btn-pl: {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			'button_border',
			array(
				'type'     => Controls::GROUP_BORDER,
				'label'    => __( 'Border', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}} button',
				'prefix'   => 'btn',
			)
		);

		$this->add_group_control(
			'button_box_shadow',
			array(
				'type'     => Controls::GROUP_BOX_SHADOW,
				'label'    => __( 'Box Shadow', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}} button',
				'prefix'   => 'btn',
			)
		);

		$this->end_section();

		// SECTION 6: MESSAGES & VALIDATION (Often ignored in free versions)
		$this->start_section(
			'section_message_style',
			array(
				'label' => __( 'Messages & Validation', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'success_text_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Success Text Color', 'smart-form-builder-by-dragwyb' ),
				'default'   => '#15803d',
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-success-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'success_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'default'   => '#dcfce7',
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-success-bg: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'error_text_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Error / Validation Color', 'smart-form-builder-by-dragwyb' ),
				'default'   => '#b91c1c',
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-error-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'error_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'default'   => '#fee2e2',
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-error-bg: {{VALUE}};',
				),
			)
		);

		$this->end_section();
	}
}
