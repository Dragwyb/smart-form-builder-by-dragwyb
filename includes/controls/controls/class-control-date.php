<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Date extends Control_Base {

	protected function register_scripts(): array {
		return array( 'dragwyb-flatpickr', 'dragwyb-editor-controls' );
	}

	protected function register_style(): array {
		return array( 'dragwyb-flatpickr', 'dragwyb-editor-controls' );
	}

	protected function register_settings() {
		return array(
			'name'         => 'string',
			'label'        => 'string',
			'default'      => 'string',
			'description'  => 'string',
			'label_inline' => 'boolean',
		);
	}

	public function __construct() {
		parent::__construct();
		$this->register_flatpickr_assets();
	}

	/**
	 * Register shared Flatpickr assets for the editor date control.
	 */
	private function register_flatpickr_assets(): void {
		if ( ! wp_script_is( 'dragwyb-flatpickr', 'registered' ) ) {
			wp_register_script(
				'dragwyb-flatpickr',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/flatpickr/flatpickr.js' ),
				array(),
				DRAGWYB_FORM_BUILDER_VERSION,
				true
			);
		}

		if ( ! wp_style_is( 'dragwyb-flatpickr', 'registered' ) ) {
			wp_register_style(
				'dragwyb-flatpickr',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/flatpickr/flatpickr.min.css' ),
				array(),
				DRAGWYB_FORM_BUILDER_VERSION,
				'all'
			);
		}
	}

	protected function init(): void {
		$this->type = 'date';
		$this->name = __( 'Date', 'smart-form-builder-by-dragwyb' );
	}

	protected function sanitize_control( $value, $settings ) {
		if ( empty( $value ) ) {
			return '';
		}

		$value = sanitize_text_field( (string) $value );
		$date  = \DateTime::createFromFormat( 'Y-m-d|', $value );

		return ( $date && $date->format( 'Y-m-d' ) === $value ) ? $value : '';
	}
}
