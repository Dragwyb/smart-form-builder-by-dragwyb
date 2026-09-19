<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Text_Shadow;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Controls\Group\Group_Control_Base;

class Control_Text_Shadow extends Group_Control_Base {


	protected function init(): void {
		$this->type = 'text_shadow';
		$this->name = __( 'Text Shadow', 'smart-form-builder-by-dragwyb' );
		$this->icon = 'fas fa-pencil-alt';
	}

	protected function register_settings(): array {
		return array(
			'name'     => 'string',
			'label'    => 'string',
			'settings' => 'custom',
		);
	}

	protected function valid_default_settings(): array {
		return array_merge(
			array_keys( $this->default_setting() ),
			array(
				'color',
				'selector',
				'variable_selector',
				'horizontal',
				'vertical',
				'blur',
				'conditions',
				'prefix',
			)
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
			$sanitized['color'] = sanitize_text_field( $value['color'] );
		}

		// Added Selector Sanitization
		if ( isset( $value['selector'] ) ) {
			$sanitized['selector'] = sanitize_text_field( $value['selector'] );
		}

		if ( isset( $value['variable_selector'] ) ) {
			$sanitized['variable_selector'] = sanitize_text_field( $value['variable_selector'] );
		}

		foreach ( array( 'horizontal', 'vertical', 'blur' ) as $key ) {
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

		// Handle root selector fallback
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
			'color'      => array( '--dragwyb-' . $prefix . '-text-shadow-color' => '{{VALUE}}' ),
			'horizontal' => array( '--dragwyb-' . $prefix . '-text-shadow-h' => '{{VALUE}}{{UNIT}}' ),
			'vertical'   => array( '--dragwyb-' . $prefix . '-text-shadow-v' => '{{VALUE}}{{UNIT}}' ),
			'blur'       => array( '--dragwyb-' . $prefix . '-text-shadow-blur' => '{{VALUE}}{{UNIT}}' ),
		);

		$text_shadow_rule = 'text-shadow: var(--dragwyb-' . $prefix . '-text-shadow-h, 0px) var(--dragwyb-' . $prefix . '-text-shadow-v, 0px) var(--dragwyb-' . $prefix . '-text-shadow-blur, 0px) var(--dragwyb-' . $prefix . '-text-shadow-color, transparent);';

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
				'units'      => array( 'px' ),
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
				'units'      => array( 'px' ),
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
				'units'      => array( 'px' ),
				'responsive' => true,
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
			if ( $selector && ! empty( $text_shadow_rule ) ) {
				$control_args['selectors'][ $selector ] = $text_shadow_rule;
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
