<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Includes\Helper\Helper;

class Dragwyb_Settings {

	private static ?self $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_settings_page' ), 60 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function register_settings_page(): void {
		add_submenu_page(
			DRAGWYB_PREFIX . '-form-overview',
			__( 'Settings', 'smart-form-builder-by-dragwyb' ),
			__( 'Settings', 'smart-form-builder-by-dragwyb' ),
			'manage_options',
			DRAGWYB_PREFIX . '-settings',
			array( $this, 'render_settings_page' )
		);
	}

	public function render_settings_page(): void {
		echo '<div class="wrap"><div id="dragwyb-settings-root"></div></div>';
	}

	public function enqueue_assets( string $hook ): void {
		// Only load on our settings page
		if ( strpos( $hook, DRAGWYB_PREFIX . '-settings' ) === false ) {
			return;
		}

		$js_assets_info = array(
			'version'      => DRAGWYB_FORM_BUILDER_VERSION,
			'dependencies' => array( 'wp-element', 'wp-i18n', 'wp-api-fetch' ),
		);

		if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/settings/settings.asset.php' ) ) {
			$dragwyb_js_assets_info = require_once DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/settings/settings.asset.php';

			if ( isset( $dragwyb_js_assets_info['dependencies'] ) ) {
				$js_assets_info['dependencies'] = array_merge( $js_assets_info['dependencies'], $dragwyb_js_assets_info['dependencies'] );
			}

			if ( isset( $dragwyb_js_assets_info['version'] ) ) {
				$js_assets_info['version'] = $dragwyb_js_assets_info['version'];
			}
		}

		wp_enqueue_script(
			'dragwyb-settings-script',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/dist/settings/settings.js' ),
			$js_assets_info['dependencies'],
			esc_attr( $js_assets_info['version'] ),
			true
		);

		if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/css/editor-global.css' ) ) {
			wp_enqueue_style(
				'dragwyb-editor-global-style',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/editor-global.css' ),
				array(),
				esc_attr( $js_assets_info['version'] )
			);
		}

		if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/css/settings.css' ) ) {
			wp_enqueue_style(
				'dragwyb-settings-style',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/settings.css' ),
				array(),
				esc_attr( $js_assets_info['version'] )
			);
		}

		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';

		// Localize data for React
		wp_localize_script(
			'dragwyb-settings-script',
			'DragwybSettingsData',
			array(
				'restUrl'    => esc_url_raw( rest_url( 'dragwyb/v1/settings' ) ),
				'nonce'      => wp_create_nonce( 'wp_rest' ),
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'adminNonce' => wp_create_nonce( 'dragwyb_admin_nonce' ),
				'pluginSlug' => DRAGWYB_TEXT_DOMAIN,
				'version'    => DRAGWYB_FORM_BUILDER_VERSION,
				'currentTab' => $current_tab,
				'i18n'       => array(),
			)
		);
	}
}
