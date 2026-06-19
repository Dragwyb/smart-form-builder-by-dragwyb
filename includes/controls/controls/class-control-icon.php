<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

use Dragwyb\Form_Builder\Includes\Controls\Icons\Icons_Helper;

class Control_Icon extends Control_Base {

	protected function register_settings() {
		return array(
			'label'        => 'string',
			'default'      => 'custom',
			'fa_lib'       => 'custom',
			'label_inline' => 'boolean',
		);
	}

	protected function init(): void {
		$this->type = 'icon';
		$this->name = __( 'Icon', 'smart-form-builder-by-dragwyb' );
	}


	protected function default_setting(): array {
		return array(
			'label_inline' => true,
			'fa_lib'       => Icons_Helper::get_icon_groups(),
		);
	}

	protected function fa_lib_setting_sanitize( array $value ) {
		$icons = array_filter(
			$value,
			function ( $icon ) {
				return in_array( $icon, Icons_Helper::get_icon_groups() );
			}
		);

		return $icons;
	}

	protected function default_setting_sanitize( array $settings ): array {

		if ( isset( $settings['icon'] ) ) {
			$sanitized_value['icon'] = sanitize_text_field( $settings['icon'] );
		}

		if ( isset( $settings['type'] ) && in_array( $settings['type'], Icons_Helper::get_icon_groups() ) ) {
			$sanitized_value['type'] = sanitize_text_field( $settings['type'] );
		}

		return $sanitized_value;
	}

	protected function sanitize_control( $value, $settings ) {
		$icon         = array();
		$icon['icon'] = '';
		$icon['type'] = '';

		if ( isset( $value['icon'] ) && isset( $value['type'] ) ) {
			$icon['icon'] = sanitize_text_field( $value['icon'] );
			$icon['type'] = sanitize_text_field( $value['type'] );
		}

		// Sanitize as a CSS class string (e.g., "fa fa-home")
		return $icon;
	}
}
