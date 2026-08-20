<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Onboarding;

use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Onboarding {

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
		add_action( 'admin_init', array( $this, 'handle_first_time_activation_redirect' ) );
	}

	/**
	 * Handle first time activation redirect to onboarding dashboard.
	 */
	public function handle_first_time_activation_redirect(): void {
		if ( get_transient( 'dragwyb_activation_redirect' ) ) {
			if ( ! isset( $_GET['activate-multi'] ) && current_user_can( 'manage_options' ) ) {
				delete_transient( 'dragwyb_activation_redirect' );
				$dargwyb_already_setuped = false;

				if ( get_option( 'dragwyb_onboarding_setup_complete' ) ) {
					$dargwyb_already_setuped = true;
				}

				if ( ! $dargwyb_already_setuped ) {
					wp_safe_redirect( admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-onboarding' ) );
					exit;
				}
			}
		}
	}

	/**
	 * Render Onboarding page root div.
	 */
	public function render_page( $screen ): void {
		if ( gettype( $screen ) === 'object' && $screen( 'onboarding' ) ) {
			echo '<div class="wrap"><div id="dragwyb-onboarding-root"></div></div>';
		}
	}

	/**
	 * Enqueue Onboarding CSS & JS assets.
	 */
	public function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, DRAGWYB_PREFIX . '-onboarding' ) === false ) {
			return;
		}

		$version = defined( 'DRAGWYB_FORM_BUILDER_VERSION' ) ? DRAGWYB_FORM_BUILDER_VERSION : '1.0.0';
		$deps    = array( 'wp-element', 'wp-i18n' );

		if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/onboarding/onboarding.asset.php' ) ) {
			$asset_info = require DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/onboarding/onboarding.asset.php';
			if ( isset( $asset_info['dependencies'] ) ) {
				$deps = array_merge( $deps, $asset_info['dependencies'] );
			}
			if ( isset( $asset_info['version'] ) ) {
				$version = $asset_info['version'];
			}
		}

		// Enqueue global editor SCSS/CSS variables & styles
		if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/css/editor-global.css' ) ) {
			wp_enqueue_style(
				'dragwyb-editor-global-style',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/editor-global.css' ),
				array(),
				esc_attr( $version )
			);
		}

		// Enqueue onboarding CSS
		if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/css/onboarding.css' ) ) {
			wp_enqueue_style(
				'dragwyb-onboarding-style',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/onboarding.css' ),
				array( 'dragwyb-editor-global-style' ),
				esc_attr( $version )
			);
		}

		// Enqueue onboarding JS
		if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/onboarding/onboarding.js' ) ) {
			wp_enqueue_script(
				'dragwyb-onboarding-script',
				esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/dist/onboarding/onboarding.js' ),
				$deps,
				esc_attr( $version ),
				true
			);
		}

		// Count published forms
		$forms_count_obj = wp_count_posts( Dragwyb_Post::POST_TYPE );
		$total_forms     = isset( $forms_count_obj->publish ) ? (int) $forms_count_obj->publish : 0;

		wp_localize_script(
			'dragwyb-onboarding-script',
			'DragwybOnboardingData',
			array(
				'ajax_url'     => esc_url( admin_url( 'admin-ajax.php' ) ),
				'adminNonce'   => wp_create_nonce( 'dragwyb_setup_complete' ),
				'editorUrl'    => esc_url( admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-form-builder' ) ),
				'dashboardUrl' => esc_url( admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-form-overview' ) ),
			)
		);
	}
}
