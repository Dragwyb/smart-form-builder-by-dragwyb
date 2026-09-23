<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Helper\Helper;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Date extends Field_Base {

	protected function register_scripts() {
		return $this->flatpickr_enabled() ? array( 'dragwyb-flatpickr-fields' ) : array();
	}

	protected function register_styles() {
		return $this->flatpickr_enabled() ? array( 'dragwyb-flatpickr' ) : array();
	}

	private function flatpickr_enabled(): bool {
		$settings = $this->get_field_settings();
		return empty( $settings ) || $this->field_key_exist( $settings, 'use_native_date', 'no' ) !== 'yes';
	}

	public function __construct() {
		parent::__construct();
		$this->register_date_assets();
	}

	private function register_date_assets(): void {
		Helper::register_flatpickr_assets();

		if ( ! wp_script_is( 'dragwyb-flatpickr-fields', 'registered' ) ) {
			wp_register_script(
				'dragwyb-flatpickr-fields',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/flatpickr-fields.js' ),
				array( 'jquery', 'dragwyb-form-frontend', 'dragwyb-flatpickr' ),
				DRAGWYB_FORM_BUILDER_VERSION,
				true
			);
		}
	}

	private function flatpickr_conditions(): array {
		return array(
			'use_native_date!' => 'yes',
		);
	}

	protected function register_field_controls(): void {
		$this->start_section(
			'section_content_general',
			array(
				'label' => __( 'General Settings', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::ContentTab,
			)
		);

		$this->add_control(
			'label',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Label', 'smart-form-builder-by-dragwyb' ),
				'default' => __( 'Select Date', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'label_icon',
			array(
				'type'  => Controls::ICON,
				'label' => __( 'Label Icon', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'placeholder',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Placeholder', 'smart-form-builder-by-dragwyb' ),
				'default' => 'YYYY-MM-DD',
			)
		);

		$this->add_control(
			'use_native_date',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Native HTML5', 'smart-form-builder-by-dragwyb' ),
				'description'  => __( 'Use the browser native date input instead of Flatpickr.', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'help_text',
			array(
				'type'  => Controls::TEXTAREA,
				'label' => __( 'Help Text', 'smart-form-builder-by-dragwyb' ),
				'rows'  => 3,
			)
		);

		$this->add_control(
			'required',
			array(
				'type'  => Controls::SWITCHER,
				'label' => __( 'Required', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->end_section();

		$this->start_section(
			'section_content_flatpickr',
			array(
				'label'      => __( 'Flatpickr Settings', 'smart-form-builder-by-dragwyb' ),
				'tab'        => self::ContentTab,
				'conditions' => $this->flatpickr_conditions(),
			)
		);

		$this->add_control(
			'picker_type',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Picker Type', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'date'     => __( 'Date', 'smart-form-builder-by-dragwyb' ),
					'datetime' => __( 'Date & Time', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'date',
				'label_inline' => true,
			)
		);

		$this->add_control(
			'selection_mode',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Selection Mode', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'single'   => __( 'Single', 'smart-form-builder-by-dragwyb' ),
					'multiple' => __( 'Multiple', 'smart-form-builder-by-dragwyb' ),
					'range'    => __( 'Range', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'single',
				'label_inline' => true,
			)
		);

		$this->add_control(
			'conjunction',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Multiple Conjunction', 'smart-form-builder-by-dragwyb' ),
				'default'     => ', ',
				'description' => __( 'Separator between multiple selected dates.', 'smart-form-builder-by-dragwyb' ),
				'conditions'  => array( 'selection_mode' => 'multiple' ),
			)
		);

		$this->add_control(
			'date_format',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Date Format', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'Y-m-d' => __( 'YYYY-MM-DD', 'smart-form-builder-by-dragwyb' ),
					'Y/m/d' => __( 'YYYY/MM/DD', 'smart-form-builder-by-dragwyb' ),
					'd/m/Y' => __( 'DD/MM/YYYY', 'smart-form-builder-by-dragwyb' ),
					'd-m-Y' => __( 'DD-MM-YYYY', 'smart-form-builder-by-dragwyb' ),
					'm/d/Y' => __( 'MM/DD/YYYY', 'smart-form-builder-by-dragwyb' ),
					'm-d-Y' => __( 'MM-DD-YYYY', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'Y-m-d',
				'label_inline' => true,
			)
		);

		$this->add_control(
			'time_24hr',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( '24-Hour Time', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'no',
				'conditions'   => array( 'picker_type' => 'datetime' ),
			)
		);

		$this->add_control(
			'min_time',
			array(
				'type'        => Controls::DATE,
				'label'       => __( 'Min Time', 'smart-form-builder-by-dragwyb' ),
				'picker'      => 'time',
				'date_format' => 'H:i',
				'description' => __( 'Earliest selectable time.', 'smart-form-builder-by-dragwyb' ),
				'conditions'  => array( 'picker_type' => 'datetime' ),
			)
		);

		$this->add_control(
			'max_time',
			array(
				'type'        => Controls::DATE,
				'label'       => __( 'Max Time', 'smart-form-builder-by-dragwyb' ),
				'picker'      => 'time',
				'date_format' => 'H:i',
				'description' => __( 'Latest selectable time.', 'smart-form-builder-by-dragwyb' ),
				'conditions'  => array( 'picker_type' => 'datetime' ),
			)
		);

		$this->add_control(
			'alt_input',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Human-friendly Dates', 'smart-form-builder-by-dragwyb' ),
				'description'  => __( 'Show a readable date while storing the date format value.', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'alt_format',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Alt Format', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'F j, Y'    => __( 'January 15, 2026', 'smart-form-builder-by-dragwyb' ),
					'M j, Y'    => __( 'Jan 15, 2026', 'smart-form-builder-by-dragwyb' ),
					'j F Y'     => __( '15 January 2026', 'smart-form-builder-by-dragwyb' ),
					'd/m/Y'     => __( '15/01/2026', 'smart-form-builder-by-dragwyb' ),
					'm/d/Y'     => __( '01/15/2026', 'smart-form-builder-by-dragwyb' ),
					'Y-m-d'     => __( '2026-01-15', 'smart-form-builder-by-dragwyb' ),
					'l, F j, Y' => __( 'Thursday, January 15, 2026', 'smart-form-builder-by-dragwyb' ),
					'custom'    => __( 'Custom', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'F j, Y',
				'label_inline' => true,
				'description'  => __( 'Display format when human-friendly dates are enabled.', 'smart-form-builder-by-dragwyb' ),
				'conditions'   => array( 'alt_input' => 'yes' ),
			)
		);

		$this->add_control(
			'alt_format_custom',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Custom Alt Format', 'smart-form-builder-by-dragwyb' ),
				'default'     => 'F j, Y',
				'placeholder' => 'F j, Y',
				'description' => __( 'Flatpickr format tokens, e.g. F j, Y or d.m.Y.', 'smart-form-builder-by-dragwyb' ),
				'conditions'  => array(
					'alt_input'  => 'yes',
					'alt_format' => 'custom',
				),
			)
		);

		$this->add_control(
			'inline_calendar',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Inline Calendar', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'week_numbers',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Week Numbers', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->end_section();

		$this->start_section(
			'section_content_date_limits',
			array(
				'label'      => __( 'Date Limits', 'smart-form-builder-by-dragwyb' ),
				'tab'        => self::ContentTab,
				'conditions' => $this->flatpickr_conditions(),
			)
		);

		$this->add_control(
			'min_date_mode',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Min Date', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'none'     => __( 'None', 'smart-form-builder-by-dragwyb' ),
					'today'    => __( 'Today', 'smart-form-builder-by-dragwyb' ),
					'custom'   => __( 'Custom Date', 'smart-form-builder-by-dragwyb' ),
					'relative' => __( 'Relative Days', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'none',
				'label_inline' => true,
			)
		);

		$this->add_control(
			'min_date',
			array(
				'type'        => Controls::DATE,
				'label'       => __( 'Min Date Value', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Earliest allowed date.', 'smart-form-builder-by-dragwyb' ),
				'conditions'  => array( 'min_date_mode' => 'custom' ),
			)
		);

		$this->add_control(
			'min_date_days',
			array(
				'type'        => Controls::NUMBER,
				'label'       => __( 'Min Days From Today', 'smart-form-builder-by-dragwyb' ),
				'default'     => 0,
				'description' => __( 'e.g. 0 = today, 14 = 14 days from today.', 'smart-form-builder-by-dragwyb' ),
				'conditions'  => array( 'min_date_mode' => 'relative' ),
			)
		);

		$this->add_control(
			'max_date_mode',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Max Date', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'none'     => __( 'None', 'smart-form-builder-by-dragwyb' ),
					'today'    => __( 'Today', 'smart-form-builder-by-dragwyb' ),
					'custom'   => __( 'Custom Date', 'smart-form-builder-by-dragwyb' ),
					'relative' => __( 'Relative Days', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'none',
				'label_inline' => true,
			)
		);

		$this->add_control(
			'max_date',
			array(
				'type'        => Controls::DATE,
				'label'       => __( 'Max Date Value', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Latest allowed date.', 'smart-form-builder-by-dragwyb' ),
				'conditions'  => array( 'max_date_mode' => 'custom' ),
			)
		);

		$this->add_control(
			'max_date_days',
			array(
				'type'        => Controls::NUMBER,
				'label'       => __( 'Max Days From Today', 'smart-form-builder-by-dragwyb' ),
				'default'     => 14,
				'description' => __( 'e.g. 14 = 14 days from today.', 'smart-form-builder-by-dragwyb' ),
				'conditions'  => array( 'max_date_mode' => 'relative' ),
			)
		);

		$this->end_section();

		$this->start_section(
			'section_content_date_enable_disable',
			array(
				'label'      => __( 'Enable / Disable Dates', 'smart-form-builder-by-dragwyb' ),
				'tab'        => self::ContentTab,
				'conditions' => $this->flatpickr_conditions(),
			)
		);

		$this->add_control(
			'disable_weekends',
			array(
				'type'         => Controls::SWITCHER,
				'label'        => __( 'Disable Weekends', 'smart-form-builder-by-dragwyb' ),
				'description'  => __( 'Disable Saturdays and Sundays.', 'smart-form-builder-by-dragwyb' ),
				'return_value' => 'yes',
				'default'      => 'no',
			)
		);

		$this->add_control(
			'disable_dates',
			array(
				'type'        => Controls::DATE,
				'label'       => __( 'Disable Dates', 'smart-form-builder-by-dragwyb' ),
				'mode'        => 'multiple',
				'description' => __( 'Pick dates to disable. You can also type ranges as from:to (e.g. 2025-04-01:2025-05-01).', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'enable_dates',
			array(
				'type'        => Controls::DATE,
				'label'       => __( 'Enable Only Dates', 'smart-form-builder-by-dragwyb' ),
				'mode'        => 'multiple',
				'description' => __( 'Pick the only dates that should be selectable. You can also type ranges as from:to.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->end_section();

		// Label Style
		$this->start_section(
			'section_style_label',
			array(
				'label' => __( 'Label Appearance', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::StyleTab,
			)
		);

		$this->add_control(
			'label_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-label' => 'color: {{VALUE}};',
					'{{WRAPPER}}'                      => '--dragwyb-label-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'label_spacing',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Bottom Margin', 'smart-form-builder-by-dragwyb' ),
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

		$this->end_section();

		// Input Style
		$this->start_section(
			'section_style_input',
			array(
				'label' => __( 'Input Box Style', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::StyleTab,
			)
		);

		$this->start_tabs( 'tabs_input_style' );

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

		$this->add_group_control(
			'input_border',
			array(
				'type'              => Controls::GROUP_BORDER,
				'selector'          => '{{WRAPPER}} .dragwyb-field-input',
				'variable_selector' => '{{WRAPPER}}',
				'prefix'            => 'input',
			)
		);

		$this->end_tab();

		$this->start_tab( 'tab_input_focus', array( 'label' => __( 'Focus', 'smart-form-builder-by-dragwyb' ) ) );

		$this->add_control(
			'input_focus_border_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Active Border Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}} .dragwyb-field-input:focus' => 'border-color: {{VALUE}};',
					'{{WRAPPER}}' => '--dragwyb-input-focus-border: {{VALUE}};',
				),
			)
		);

		$this->end_tab();

		$this->end_tabs();

		$this->add_control(
			'input_padding',
			array(
				'type'       => Controls::DIMENSIONS,
				'label'      => __( 'Inner Padding', 'smart-form-builder-by-dragwyb' ),
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .dragwyb-field-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					'{{WRAPPER}}'                      => '--dragwyb-input-pt: {{TOP}}{{UNIT}}; --dragwyb-input-pr: {{RIGHT}}{{UNIT}}; --dragwyb-input-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-input-pl: {{LEFT}}{{UNIT}};',
				),
				'separator'  => 'before',
			)
		);

		$this->end_section();
	}

	protected function init(): void {
		$this->type     = 'date';
		$this->name     = __( 'Date Field', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'far fa-calendar';
		$this->category = 'standard-fields';
		$this->keywords = array( 'calendar', 'day', 'month', 'year', 'flatpickr', 'datetime', 'range' );
	}

	private function resolve_alt_format( array $settings ): string {
		$presets = array( 'F j, Y', 'M j, Y', 'j F Y', 'd/m/Y', 'm/d/Y', 'Y-m-d', 'l, F j, Y' );
		$format  = (string) $this->field_key_exist( $settings, 'alt_format', 'F j, Y' );

		if ( 'custom' === $format ) {
			$custom = trim( (string) $this->field_key_exist( $settings, 'alt_format_custom', 'F j, Y' ) );
			return '' !== $custom ? $custom : 'F j, Y';
		}

		if ( in_array( $format, $presets, true ) ) {
			return $format;
		}

		return '' !== $format ? $format : 'F j, Y';
	}

	private function is_datetime( array $settings ): bool {
		return 'datetime' === (string) $this->field_key_exist( $settings, 'picker_type', 'date' );
	}

	private function resolve_date_format( array $settings ): string {
		$allowed = array( 'Y-m-d', 'Y/m/d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'm-d-Y' );
		$format  = (string) $this->field_key_exist( $settings, 'date_format', 'Y-m-d' );

		if ( ! in_array( $format, $allowed, true ) ) {
			$format = 'Y-m-d';
		}

		return $this->is_datetime( $settings ) ? $format . ' H:i' : $format;
	}

	/**
	 * @return array<string, mixed>
	 */
	private function build_flatpickr_config( array $settings ): array {
		$mode        = (string) $this->field_key_exist( $settings, 'selection_mode', 'single' );
		$is_datetime = $this->is_datetime( $settings );

		$config = array(
			'mode'       => $mode,
			'dateFormat' => $this->resolve_date_format( $settings ),
			'allowInput' => true,
		);

		if ( $is_datetime ) {
			$config['enableTime'] = true;
			$config['time_24hr']  = $this->field_key_exist( $settings, 'time_24hr', 'no' ) === 'yes';

			$min_time = (string) $this->field_key_exist( $settings, 'min_time', '' );
			$max_time = (string) $this->field_key_exist( $settings, 'max_time', '' );
			if ( '' !== $min_time ) {
				$config['minTime'] = $min_time;
			}
			if ( '' !== $max_time ) {
				$config['maxTime'] = $max_time;
			}
		}

		if ( $this->field_key_exist( $settings, 'inline_calendar', 'no' ) === 'yes' ) {
			$config['inline'] = true;
		}
		if ( $this->field_key_exist( $settings, 'week_numbers', 'no' ) === 'yes' ) {
			$config['weekNumbers'] = true;
		}
		if ( $this->field_key_exist( $settings, 'alt_input', 'no' ) === 'yes' ) {
			$config['altInput']  = true;
			$config['altFormat'] = $this->resolve_alt_format( $settings );
		}

		if ( 'multiple' === $mode ) {
			$config['conjunction'] = (string) $this->field_key_exist( $settings, 'conjunction', ', ' );
		}

		$min_bound = $this->resolve_date_bound( $settings, 'min' );
		$max_bound = $this->resolve_date_bound( $settings, 'max' );
		if ( null !== $min_bound ) {
			$config['minDate'] = $min_bound;
		}
		if ( null !== $max_bound ) {
			$config['maxDate'] = $max_bound;
		}

		if ( $this->field_key_exist( $settings, 'disable_weekends', 'no' ) === 'yes' ) {
			$config['disableWeekends'] = true;
		}

		$disable_dates = (string) $this->field_key_exist( $settings, 'disable_dates', '' );
		$enable_dates  = (string) $this->field_key_exist( $settings, 'enable_dates', '' );
		if ( '' !== $disable_dates ) {
			$config['disableDates'] = $disable_dates;
		}
		if ( '' !== $enable_dates ) {
			$config['enableDates'] = $enable_dates;
		}

		return $config;
	}

	/**
	 * Resolve min/max bound for Flatpickr (today, custom, relative).
	 *
	 * @param array  $settings Field settings.
	 * @param string $which    min|max.
	 * @return array|string|null
	 */
	private function resolve_date_bound( array $settings, string $which ) {
		$mode_key = 'min' === $which ? 'min_date_mode' : 'max_date_mode';
		$date_key = 'min' === $which ? 'min_date' : 'max_date';
		$days_key = 'min' === $which ? 'min_date_days' : 'max_date_days';
		$mode     = (string) $this->field_key_exist( $settings, $mode_key, 'none' );

		if ( 'none' === $mode ) {
			$legacy = (string) $this->field_key_exist( $settings, $date_key, '' );
			return '' !== $legacy ? $legacy : null;
		}

		if ( 'today' === $mode ) {
			return 'today';
		}

		if ( 'custom' === $mode ) {
			$date = (string) $this->field_key_exist( $settings, $date_key, '' );
			return '' !== $date ? $date : null;
		}

		if ( 'relative' === $mode ) {
			$days = (int) $this->field_key_exist( $settings, $days_key, 0 );
			return array(
				'type' => 'relative',
				'days' => $days,
			);
		}

		return null;
	}

	/**
	 * Resolve bound to a DateTime for server-side validation.
	 *
	 * @param array  $field_attr Field attributes.
	 * @param string $which      min|max.
	 * @return \DateTime|null
	 */
	private function resolve_bound_datetime( array $field_attr, string $which ): ?\DateTime {
		$mode_key = 'min' === $which ? 'min_date_mode' : 'max_date_mode';
		$date_key = 'min' === $which ? 'min_date' : 'max_date';
		$days_key = 'min' === $which ? 'min_date_days' : 'max_date_days';
		$mode     = isset( $field_attr[ $mode_key ] ) ? (string) $field_attr[ $mode_key ] : 'none';

		try {
			if ( 'none' === $mode || '' === $mode ) {
				if ( ! empty( $field_attr[ $date_key ] ) ) {
					$dt = new \DateTime( (string) $field_attr[ $date_key ] );
					$dt->setTime( 0, 0, 0 );
					return $dt;
				}
				return null;
			}

			if ( 'today' === $mode ) {
				$dt = new \DateTime( 'today' );
				$dt->setTime( 0, 0, 0 );
				return $dt;
			}

			if ( 'custom' === $mode && ! empty( $field_attr[ $date_key ] ) ) {
				$dt = new \DateTime( (string) $field_attr[ $date_key ] );
				$dt->setTime( 0, 0, 0 );
				return $dt;
			}

			if ( 'relative' === $mode ) {
				$days = isset( $field_attr[ $days_key ] ) ? (int) $field_attr[ $days_key ] : 0;
				$dt   = new \DateTime( 'today' );
				$dt->modify( ( $days >= 0 ? '+' : '' ) . $days . ' days' );
				$dt->setTime( 0, 0, 0 );
				return $dt;
			}
		} catch ( \Exception $e ) {
			return null;
		}

		return null;
	}

	/**
	 * Convert a resolved bound to Y-m-d for native HTML5 min/max.
	 *
	 * @param array|string|null $bound Bound value.
	 * @return string|null
	 */
	private function bound_to_ymd( $bound ): ?string {
		if ( null === $bound || '' === $bound ) {
			return null;
		}

		if ( 'today' === $bound ) {
			return gmdate( 'Y-m-d' );
		}

		if ( is_array( $bound ) && isset( $bound['type'] ) && 'relative' === $bound['type'] ) {
			$days = isset( $bound['days'] ) ? (int) $bound['days'] : 0;
			$dt   = new \DateTime( 'today', new \DateTimeZone( 'UTC' ) );
			$dt->modify( ( $days >= 0 ? '+' : '' ) . $days . ' days' );
			return $dt->format( 'Y-m-d' );
		}

		if ( is_string( $bound ) ) {
			return $bound;
		}

		return null;
	}

	protected function render_field() {
		$settings    = $this->get_field_settings();
		$id          = $this->get_the_id();
		$field_id    = $this->field_key_exist( $settings, 'field_id', uniqid( 'date_' ) );
		$label       = $this->field_key_exist( $settings, 'label', 'Select Date' );
		$placeholder = $this->field_key_exist( $settings, 'placeholder', 'YYYY-MM-DD' );
		$required    = $this->field_key_exist( $settings, 'required', '' ) === 'yes';
		$classes     = $this->field_key_exist( $settings, 'css_classes', '' );
		$use_native  = $this->field_key_exist( $settings, 'use_native_date', 'no' ) === 'yes';

		$input_class = 'dragwyb-field-input dragwyb-date-field';
		if ( $use_native ) {
			$input_class .= ' dragwyb-use-native';
		}

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => $this->field_wrapper_id( $id ),
				'class' => $this->field_wrapper_class( $classes, $settings ) . ' dragwyb-no-float',
			)
		);

		$input_attrs = array(
			'type'        => $use_native ? 'date' : 'text',
			'id'          => $field_id,
			'name'        => $field_id,
			'placeholder' => $placeholder,
			'class'       => $input_class,
		);

		if ( $use_native ) {
			$input_attrs['pattern'] = '[0-9]{4}-[0-9]{2}-[0-9]{2}';

			$min_bound = $this->resolve_date_bound( $settings, 'min' );
			$max_bound = $this->resolve_date_bound( $settings, 'max' );

			$native_min = $this->bound_to_ymd( $min_bound );
			$native_max = $this->bound_to_ymd( $max_bound );

			if ( $native_min ) {
				$input_attrs['min'] = $native_min;
			}
			if ( $native_max ) {
				$input_attrs['max'] = $native_max;
			}
		} else {
			$config                        = $this->build_flatpickr_config( $settings );
			$input_attrs['data-fp-config'] = wp_json_encode( $config );
		}

		$this->add_field_attributes( 'input', $input_attrs );
		?>
		<div <?php $this->render_field_attributes( 'wrapper' ); ?>>
			<?php if ( ! empty( $label ) ) : ?>
				<?php $this->render_field_label( $field_id, $label, $required, $settings ); ?>
			<?php endif; ?>

			<input
			<?php
			$this->render_field_attributes( 'input' );
			echo $required ? 'required' : '';
			?>
			/>
		</div>

		<?php
	}

	/**
	 * Parse a submitted date string into DateTime using the field format.
	 *
	 * @param string $value  Submitted value part.
	 * @param string $format Flatpickr-like format converted for PHP.
	 * @return \DateTime|null
	 */
	private function parse_date_value( string $value, string $format ): ?\DateTime {
		$php_format = $this->flatpickr_format_to_php( $format );
		$date       = \DateTime::createFromFormat( '!' . $php_format, $value );

		if ( ! $date ) {
			return null;
		}

		$errors = \DateTime::getLastErrors();
		if ( is_array( $errors ) && ( $errors['warning_count'] > 0 || $errors['error_count'] > 0 ) ) {
			return null;
		}

		return $date;
	}

	/**
	 * Convert common Flatpickr tokens to PHP date format.
	 */
	private function flatpickr_format_to_php( string $format ): string {
		$map = array(
			'Y' => 'Y',
			'm' => 'm',
			'd' => 'd',
			'H' => 'H',
			'i' => 'i',
		);

		$result = '';
		$length = strlen( $format );
		for ( $i = 0; $i < $length; $i++ ) {
			$char    = $format[ $i ];
			$result .= $map[ $char ] ?? $char;
		}

		return $result;
	}

	/**
	 * Split submitted value into date parts based on mode.
	 *
	 * @return string[]
	 */
	private function split_submitted_dates( string $value, string $mode, string $conjunction ): array {
		$value = trim( $value );
		if ( '' === $value ) {
			return array();
		}

		if ( 'range' === $mode ) {
			$parts = preg_split( '/\s+to\s+/i', $value );
			return array_values( array_filter( array_map( 'trim', (array) $parts ) ) );
		}

		if ( 'multiple' === $mode ) {
			$sep   = '' !== $conjunction ? $conjunction : ', ';
			$parts = array_map( 'trim', explode( $sep, $value ) );
			if ( 1 === count( $parts ) && ', ' !== $sep ) {
				$parts = array_map( 'trim', explode( ',', $value ) );
			}
			return array_values( array_filter( $parts ) );
		}

		return array( $value );
	}

	public function validate( $value, $field_id, $form_config, Form_Submission_Handler $error_handler ): void {
		if ( ! isset( $form_config['fields'][ $field_id ] ) ) {
			$error_handler->add_error( $field_id, __( 'Invalid field.', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		$field_attr = isset( $form_config['fields'][ $field_id ]['attributes'] ) ? $form_config['fields'][ $field_id ]['attributes'] : array();

		if ( empty( $value ) && isset( $field_attr['required'] ) && 'yes' == $field_attr['required'] ) {
			$error_handler->add_error( $field_id, __( 'This field is required', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		if ( empty( $value ) ) {
			return;
		}

		$use_native  = isset( $field_attr['use_native_date'] ) && 'yes' === $field_attr['use_native_date'];
		$mode        = isset( $field_attr['selection_mode'] ) ? (string) $field_attr['selection_mode'] : 'single';
		$conjunction = isset( $field_attr['conjunction'] ) ? (string) $field_attr['conjunction'] : ', ';

		if ( $use_native ) {
			$format = 'Y-m-d';
			$mode   = 'single';
		} else {
			$format = $this->resolve_date_format( $field_attr );
		}

		$parts = $this->split_submitted_dates( (string) $value, $mode, $conjunction );

		if ( 'range' === $mode && 2 !== count( $parts ) ) {
			$error_handler->add_error( $field_id, __( 'Invalid date range', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		if ( empty( $parts ) ) {
			$error_handler->add_error( $field_id, __( 'Invalid date format', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		$min_bound = $this->resolve_bound_datetime( $field_attr, 'min' );
		$max_bound = $this->resolve_bound_datetime( $field_attr, 'max' );

		foreach ( $parts as $part ) {
			$date = $this->parse_date_value( $part, $format );
			if ( ! $date ) {
				$error_handler->add_error( $field_id, __( 'Invalid date format', 'smart-form-builder-by-dragwyb' ) );
				return;
			}

			$compare = clone $date;
			$compare->setTime( 0, 0, 0 );

			if ( $min_bound && $compare < $min_bound ) {
				$error_handler->add_error( $field_id, __( 'Value is below minimum', 'smart-form-builder-by-dragwyb' ) );
				return;
			}

			if ( $max_bound && $compare > $max_bound ) {
				$error_handler->add_error( $field_id, __( 'Value exceeds maximum', 'smart-form-builder-by-dragwyb' ) );
				return;
			}
		}
	}

	/**
	 * Sanitize the field value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed Sanitized value.
	 */
	public function sanitize( $value = null ) {
		if ( null === $value || '' === $value ) {
			return null;
		}

		return sanitize_text_field( (string) $value );
	}
}
