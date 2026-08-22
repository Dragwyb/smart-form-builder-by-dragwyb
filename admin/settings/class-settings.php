<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Settings;

use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Settings {

	private static ?self $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function __construct() {
		add_action( 'Dragwyb_Menu_Page', array( $this, 'render_page' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
	}

	public function render_page( $screen ): void {
		if ( gettype( $screen ) === 'object' && $screen( 'settings' ) ) {
			$logo_url = DRAGWYB_FORM_BUILDER_URL . 'assets/img/menu-logo.svg';
			?>
			<!-- Header Row Card -->
			<div class="dragwyb-dashboard-header">
				<div class="dragwyb-db-brand">
					<div class="dragwyb-db-logo">
						<img src="<?php echo esc_url( $logo_url ); ?>" alt="Smart Form Builder Logo" />
					</div>
					<div class="dragwyb-header-title-meta">
						<h1 class="dragwyb-db-brand-name"><?php esc_html_e( 'Settings', 'smart-form-builder-by-dragwyb' ); ?></h1>
						<p class="dragwyb-db-sub-title"><?php esc_html_e( 'Manage all settings and preferences for your forms.', 'smart-form-builder-by-dragwyb' ); ?></p>
					</div>
				</div>
				<div class="dragwyb-db-header-actions">
					<button class="dragwyb-btn-primary-add">
						<svg viewBox="0 0 640 640" fill="currentColor"><path d="M160 96C124.7 96 96 124.7 96 160L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 237.3C544 220.3 537.3 204 525.3 192L448 114.7C436 102.7 419.7 96 402.7 96L160 96zM192 192C192 174.3 206.3 160 224 160L384 160C401.7 160 416 174.3 416 192L416 256C416 273.7 401.7 288 384 288L224 288C206.3 288 192 273.7 192 256L192 192zM320 352C355.3 352 384 380.7 384 416C384 451.3 355.3 480 320 480C284.7 480 256 451.3 256 416C256 380.7 284.7 352 320 352z"/></svg>
						<span><?php esc_html_e( 'Save Settings', 'smart-form-builder-by-dragwyb' ); ?></span>
					</button>
				</div>
			</div>
			<?php
			echo '<div id="dragwyb-settings-root"></div>';
		}
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

		if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/css/dashboard.css' ) ) {
			wp_enqueue_style(
				'dragwyb-dashboard-style',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/dashboard.css' ),
				array(),
				esc_attr( $js_assets_info['version'] )
			);
		}

		if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/css/settings.css' ) ) {
			wp_enqueue_style(
				'dragwyb-settings-style',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/settings.css' ),
				array( 'dragwyb-dashboard-style' ),
				esc_attr( $js_assets_info['version'] )
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : '';

		// Get actual form count
		$forms_count_obj = wp_count_posts( sanitize_key( Dragwyb_Post::POST_TYPE ) );
		$total_forms     = isset( $forms_count_obj->publish ) ? (int) $forms_count_obj->publish : 0;

		$extra_plugins = array(
			array(
				'name'        => 'AI Chatbot & Floating widget',
				'description' => 'Add AI Chatbot & Floating chat widgets to your website.',
				'url'         => admin_url( 'plugin-install.php?tab=plugin-information&plugin=dragwyb-click-to-chat' ),
				'icon'        => 'chatbot-ai.png',
			),
		);

		// Localize data for React
		wp_localize_script(
			'dragwyb-settings-script',
			'DragwybSettingsData',
			array(
				'restUrl'        => esc_url_raw( rest_url( 'dragwyb/v1/settings' ) ),
				'nonce'          => wp_create_nonce( 'wp_rest' ),
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'adminNonce'     => wp_create_nonce( 'dragwyb_admin_nonce' ),
				'pluginSlug'     => DRAGWYB_TEXT_DOMAIN,
				'version'        => DRAGWYB_FORM_BUILDER_VERSION,
				'puginUrl'       => esc_url( DRAGWYB_FORM_BUILDER_URL ),
				'currentTab'     => $current_tab,
				'extraPlugins'   => $extra_plugins,
				'freeSupportUrl' => esc_url( 'https://wordpress.org/support/plugin/smart-form-builder-by-dragwyb/' ),
				'supportUrl'     => esc_url( 'https://dragwyb.com/contact/?utm_source=settings&utm_medium=contact&utm_campaign=form-builder' ),
				'morePluginsUrl' => esc_url( 'https://dragwyb.com/products/?utm_source=settings&utm_medium=plugin&utm_campaign=form-builder' ),
				'totalForms'     => $total_forms > 0 ? number_format( $total_forms ) : '0',
			)
		);
	}
}
