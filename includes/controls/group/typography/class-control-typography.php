<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Typography;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Controls\Group\Group_Control_Base;

class Control_Typography extends Group_Control_Base {

	protected function init(): void {
		$this->type = 'typography';
		$this->name = __( 'Typography', 'smart-form-builder-by-dragwyb' );
		$this->icon = 'fas fa-pen';
	}

	protected function register_settings(): array {
		return array(
			'name'     => 'string',
			'label'    => 'string',
			'settings' => 'custom', // Custom array structure
		);
	}

	/**
	 * Define default configuration for all sub-controls
	 */
	protected function default_setting(): array {
		$units_global = array( 'px', '%', 'em', 'rem', 'vh', 'vw' );

		$range_size = array(
			'px'  => array(
				'min'  => 0,
				'max'  => 200,
				'step' => 1,
			),
			'%'   => array(
				'min'  => 0,
				'max'  => 200,
				'step' => 1,
			),
			'em'  => array(
				'min'  => 0,
				'max'  => 20,
				'step' => 0.1,
			),
			'rem' => array(
				'min'  => 0,
				'max'  => 20,
				'step' => 0.1,
			),
			'vh'  => array(
				'min'  => 0,
				'max'  => 100,
				'step' => 1,
			),
			'vw'  => array(
				'min'  => 0,
				'max'  => 100,
				'step' => 1,
			),
		);

		$range_spacing = array(
			'px'  => array(
				'min'  => -50,
				'max'  => 100,
				'step' => 0.1,
			),
			'em'  => array(
				'min'  => -5,
				'max'  => 10,
				'step' => 0.01,
			),
			'rem' => array(
				'min'  => -5,
				'max'  => 10,
				'step' => 0.01,
			),
			'%'   => array(
				'min'  => 0,
				'max'  => 100,
				'step' => 1,
			),
		);

		return array(
			// --- Font Size ---
			'size'           => array(
				'units'   => $units_global,
				'range'   => $range_size,
				'default' => array(
					'unit' => 'px',
					'size' => 16,
				),
			),
			// --- Font Family Config ---
			'font'           => array(
				'family'        => array(),
				'exclude_fonts' => array(),
				'fonts_group'   => array(),
			),
			// --- Weight ---
			'weight'         => array(
				'options' => array(
					'default' => __( 'Default', 'smart-form-builder-by-dragwyb' ),
					'normal'  => __( 'Normal', 'smart-form-builder-by-dragwyb' ),
					'bold'    => __( 'Bold', 'smart-form-builder-by-dragwyb' ),
					'100'     => '100',
					'200'     => '200',
					'300'     => '300',
					'400'     => '400',
					'500'     => '500',
					'600'     => '600',
					'700'     => '700',
					'800'     => '800',
					'900'     => '900',
				),
				'default' => 'default',
			),
			// --- Transform ---
			'transform'      => array(
				'options' => array(
					''           => __( 'Default', 'smart-form-builder-by-dragwyb' ),
					'none'       => __( 'None', 'smart-form-builder-by-dragwyb' ),
					'uppercase'  => __( 'Uppercase', 'smart-form-builder-by-dragwyb' ),
					'lowercase'  => __( 'Lowercase', 'smart-form-builder-by-dragwyb' ),
					'capitalize' => __( 'Capitalize', 'smart-form-builder-by-dragwyb' ),
				),
				'default' => '',
			),
			// --- Style ---
			'style'          => array(
				'options' => array(
					''        => __( 'Default', 'smart-form-builder-by-dragwyb' ),
					'normal'  => __( 'Normal', 'smart-form-builder-by-dragwyb' ),
					'italic'  => __( 'Italic', 'smart-form-builder-by-dragwyb' ),
					'oblique' => __( 'Oblique', 'smart-form-builder-by-dragwyb' ),
				),
				'default' => '',
			),
			// --- Decoration ---
			'decoration'     => array(
				'options' => array(
					''             => __( 'Default', 'smart-form-builder-by-dragwyb' ),
					'none'         => __( 'None', 'smart-form-builder-by-dragwyb' ),
					'underline'    => __( 'Underline', 'smart-form-builder-by-dragwyb' ),
					'overline'     => __( 'Overline', 'smart-form-builder-by-dragwyb' ),
					'line-through' => __( 'Line Through', 'smart-form-builder-by-dragwyb' ),
				),
				'default' => '',
			),
			// --- Sliders ---
			'line_height'    => array(
				'units'   => $units_global,
				'range'   => $range_size,
				'default' => array(
					'unit' => 'em',
					'size' => 1.5,
				),
			),
			'letter_spacing' => array(
				'units'   => $units_global,
				'range'   => $range_spacing,
				'default' => array(
					'unit' => 'px',
					'size' => 0,
				),
			),
			'word_spacing'   => array(
				'units'   => $units_global,
				'range'   => $range_spacing,
				'default' => array(
					'unit' => 'px',
					'size' => 0,
				),
			),
			// --- Alignment ---
			'alignment'      => array(
				'options' => array(
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
				'default' => '',
			),
			'selector'          => '',
			'variable_selector' => '',
		);
	}

	protected function valid_default_settings(): array {
		return array(
			'font',
			'size',
			'weight',
			'transform',
			'style',
			'decoration',
			'line_height',
			'letter_spacing',
			'word_spacing',
			'alignment',
			'selector',
			'variable_selector',
			'conditions',
			'prefix',
		);
	}

	/**
	 * Entry point for sanitizing the control settings array.
	 */
	protected function settings_setting_sanitize( $value ) {
		return $this->sanitize_control( $value );
	}

	protected function sanitize_control( $value, $settings = null ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$sanitized = array();

		if ( isset( $value['selector'] ) ) {
			$sanitized['selector'] = sanitize_text_field( $value['selector'] );
		}

		if ( isset( $value['variable_selector'] ) ) {
			$sanitized['variable_selector'] = sanitize_text_field( $value['variable_selector'] );
		}

		// 1. Responsive Sliders (Size, Line Height, Spacing)
		$slider_keys = array( 'size', 'line_height', 'letter_spacing', 'word_spacing' );
		foreach ( $slider_keys as $key ) {
			if ( isset( $value[ $key ] ) ) {
				$sanitized[ $key ] = $this->sanitize_responsive_slider( $value[ $key ] );
			}
		}

		// 2. Selects (Weight, Transform, Style, etc.)
		$select_keys = array( 'weight', 'transform', 'style', 'decoration' );
		foreach ( $select_keys as $key ) {
			if ( isset( $value[ $key ] ) ) {
				$sanitized[ $key ] = $this->sanitize_select_options( $value[ $key ] );
			}
		}

		// 3. Font Family Group (Nested arrays inside 'font')
		if ( isset( $value['font'] ) && is_array( $value['font'] ) ) {
			$font_keys = array( 'exclude_fonts', 'fonts_group', 'family' );
			foreach ( $font_keys as $f_key ) {
				if ( isset( $value['font'][ $f_key ] ) ) {
					$sanitized['font'][ $f_key ] = $this->sanitize_array_list( $value['font'][ $f_key ] );
				}
			}
		}

		// 4. Alignment
		if ( isset( $value['alignment'] ) ) {
			$sanitized['alignment'] = $this->sanitize_choose_options( $value['alignment'] );
		}

		return $sanitized;
	}

	protected function sanitize_choose_options( $setting ) {
		if ( ! is_array( $setting ) ) {
			return array();
		}
		$clean = array();

		// Sanitize Options Array
		if ( isset( $setting['options'] ) && is_array( $setting['options'] ) ) {
			foreach ( $setting['options'] as $o => $l ) {
				$clean['options'][ $this->string_sanitize( $o ) ] = array(
					'title' => $this->string_sanitize( $l['title'] ?? $o ),
					'icon'  => $this->string_sanitize( $l['icon'] ?? '' ),
				);
			}
		}

		// Sanitize Default
		if ( isset( $setting['default'] ) ) {
			$clean['default'] = $this->string_sanitize( $setting['default'] );
		}

		return $clean;
	}

	protected function sanitize_responsive_slider( $setting ) {
		if ( ! is_array( $setting ) ) {
			return array();
		}
		$clean = array();

		// Sanitize Units
		if ( isset( $setting['units'] ) ) {
			$clean['units'] = $this->sanitize_array_list( $setting['units'] );
		}

		// Sanitize Range Config (min/max/step)
		if ( isset( $setting['range'] ) && is_array( $setting['range'] ) ) {
			foreach ( $setting['range'] as $u => $l ) {
				$clean['range'][ $this->string_sanitize( $u ) ] = array(
					'min'  => $this->number_sanitize( $l['min'] ?? 0 ),
					'max'  => $this->number_sanitize( $l['max'] ?? 100 ),
					'step' => $this->number_sanitize( $l['step'] ?? 1 ),
				);
			}
		}

		// Sanitize Defaults
		if ( isset( $setting['default'] ) ) {
			$clean['default'] = array(
				'unit' => $this->string_sanitize( $setting['default']['unit'] ?? 'px' ),
				'size' => $this->number_sanitize( $setting['default']['size'] ?? 0 ),
			);
		}

		return $clean;
	}

	protected function sanitize_select_options( $setting ) {
		if ( ! is_array( $setting ) ) {
			return array();
		}
		$clean = array();

		// Sanitize Options Array
		if ( isset( $setting['options'] ) && is_array( $setting['options'] ) ) {
			foreach ( $setting['options'] as $k => $v ) {
				$clean['options'][ $this->string_sanitize( (string) $k ) ] = $this->string_sanitize( $v );
			}
		}

		// Sanitize Default Value
		if ( isset( $setting['default'] ) ) {
			$clean['default'] = $this->string_sanitize( $setting['default'] );
		}

		return $clean;
	}

	protected function sanitize_array_list( $list ) {
		if ( ! is_array( $list ) ) {
			return array();
		}
		return array_map( array( $this, 'string_sanitize' ), $list );
	}

	// --- Core Sanitization Helpers ---

	protected function string_sanitize( $val ) {
		return sanitize_text_field( $val );
	}

	/**
	 * Smartly cast values to int or float based on content.
	 * Prevents errors when "15.5" (string) is passed to strict float types.
	 *
	 * @param mixed $value
	 * @return int|float
	 */
	protected function number_sanitize( $value ) {
		if ( ! is_numeric( $value ) ) {
			return 0;
		}
		// Adding zero forces PHP to cast to int if whole, or float if decimal exists
		return $value + 0;
	}

	// --- Control Registration ---



	/**
	 * Merge defaults with user data, supporting the 'settings' nesting
	 */
	private function get_display_settings(): array {
		$defaults       = $this->default_setting();
		$valid_settings = $this->valid_default_settings();

		// 1. Determine where the overrides are coming from
		// If 'settings' key exists and is an array, use it. Otherwise use root data.
		$user_data = isset( $this->data['settings'] ) && is_array( $this->data['settings'] )
			? $this->data['settings']
			: $this->data;

		if ( isset( $this->data['selector'] ) && empty( $user_data['selector'] ) ) {
			$user_data['selector'] = $this->data['selector'];
		}
		if ( isset( $this->data['variable_selector'] ) && empty( $user_data['variable_selector'] ) ) {
			$user_data['variable_selector'] = $this->data['variable_selector'];
		}

		// 2. Security: Only allow keys that exist in our defaults
		foreach ( $valid_settings as $key ) {
			if ( ! array_key_exists( $key, $user_data ) ) {
				continue;
			}

			if ( is_array( $user_data[ $key ] ) && in_array( $key, array( 'range', 'units' ) ) ) {
				$defaults[ $key ] = $user_data[ $key ];
			} elseif ( is_array( $user_data[ $key ] ) ) {
				$this->merge_array_settings( $user_data[ $key ], $user_data[ $key ], $defaults[ $key ] );
			} else {
				$defaults[ $key ] = $user_data[ $key ];
			}
		}

		return $defaults;
	}

	private function merge_array_settings( $data, $user_data, &$defaults ) {
		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) && in_array( $key, array( 'range', 'units', 'options' ) ) ) {
				$defaults[ $key ] = $user_data[ $key ];
			} elseif ( is_array( $value ) ) {
				$this->merge_array_settings( $value, $user_data[ $key ], $defaults[ $key ] );
			} else {
				$defaults[ $key ] = $value;
			}
		}
	}

	protected function register_group_controls(): void {
		$settings          = $this->get_display_settings();
		$id                = $this->string_sanitize( $this->id );
		$selector          = isset( $settings['selector'] ) && ! empty( $settings['selector'] ) ? $settings['selector'] : false;
		$variable_selector = isset( $settings['variable_selector'] ) && ! empty( $settings['variable_selector'] ) ? $settings['variable_selector'] : '{{WRAPPER}}';
		$prefix            = isset( $settings['prefix'] ) && ! empty( $settings['prefix'] ) ? $settings['prefix'] : 'form';

		$direct_selectors = array(
			'family'         => 'font-family: {{VALUE}};',
			'size'           => 'font-size: {{VALUE}}{{UNIT}};',
			'weight'         => 'font-weight: {{VALUE}};',
			'transform'      => 'text-transform: {{VALUE}};',
			'style'          => 'font-style: {{VALUE}};',
			'decoration'     => 'text-decoration: {{VALUE}};',
			'line_height'    => 'line-height: {{VALUE}}{{UNIT}};',
			'letter_spacing' => 'letter-spacing: {{VALUE}}{{UNIT}};',
			'word_spacing'   => 'word-spacing: {{VALUE}}{{UNIT}};',
			'alignment'      => 'text-align: {{VALUE}};',
		);

		$var_selectors = array(
			'family'         => '--dragwyb-' . $prefix . '-typography-family: {{VALUE}};',
			'size'           => '--dragwyb-' . $prefix . '-typography-size: {{VALUE}}{{UNIT}};',
			'weight'         => '--dragwyb-' . $prefix . '-typography-wt: {{VALUE}};',
			'transform'      => '--dragwyb-' . $prefix . '-typography-ts: {{VALUE}};',
			'style'          => '--dragwyb-' . $prefix . '-typography-st: {{VALUE}};',
			'decoration'     => '--dragwyb-' . $prefix . '-typography-dt: {{VALUE}};',
			'line_height'    => '--dragwyb-' . $prefix . '-typography-lh: {{VALUE}}{{UNIT}};',
			'letter_spacing' => '--dragwyb-' . $prefix . '-typography-ls: {{VALUE}}{{UNIT}};',
			'word_spacing'   => '--dragwyb-' . $prefix . '-typography-ws: {{VALUE}}{{UNIT}};',
			'alignment'      => '--dragwyb-' . $prefix . '-typography-align: {{VALUE}};',
		);

		// 2. Control Map
		$map = array(
			'family'         => array(
				'type'    => Controls::FONTS,
				'label'   => __( 'Family', 'smart-form-builder-by-dragwyb' ),
				'default' => 'Default',
			),
			'size'           => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Font Size', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'units'      => array( 'px' ),
				'responsive' => true,
			),
			'weight'         => array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Weight', 'smart-form-builder-by-dragwyb' ),
				'label_inline' => true,
			),
			'transform'      => array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Transform', 'smart-form-builder-by-dragwyb' ),
				'label_inline' => true,
			),
			'style'          => array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Style', 'smart-form-builder-by-dragwyb' ),
				'label_inline' => true,
			),
			'decoration'     => array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Decoration', 'smart-form-builder-by-dragwyb' ),
				'label_inline' => true,
			),
			'line_height'    => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Line Height', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'responsive' => true,
			),
			'letter_spacing' => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Letter Spacing', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'units'      => array( 'px' ),
				'responsive' => true,
			),
			'word_spacing'   => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Word Spacing', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'units'      => array( 'px' ),
				'responsive' => true,
			),
			'alignment'      => array(
				'type'         => Controls::CHOOSE,
				'label'        => __( 'Alignment', 'smart-form-builder-by-dragwyb' ),
				'label_inline' => true,
			),
		);

		// Access nested 'font' settings safely
		if ( ! empty( $settings['font']['exclude_fonts'] ) ) {
			$map['family']['exclude_fonts'] = $settings['font']['exclude_fonts'];
		}
		if ( ! empty( $settings['font']['fonts_group'] ) ) {
			$map['family']['groups'] = $settings['font']['fonts_group'];
		}

		// 3. Generate Controls
		foreach ( $map as $key => $meta ) {
			$config = $key === 'family' ? $settings['font'] : ( isset( $settings[ $key ] ) ? $settings[ $key ] : array() );

			$control_args = array_filter(
				$meta,
				function ( $key, $value ) {
					return $key !== 'responsive';
				},
				ARRAY_FILTER_USE_BOTH
			);

			if ( isset( $config['default'] ) ) {
				$control_args['default'] = $config['default'];
			}

			if ( $settings['conditions'] && ! empty( $settings['conditions'] ) ) {
				$control_args['conditions'] = $settings['conditions'];
			}

			if ( $meta['type'] === Controls::SLIDER ) {
				if ( isset( $config['range'] ) ) {
					$control_args['range'] = $config['range'];
				}
				if ( isset( $config['units'] ) ) {
					$control_args['units'] = $config['units'];
				}
			} elseif ( $meta['type'] === Controls::SELECT ) {
				if ( isset( $config['options'] ) ) {
					$control_args['options'] = $config['options'];
				}
			} elseif ( $meta['type'] === Controls::CHOOSE ) {
				if ( isset( $config['options'] ) ) {
					$control_args['options'] = $config['options'];
				}
			}

			$control_args['selectors'] = array();

			// 1. Direct CSS properties applied to $selector (if provided)
			if ( $selector && isset( $direct_selectors[ $key ] ) && ! empty( $direct_selectors[ $key ] ) ) {
				$control_args['selectors'][ $selector ] = $direct_selectors[ $key ];
			}

			// 2. CSS variables applied to $variable_selector (default {{WRAPPER}})
			if ( $variable_selector && isset( $var_selectors[ $key ] ) && ! empty( $var_selectors[ $key ] ) ) {
				if ( isset( $control_args['selectors'][ $variable_selector ] ) ) {
					$control_args['selectors'][ $variable_selector ] .= ' ' . $var_selectors[ $key ];
				} else {
					$control_args['selectors'][ $variable_selector ] = $var_selectors[ $key ];
				}
			}

			if ( isset( $meta['responsive'] ) && $meta['responsive'] ) {
				$this->add_responsive_control( $id . '_' . $key, $control_args );
			} else {
				$this->add_control( $id . '_' . $key, $control_args );
			}
		}
	}
}
