<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Icons;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dragwyb\Form_Builder\Admin\Settings\Settings_Manager;

class Icons_Manager {

	/**
	 * Render Icon.
	 *
	 * Used to render Icon for the frontend.
	 *
	 * @param array  $icon       Icon array containing 'icon' and 'type'.
	 * @param array  $attributes HTML attributes to add to the rendered icon.
	 * @param string $tag        HTML tag to use for the icon. Default 'i'.
	 *
	 * @return void
	 */
	public static function render_icon( array $icon, array $attributes = array(), string $tag = 'i' ): void {
		if ( empty( $icon['icon'] ) ) {
			return;
		}

		$icon_type = isset( $icon['type'] ) ? sanitize_key( $icon['type'] ) : 'solid';
		$icon_name = sanitize_html_class( $icon['icon'] );

		if ( ! isset( $attributes['aria-hidden'] ) ) {
			$attributes['aria-hidden'] = 'true';
		}

		$is_svg_enable = Settings_Manager::instance()->get_setting( 'performance', 'load_svg_icons', 'no' );

		if ( $is_svg_enable ) {
			self::render_svg_icon( $icon_type, $icon_name, $attributes );
			return;
		}

		if ( ! isset( $attributes['class'] ) ) {
			$attributes['class'] = array();
		} elseif ( ! is_array( $attributes['class'] ) ) {
			$attributes['class'] = explode( ' ', $attributes['class'] );
		}

		$prefix = self::get_icon_prefix( $icon_type );

		// Add prefix classes
		$attributes['class'][] = trim( $prefix );

		// FontAwesome prefixes the icon name with 'fa-' usually
		if ( strpos( $icon_name, 'fa-' ) !== 0 && $prefix !== 'dragwyb-icon' ) {
			$icon_class = 'fa-' . $icon_name;
		} else {
			$icon_class = $icon_name;
		}

		$attributes['class'][] = $icon_class;

		// Ensure unique and clean classes
		$attributes['class'] = array_unique( array_filter( $attributes['class'] ) );

		self::render_html_tag( $tag, $attributes );
	}

	/**
	 * Get Icon HTML.
	 *
	 * @param array  $icon       Icon array containing 'icon' and 'type'.
	 * @param array  $attributes HTML attributes to add to the rendered icon.
	 * @param string $tag        HTML tag to use for the icon. Default 'i'.
	 *
	 * @return string
	 */
	public static function get_icon_html( array $icon, array $attributes = array(), string $tag = 'i' ): string {
		ob_start();
		self::render_icon( $icon, $attributes, $tag );
		return ob_get_clean();
	}

	/**
	 * Get icon prefix based on icon type.
	 *
	 * @param string $type
	 * @return string
	 */
	private static function get_icon_prefix( string $type ): string {
		$prefixes = array(
			'solid'   => 'fas',
			'regular' => 'far',
			'brands'  => 'fab',
			'custom'  => 'dragwyb-icon',
		);

		return $prefixes[ $type ] ?? 'fas';
	}

	/**
	 * Render HTML Tag.
	 *
	 * @param string $tag
	 * @param array  $attributes
	 * @return void
	 */
	private static function render_html_tag( string $tag, array $attributes = array() ): void {
		$attributes_string = self::render_attributes( $attributes );

		// Prevent rendering invalid tags
		$tag = tag_escape( $tag );
		if ( empty( $tag ) ) {
			$tag = 'i';
		}

		$allowed_attributes = array();

		foreach ( $attributes as $attribute_key => $attribute_values ) {
			$allowed_attributes[ $attribute_key ] = array();
		}

		echo wp_kses(
			sprintf( '<%1$s %2$s></%1$s>', esc_attr( $tag ), trim( $attributes_string ) ),
			array(
				$tag => $allowed_attributes,
			)
		);
	}

	/**
	 * Render SVG Icon.
	 *
	 * @param string $icon_type Icon type (solid, regular, brands, custom).
	 * @param string $icon_name Icon name.
	 * @param array  $attributes HTML attributes to add to the rendered icon.
	 *
	 * @return void
	 */
	private static function render_svg_icon( string $icon_type, string $icon_name, array $attributes = array() ): void {
		if ( ! isset( $icon_type ) || ! isset( $icon_name ) || empty( $icon_type ) || empty( $icon_name ) ) {
			return;
		}

		$valid_icon_types = Icons_Helper::get_icon_groups();

		if ( ! in_array( $icon_type, $valid_icon_types ) ) {
			return;
		}

		$svg_file_path = DRAGWYB_FORM_BUILDER_PATH . 'includes/controls/icons/json/' . sanitize_file_name( $icon_type ) . '.php';

		if ( ! file_exists( $svg_file_path ) ) {
			return;
		}

		$dragwyb_svg_icons = require $svg_file_path;

		if ( ! isset( $dragwyb_svg_icons[ $icon_name ] ) || empty( $dragwyb_svg_icons[ $icon_name ] ) ) {
			return;
		}

		$dragwyb_svg_icon_data = $dragwyb_svg_icons[ $icon_name ];

		// 1. Extract the required data from the array
		$dragwyb_icon_width  = $dragwyb_svg_icon_data[0];
		$dragwyb_icon_height = $dragwyb_svg_icon_data[1];
		$dragwyb_icon_path   = $dragwyb_svg_icon_data[4];

		$allowed_attributes = array();

		foreach ( $attributes as $attribute_key => $attribute_values ) {
			$allowed_attributes[ $attribute_key ] = array();
		}

		// 2. Build the SVG HTML string
		// We add fill="currentColor" so the icon inherits the text color of its parent container
		echo wp_kses(
			sprintf(
				'<svg %s xmlns="http://www.w3.org/2000/svg" viewBox="0 0 %s %s" fill="currentColor">
                    <path d="%s"></path>
                </svg>',
				self::render_attributes( $attributes ),
				esc_attr( $dragwyb_icon_width ),
				esc_attr( $dragwyb_icon_height ),
				esc_attr( $dragwyb_icon_path ) // The massive string of coordinates
			),
			array(
				'svg'  => array_merge(
					array(
						'class'   => array(),
						'xmlns'   => array(),
						'viewbox' => array(),
						'fill'    => array(),
					),
					$allowed_attributes
				),
				'path' => array(
					'd' => array(),
				),
			)
		);
	}

	/**
	 * Render Attributes.
	 *
	 * @param array $attributes
	 * @return string
	 */
	private static function render_attributes( array $attributes ): string {
		$rendered_attributes = array();

		foreach ( $attributes as $attribute_key => $attribute_values ) {
			$attribute_key = sanitize_key( $attribute_key );

			if ( is_array( $attribute_values ) ) {
				$attribute_values = implode( ' ', $attribute_values );
			}

			$rendered_attributes[] = sprintf( '%1$s="%2$s"', $attribute_key, esc_attr( (string) $attribute_values ) );
		}

		return implode( ' ', $rendered_attributes );
	}
}
