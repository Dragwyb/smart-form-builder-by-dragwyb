<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Dynamic_Tags;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Dynamic_Tags_Manager
 *
 * Handles dynamic tags registration and values substitution on controls.
 */
class Dynamic_Tags_Manager {

	/**
	 * Stores the single instance of the class.
	 */
	private static $instance = null;

	/**
	 * Registered dynamic tags array.
	 */
	private $tags = array();

	/**
	 * Returns the single instance of the class.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor. Registers default tags and triggers registration filter.
	 */
	public function __construct() {
		$this->register_default_tags();
	}

	/**
	 * Register default out-of-the-box dynamic tags.
	 */
	private function register_default_tags(): void {
		$this->register_tag(
			'form_id',
			array(
				'label'   => __( 'Form ID', 'smart-form-builder-by-dragwyb' ),
				'replace' => function( $form_id ) {
					return $form_id;
				},
			)
		);

		$this->register_tag(
			'post_id',
			array(
				'label'   => __( 'Post ID', 'smart-form-builder-by-dragwyb' ),
				'replace' => function( $form_id ) {
					return get_the_ID() ? get_the_ID() : '';
				},
			)
		);

		$this->register_tag(
			'post_title',
			array(
				'label'   => __( 'Post Title', 'smart-form-builder-by-dragwyb' ),
				'replace' => function( $form_id ) {
					return get_the_title() ? get_the_title() : '';
				},
			)
		);

		$this->register_tag(
			'site_title',
			array(
				'label'   => __( 'Site Title', 'smart-form-builder-by-dragwyb' ),
				'replace' => function( $form_id ) {
					return get_bloginfo( 'name' );
				},
			)
		);

		// Allow developers/themes to extend dynamic tags
		$this->tags = apply_filters( 'Dragwyb/Dynamic_Tags/Register', $this->tags );
	}

	/**
	 * Register a custom dynamic tag.
	 */
	public function register_tag( string $tag, array $args ): void {
		$this->tags[ $tag ] = array_merge(
			array(
				'label'   => $tag,
				'replace' => '__return_empty_string',
			),
			$args
		);
	}

	/**
	 * Get all registered tags.
	 */
	public function get_tags(): array {
		return $this->tags;
	}

	/**
	 * Recursively replace dynamic tags in string or array values.
	 *
	 * @param mixed $value The string or array of values to scan and replace.
	 * @param int   $form_id The current Form ID.
	 * @param array $field_values Optional array of field values for replacing {field:field_id}.
	 * @return mixed The parsed values with replaced tags.
	 */
	public function replace_tags( $value, int $form_id, array $field_values = array() ) {
		if ( is_string( $value ) ) {
			// Replace standard registered tags
			foreach ( $this->tags as $tag => $data ) {
				if ( strpos( $value, '{' . $tag . '}' ) !== false ) {
					$replace_value = '';
					if ( is_callable( $data['replace'] ) ) {
						$replace_value = call_user_func( $data['replace'], $form_id );
					}
					$value = str_replace( '{' . $tag . '}', (string) $replace_value, $value );
				}
			}

			// Replace field value tags (e.g. {field:my_field_id})
			if ( preg_match_all( '/\{field:([A-Za-z0-9_]+)\}/', $value, $matches ) ) {
				foreach ( $matches[1] as $index => $field_id ) {
					$replace_value = '';
					if ( isset( $field_values[ $field_id ] ) ) {
						$replace_value = $field_values[ $field_id ];
					} elseif ( isset( $field_values[ 'field_' . $field_id ] ) ) {
						$replace_value = $field_values[ 'field_' . $field_id ];
					}

					if ( is_array( $replace_value ) ) {
						$replace_value = implode( ' ', $replace_value );
					}

					$value = str_replace( $matches[0][ $index ], (string) $replace_value, $value );
				}
			}
		} elseif ( is_array( $value ) ) {
			foreach ( $value as $key => $sub_value ) {
				$value[ $key ] = $this->replace_tags( $sub_value, $form_id, $field_values );
			}
		}

		return $value;
	}
}
