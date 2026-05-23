<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Dimensions extends Control_Base {

	protected function register_settings() {
		return array(
			'name'       => 'string',
			'label'      => 'string',
			'default'    => 'custom',
			'show_label' => 'boolean',
			'linked'     => 'boolean',
			'units'      => 'custom',
		);
	}

	protected function default_setting(): array {
		return array(
			'units'      => array( 'px' ),
			'show_label' => true,
			'linked'     => false,
		);
	}

	protected function init(): void {
		$this->type = 'dimensions';
		$this->name = __( 'Dimensions', 'smart-form-builder-by-dragwyb' );
	}

	/**
	 * Sanitize control value
	 */
	protected function sanitize_control( $value, $settings ) {
		$allowed   = array( 'top', 'right', 'bottom', 'left', 'linked', 'unit' );
		$sanitized = array();

		if ( ! is_array( $value ) ) {
			return '';
		}

		if ( ( ! isset( $value['top'] ) || ( empty( $value['top'] ) && 0 !== $value['top'] ) ) && ( ! isset( $value['right'] ) || ( empty( $value['right'] ) && 0 !== $value['right'] ) ) && ( ! isset( $value['bottom'] ) || ( empty( $value['bottom'] ) && 0 !== $value['bottom'] ) ) && ( ! isset( $value['left'] ) || ( empty( $value['left'] ) && 0 !== $value['left'] ) ) ) {
			return array();
		}

		foreach ( $allowed as $key ) {
			if ( ! array_key_exists( $key, $value ) ) {
				continue;
			}

			switch ( $key ) {
				case 'linked':
					$sanitized['linked'] = (bool) $value['linked'];
					break;
				case 'unit':
					$sanitized['unit'] = $this->string_sanitize( $value['unit'] );
					break;
				default:
					$sanitized[ $key ] = $this->number_sanitize( $value[ $key ] );
					break;
			}
		}

		return $sanitized;
	}

	/**
	 * Default structure for this control
	 */
	protected function default_setting_sanitize( $value ) {
		$default = array(
			'linked' => true,
			'unit'   => 'px',
		);

		if ( ! is_array( $value ) ) {
			return $default;
		}

		foreach ( $default as $key => $fallback ) {
			if ( ! array_key_exists( $key, $value ) ) {
				continue;
			}

			if ( $key === 'unit' ) {
				$default['unit'] = $this->string_sanitize( $value[ $key ] );
			} elseif ( $key === 'linked' ) {
				$default['linked'] = (bool) $value[ $key ];
			} else {
				$default[ $key ] = $this->number_sanitize( $value[ $key ] );
			}
		}

		return $default;
	}

	/**
	 * Sanitize allowed units
	 */
	protected function units_setting_sanitize( $value ): array {
		$filtered_units = array( 'px' );

		if ( is_array( $value ) && count( $value ) > 0 ) {
			foreach ( $value as $unit ) {
				$unit = $this->string_sanitize( $unit );
				if ( ! in_array( $unit, $filtered_units, true ) ) {
					$filtered_units[] = $unit;
				}
			}
		}

		return $filtered_units;
	}

	protected function style_placeholders(): array {
		return array(
			'TOP'    => 'top',
			'RIGHT'  => 'right',
			'BOTTOM' => 'bottom',
			'LEFT'   => 'left',
			'UNIT'   => 'unit',
		);
	}
}
