<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Box_Shadow;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Controls\Group\Group_Control_Base;

class Control_Box_Shadow extends Group_Control_Base {

	protected function init(): void {
		$this->type = 'box-shadow';
		$this->name = __( 'Box Shadow', 'smart-form-builder-by-dragwyb' );
		$this->icon = 'fas fa-clone';
	}

	protected function register_settings(): array {
		return array(
			'name'     => 'string',
			'label'    => 'string',
			'settings' => 'custom',
		);
	}

	protected function valid_default_settings(): array {
		return array(
			'color',
			'horizontal',
			'vertical',
			'blur',
			'spread',
			'position',
			'selector',
			'variable_selector',
			'conditions',
			'prefix',
		);
	}

	protected function settings_setting_sanitize( $value ) {
		return $this->sanitize_control( $value );
	}

	protected function sanitize_control( $value, $settings = null ) {
		if ( ! is_array( $value ) ) {
			return array();
		}
		$sanitized = array();

		if ( isset( $value['color'] ) ) {
			$sanitized['color'] = sanitize_text_field( $value['color'] ); // Allow RGBA strings
		}

		if ( isset( $value['position'] ) ) {
			$sanitized['position'] = sanitize_text_field( $value['position'] );
		}

		// Added Selector Sanitization
		if ( isset( $value['selector'] ) ) {
			$sanitized['selector'] = sanitize_text_field( $value['selector'] );
		}

		if ( isset( $value['variable_selector'] ) ) {
			$sanitized['variable_selector'] = sanitize_text_field( $value['variable_selector'] );
		}

		foreach ( array( 'horizontal', 'vertical', 'blur', 'spread' ) as $key ) {
			if ( isset( $value[ $key ] ) && is_array( $value[ $key ] ) ) {
				$sanitized[ $key ] = array(
					'size' => floatval( $value[ $key ]['size'] ?? 0 ),
					'unit' => sanitize_text_field( $value[ $key ]['unit'] ?? 'px' ),
				);
			}
		}

		return $sanitized;
	}

	protected function string_sanitize( $val ) {
		return sanitize_text_field( $val );
	}

	private function get_display_settings(): array {
		$valid_settings = $this->valid_default_settings();
		$user_data      = isset( $this->data['settings'] ) && is_array( $this->data['settings'] )
			? $this->data['settings']
			: $this->data;

		// Ensure we handle the root selector if passed directly in data
		if ( isset( $this->data['selector'] ) && empty( $user_data['selector'] ) ) {
			$user_data['selector'] = $this->data['selector'];
		}
		if ( isset( $this->data['variable_selector'] ) && empty( $user_data['variable_selector'] ) ) {
			$user_data['variable_selector'] = $this->data['variable_selector'];
		}

		// Only valid keys from defaults
		$valid_user_data = array();

		foreach ( $valid_settings as $key ) {
			if ( ! array_key_exists( $key, $user_data ) ) {
				continue;
			}

			if ( isset( $user_data[ $key ] ) ) {
				$valid_user_data[ $key ] = $user_data[ $key ];
			}
		}

		return $valid_user_data;
	}

	protected function register_group_controls(): void {
		$settings          = $this->get_display_settings();
		$id                = $this->string_sanitize( $this->id );
		$selector          = isset( $settings['selector'] ) && ! empty( $settings['selector'] ) ? $settings['selector'] : false;
		$variable_selector = isset( $settings['variable_selector'] ) && ! empty( $settings['variable_selector'] ) ? $settings['variable_selector'] : '{{WRAPPER}}';
		$prefix            = isset( $settings['prefix'] ) && ! empty( $settings['prefix'] ) ? $settings['prefix'] : 'form';

		$selectors = array(
			'color'      => array( '--dragwyb-' . $prefix . '-box-shadow-color' => '{{VALUE}}' ),
			'horizontal' => array( '--dragwyb-' . $prefix . '-box-shadow-h' => '{{VALUE}}{{UNIT}}' ),
			'vertical'   => array( '--dragwyb-' . $prefix . '-box-shadow-v' => '{{VALUE}}{{UNIT}}' ),
			'blur'       => array( '--dragwyb-' . $prefix . '-box-shadow-blur' => '{{VALUE}}{{UNIT}}' ),
			'spread'     => array( '--dragwyb-' . $prefix . '-box-shadow-spread' => '{{VALUE}}{{UNIT}}' ),
			'position'   => array( '--dragwyb-' . $prefix . '-box-shadow-position' => '{{VALUE}}' ),
		);

		$box_shadow_rule = 'box-shadow: var(--dragwyb-' . $prefix . '-box-shadow-position, ) var(--dragwyb-' . $prefix . '-box-shadow-h, 0px) var(--dragwyb-' . $prefix . '-box-shadow-v, 0px) var(--dragwyb-' . $prefix . '-box-shadow-blur, 0px) var(--dragwyb-' . $prefix . '-box-shadow-spread, 0px) var(--dragwyb-' . $prefix . '-box-shadow-color, transparent);';

		// 2. Control Map
		$map = array(
			'color'      => array(
				'type'  => Controls::COLOR,
				'label' => __( 'Color', 'smart-form-builder-by-dragwyb' ),
			),
			'horizontal' => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Horizontal', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min'  => -100,
						'max'  => 100,
						'step' => 1,
					),
				),
				'responsive' => true,
			),
			'vertical'   => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Vertical', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min'  => -100,
						'max'  => 100,
						'step' => 1,
					),
				),
				'responsive' => true,
			),
			'blur'       => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Blur', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 100,
						'step' => 1,
					),
				),
				'responsive' => true,
			),
			'spread'     => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Spread', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min'  => -100,
						'max'  => 100,
						'step' => 1,
					),
				),
				'responsive' => true,
			),
			'position'   => array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Position', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					''      => __( 'Default', 'smart-form-builder-by-dragwyb' ),
					'inset' => __( 'Inset', 'smart-form-builder-by-dragwyb' ),
				),
				'label_inline' => true,
			),
		);

		// 3. Generate Controls
		foreach ( $map as $key => $meta ) {
			$config = isset( $settings[ $key ] ) ? $settings[ $key ] : array();

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
			}

			$control_args['selectors'] = array();

			// 1. Direct CSS properties applied to $selector (if provided)
			if ( $selector && ! empty( $box_shadow_rule ) ) {
				$control_args['selectors'][ $selector ] = $box_shadow_rule;
			}

			// 2. CSS variables applied to $variable_selector (default {{WRAPPER}})
			if ( $variable_selector && isset( $selectors[ $key ] ) && is_array( $selectors[ $key ] ) ) {
				$selector_style = '';
				foreach ( $selectors[ $key ] as $selector_key => $selector_value ) {
					$selector_style .= $selector_key . ':' . $selector_value . ';';
				}

				if ( ! empty( $selector_style ) ) {
					if ( isset( $control_args['selectors'][ $variable_selector ] ) ) {
						$control_args['selectors'][ $variable_selector ] .= ' ' . $selector_style;
					} else {
						$control_args['selectors'][ $variable_selector ] = $selector_style;
					}
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
