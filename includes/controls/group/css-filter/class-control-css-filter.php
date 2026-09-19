<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Css_Filter;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Controls\Group\Group_Control_Base;

class Control_Css_Filter extends Group_Control_Base {

	protected function init(): void {
		$this->type = 'css-filter';
		$this->name = __( 'CSS Filter', 'smart-form-builder-by-dragwyb' );
		$this->icon = 'fas fa-filter';
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
			'blur',
			'brightness',
			'contrast',
			'saturate',
			'hue',
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

		if ( isset( $value['selector'] ) ) {
			$sanitized['selector'] = sanitize_text_field( $value['selector'] );
		}

		if ( isset( $value['variable_selector'] ) ) {
			$sanitized['variable_selector'] = sanitize_text_field( $value['variable_selector'] );
		}

		foreach ( array( 'blur', 'brightness', 'contrast', 'saturate', 'hue' ) as $key ) {
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

		if ( isset( $this->data['selector'] ) && empty( $user_data['selector'] ) ) {
			$user_data['selector'] = $this->data['selector'];
		}
		if ( isset( $this->data['variable_selector'] ) && empty( $user_data['variable_selector'] ) ) {
			$user_data['variable_selector'] = $this->data['variable_selector'];
		}

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
			'blur'       => array( '--dragwyb-' . $prefix . '-filter-blur' => '{{VALUE}}{{UNIT}}' ),
			'brightness' => array( '--dragwyb-' . $prefix . '-filter-brightness' => '{{VALUE}}%' ),
			'contrast'   => array( '--dragwyb-' . $prefix . '-filter-contrast' => '{{VALUE}}%' ),
			'saturate'   => array( '--dragwyb-' . $prefix . '-filter-saturate' => '{{VALUE}}%' ),
			'hue'        => array( '--dragwyb-' . $prefix . '-filter-hue' => '{{VALUE}}deg' ),
		);

		$filter_rule = 'filter: brightness(var(--dragwyb-' . $prefix . '-filter-brightness, 100%)) contrast(var(--dragwyb-' . $prefix . '-filter-contrast, 100%)) saturate(var(--dragwyb-' . $prefix . '-filter-saturate, 100%)) blur(var(--dragwyb-' . $prefix . '-filter-blur, 0px)) hue-rotate(var(--dragwyb-' . $prefix . '-filter-hue, 0deg));';

		$map = array(
			'blur'       => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Blur', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min'  => 0,
						'max'  => 10,
						'step' => 0.1,
					),
				),
				'default'    => array( 'size' => 0 ),
				'responsive' => true,
			),
			'brightness' => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Brightness', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'default'    => array( 'size' => 100 ),
				'responsive' => true,
			),
			'contrast'   => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Contrast', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'default'    => array( 'size' => 100 ),
				'responsive' => true,
			),
			'saturate'   => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Saturation', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 200,
					),
				),
				'default'    => array( 'size' => 100 ),
				'responsive' => true,
			),
			'hue'        => array(
				'type'       => Controls::SLIDER,
				'label'      => __( 'Hue', 'smart-form-builder-by-dragwyb' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 360,
					),
				),
				'default'    => array( 'size' => 0 ),
				'responsive' => true,
			),
		);

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
			if ( $selector && ! empty( $filter_rule ) ) {
				$control_args['selectors'][ $selector ] = $filter_rule;
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
