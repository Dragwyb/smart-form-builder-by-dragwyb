<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Control_Preset_Style extends Control_Base {

	protected function register_settings() {
		return array(
			'name'         => 'string',
			'label'        => 'string',
			'default'      => 'string',
			'options'      => 'custom',
			'label_inline' => 'boolean',
		);
	}

	protected function init(): void {
		$this->type = 'preset_style';
		$this->name = __( 'Preset Style', 'smart-form-builder-by-dragwyb' );
	}

	protected function sanitize_control( $value, $settings ) {
		if ( ! isset( $settings['options'][ $value ] ) ) {
			return false;
		}

		return sanitize_text_field( $value );
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
