<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Color extends Control_Base {

	protected function register_settings() {
		return array(
			'name'         => 'string',
			'label'        => 'string',
			'default'      => 'string',
			'label_inline' => 'boolean',
		);
	}

	protected function init(): void {
		$this->type = 'color';
		$this->name = __( 'Color', 'smart-form-builder-by-dragwyb' );
	}

	protected function sanitize_control( $value, $settings ) {
		if ( ! is_string( $value ) || '' === trim( $value ) ) {
			return '';
		}

		$value = sanitize_text_field( wp_unslash( $value ) );

		// Remove dangerous CSS injection / XSS characters like ;, {, }, <, >, ", ', and malicious protocols
		$value = preg_replace( '/[;{}<>\'"]/', '', $value );
		$value = preg_replace( '/(?:url|expression|javascript)\s*\(/i', '', $value );

		// Validate safe CSS color characters: hex (#), alphanumerics, spaces, dashes, underscores, %, commas, dots, parentheses
		if ( preg_match( '/^[\#a-zA-Z0-9\s\-_%,.\(\)]+$/', $value ) ) {
			return trim( $value );
		}

		return '';
	}
}
