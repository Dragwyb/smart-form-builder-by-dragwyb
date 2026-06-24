<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Url extends Control_Base {

	protected function register_settings() {
		return array(
			'label'         => 'string',
			'default'       => 'url', // Custom sanitizer below
			'placeholder'   => 'string',
			'show_external' => 'boolean', // Option to hide/show the "New Window" checkbox
			'is_external'   => 'boolean', // Option to hide/show the "New Window" checkbox
			'nofollow'      => 'boolean', // Option to hide/show the "New Window" checkbox
			'url'           => 'url', // Option to hide/show the "New Window" checkbox
			'dynamic_tag'   => 'custom',
			'field_id_tags' => 'boolean',
		);
	}

	protected function init(): void {
		$this->type = 'url';
		$this->name = __( 'URL', 'smart-form-builder-by-dragwyb' );
	}

	/**
	 * Sanitize the main value.
	 * Expected format: ['url' => '...', 'is_external' => true/false, 'nofollow' => true/false]
	 */
	protected function sanitize_control( $value, $settings ) {
		if ( ! is_array( $value ) ) {
			return array();
		}

		return array(
			'url'         => isset( $value['url'] ) ? esc_url_raw( $value['url'] ) : '',
			'is_external' => isset( $value['is_external'] ) ? (bool) $value['is_external'] : false,
			'nofollow'    => isset( $value['nofollow'] ) ? (bool) $value['nofollow'] : false,
		);
	}

	/**
	 * Default values for the control settings
	 */
	protected function default_setting(): array {
		return array(
			'url'         => '',
			'is_external' => false,
			'nofollow'    => false,
			'dynamic_tag' => array(
				'active'    => true,
				'field_ids' => false,
			),
		);
	}

	// Custom sanitizer for the 'default' setting in register_settings
	protected function url_setting_sanitize( $value ) {
		return $this->sanitize_control( $value );
	}
}
