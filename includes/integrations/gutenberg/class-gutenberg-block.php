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
		add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );
	}

	/**
	 * Register the Gutenberg block.
	 */
	public function register_block(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		$asset_file = DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/gutenberg/gutenberg.asset.php';
		$asset      = file_exists( $asset_file )
			? require $asset_file
			: array(
				'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-server-side-render' ),
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

		// Register frontend styles for Gutenberg block editor
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
		wp_enqueue_style(
			'dragwyb-form-frontend-style',
			DRAGWYB_FORM_BUILDER_URL . 'assets/css/form-frontend.css',
			array(),
			DRAGWYB_FORM_BUILDER_VERSION
		);

		wp_enqueue_style(
			'dragwyb-flatpickr-style',
			DRAGWYB_FORM_BUILDER_URL . 'assets/lib/flatpickr/css/flatpickr.min.css',
			array(),
			DRAGWYB_FORM_BUILDER_VERSION
		);
	}

	/**
	 * Enqueue frontend block assets (loads in both editor iframe and frontend).
	 */
	public function enqueue_block_assets(): void {
		if ( is_admin() ) {
			wp_enqueue_style(
				'dragwyb-form-frontend-style',
				DRAGWYB_FORM_BUILDER_URL . 'assets/css/form-frontend.css',
				array(),
				DRAGWYB_FORM_BUILDER_VERSION
			);
		}
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

		// When rendering inside Gutenberg editor preview (REST request / admin preview):
		if ( ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || is_admin() ) {
			$css_manager      = CSS_Manager::instance();
			$form_css         = $css_manager->get_form_css( $form_id );
			$frontend_css_url = DRAGWYB_FORM_BUILDER_URL . 'assets/css/form-frontend.css';
			$flatpickr_css    = DRAGWYB_FORM_BUILDER_URL . 'assets/lib/flatpickr/css/flatpickr.min.css';

			$preview_assets  = '<link rel="stylesheet" id="dragwyb-form-frontend-css" href="' . esc_url( $frontend_css_url ) . '?ver=' . esc_attr( DRAGWYB_FORM_BUILDER_VERSION ) . '" type="text/css" media="all" />';
			$preview_assets .= '<link rel="stylesheet" id="dragwyb-flatpickr-preview-css" href="' . esc_url( $flatpickr_css ) . '?ver=' . esc_attr( DRAGWYB_FORM_BUILDER_VERSION ) . '" type="text/css" media="all" />';

			if ( ! empty( $form_css ) ) {
				$preview_assets .= '<style id="dragwyb-form-preview-custom-css-' . esc_attr( (string) $form_id ) . '">' . $form_css . '</style>';
			}

			return $preview_assets . $rendered_form;
		}

		return $rendered_form;
	}
}
