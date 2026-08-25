<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Integrations\Gutenberg;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Includes\Integrations\Integrations_Manager;
use Dragwyb\Form_Builder\Includes\Frontend\Managers\CSS_Manager;

/**
 * Class Gutenberg_Block
 *
 * Handles the registration, assets, and rendering of the Smart Form Gutenberg block.
 */
class Gutenberg_Block {

	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init(): void {
		add_action( 'init', array( $this, 'register_block' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
	}

	/**
	 * Register the Gutenberg block and its editor assets.
	 */
	public function register_block(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$asset_file = DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/gutenberg/gutenberg.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
				'version'      => DRAGWYB_FORM_BUILDER_VERSION,
			);

		wp_register_script(
			'dragwyb-gutenberg-block',
			DRAGWYB_FORM_BUILDER_URL . 'assets/dist/gutenberg/gutenberg.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);

		// Localize forms list for the block dropdown
		wp_localize_script(
			'dragwyb-gutenberg-block',
			'dragwybBlockData',
			array(
				'forms' => Integrations_Manager::get_forms_for_js(),
			)
		);

		// Register static frontend styles for block editor & frontend
		wp_register_style(
			'dragwyb-form-frontend-style',
			DRAGWYB_FORM_BUILDER_URL . 'assets/css/form-frontend.css',
			array(),
			DRAGWYB_FORM_BUILDER_VERSION
		);

		wp_register_style(
			'dragwyb-flatpickr-style',
			DRAGWYB_FORM_BUILDER_URL . 'assets/lib/flatpickr/css/flatpickr.min.css',
			array(),
			DRAGWYB_FORM_BUILDER_VERSION
		);

		register_block_type(
			'dragwyb/form',
			array(
				'api_version'     => 2,
				'editor_script'   => 'dragwyb-gutenberg-block',
				'editor_style'    => array( 'dragwyb-form-frontend-style', 'dragwyb-flatpickr-style' ),
				'style'           => 'dragwyb-form-frontend-style',
				'render_callback' => array( $this, 'render_block' ),
				'attributes'      => array(
					'formId' => array(
						'type'    => 'string',
						'default' => '',
					),
				),
			)
		);
	}

	/**
	 * Enqueue editor assets on enqueue_block_editor_assets hook.
	 */
	public function enqueue_editor_assets(): void {
		wp_enqueue_style( 'dragwyb-form-frontend-style' );
		wp_enqueue_style( 'dragwyb-flatpickr-style' );
	}

	/**
	 * Render callback for the dynamic Gutenberg block.
	 *
	 * @param array<string, mixed> $attributes Block attributes.
	 * @return string Rendered HTML.
	 */
	public function render_block( array $attributes ): string {
		$form_id = isset( $attributes['formId'] ) ? absint( $attributes['formId'] ) : 0;

		if ( empty( $form_id ) ) {
			return '';
		}

		$rendered_form = do_shortcode( '[dragwyb-form id="' . $form_id . '"]' );

		// In block editor preview (REST request / admin), inject dynamic form CSS in a <style> tag.
		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_admin() ) {
			$css_manager = CSS_Manager::instance();
			$form_css    = $css_manager->get_form_css( $form_id );

			if ( ! empty( $form_css ) ) {
				$form_css_escaped = wp_strip_all_tags( $form_css );
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic CSS generated and sanitized by CSS_Manager for editor preview.
				$rendered_form = '<style id="dragwyb-form-preview-style-' . esc_attr( (string) $form_id ) . '">' . $form_css_escaped . '</style>' . $rendered_form;
			}
		}

		return $rendered_form;
	}
}
