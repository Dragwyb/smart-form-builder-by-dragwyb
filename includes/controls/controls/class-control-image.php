<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Image extends Control_Base {

	protected function register_settings() {
		return array(
			'label'   => 'string',
			'default' => 'image', // Custom sanitizer
		);
	}

	protected function init(): void {
		$this->type = 'image';
		$this->name = __( 'Image', 'smart-form-builder-by-dragwyb' );
	}

	/**
	 * Override enqueue_assets to load the WP Media Modal core
	 */
	protected function register_scripts(): array {
		if ( ! did_action( 'wp_enqueue_media' ) ) {
			wp_enqueue_media();
		}

		return array();
	}

	/**
	 * Sanitize Image Data
	 * Expected format: Object {id: 1, url: '...'}
	 */
	protected function sanitize_control( $value, $settings ) {
		if ( ! is_array( $value ) ) {
			return array(
				'id'  => '',
				'url' => '',
			);
		}

		return array(
			'id'  => isset( $value['id'] ) ? absint( $value['id'] ) : '',
			'url' => isset( $value['url'] ) ? esc_url_raw( $value['url'] ) : '',
		);
	}

	// Default sanitizer for the setting
	protected function image_setting_sanitize( $value ) {
		return $this->sanitize_control( $value );
	}

	protected function style_placeholders(): array {
		return array( 'URL' => 'url' );
	}
}
