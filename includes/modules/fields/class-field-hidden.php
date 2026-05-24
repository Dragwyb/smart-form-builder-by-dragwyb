<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Hidden extends Field_Base {

	protected function init(): void {
		$this->type     = 'hidden';
		$this->name     = __( 'Hidden Field', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'fas fa-eye-slash';
		$this->category = 'advanced-fields';
		$this->keywords = array( 'invisible', 'secret', 'tracking' );
	}

	protected function header_controls(): array {
		$header_tab = array();
		$tabs       = array();

		$tabs = apply_filters( 'Dragwyb/Editor/render_controls/header_tabs', $tabs );

		if ( $tabs && is_array( $tabs ) && count( $tabs ) > 0 ) {
			$header_tab['header_controls'] = array(
				'type' => 'tabs',
				'tabs' => $tabs,
			);
		}

		return $header_tab;
	}

	protected function tab_condition( &$conditions, $data ): array {
		if ( ( isset( $data['tab'] ) && ! empty( $data['tab'] ) ) ) {
			$conditions['header_controls'] = $data['tab'];
		}

		return $conditions;
	}

	protected function layout_id_controls(): void {}

	protected function register_field_controls(): void {
		// Hidden fields only need basic settings
		$this->start_section( 'section_content', array( 'label' => 'Settings' ) );

		$this->add_control(
			'field_id',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Field Name / ID', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'The name attribute used to identify this data (e.g., source_id).', 'smart-form-builder-by-dragwyb' ),
				'default'     => uniqid( 'hidden_' ),
			)
		);

		$this->add_control(
			'default_value',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Default Value', 'smart-form-builder-by-dragwyb' ),
				'default' => '',
			)
		);

		$this->end_section();
	}

	protected function render_field() {
		$settings = $this->get_field_settings();
		$id       = $this->field_key_exist( $settings, 'field_id', uniqid( 'hidden_' ) );
		$value    = $this->field_key_exist( $settings, 'default_value', '' );

		$this->add_field_attributes(
			'input',
			array(
				'type'  => 'hidden',
				'name'  => $id,
				'value' => $value,
			)
		);

		// Just render the input, no wrapper needed in frontend
		?>
		<input <?php $this->render_field_attributes( 'input' ); ?> />
		<?php
	}

	public function validate( $value, $field_id, $form_config, Form_Submission_Handler $error_handler ): void {
		if ( ! isset( $form_config['fields'][ $field_id ] ) ) {
			$error_handler->add_error( $field_id, __( 'Invalid field.', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		if ( empty( $value ) ) {
			$error_handler->add_error( $field_id, __( 'This field is required', 'smart-form-builder-by-dragwyb' ) );
			return;
		}
	}

	/**
	 * Sanitize the field value.
	 *
	 * @param string $default The default value.
	 * @param mixed  $value The value to sanitize.
	 * @return mixed Sanitized value.
	 */
	public function sanitize( $default = '', $value = null ) {
		if ( $value && is_string( $value ) ) {
			return sanitize_text_field( $value );
		}

		return null;
	}
}
