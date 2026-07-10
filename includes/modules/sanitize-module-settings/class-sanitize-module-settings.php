<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Sanitize_Module_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Modules\Modules;
use Dragwyb\Form_Builder\Includes\Toolbars\Sanitize_Data;

if ( ! class_exists( 'Sanitize_Module_Settings' ) ) {
	class Sanitize_Module_Settings {

		private static $filtered_data   = array();
		private static $root_containers = array();

		private static $form_fields = null;

		private static $field_module = null;

		private static $instance = null;

		private static $module = null;

		public static function instance( array $control_data ): self {
			if ( null === self::$instance ) {
				self::$instance = new self( $control_data );
			}
			return self::$instance;
		}

		public function __construct( $data ) {
			self::$form_fields = $data;

			$this->set_module();
			$this->field_loop();
		}

		private function set_module(): void {
			self::$module = new Modules();
		}

		private function field_loop(): void {
			foreach ( self::$form_fields as $index => $field ) {
				if ( ! isset( $field['_id'] ) || ! $field['type'] || null === self::$module->get_field( $field['type'] ) ) {
					continue;
				}

				self::$filtered_data[ $field['_id'] ]['_id']  = sanitize_text_field( $field['_id'] );
				self::$filtered_data[ $field['_id'] ]['type'] = sanitize_text_field( $field['type'] );

				if ( isset( $field['children'] ) && is_array( $field['children'] ) && count( $field['children'] ) > 0 ) {
					self::$filtered_data[ $field['_id'] ]['children'] = array_map(
						function ( $child ) {
							return isset( $child ) ? sanitize_text_field( $child ) : null;
						},
						$field['children']
					);
				}

				if ( isset( $field['parentId'] ) ) {
					self::$filtered_data[ $field['_id'] ]['parentId'] = sanitize_text_field( $field['parentId'] );
				}

				if ( isset( $field['children'] ) || isset( $field['is_root_container'] ) ) {
					$type = sanitize_text_field( $field['type'] );
					$this->set_field_module( $type );

					$is_root_container = self::$field_module[ $type ]->is_root_container();
					if ( $is_root_container ) {
						self::$root_containers[]                                   = $field['_id'];
						self::$filtered_data[ $field['_id'] ]['is_root_container'] = true;
					}
				}

				if ( isset( $field['type'] ) && isset( $field['attributes'] ) ) {
					if ( is_array( $field['attributes'] ) && count( $field['attributes'] ) > 0 ) {
						$type       = sanitize_text_field( $field['type'] );
						$attributes = $field['attributes'];

						$this->set_field_module( $type );
						if ( ! isset( self::$field_module[ $type ] ) ) {
							$field_module = self::$module->get_field( $type );

							if ( ! $field_module ) {
								continue;
							}

							$field_module->render_controls();
							self::$field_module[ $type ] = $field_module;
						}

						$controls = self::$field_module[ $type ]->get_settings();

						$sanitize_data = new Sanitize_Data( $attributes, $controls );
						$data          = $sanitize_data->get_data();

						self::$filtered_data[ $field['_id'] ]['attributes'] = $data;
					}
				}

				if ( self::$filtered_data && isset( self::$filtered_data[ $field['_id'] ] ) && ! isset( self::$filtered_data[ $field['_id'] ]['attributes'] ) ) {
					self::$filtered_data[ $field['_id'] ]['attributes'] = array();
				}
			}
		}

		private function set_field_module( $type ): void {
			if ( ! isset( self::$field_module[ $type ] ) ) {
				$field_module = self::$module->get_field( $type );

				if ( ! $field_module ) {
					return;
				}

				$field_module->render_controls();
				self::$field_module[ $type ] = $field_module;
			}
		}

		/**
		 * Get the filtered data.
		 *
		 * @return array|false Array of data or false if no data.
		 */
		public function get_data() {
			return ( is_array( self::$filtered_data ) && count( self::$filtered_data ) > 0 )
				? self::$filtered_data
				: false;
		}

		public function get_root_containers(): array {
			return ( is_array( self::$root_containers ) && count( self::$root_containers ) > 0 )
				? self::$root_containers
				: array();
		}

		public function __destruct() {
			self::$filtered_data   = array();
			self::$form_fields     = null;
			self::$root_containers = array();
			self::$field_module    = null;
			self::$module          = null;
		}
	}
}
