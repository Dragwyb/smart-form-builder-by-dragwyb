<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Multiselect extends Control_Base {

	protected function register_settings() {
		return array(
			'name'    => 'string',
			'label'   => 'string',
			'default' => 'array',
			'options' => 'custom',
		);
	}

	protected function init(): void {
		$this->type = 'multiselect';
		$this->name = __( 'Multiselect', 'smart-form-builder-by-dragwyb' );
	}

	protected function sanitize_control( $value, $settings ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		$filterd_values = array();

		foreach ( $value as $single_value ) {
			if ( isset( $settings['options'][ $single_value ] ) ) {
				$filterd_values[] = sanitize_text_field( $single_value );
			}
		}

		return $filterd_values;
	}

	protected function options_setting_sanitize( $value ) {
		$filterd_options = array();
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $value ) {
				$filterd_options[ sanitize_text_field( $key ) ] = sanitize_text_field( $value );
			}
		}
		return $filterd_options;
	}
}
