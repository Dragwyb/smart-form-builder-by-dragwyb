<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Choose extends Control_Base {


	protected function register_scripts(): array {
		// Assuming the same script handle handles all basic controls
		return array( 'dragwyb-editor-controls' );
	}

	protected function register_style(): array {
		return array( 'dragwyb-editor-controls' );
	}

	protected function register_settings() {
		return array(
			'label'        => 'string',
			'default'      => 'string',
			'options'      => 'options', // Custom type handled below
			'toggle'       => 'boolean', // Allow unselecting the option
			'label_inline' => 'boolean', // Allow inline label
		);
	}

	protected function init(): void {
		$this->type = 'choose';
		$this->name = __( 'Choose', 'smart-form-builder-by-dragwyb' );
	}

	protected function default_setting(): array {
		return array(
			'label_inline' => true,
			'toggle'       => true,
		);
	}

	protected function sanitize_control( $value, $settings ) {
		return sanitize_key( $value );
	}

	/**
	 * Custom Sanitizer for the 'options' setting.
	 * Note: Must be protected/public so the Parent class can access it via $this.
	 */
	protected function options_setting_sanitize( $options ) {
		if ( ! is_array( $options ) ) {
			return array();
		}

		$sanitized_options = array();

		foreach ( $options as $value_key => $option_data ) {
			$key = sanitize_key( $value_key );

			if ( is_array( $option_data ) ) {
				$sanitized_options[ $key ] = array(
					'title' => isset( $option_data['title'] ) ? sanitize_text_field( $option_data['title'] ) : '',
					'icon'  => isset( $option_data['icon'] ) ? sanitize_text_field( $option_data['icon'] ) : '',
				);
			}
		}

		return $sanitized_options;
	}
}
