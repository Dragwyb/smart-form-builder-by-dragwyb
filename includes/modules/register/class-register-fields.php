<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Register;

use Dragwyb\Form_Builder\Includes\Helper\Helper;
use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;
use Dragwyb\Form_Builder\Admin\Settings\Settings_Manager;

class Register_Fields {

	private static $instance = null;

	private array $fields = array();

	private array $registered_fields = array();

	private array $field_manager_setting = array();

	private array $default_fields = array( 'button', 'checkbox', 'date', 'text', 'email', 'hidden', 'number', 'radio', 'textarea', 'select', 'row', 'html', 'section', 'url', 'phone', 'name', 'address', 'time', 'range', 'captcha', 'file', 'step' );

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		if ( empty( $this->field_manager_setting ) ) {
			$this->set_field_manager_setting();
		}

		$this->register_default_fields();

		do_action( 'Dragwyb/form_builder/fields/register', $this );
	}


	private function register_default_fields(): void {
		foreach ( $this->default_fields as $field ) {

			$dragwyb_name_space = Helper::namespace_into_dir_path( __NAMESPACE__ );
			$dir                = dirname( $dragwyb_name_space );
			$dir                = Helper::dir_path_into_namespace( $dir );

			$class = $dir . '\Fields\Field_' . ucfirst( esc_html( $field ) );

			if ( class_exists( $class ) ) {
				$this->register_field( new $class() );
			}
		}
	}

	private function set_field_manager_setting(): void {
		$dragwyb_settings = get_option( 'dragwyb_form_settings', array() );

		if ( isset( $dragwyb_settings['fields_manager'] ) && ! empty( $dragwyb_settings['fields_manager'] ) ) {
			foreach ( $dragwyb_settings['fields_manager'] as $field_type => $field_enabled ) {
				$this->field_manager_setting[ sanitize_text_field( $field_type ) ] = (bool) $field_enabled;
			}
		}
	}

	private function set_register_fields( Field_Base $field ): void {
		if ( ! in_array( $field->get_type(), $this->registered_fields ) ) {
			$this->registered_fields[ $field->get_type() ] = array(
				'name'              => $field->get_name(),
				'icon'              => $field->get_icon(),
				'category'          => $field->get_category(),
				'keywords'          => $field->get_keywords(),
				'is_root_container' => $field->is_root_container(),
				'allow_child'       => $field->get_allow_child(),
			);
		}
	}

	public function register_field( Field_Base $field ): void {
		$this->set_register_fields( $field );

		if ( isset( $this->field_manager_setting[ $field->get_type() ] ) && $this->field_manager_setting[ $field->get_type() ] !== true && ! in_array( $field->get_type(), array( 'row', 'button' ) ) ) {
			return;
		}

		$this->fields[ $field->get_type() ] = $field;
	}

	public function get_fields(): array {
		return $this->fields;
	}

	public function get_registered_fields(): array {
		return $this->registered_fields;
	}
}
