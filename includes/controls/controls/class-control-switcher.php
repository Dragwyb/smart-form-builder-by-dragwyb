<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Switcher extends Control_Base {

	protected function register_settings() {
		return array(
			'name'         => 'string',
			'label'        => 'string',
			'default'      => 'string',
			'on_label'     => 'string',
			'off_label'    => 'string',
			'return_value' => 'string',
			'show_label'   => 'boolean',
			'label_inline' => 'boolean',
			'disabled'     => 'boolean',
			'default'      => 'string',
		);
	}

	protected function default_setting(): array {
		return array(
			'on_label'     => __( 'Yes', 'smart-form-builder-by-dragwyb' ),
			'off_label'    => __( 'No', 'smart-form-builder-by-dragwyb' ),
			'return_value' => 'yes',
			'default'      => 'no',
			'show_label'   => true,
		);
	}

	protected function init(): void {
		$this->type = 'switcher';
		$this->name = __( 'Switcher', 'smart-form-builder-by-dragwyb' );
	}

	protected function sanitize_control( $value, $settings ) {
		return sanitize_text_field( $value );
	}
}
