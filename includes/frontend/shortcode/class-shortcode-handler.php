<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Frontend\Shortcode;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;
use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;
use Dragwyb\Form_Builder\Includes\Frontend\Managers\CSS_Manager;

class Shortcode_Handler {


	private static ?self $instance = null;

	private static $frontend_render = null;

	/**
	 * Whether the frontend static assets have been enqueued.
	 */
	private static $static_assets_enqueued = false;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_shortcode( 'dragwyb-form', array( $this, 'render_shortcode' ) );
	}

	/**
	 * Handles the [dragwyb_form id="123"] shortcode.
	 */
	public function render_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts
		);

		$form_id = absint( $atts['id'] );
		if ( ! $form_id || get_post_type( $form_id ) !== Dragwyb_Post::POST_TYPE ) {
			return '<p>' . esc_html__( 'Form not found or invalid.', 'smart-form-builder-by-dragwyb' ) . '</p>';
		}

		// Load form data from post meta
		$form_settings = get_post_meta( $form_id, '_dragwyb_form_data', true );

		if ( empty( $form_settings ) || ! is_array( $form_settings ) || ! isset( $form_settings['fields'] ) || count( $form_settings ) < 1 ) {
			return '<p>' . esc_html__( 'No fields found in this form.', 'smart-form-builder-by-dragwyb' ) . '</p>';
		}

		self::$frontend_render = Frontend_Render::instance();
		self::$frontend_render->init( $form_id );

		if ( ! self::$static_assets_enqueued ) {
			self::$static_assets_enqueued = true;
			self::$frontend_render::enqueue_static_assets();
		}

		self::$frontend_render::localize_form_data();

		$css_manager = CSS_Manager::instance();

		$css_manager->enqueue_form_styles( $form_id, self::$frontend_render );

		$toolbar_values = self::$frontend_render->get_toolbars_values( 'style' );
		$label_position = isset( $toolbar_values['label_position'] ) ? $toolbar_values['label_position'] : 'top';
		$form_bg_type   = isset( $toolbar_values['form_container_bg_background'] ) ? $toolbar_values['form_container_bg_background'] : 'color';

		$class = 'dragwyb-form-wrapper';

		if ( isset( $label_position ) && ! empty( $label_position ) ) {
			$class .= ' dragwyb-layout-' . esc_attr( $label_position );

			if ( isset( $form_bg_type ) && ! empty( $form_bg_type ) ) {
				$class .= ' dragwyb-bg-' . esc_attr( $form_bg_type );
			}

			if ( $label_position === 'floating' ) {
				$floating_style = isset( $toolbar_values['floating_style'] ) ? $toolbar_values['floating_style'] : 'outlined';

				$class .= ' dragwyb-float-' . esc_attr( $floating_style );
			}
		}

		return '<div class="' . esc_attr( $class ) . '" id="dragwyb-form-wrapper-' . esc_attr( $form_id ) . '">' . self::$frontend_render->render() . '</div>';
	}

	private function allowed_html_for_form(): array {
		$allowed_html = wp_kses_allowed_html( 'post' ); // includes basic tags like <a>, <p>, <br>, <strong>, etc.

		$form_tags = array(
			'form'     => array(
				'action'       => true,
				'method'       => true,
				'name'         => true,
				'id'           => true,
				'class'        => true,
				'enctype'      => true,
				'target'       => true,
				'novalidate'   => true,
				'autocomplete' => true,
			),
			'input'    => array(
				'type'         => true,
				'name'         => true,
				'value'        => true,
				'placeholder'  => true,
				'checked'      => true,
				'disabled'     => true,
				'readonly'     => true,
				'required'     => true,
				'min'          => true,
				'max'          => true,
				'step'         => true,
				'id'           => true,
				'class'        => true,
				'size'         => true,
				'autocomplete' => true,
			),
			'select'   => array(
				'name'     => true,
				'id'       => true,
				'class'    => true,
				'multiple' => true,
				'required' => true,
			),
			'option'   => array(
				'value'    => true,
				'selected' => true,
			),
			'textarea' => array(
				'name'        => true,
				'id'          => true,
				'class'       => true,
				'placeholder' => true,
				'rows'        => true,
				'cols'        => true,
				'maxlength'   => true,
				'required'    => true,
				'readonly'    => true,
			),
			'button'   => array(
				'type'  => true,
				'name'  => true,
				'value' => true,
				'id'    => true,
				'class' => true,
			),
			'label'    => array(
				'for'   => true,
				'class' => true,
			),
			'fieldset' => array(
				'id'       => true,
				'class'    => true,
				'disabled' => true,
			),
			'legend'   => array(
				'class' => true,
			),
			'datalist' => array(
				'id' => true,
			),
		);

		$form_tags = array_merge_recursive( $allowed_html, $form_tags );

		$form_tags = apply_filters( 'Dragwyb/Frontend/Render/Allowed_Tags', $form_tags );

		// Merge with wp_kses_post default allowed tags
		return $form_tags;
	}
}
