<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Frontend;

use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;
use Dragwyb\Form_Builder\Includes\Helper\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Form_Preview {

	private static $instance = null;

	private static $form_id = 0;

	private static $is_iframe_mode = false;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'template_redirect', array( $this, 'init' ) );
		add_action( 'Dragwyb/Editor/Preview/Init', array( $this, 'init_iframe' ) );
	}

	public function init() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! Helper::is_preview_mode() ) {
			return;
		}

		if ( function_exists( 'status_header' ) ) {
			status_header( 200 );
		}

		$form_id = isset( $_GET['p'] ) ? absint( $_GET['p'] ) : 0;

		if ( Helper::is_editor_preview_mode() ) {

			add_filter( 'show_admin_bar', '__return_false' );

			// Remove all WordPress actions
			remove_all_actions( 'wp_head' );
			remove_all_actions( 'wp_print_styles' );
			remove_all_actions( 'wp_print_head_scripts' );
			remove_all_actions( 'wp_footer' );

			// Handle `wp_head`
			add_action( 'wp_head', 'wp_enqueue_scripts', 1 );
			add_action( 'wp_head', 'wp_print_styles', 8 );
			add_action( 'wp_head', 'wp_print_head_scripts', 9 );
			add_action( 'wp_head', 'wp_site_icon' );

			// Handle `wp_footer`
			add_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
			add_action( 'wp_footer', 'wp_auth_check_html', 30 );

			// Handle `wp_enqueue_scripts`
			remove_all_actions( 'wp_enqueue_scripts' );

			// Also remove all scripts hooked into after_wp_tiny_mce.
			remove_all_actions( 'after_wp_tiny_mce' );

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_editor_preview_scripts' ), 999999 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_editor_preview_styles' ), 999999 );

			// Setup default heartbeat options
			// add_filter(
			// 'heartbeat_settings',
			// function ( $settings ) {
			// $settings['interval'] = 15;
			// return $settings;
			// }
			// );
			do_action( 'Dragwyb/Editor/Preview/Init' );
			$frontend_render      = Frontend_Render::instance();
			self::$form_id        = $form_id;
			self::$is_iframe_mode = true;
			$frontend_render->init( self::$form_id );
			$frontend_render->enqueue_static_assets();
			$frontend_render->get_toolbar_data( 'fields' );
			wp_head();
			wp_footer();
			exit;
		}

		$post_id = $form_id;

		! defined( 'DRAGWYB_FORM_PREVIEW' ) && define( 'DRAGWYB_FORM_PREVIEW', true );

		add_filter( 'pre_get_document_title', array( $this, 'set_document_title' ), 999 );

		if ( function_exists( 'get_header' ) ) {
			get_header();
		}

		// 3. ECHO THE SHORTCODE (Crucial Step)
		echo '<div id="dragwyb-preview-wrapper" style="width: 100%;">';
		echo do_shortcode( '[dragwyb-form id="' . $post_id . '"]' );
		echo '</div>';

		if ( function_exists( 'get_footer' ) ) {
			get_footer();
		}

		remove_filter( 'pre_get_document_title', array( $this, 'set_document_title' ), 999 );

		// 4. STOP EXECUTION
		exit;
	}

	public function init_iframe() {}

	public function enqueue_editor_preview_styles() {
		$css_assets_info = array(
			'version'      => DRAGWYB_FORM_BUILDER_VERSION,
			'dependencies' => array(
				'wp-auth-check',
			),
		);

		wp_enqueue_style(
			'dragwyb-form-editor-global',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/editor-global.css' ),
			$css_assets_info['dependencies'],
			$css_assets_info['version']
		);

		wp_enqueue_style( 'dragwyb-editor-preview', esc_url( DRAGWYB_FORM_BUILDER_URL . '/assets/css/editor-preview.css' ), array( 'dragwyb-form-editor-global' ), esc_attr( DRAGWYB_FORM_BUILDER_VERSION ) );
	}

	public function enqueue_editor_preview_scripts() {
			$js_assets_info = array(
				'version'      => DRAGWYB_FORM_BUILDER_VERSION,
				'dependencies' => array(
					'dragwyb_editor_preview',
				),
			);

			$js_editor_preview_assets_info = array(
				'version'      => DRAGWYB_FORM_BUILDER_VERSION,
				'dependencies' => array(
					'dragwyb-form-core',
					'wp-auth-check',
					'heartbeat',
					'react',
					'react-dom',
				),
			);

			if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/editorFields/editorFields.asset.php' ) ) {
				$dragwyb_js_assets_info = require_once DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/editorFields/editorFields.asset.php';

				if ( isset( $dragwyb_js_assets_info['dependencies'] ) ) {
					$js_assets_info['dependencies'] = array_merge( $js_assets_info['dependencies'], $dragwyb_js_assets_info['dependencies'] );
				}

				if ( isset( $dragwyb_js_assets_info['version'] ) ) {
					$js_assets_info['version'] = $dragwyb_js_assets_info['version'];
				}
			}

			if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/editorPreview/editorPreview.asset.php' ) ) {
				$dragwyb_js_assets_info = require_once DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/editorPreview/editorPreview.asset.php';

				if ( isset( $dragwyb_js_assets_info['dependencies'] ) ) {
					$js_editor_preview_assets_info['dependencies'] = array_merge( $js_editor_preview_assets_info['dependencies'], $dragwyb_js_assets_info['dependencies'] );
				}

				if ( isset( $dragwyb_js_assets_info['version'] ) ) {
					$js_editor_preview_assets_info['version'] = $dragwyb_js_assets_info['version'];
				}
			}

			wp_register_script( 'dragwyb_editor_preview', esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editorPreview/editorPreview.js' ), $js_editor_preview_assets_info['dependencies'], esc_attr( $js_editor_preview_assets_info['version'] ), true );

			wp_register_script( 'dragwyb_editor_fields', esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editorFields/editorFields.js' ), $js_assets_info['dependencies'], esc_attr( $js_assets_info['version'] ), true );

			wp_enqueue_script( 'dragwyb_editor_preview' );
			wp_enqueue_script( 'dragwyb_editor_fields' );

			do_action( 'Dragwyb/Editor/Preview/Enqueue_Scripts' );
	}

	public function set_document_title( string $title ): string {
		$form_preview_id = isset( $_GET['preview_id'] ) ? sanitize_text_field( wp_unslash( $_GET['preview_id'] ) ) : '';
		$form_id         = isset( $_GET['p'] ) ? absint( $_GET['p'] ) : 0;

		if ( $form_preview_id && $form_id && $this->current_user_can_preview( $form_id ) && wp_verify_nonce( $form_preview_id, self::private_key_name( $form_id ) ) ) {
			return get_the_title( $form_id );
		}

		return $title;
	}

	private function current_user_can_preview( int $form_id ): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'edit_post', $form_id );
	}

	private static function private_key_name( int $form_id ): string {
		$post_type = Dragwyb_Post::POST_TYPE;
		return $post_type . '-' . $form_id;
	}

	final public static function generate_key( int $form_id ): string {
		return wp_create_nonce( self::private_key_name( $form_id ) );
	}
}
