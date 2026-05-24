<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

abstract class Control_Base {

	protected string $type;
	protected string $name;
	private $value        = null;
	private $settings     = array();
	protected $control_id = null;

	protected function register_scripts(): array {
		return array();
	}
	protected function register_style(): array {
		return array();
	}
	abstract protected function init(): void;
	abstract protected function sanitize_control( $value, $settings );
	abstract protected function register_settings();

	protected function default_setting(): array {
		return array();
	}

	public function __construct() {
		$this->init();
	}

	public function enqueue_assets() {
		$scripts = $this->register_scripts();
		$styles  = $this->register_style();

		foreach ( $scripts as $script ) {
			if ( ! wp_script_is( $script, 'enqueued' ) ) {
				wp_enqueue_script( $script );
			}
		}
		foreach ( $styles as $style ) {
			if ( ! wp_style_is( $style, 'enqueued' ) ) {
				wp_enqueue_style( $style );
			}
		}
	}

	public function get_type(): string {
		return $this->type;
	}

	public function get_name(): string {
		return $this->name;
	}

	public function set_settings( array $data ): void {
		$control_settings = $this->register_settings();

		if ( ! isset( $control_settings['type'] ) ) {
			$control_settings['type'] = 'string';
		}

		if ( ! isset( $control_settings['selectors'] ) ) {
			$control_settings['selectors'] = 'custom';
		}

		if ( ! isset( $control_settings['conditions'] ) ) {
			$control_settings['conditions'] = 'custom';
		}

		if ( ! isset( $control_settings['responsive'] ) ) {
			$control_settings['responsive'] = 'boolean';
		}

		if ( ! isset( $control_settings['responsive_type'] ) ) {
			$control_settings['responsive_type'] = 'string';
		}

		if ( ! isset( $control_settings['responsive_control'] ) ) {
			$control_settings['responsive_control'] = 'boolean';
		}

		// Default value always set in last index.
		if ( isset( $control_settings['default'] ) ) {
			$default = $control_settings['default'];

			unset( $control_settings['default'] );

			$control_settings['default'] = $default;
		}

		$matched_keys = array_intersect_key( $control_settings, $data );

		foreach ( $matched_keys as $key => $key_type ) {
			if ( ! array_key_exists( $key, $control_settings ) || ! array_key_exists( $key, $data ) ) {
				continue;
			}

			$setting = $key;
			$value   = isset( $data[ $key ] ) ? $data[ $key ] : '';

			$sanitize_setting = $this->filter_setting_data( $control_settings[ $setting ], $value, $setting );

			if ( $sanitize_setting || ( isset( $control_settings[ $key ] ) && 'boolean' === $control_settings[ $key ] && false === $sanitize_setting ) ) {
				$this->settings[ $setting ] = $sanitize_setting;
			}
		}

		$default_setting = $this->default_setting();

		foreach ( $default_setting as $key => $value ) {
			if ( ! array_key_exists( $key, $data ) && isset( $control_settings[ $key ] ) ) {
				$sanitize_setting = $this->filter_setting_data( $control_settings[ $key ], $value, $key );

				if ( $sanitize_setting || ( isset( $control_settings[ $key ] ) && 'boolean' === $control_settings[ $key ] && false === $sanitize_setting ) ) {
					$this->settings[ $key ] = $sanitize_setting;
				}
			}
		}
	}

	public function get_settings(): array {
		return $this->settings;
	}

	public function set_value( $data, string $control_id, $extra_data = null ): void {
		$this->control_id = sanitize_text_field( $control_id );

		$this->set_filter_value( $data, $extra_data );
	}

	private function set_filter_value( $data, $extra_data ): void {
		$this->value = $this->sanitize_control( $data, $extra_data );
	}

	public function get_value() {
		return $this->value;
	}

	protected function get_display_value() {
		return $this->value;
	}

	final public function get_style_placeholders(): array {
		return $this->style_placeholders();
	}

	protected function style_placeholders(): array {
		return array(
			'VALUE' => true,
		);
	}

	private function filter_setting_data( $type, $value, $key ) {

		if ( ! $value || ! isset( $value ) || empty( $value ) || ( is_array( $value ) && count( $value ) <= 0 ) ) {
			return false;
		}

		if ( $type === 'custom' ) {
			$sanitize_setting = $key . '_setting_sanitize';

			if ( ! method_exists( $this, $sanitize_setting ) ) {
				return false;
			}

			return $this->$sanitize_setting( $value );
		}

		$sanitize_setting = $type . '_setting_sanitize';

		if ( ! method_exists( $this, $sanitize_setting ) ) {
			return false;
		}

		return $this->$sanitize_setting( $value );
	}

	protected function string_sanitize( string $value ) {
		return $this->string_setting_sanitize( $value );
	}

	private function string_setting_sanitize( string $value ) {
		return sanitize_text_field( $value );
	}

	protected function boolean_sanitize( bool $value ) {
		return $this->boolean_setting_sanitize( $value );
	}

	private function boolean_setting_sanitize( bool $value ) {
		return (bool) $value;
	}

	protected function number_sanitize( $value ) {
		return $this->number_setting_sanitize( $value );
	}

	private function number_setting_sanitize( $value ) {

		// 1. Check if it's a valid number (accepts "10", 10, 10.5, "10.5")
		if ( ! is_numeric( $value ) || $value === '' ) {
			return '';
		}

		// 2. Check if it's a float
		if ( is_float( $value ) ) {
			return floatval( $value );
		}

		// 3. If it's an integer
		return intval( $value );
	}

	private function conditions_setting_sanitize( array $conditions ) {
		$condition = array();

		foreach ( $conditions as $key => $value ) {
			if ( is_array( $value ) ) {
				$condition[ sanitize_text_field( esc_html( $key ) ) ] = array_map(
					function ( $item ) {
						return sanitize_text_field( esc_html( $item ) );
					},
					$value
				);
			} else {
				$condition[ sanitize_text_field( esc_html( $key ) ) ] = sanitize_text_field( esc_html( $value ) );
			}
		}

		return $condition;
	}

	private function selectors_setting_sanitize( $value ) {
		$return = array();

		foreach ( $value as $key => $value ) {
			$return[ sanitize_textarea_field( $key ) ] = sanitize_text_field( $value );
		}

		return $return;
	}

	protected function range_sanitize( array $range ) {
		return $this->range_setting_sanitize( $range );
	}

	private function range_setting_sanitize( array $range ): array {
		$filtered_range = array();

		foreach ( $range as $unit => $data ) {
			if ( is_array( $data ) & count( $data ) > 0 ) {
				$filtered_range[ $this->string_setting_sanitize( $unit ) ] = array();

				foreach ( $data as $key => $value ) {
					$filtered_range[ $this->string_setting_sanitize( $unit ) ][ $this->string_setting_sanitize( $key ) ] = $this->number_setting_sanitize( $value );
				}
			}
		}

		return $filtered_range;
	}

	public function __destruct() {
		$this->type     = '';
		$this->name     = '';
		$this->value    = null;
		$this->settings = null;
	}

	/**
	 * Create a new instance of the called class.
	 *
	 * @return static
	 */
	public static function newInstance() {
		return new static();
	}
}
