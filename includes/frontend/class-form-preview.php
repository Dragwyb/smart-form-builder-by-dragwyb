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
		add_action( 'admin_bar_menu', array( $this, 'add_editor_button_to_admin_bar' ), 999 );
	}

	public function init() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		if ( ! Helper::is_preview_mode() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is not required here nonce already verified in Helper::is_preview_mode() check.
		$form_id = isset( $_GET['p'] ) ? absint( $_GET['p'] ) : 0;

		if ( ! current_user_can( 'edit_post', $form_id ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( function_exists( 'status_header' ) ) {
			status_header( 200 );
		}

		if ( Helper::is_editor_preview_mode() ) {

			add_filter( 'show_admin_bar', '__return_false' );

			// Filter actions to keep only theme, core, and Dragwyb hooks, preventing third-party plugin conflicts.
			$this->filter_theme_hook_callbacks(
				'wp_head',
				array(
					'wp_enqueue_scripts',
					'wp_print_styles',
					'wp_print_head_scripts',
					'wp_site_icon',
					'wp_custom_css_cb',
					'wp_print_font_faces',
				)
			);
			$this->filter_theme_hook_callbacks(
				'wp_enqueue_scripts',
				array(
					'wp_enqueue_global_styles',
					'wp_common_block_scripts_and_styles',
					'wp_enqueue_block_template_skip_link',
				)
			);

			if ( function_exists( 'wp_enqueue_classic_theme_styles' ) ) {
				$this->filter_theme_hook_callbacks(
					'wp_enqueue_scripts',
					array(
						'wp_enqueue_classic_theme_styles',
					)
				);
			}

			$this->filter_theme_hook_callbacks(
				'wp_footer',
				array(
					'wp_print_footer_scripts',
					'wp_auth_check_html',
				)
			);

			// Handle core `wp_head` hooks
			if ( ! has_action( 'wp_head', 'wp_enqueue_scripts' ) ) {
				add_action( 'wp_head', 'wp_enqueue_scripts', 1 );
			}
			if ( ! has_action( 'wp_head', 'wp_print_styles' ) ) {
				add_action( 'wp_head', 'wp_print_styles', 8 );
			}
			if ( ! has_action( 'wp_head', 'wp_print_head_scripts' ) ) {
				add_action( 'wp_head', 'wp_print_head_scripts', 9 );
			}
			if ( ! has_action( 'wp_head', 'wp_site_icon' ) ) {
				add_action( 'wp_head', 'wp_site_icon' );
			}
			if ( function_exists( 'wp_custom_css_cb' ) && ! has_action( 'wp_head', 'wp_custom_css_cb' ) ) {
				add_action( 'wp_head', 'wp_custom_css_cb', 101 );
			}
			if ( function_exists( 'wp_print_font_faces' ) && ! has_action( 'wp_head', 'wp_print_font_faces' ) ) {
				add_action( 'wp_head', 'wp_print_font_faces', 50 );
			}

			// Handle core `wp_footer` hooks
			if ( ! has_action( 'wp_footer', 'wp_print_footer_scripts' ) ) {
				add_action( 'wp_footer', 'wp_print_footer_scripts', 20 );
			}
			if ( ! has_action( 'wp_footer', 'wp_auth_check_html' ) ) {
				add_action( 'wp_footer', 'wp_auth_check_html', 30 );
			}

			// Also remove all scripts hooked into after_wp_tiny_mce.
			remove_all_actions( 'after_wp_tiny_mce' );

			// Theme assets & editor preview assets
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_theme_assets' ), 10 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_editor_preview_scripts' ), 999999 );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_editor_preview_styles' ), 999999 );

			// Setup default heartbeat options
			add_filter(
				'heartbeat_settings',
				function ( $settings ) {
					$settings['interval'] = 15;
					return $settings;
				}
			);

			do_action( 'Dragwyb/Editor/Preview/Init' );
			$frontend_render      = Frontend_Render::instance();
			self::$form_id        = $form_id;
			self::$is_iframe_mode = true;
			$frontend_render->init( self::$form_id );
			$frontend_render->enqueue_static_assets();
			$frontend_render->get_toolbar_data( 'fields' );
			?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
			<?php wp_head(); ?>
</head>
<body <?php body_class( 'dragwyb-editor-preview-iframe-body' ); ?>>
			<?php wp_footer(); ?>
</body>
</html>
			<?php
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

	/**
	 * Add Smart Form button to admin bar on form preview page.
	 *
	 * @param \WP_Admin_Bar $wp_admin_bar WordPress Admin Bar object.
	 */
	public function add_editor_button_to_admin_bar( $wp_admin_bar ) {
		if ( ! Helper::is_preview_mode() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is done in Helper::is_preview_mode().
		$form_id = isset( $_GET['p'] ) ? absint( $_GET['p'] ) : 0;

		if ( ! $form_id ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $form_id ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$editor_url = admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-form-builder&form_id=' . $form_id );
		$logo_url   = DRAGWYB_FORM_BUILDER_URL . 'assets/img/menu-logo.svg';
		$logo_html  = '<img src="' . esc_url( $logo_url ) . '" style="width: 16px; height: 16px; vertical-align: middle; margin-right: 6px; margin-top: -2px;" />';
		$title      = $logo_html . __( 'Edit Form', 'smart-form-builder-by-dragwyb' );

		$wp_admin_bar->add_node(
			array(
				'id'    => 'dragwyb-edit-form',
				'title' => wp_kses(
					$title,
					array(
						'img' => array(
							'src'   => array(),
							'style' => array(),
						),
					)
				),
				'href'  => esc_url( $editor_url ),
				'meta'  => array(
					'class' => 'dragwyb-admin-bar-btn',
				),
			)
		);
	}

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

	/**
	 * Enqueue active theme stylesheet, block global styles, and theme editor assets.
	 */
	public function enqueue_theme_assets(): void {
		// 1. Enqueue active theme stylesheet.
		$stylesheet_uri = get_stylesheet_uri();
		if ( ! empty( $stylesheet_uri ) ) {
			$theme         = wp_get_theme();
			$theme_version = $theme->get( 'Version' ) ?: DRAGWYB_FORM_BUILDER_VERSION;
			wp_enqueue_style( 'dragwyb-theme-style', $stylesheet_uri, array(), $theme_version );
		}

		// 2. Enqueue parent theme stylesheet if child theme is active.
		if ( is_child_theme() ) {
			$parent_stylesheet_uri = get_template_directory_uri() . '/style.css';
			if ( ! empty( $parent_stylesheet_uri ) && $parent_stylesheet_uri !== $stylesheet_uri ) {
				$parent_theme   = wp_get_theme()->parent();
				$parent_version = $parent_theme ? $parent_theme->get( 'Version' ) : DRAGWYB_FORM_BUILDER_VERSION;
				wp_enqueue_style( 'dragwyb-parent-theme-style', $parent_stylesheet_uri, array(), $parent_version );
			}
		}

		// 3. Enqueue block theme global styles and block library styles.
		if ( function_exists( 'wp_enqueue_global_styles' ) ) {
			wp_enqueue_global_styles();
		}
		if ( function_exists( 'wp_enqueue_classic_theme_styles' ) ) {
			wp_enqueue_classic_theme_styles();
		}
		if ( function_exists( 'wp_common_block_scripts_and_styles' ) ) {
			wp_common_block_scripts_and_styles();
		}

		// 4. Enqueue theme editor styles if supported.
		if ( current_theme_supports( 'editor-styles' ) && function_exists( 'get_editor_stylesheets' ) ) {
			$editor_stylesheets = get_editor_stylesheets();
			if ( ! empty( $editor_stylesheets ) && is_array( $editor_stylesheets ) ) {
				foreach ( $editor_stylesheets as $index => $style_url ) {
					wp_enqueue_style( 'dragwyb-theme-editor-style-' . $index, $style_url, array(), wp_get_theme()->get( 'Version' ) );
				}
			}
		}

		// 5. Customizer Additional CSS.
		if ( function_exists( 'wp_get_custom_css' ) ) {
			$custom_css = wp_get_custom_css();
			if ( ! empty( $custom_css ) ) {
				wp_add_inline_style( 'dragwyb-editor-preview', $custom_css );
			}
		}

		do_action( 'Dragwyb/Editor/Preview/Enqueue_Theme_Assets' );
	}

	/**
	 * Filter hook callbacks to keep only theme, core, and Dragwyb callbacks,
	 * preventing third-party plugins from injecting conflicting scripts.
	 *
	 * @param string   $hook_name               The action hook name (e.g., 'wp_enqueue_scripts', 'wp_head').
	 * @param string[] $allowed_core_callbacks  Optional list of core string callbacks to explicitly keep.
	 */
	private function filter_theme_hook_callbacks( string $hook_name, array $allowed_core_callbacks = array() ): void {
		global $wp_filter;

		if ( ! isset( $wp_filter[ $hook_name ] ) || ! $wp_filter[ $hook_name ] instanceof \WP_Hook ) {
			return;
		}

		$theme_dir    = wp_normalize_path( get_stylesheet_directory() );
		$template_dir = wp_normalize_path( get_template_directory() );
		$wp_includes  = wp_normalize_path( ABSPATH . WPINC );
		$plugin_dir   = wp_normalize_path( DRAGWYB_FORM_BUILDER_PATH );

		foreach ( $wp_filter[ $hook_name ]->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $idx => $item ) {
				$callback = $item['function'] ?? null;
				if ( ! $callback ) {
					continue;
				}

				// Check explicit allowlist for core functions.
				if ( is_string( $callback ) && in_array( $callback, $allowed_core_callbacks, true ) ) {
					continue;
				}

				$file = $this->get_callback_filepath( $callback );

				if ( ! $file ) {
					// Drop unknown/unresolvable callback.
					unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $idx ] );
					continue;
				}

				$norm_file = wp_normalize_path( $file );

				$is_allowed = ( 0 === strpos( $norm_file, $theme_dir ) )
					|| ( 0 === strpos( $norm_file, $template_dir ) )
					|| ( 0 === strpos( $norm_file, $wp_includes ) )
					|| ( 0 === strpos( $norm_file, $plugin_dir ) );

				if ( ! $is_allowed ) {
					unset( $wp_filter[ $hook_name ]->callbacks[ $priority ][ $idx ] );
				}
			}
		}
	}

	/**
	 * Get the file path where a callback function or method is defined.
	 *
	 * @param mixed $callback The callback to inspect.
	 * @return string|null The file path, or null if reflection fails.
	 */
	private function get_callback_filepath( $callback ): ?string {
		try {
			if ( is_string( $callback ) ) {
				if ( function_exists( $callback ) ) {
					$ref = new \ReflectionFunction( $callback );
					return $ref->getFileName() ?: null;
				}
			} elseif ( is_array( $callback ) && isset( $callback[0], $callback[1] ) ) {
				if ( is_object( $callback[0] ) || class_exists( $callback[0] ) ) {
					$ref = new \ReflectionMethod( $callback[0], $callback[1] );
					return $ref->getFileName() ?: null;
				}
			} elseif ( $callback instanceof \Closure ) {
				$ref = new \ReflectionFunction( $callback );
				return $ref->getFileName() ?: null;
			}
		} catch ( \Throwable $e ) {
			return null;
		}

		return null;
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
