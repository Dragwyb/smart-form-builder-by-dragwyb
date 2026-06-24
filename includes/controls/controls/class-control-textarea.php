<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Textarea extends Control_Base {

	protected function register_settings() {
		return array(
			'name'          => 'string',
			'label'         => 'string',
			'default'       => 'string',
			'dynamic_tag'   => 'custom',
			'field_id_tags' => 'boolean',
		);
	}

	protected function init(): void {
		$this->type = 'textarea';
		$this->name = __( 'Textarea', 'smart-form-builder-by-dragwyb' );
	}

	protected function default_setting(): array {
		return array(
			'dynamic_tag' => array(
				'active'    => true,
				'field_ids' => false,
			),
		);
	}

	protected function sanitize_control( $value, $settings ) {
		return sanitize_text_field( $value );
	}
}
