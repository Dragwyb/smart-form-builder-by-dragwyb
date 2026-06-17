<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Frontend;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Modules\Modules;
use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbars;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;
use Dragwyb\Form_Builder\Includes\Controls\Fonts\Fonts_Helper;
use Dragwyb\Form_Builder\Includes\Dragwyb_Init;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Frontend_Render {

	private static $form_id;
	private static $fields = array();

	private static $control = null;
	private static $module  = null;

	private static $field_module_cache = null;

	private static $form_data        = null;
	private static $toolbars         = null;
	private static $toolbar_data     = array();
	private static $toolbar_settings = array();
	private static $root_containers  = array();

	private static $css_cache = array();

	private static $google_fonts_cache = null;

	private static $google_fonts = null;

	private static $instance = null;

	private static $frontend_localize_data = array();

	private static $set_initial_config = false;


	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init( int $form_id ) {
		$this->clean_old_data();
		self::$form_id      = absint( $form_id );
		self::$toolbar_data = array();
		self::$fields       = array();
		self::$form_data    = get_post_meta( self::$form_id, '_dragwyb_form_data', true );

		if ( empty( self::$form_data ) || ! is_array( self::$form_data ) || ! isset( self::$form_data['fields'] ) || count( self::$form_data ) < 1 ) {
			self::$fields = array();
			return;
		}

		$this->initial_config();
	}

	private function initial_config(): void {
		if ( true === self::$set_initial_config ) {
			return;
		}

		$this->set_control();
		$this->set_module();
		$this->set_toolbar_data();
		self::$set_initial_config = true;
	}

	private function set_module(): void {
		self::$module = new Modules();
	}

	private function set_control(): void {
		self::$control = new Controls();
	}

	private function set_toolbar_data(): void {
		$toolbar_obj    = Toolbars::instance();
		self::$toolbars = $toolbar_obj->get_toolbars();

		foreach ( self::$form_data as $key => $value ) {
			if ( $key === 'id' ) {
				continue;
			}

			if ( isset( self::$toolbars ) && count( self::$toolbars ) > 0 && isset( self::$toolbars[ $key ] ) && self::$toolbars[ $key ] instanceof Toolbar_Base ) {
				$toolbar = self::$toolbars[ $key ];
				$toolbar->set_form_id( self::$form_id );
				$toolbar->set_toolbar_data( $value );
				$toolbar_data = $toolbar->get_toolbar_data();

				$root_containers = $toolbar->get_root_containers();

				if ( is_array( $root_containers ) && count( $root_containers ) > 0 ) {
					self::$root_containers = array_unique( array_merge( self::$root_containers, $root_containers ) );
				}

				if ( $toolbar_data ) {
					if ( $key === 'fields' ) {
						self::$fields = $toolbar_data;
					} else {
						self::$toolbar_data[ $key ] = $toolbar_data;
					}
				}
			}
		}
	}

	public function render(): string {
		return $this->render_fields();
	}

	public function get_fields_values(): array {
		return self::$fields;
	}

	public function get_toolbars_values( string $type ): array {
		$this->initial_config();

		if ( ! isset( self::$toolbar_data[ $type ] ) ) {
			return array();
		}

		return self::$toolbar_data[ $type ];
	}

	public function get_toolbar_data( string $type ): array {
		$this->initial_config();

		if ( ! isset( self::$toolbars[ $type ] ) ) {
			return array();
		}

		if ( ! isset( self::$toolbar_settings[ $type ] ) ) {
			self::$toolbar_settings[ $type ] = self::$toolbars[ $type ]->get_toolbar_settings();
		}
		return self::$toolbar_settings[ $type ];
	}

	public function get_field_data( string $field_id ): array {
		if ( ! isset( self::$fields[ $field_id ] ) ) {
			return array();
		}

		return self::$fields[ $field_id ];
	}

	public function get_control( string $type ) {
		return self::$control->get_control( $type );
	}

	public function get_module( string $type ) {
		if ( ! isset( self::$field_module_cache[ $type ] ) ) {
			$field_module_cache = self::$module->get_field( $type );

			if ( ! $field_module_cache instanceof Field_Base ) {
				return array();
			}

			$field_module_cache->render_controls();
			self::$field_module_cache[ $type ] = $field_module_cache;
		}

		return self::$field_module_cache[ $type ];
	}

	public function get_root_containers(): array {
		return self::$root_containers;
	}

	private function render_fields() {
		ob_start();

		if ( count( self::$root_containers ) < 1 ) {
			echo '<p>' . esc_html__( 'No fields found in this form.', 'smart-form-builder-by-dragwyb' ) . '</p>';
			return ob_get_clean();
		}

		echo '<form class="dragwyb-form" id="dragwyb-form-' . esc_attr( self::$form_id ) . '">';

		$advance_settings = $this->get_toolbars_values( 'advance' );
		if ( isset( $advance_settings['honeypot'] ) && $advance_settings['honeypot'] === 'yes' ) {
			echo '<div class="dragwyb-field-h-dragwyb">';
			echo '<input type="text" name="dragwyb_h_email" value="" tabindex="-1" autocomplete="off" />';
			echo '</div>';
		}

		foreach ( self::$root_containers as $root_container ) {
			$row_field = self::$fields[ $root_container ];

			if ( ! isset( $row_field['_id'] ) || ! isset( $row_field['type'] ) || empty( $row_field['_id'] ) || empty( $row_field['type'] ) ) {
				continue;
			}

			$type = $row_field['type'];

			if ( ! isset( self::$field_module_cache[ $type ] ) ) {
				$field_module_cache = self::$module->get_field( $type );
				$field_module_cache->render_controls();
				self::$field_module_cache[ $type ] = $field_module_cache;
			}

			if ( ! self::$field_module_cache[ $type ] instanceof Field_Base ) {
				return;
			}

			$is_root_container = self::$field_module_cache[ $type ]->is_root_container();

			if ( true !== $is_root_container ) {
				continue;
			}

			$field_data = array();

			$field_data['_id'] = $row_field['_id'];

			if ( isset( $row_field['type'] ) ) {
				$type = $row_field['type'];

				if ( ! isset( self::$field_module_cache[ $type ] ) ) {
					$field_module_cache = self::$module->get_field( $type );
					$field_module_cache->render_controls( $this );
					self::$field_module_cache[ $type ] = $field_module_cache;
				}

				if ( ! self::$field_module_cache[ $type ] instanceof Field_Base ) {
					return;
				}

				self::$field_module_cache[ $type ]->set_the_id( sanitize_text_field( $field_data['_id'] ) );
				self::$field_module_cache[ $type ]->set_frontend_handler( $this );

				if ( isset( $row_field['attributes'] ) && ! empty( $row_field['attributes'] ) ) {
					$attributes               = $row_field['attributes'];
					$field_data['attributes'] = array();
					$this->attributes_loop( $attributes, $type, $field_data );
					$field_data['attributes'] = array_merge( $field_data['attributes'], array( 'children' => $row_field['children'] ) );
					self::$field_module_cache[ $type ]->set_field_settings( $field_data['attributes'] );
				} else {
					$field_data['attributes'] = array();
					$field_data['attributes'] = array_merge( $field_data['attributes'], array( 'children' => $row_field['children'] ) );
					self::$field_module_cache[ $type ]->set_field_settings( $field_data['attributes'] );
				}

				self::$field_module_cache[ $type ]->render();
			}

			$field_data = null;
		}

		echo '</form>';

		return ob_get_clean();
	}

	private function attributes_loop( $attributes, $type, &$field_data ): void {

		if ( ! self::$field_module_cache[ $type ] instanceof Field_Base ) {
			return;
		}

		foreach ( $attributes as $attribute => $value ) {
			if ( $field_control = self::$field_module_cache[ $type ]->get_control( $attribute ) ) {
				if ( isset( $field_control['type'] ) ) {
					$control_type = $field_control['type'];

					$control_obj = self::$control->get_control( $control_type );

					if ( ! $control_obj || ! $control_obj instanceof Control_Base ) {
						continue;
					}

					$control_obj = $control_obj::newInstance();

					$control_obj->set_value( $value, $attribute, $field_control );
					$filtered_value = $control_obj->get_value();

					if ( isset( $filtered_value ) ) {
						$field_data['attributes'][ $attribute ] = $filtered_value;
					}
				}
			}
		}
	}

	public function get_generated_css(): array {
		if ( ! isset( self::$toolbars ) ) {
			return array();
		}

		foreach ( self::$toolbar_data as $toolbar_key => $settings ) {
			if ( isset( self::$toolbars[ $toolbar_key ] ) && method_exists( self::$toolbars[ $toolbar_key ], 'get_toolbar_settings' ) ) {
				self::$toolbar_settings[ $toolbar_key ] = self::$toolbars[ $toolbar_key ]->get_toolbar_settings();

				if ( isset( self::$toolbar_settings[ $toolbar_key ]['controls'] ) && is_array( self::$toolbar_settings[ $toolbar_key ]['controls'] ) && count( self::$toolbar_settings[ $toolbar_key ]['controls'] ) > 0 ) {
					$this->extract_css_from_settings( $settings, self::$toolbar_settings[ $toolbar_key ]['controls'], $toolbar_key );
				}
			}
		}

		// 2. Fields CSS
		if ( ! empty( self::$fields ) ) {
			foreach ( self::$fields as $field ) {
				if ( empty( $field['type'] ) || empty( $field['_id'] ) ) {
					continue;
				}

				$type = $field['type'];

				// Load Module if not loaded
				if ( ! isset( self::$field_module_cache[ $type ] ) ) {
					$field_module = self::$module->get_field( $type );
					if ( $field_module ) {
						self::$field_module_cache[ $type ] = $field_module;
					}
				}

				// Extract CSS from Field Attributes
				if ( isset( self::$field_module_cache[ $type ] ) && ! empty( $field['attributes'] ) && method_exists( self::$field_module_cache[ $type ], 'get_settings' ) ) {
					$field_settings = self::$field_module_cache[ $type ]->get_settings();

					if ( $field_settings && is_array( $field_settings ) && count( $field_settings ) > 0 ) {
						$this->extract_css_from_settings( $field['attributes'], $field_settings, 'fields', $field['_id'] );
					}
				}
			}
		}

		if ( defined( 'DRAGWYB_EDITOR' ) && true === DRAGWYB_EDITOR ) {
			return array(
				'css'          => self::$css_cache,
				'google_fonts' => self::$google_fonts,
			);
		}

		if ( self::$css_cache && count( self::$css_cache ) > 0 ) {
			$tablet_css = '';
			$mobile_css = '';

			if ( isset( self::$css_cache['tablet'] ) ) {
				$tablet_css = self::$css_cache['tablet'];
				unset( self::$css_cache['tablet'] );
			}

			if ( isset( self::$css_cache['mobile'] ) ) {
				$mobile_css = self::$css_cache['mobile'];
				unset( self::$css_cache['mobile'] );
			}

			$css_string = $this->convert_css_into_strings( self::$css_cache );

			if ( $tablet_css && $tablet_css !== '' ) {
				$css_string .= '@media (max-width: 768px) {' . $this->convert_css_into_strings( $tablet_css ) . '}';
			}

			if ( $mobile_css && $mobile_css !== '' ) {
				$css_string .= '@media (max-width: 480px) {' . $this->convert_css_into_strings( $mobile_css ) . '}';
			}

			return array(
				'css'          => $css_string,
				'google_fonts' => self::$google_fonts,
			);
		}

		return array(
			'css'          => '',
			'google_fonts' => array(),
		);
	}

	private function convert_css_into_strings( $css_cache ): string {
		$css_string = '';

		foreach ( $css_cache as $selector => $styles ) {
			$css_string .= $selector . '{';
			$css_string .= $styles;
			$css_string .= '}';
		}

		return sanitize_text_field( $css_string );
	}

	public static function enqueue_static_assets() {
		self::frontend_assets();
	}

	private static function frontend_assets() {
		Dragwyb_Init::core_script();

		self::enqueue_style_asset();
		self::enqueue_script_asset();
	}

	private static function enqueue_style_asset() {
		do_action( 'Dragwyb/Frontend/Before_Enqueue/Style' );

		wp_enqueue_style( 'smart-form-builder-by-dragwyb', esc_url( DRAGWYB_FORM_BUILDER_URL . '/assets/css/form-frontend.css' ), array(), esc_attr( DRAGWYB_FORM_BUILDER_VERSION ) );

		do_action( 'Dragwyb/Frontend/After_Enqueue/Style' );
	}

	private static function enqueue_script_asset() {
		do_action( 'Dragwyb/Frontend/Before_Enqueue/Script' );

		wp_enqueue_script( 'dragwyb-form-core' );

		$js_assets_info = array(
			'version'      => DRAGWYB_FORM_BUILDER_VERSION,
			'dependencies' => array( 'jquery', 'dragwyb-form-core' ),
		);

		if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/frontend/frontend.asset.php' ) ) {
			$dragwyb_js_assets_info = require_once DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/frontend/frontend.asset.php';

			if ( isset( $dragwyb_js_assets_info['dependencies'] ) ) {
				$js_assets_info['dependencies'] = array_merge( $js_assets_info['dependencies'], $dragwyb_js_assets_info['dependencies'] );
			}

			if ( isset( $dragwyb_js_assets_info['version'] ) ) {
				$js_assets_info['version'] = $dragwyb_js_assets_info['version'];
			}
		}

		wp_register_script(
			'dragwyb-form-frontend',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/dist/frontend/frontend.js' ),
			$js_assets_info['dependencies'],
			esc_attr( $js_assets_info['version'] ),
			true
		);

		wp_enqueue_script( 'dragwyb-form-frontend' );

		$dragwyb_fontend_localize_data = apply_filters( 'Dragwyb/Frontend/Localize_Settings', array() );

		self::$frontend_localize_data = array_merge(
			array(
				'frontendRoute' => rest_url( 'dragwyb-form-builder/v1/' ),
				'nonce'         => wp_create_nonce( 'wp_rest' ),
			),
			$dragwyb_fontend_localize_data
		);

		if ( defined( 'DRAGWYB_FORM_PREVIEW' ) && true === DRAGWYB_FORM_PREVIEW && function_exists( 'wp_add_inline_style' ) ) {
			$form_id          = self::$form_id;
			$unique_id        = get_post_meta( $form_id, '_dragwyb_form_assets_id', true );
			$atfp_style_exist = false;

			if ( $unique_id && $unique_id !== '' ) {
				$dragwyb_upload_info = wp_upload_dir();

				// Create a specific folder for your plugin's CSS
				$atfp_upload_dir = $dragwyb_upload_info['basedir'] . '/dragwyb-forms/css/';
				$atfp_upload_url = self::get_upload_dir_url( $dragwyb_upload_info['baseurl'] ) . '/dragwyb-forms/css/';

				$atfp_file_name = 'form-' . $form_id . '-' . $unique_id . '.css';
				$atfp_file_path = $atfp_upload_dir . $atfp_file_name;
				$atfp_file_url  = $atfp_upload_url . $atfp_file_name;

				if ( file_exists( $atfp_file_path ) ) {
					wp_enqueue_style( 'dragwyb-form-' . $form_id, esc_url( $atfp_file_url ), array(), esc_attr( DRAGWYB_FORM_BUILDER_VERSION ) );
					$atfp_style_exist = true;
				}
			}

			if ( ! $atfp_style_exist ) {
				$style_content = self::instance()->get_generated_css();
				$style_content = wp_strip_all_tags( $style_content['css'] );
				wp_add_inline_style( 'smart-form-builder-by-dragwyb', wp_kses_post( $style_content ) );
			}
		}

		do_action( 'Dragwyb/Frontend/After_Enqueue/Script' );
	}

	final static function localize_form_data(): void {
		$dragwyb_fontend_localize_data = array();

		if ( isset( self::$toolbar_data['after-submission']['after_submissions'] ) ) {
			$dragwyb_fontend_localize_data['actions'] = array_values( self::$toolbar_data['after-submission']['after_submissions'] );
		}

		$dragwyb_fontend_localize_data = apply_filters( 'Dragwyb/Frontend/Form/Localize_Settings', $dragwyb_fontend_localize_data, self::$form_id );

		$dragwyb_fontend_localize_data['nonce'] = wp_create_nonce( self::get_submission_key( self::$form_id ) );

		self::$frontend_localize_data[ 'form_' . self::$form_id ] = $dragwyb_fontend_localize_data;

		if ( ! isset( self::$frontend_localize_data['render_forms'] ) ) {
			self::$frontend_localize_data['render_forms'] = array();
		}

		if ( ! in_array( self::$form_id, self::$frontend_localize_data['render_forms'] ) ) {
			self::$frontend_localize_data['render_forms'][] = self::$form_id;
		}

		wp_localize_script( 'dragwyb-form-frontend', 'DragwybFrontendData', self::$frontend_localize_data );
	}

	/**
	 * Get submission nonce key for a form.
	 */
	final static function get_submission_key( int $form_id ): string {
		return 'dragwyb_form_submission_' . absint( $form_id );
	}

	/**
	 * Filter and return url based on ssl protocol form start
	 */
	private static function get_upload_dir_url( string $url ): string {
		return is_ssl() ? preg_replace( '/^http:/', 'https:', $url ) : $url;
	}

	/**
	 * Generates CSS string from settings.
	 * Only processes controls that define 'selectors'.
	 */
	private function extract_css_from_settings( array $settings, array $controls, $type, $field_id = null ): void {
		$form_wrapper_id = '#dragwyb-form-wrapper-' . self::$form_id;

		if ( isset( self::$fields[ $field_id ]['is_root_container'] ) && true === self::$fields[ $field_id ]['is_root_container'] && isset( self::$fields[ $field_id ]['type'] ) ) {
			$form_wrapper_id .= ' #dragwyb-' . self::$fields[ $field_id ]['type'] . '-' . $field_id;
		} elseif ( $field_id && is_string( $field_id ) && ! empty( $field_id ) ) {
			$form_wrapper_id .= ' #dragwyb-field-wrapper-' . $field_id;
		}

		foreach ( $controls as $control_id => $control_settings ) {
			// 1. Validate: Ensure control definition and 'selectors' exist
			if (
				! isset( $control_settings['type'] )
			) {
				continue;
			}

			// 2. Instantiate Control
			$control_manager = self::$control->get_control( $control_settings['type'] );

			if ( ! $control_manager || ! $control_manager instanceof Control_Base ) {
				continue;
			}

			$setting_value = null;

			if ( isset( $settings[ $control_id ] ) ) {
				$setting_value = $settings[ $control_id ];
			}

			if ( ! isset( $setting_value ) ) {
				continue;
			}

			if ( $control_settings['type'] === 'repeater' && isset( $control_settings['items'] ) ) {
				$control_instance = $control_manager::newInstance();

				$control_instance->set_value( $setting_value, $control_id, $control_settings );
				$repeater_values = $control_instance->get_value();

				$repeater_controls_instance_cache = array();
				foreach ( $repeater_values as $index => $item ) {
					if ( ! isset( $item['attributes'] ) || count( $item['attributes'] ) < 1 ) {
						continue;
					}

					$repeater_id = $item['_id'];

					foreach ( $item['attributes'] as $item_control_id => $item_control_data ) {
						if ( ! isset( $control_settings['items'][ $item_control_id ] ) ) {
							continue;
						}
						if ( ! isset( $control_settings['items'][ $item_control_id ]['selectors'] ) ) {
							continue;
						}
						if ( count( $control_settings['items'][ $item_control_id ]['selectors'] ) < 1 ) {
							continue;
						}

						if ( ! isset( $repeater_controls_instance_cache[ $item_control_id ] ) ) {
							$repeater_control_manager                             = self::$control->get_control( $control_settings['items'][ $item_control_id ]['type'] );
							$repeater_controls_instance_cache[ $item_control_id ] = $repeater_control_manager::newInstance();
						}

						if ( ! $this->control_render_conditions( $control_settings['items'][ $item_control_id ], $control_settings['items'], $item['attributes'] ) ) {
							continue;
						}

						$repeater_controls_instance_cache[ $item_control_id ]->set_value( $item_control_data, $item_control_id );
						$repeater_item_value = $repeater_controls_instance_cache[ $item_control_id ]->get_value();

						// google fonts cache
						$this->fonts_family_cache( $repeater_controls_instance_cache[ $item_control_id ], $repeater_item_value );

						$placeholders = $this->get_control_placeholders( $control_instance );

						$placeholders = $this->replace_selector_placeholders( $form_wrapper_id, $control_settings['items'][ $item_control_id ]['selectors'], $placeholders, $repeater_item_value, $type, $control_id, $control_settings['items'][ $item_control_id ], $field_id, $repeater_id );
					}
				}
				continue;
			} elseif (
				! isset( $control_settings['selectors'] ) ||
				! is_array( $control_settings['selectors'] ) &&
				count( $control_settings['selectors'] ) < 1
			) {
				continue;
			}

			$control_instance = $control_manager::newInstance();
			$control_instance->set_value( $setting_value, $control_id, $control_settings );
			$control_value = $control_instance->get_value();

			if ( ! $this->control_render_conditions( $control_settings, $controls, $settings ) ) {
				continue;
			}

			// google fonts cache
			$this->fonts_family_cache( $control_instance, $control_value );

			// Uses helper method to support both complex and simple controls
			$placeholders = $this->get_control_placeholders( $control_instance );

			$this->replace_selector_placeholders( $form_wrapper_id, $control_settings['selectors'], $placeholders, $control_value, $type, $control_id, $control_settings, $field_id );
		}
	}

	private function replace_selector_placeholders( $wrapper_id, $selectors, $placeholders, $value, $type, $control_id, $control_config, $field_id = null, $current_item = null ): void {
		$css_array = &self::$css_cache;

		$responsive = false;
		if ( isset( $control_config['responsive_control'] ) && isset( $control_config['responsive_type'] ) && true === $control_config['responsive_control'] && ! empty( $control_config['responsive_type'] ) && is_string( $control_config['responsive_type'] ) && 'desktop' !== $control_config['responsive_type'] ) {
			$responsive = sanitize_text_field( $control_config['responsive_type'] );

			if ( ! isset( $css_array[ $responsive ] ) ) {
				$css_array[ $responsive ] = array();
			}

			$css_array = &$css_array[ $responsive ];
		}

		// 4. Process 'selectors' Loop
		// Format: ['{{WRAPPER}} .title' => 'color: {{VALUE}};']
		foreach ( $selectors as $css_selector => $css_property ) {

			$final_selector = trim( sanitize_text_field( $css_selector ) );

			// A. Parse Selector (Replace {{WRAPPER}})
			$final_selector = str_replace( '{{WRAPPER}}', $wrapper_id, $final_selector );

			if ( $current_item && is_string( $current_item ) ) {
				$final_selector = str_replace( '{{CURRENT_ITEM}}', '.' . $current_item, $final_selector );
			}

			// B. Parse Property (Replace {{VALUE}}, {{UNIT}}, etc.)
			$final_property = $css_property;

			foreach ( $placeholders as $ph_key => $ph_value ) {
				$ph_key    = sanitize_text_field( $ph_key );
				$ph_value  = is_string( $ph_value ) ? sanitize_text_field( $ph_value ) : boolval( $ph_value );
				$css_value = '';
				if ( $ph_value === true ) {
					$css_value = sanitize_text_field( $value );
				} else {
					$css_value = isset( $value[ $ph_value ] ) ? $value[ $ph_value ] : '';
				}

				if ( is_string( $css_value ) ) {
					$css_value = wp_strip_all_tags( $css_value );
					if ( ! preg_match( '/^[a-zA-Z0-9\s#.,()%\-\'"]*$/', $css_value ) ) {
						$css_value = '';
					}
				}

				if ( $css_value === '' && isset( $placeholders['UNIT'] ) ) {
					$is_unit_value = '{{' . $ph_key . '}}' . '{{UNIT}}';
					if ( strpos( $final_property, $is_unit_value ) !== false ) {
						$final_property = str_replace( $is_unit_value, (string) $css_value, $final_property );
					}
				}
				$final_property = trim( str_replace( '{{' . $ph_key . '}}', (string) $css_value, $final_property ) );
			}

			$final_property = $this->clean_css_params( $final_property );

			if ( trim( $final_property ) === '' ) {
				continue;
			}

			// C. Append to CSS string if property is valid
			if ( ! empty( $final_property ) ) {
				$this->set_css_cache( $css_array, $final_selector, $final_property, $type, $control_id, $field_id, $current_item );
			}
		}
	}

	private function clean_css_params( $css ) {
		$css = preg_replace( '/[\w-]*:\s*(;|$)/', '', $css );
		return trim( preg_replace( '/\s+/', ' ', $css ) );
	}

	private function set_css_cache( &$css_array, $selector, $property, $type, $control_id, $field_id = null, $current_item = null ) {
		$property = str_ends_with( $property, ';' ) ? $property : $property . ';';

		if ( defined( 'DRAGWYB_EDITOR' ) && true === DRAGWYB_EDITOR ) {
			$unique_key = sanitize_text_field( $type );

			if ( $field_id && is_string( $field_id ) ) {
				$unique_key .= '_' . sanitize_text_field( $field_id );
			}

			$unique_key .= '_' . sanitize_text_field( $control_id );

			if ( $current_item && is_string( $current_item ) ) {
				$unique_key .= '_' . sanitize_text_field( $current_item );
			}

			if ( ! isset( $css_array[ $unique_key ] ) ) {
				$css_array[ $unique_key ] = array();
			}

			$css_array[ $unique_key ][ $selector ] = $property;
		} elseif ( ! isset( $css_array[ $selector ] ) ) {
				$css_array[ $selector ] = $property;
		} else {
			$css_array[ $selector ] = $css_array[ $selector ] . $property;
		}
	}

	/**
	 * Helper: Normalize placeholder data retrieval
	 */
	private function get_control_placeholders( $control_instance ): array {
		// If control has specific logic (e.g., Dimensions returns TOP, RIGHT, UNIT)
		if ( method_exists( $control_instance, 'get_style_placeholders' ) ) {
			return $control_instance->get_style_placeholders();
		}

		// Fallback for simple controls (Color, Text) that just use {{VALUE}}
		return array( 'VALUE' => 'value' );
	}

	private function fonts_family_cache( $control_instance, $value ): void {
		$type = $control_instance->get_type();

		if ( $type !== 'fonts' ) {
			return;
		}

		if ( ! isset( self::$google_fonts_cache ) || empty( self::$google_fonts_cache ) ) {
			self::$google_fonts_cache = Fonts_Helper::get_fonts_by_groups( array( 'google' ) );
		}

		if ( isset( self::$google_fonts_cache[ sanitize_text_field( $value ) ] ) && 'google' === self::$google_fonts_cache[ sanitize_text_field( $value ) ] && ( ! isset( self::$google_fonts ) || ! in_array( sanitize_text_field( $value ), self::$google_fonts ) ) ) {
			self::$google_fonts[] = sanitize_text_field( $value );
		}
	}

	/**
	 * Helper: Check if a control should be rendered based on conditions
	 */
	private function control_render_conditions( $current_control, $controls, $toolbar_values ): bool {
		$conditions = $current_control['conditions'] ?? null;

		// If no conditions or not an array, allow rendering
		if ( empty( $conditions ) || ! is_array( $conditions ) ) {
			return true;
		}

		foreach ( $conditions as $key => $expected ) {
			// Check if key ends with "!"
			$is_not = ( substr( $key, -1 ) === '!' );

			// Remove the "!" to get the real control ID
			$clean_key = $is_not ? substr( $key, 0, -1 ) : $key;

			// We look up the dependency control config using the clean key
			if ( isset( $controls[ $clean_key ] ) ) {
				$dependency_control = $controls[ $clean_key ];
				$type               = $dependency_control['type'] ?? '';

				// If the dependency is just a layout element, ignore this condition
				if ( in_array( $type, array( 'section', 'tabs' ), true ) ) {
					continue;
				}
			}

			if ( ! isset( $toolbar_values[ $clean_key ] ) && $clean_key === 'section' ) {
				continue;
			}

			// We use array_key_exists to ensure we catch null values correctly
			if ( ! array_key_exists( $clean_key, $toolbar_values ) && $clean_key !== 'section' ) {
				if ( ! array_key_exists( $clean_key, $controls ) || ! array_key_exists( 'default', $controls[ $clean_key ] ) ) {
					return false;
				}
			}

			$actual = '';
			if ( isset( $toolbar_values[ $clean_key ] ) ) {
				$actual = $toolbar_values[ $clean_key ];
			} else {
				$actual = sanitize_text_field( $controls[ $clean_key ]['default'] );
			}

			// 4. Comparison Logic
			$control_render_status = $this->control_render_conditions_status( $expected, $actual, $is_not );

			if ( null !== $control_render_status ) {
				return $control_render_status;
			}
		}

		return true;
	}

	/**
	 * Compare the actual value with the expected value based on the condition type.
	 *
	 * @return bool|null Returns true if the condition is met, false if it's not met, and null if the type is not supported.
	 */
	private function control_render_conditions_status( $expected, $actual, $is_not ) {
		$dragwyb_control_render_status = null;

		if ( $is_not ) {
			if ( is_array( $actual ) ) {
				if ( is_array( $expected ) ) {
					$dragwyb_condition_skipLoop = null;
					foreach ( $expected as $exp ) {
						if ( in_array( $exp, $actual ) ) {
							$dragwyb_condition_skipLoop = true;
							break;
						}
					}

					if ( $dragwyb_condition_skipLoop !== true ) {
						$dragwyb_control_render_status = false;
					}
				} elseif ( in_array( $expected, $actual ) ) {
					$dragwyb_control_render_status = false;
				}
			} elseif ( is_array( $expected ) ) {
				if ( in_array( $actual, $expected ) ) {
					$dragwyb_control_render_status = false;
				}
			} elseif ( $actual === $expected ) {
				$dragwyb_control_render_status = false;
			}
		} elseif ( is_array( $actual ) ) {
			if ( is_array( $expected ) ) {
				$dragwyb_condition_skipLoop = null;
				foreach ( $expected as $exp ) {
					if ( in_array( $exp, $actual ) ) {
						$dragwyb_condition_skipLoop = true;
						break;
					}
				}

				if ( $dragwyb_condition_skipLoop !== true ) {
					$dragwyb_control_render_status = false;
				}
			} elseif ( ! in_array( $expected, $actual ) ) {
				$dragwyb_control_render_status = false;
			}
		} elseif ( is_array( $expected ) ) {
			if ( ! in_array( $actual, $expected ) ) {
				$dragwyb_control_render_status = false;
			}
		} elseif ( $actual !== $expected ) {
			$dragwyb_control_render_status = false;
		}

		return $dragwyb_control_render_status;
	}

	private function clean_old_data(): void {
		self::$form_id            = null;
		self::$fields             = array();
		self::$root_containers    = array();
		self::$module             = null;
		self::$field_module_cache = null;
		self::$form_data          = null;
		self::$toolbar_data       = array();
		self::$toolbar_settings   = array();
		self::$css_cache          = array();
		self::$google_fonts_cache = null;
		self::$google_fonts       = null;
	}
}
