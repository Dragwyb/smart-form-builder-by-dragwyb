<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Dragwyb_Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Includes\Helper\Helper;
use Dragwyb\Form_Builder\Admin\Dragwyb_Editor\Dragwyb_Builder_Editor;
use Dragwyb\Form_Builder\Admin\Form_Overview\Form_Overview;
use Dragwyb\Form_Builder\Admin\Settings\Settings_Manager;

class Dragwyb_Pages {

	/**
	 * Post type name
	 */
	const POST_TYPE = 'Dragwyb_Page';

	private static $default_pages;

	private static $allowed_pages;

	/**
	 * Constructor
	 */
	public function __construct() {
		// Overview Page
		Form_Overview::instance();

		$this->form_admin_page();
		$this->init_admin_pages();
		// Add main menu page
		add_action( 'admin_menu', array( $this, 'add_main_menu_page' ) );
		add_action( 'admin_print_scripts', array( $this, 'hide_unrelated_admin_notices' ) );
		do_action( 'Dragwyb_Current_Screen', $this->current_page() );
	}

	/**
	 * Add main menu page
	 */
	public function add_main_menu_page(): void {
		add_menu_page(
			__( 'Smart Forms', 'smart-form-builder-by-dragwyb' ),
			__( 'Smart Forms', 'smart-form-builder-by-dragwyb' ),
			'manage_options',
			DRAGWYB_PREFIX . '-dashboard',
			array( $this, 'dragwyb_render_page' ),
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/img/menu-logo.svg' ),
			58
		);

		// Add submenu page for Dashboard (Default)
		add_submenu_page(
			DRAGWYB_PREFIX . '-dashboard',
			__( 'Dashboard', 'smart-form-builder-by-dragwyb' ),
			__( 'Dashboard', 'smart-form-builder-by-dragwyb' ),
			'manage_options',
			DRAGWYB_PREFIX . '-dashboard',
			array( $this, 'dragwyb_render_page' )
		);

		// Add submenu page for Form List (All Forms)
		add_submenu_page(
			DRAGWYB_PREFIX . '-dashboard',
			__( 'Forms', 'smart-form-builder-by-dragwyb' ),
			__( 'Forms', 'smart-form-builder-by-dragwyb' ),
			'manage_options',
			DRAGWYB_PREFIX . '-form-overview',
			array( $this, 'dragwyb_render_page' )
		);

		// Add submenu page for adding a form
		add_submenu_page(
			DRAGWYB_PREFIX . '-dashboard',
			__( 'Add Form', 'smart-form-builder-by-dragwyb' ),
			__( 'Add Form', 'smart-form-builder-by-dragwyb' ),
			'manage_options',
			DRAGWYB_PREFIX . '-form-builder',
			array( $this, 'dragwyb_render_page' )
		);

		// Add submenu page for entries
		add_submenu_page(
			DRAGWYB_PREFIX . '-dashboard',
			__( 'Entries', 'smart-form-builder-by-dragwyb' ),
			__( 'Entries', 'smart-form-builder-by-dragwyb' ),
			'manage_options',
			DRAGWYB_PREFIX . '-entries',
			array( $this, 'dragwyb_render_page' )
		);

		// Add submenu page for error logs
		add_submenu_page(
			DRAGWYB_PREFIX . '-dashboard',
			__( 'Error Log', 'smart-form-builder-by-dragwyb' ),
			__( 'Error Log', 'smart-form-builder-by-dragwyb' ),
			'manage_options',
			DRAGWYB_PREFIX . '-error-log',
			array( $this, 'dragwyb_render_page' )
		);

		// Add submenu page for analytics
		add_submenu_page(
			DRAGWYB_PREFIX . '-dashboard',
			__( 'Analytics', 'smart-form-builder-by-dragwyb' ),
			__( 'Analytics', 'smart-form-builder-by-dragwyb' ),
			'manage_options',
			DRAGWYB_PREFIX . '-form-analytics',
			array( $this, 'dragwyb_render_page' )
		);

		// Add submenu page for settings
		add_submenu_page(
			DRAGWYB_PREFIX . '-dashboard',
			__( 'Settings', 'smart-form-builder-by-dragwyb' ),
			__( 'Settings', 'smart-form-builder-by-dragwyb' ),
			'manage_options',
			DRAGWYB_PREFIX . '-settings',
			array( $this, 'dragwyb_render_page' )
		);

		if ( ! get_option( 'dragwyb_onboarding_setup_complete' ) ) {
			// Add submenu page for onboarding
			add_submenu_page(
				DRAGWYB_PREFIX . '-dashboard',
				__( 'Setup', 'smart-form-builder-by-dragwyb' ),
				__( 'Setup', 'smart-form-builder-by-dragwyb' ),
				'manage_options',
				DRAGWYB_PREFIX . '-onboarding',
				array( $this, 'dragwyb_render_page' )
			);
		}
	}

	/**
	 * Render main menu page
	 */
	public function dragwyb_render_page(): void {
		do_action( 'Dragwyb_Menu_Page', $this->current_page() );
	}

	private function form_admin_page(): void {
		$default_pages_names = array( 'dashboard', 'form-overview', 'entries', 'error-log', 'form-analytics', 'settings', 'onboarding' );
		$dragwyb_name_space  = Helper::namespace_into_dir_path( __NAMESPACE__ );
		$dir                 = dirname( $dragwyb_name_space );
		$dir                 = Helper::dir_path_into_namespace( $dir );

		$default_page = array();

		foreach ( $default_pages_names as $default_pages_name ) {
			$class_name = str_replace( '-', '_', $default_pages_name );

			// Use regex to capitalize the first letter of each word after "_"
			$folder_name = preg_replace_callback(
				'/(?:^|\-)([a-z])/',
				function ( $matches ) {
					return strtoupper( $matches[0] );
				},
				$default_pages_name
			);

			// Use regex to capitalize the first letter of each word after "_"
			$class_name = preg_replace_callback(
				'/(?:^|\_)([a-z])/',
				function ( $matches ) {
					return strtoupper( $matches[0] );
				},
				$class_name
			);

			$class_name = $dir . '\\' . $class_name . '\\' . $class_name;

			$default_page[ DRAGWYB_PREFIX . '-' . $default_pages_name ] = $class_name;
		}

		self::$default_pages = $default_page;
		self::$allowed_pages = apply_filters( 'Dragwyb_Admin_Pages', array_keys( self::$default_pages ) );
	}

	public function current_page() {
		return function ( $slug ) {
			if ( in_array( $slug, self::$allowed_pages ) || in_array( DRAGWYB_PREFIX . '-' . $slug, self::$allowed_pages ) ) {
				return self::current_screen( $slug );
			}
			return false;
		};
	}

	public static function current_screen( $slug ) {
		$slug = sanitize_text_field( $slug );
		return self::current_page_name( $slug );
	}

	private static function allowed_pages() {
		$default_pages = array( 'dashboard', 'form-overview', 'entries', 'error-log', 'form-analytics', 'settings', 'onboarding' );

		$allowed_pages = apply_filters( 'Dragwyb_allowed_pages', $default_pages );

		return $allowed_pages;
	}

	private static function current_page_name( $slug ) {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No nonce is required for admin dashboard pages check
		if ( isset( $_REQUEST['page'] ) && ( in_array( $slug, self::allowed_pages() ) || in_array( DRAGWYB_PREFIX . '-' . $slug, self::allowed_pages() ) ) ) {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No nonce is required for admin dashboard pages check
			return ( $slug === sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) || DRAGWYB_PREFIX . '-' . $slug === sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) );
		}

		return false;
	}

	public function init_admin_pages() {
		foreach ( self::$default_pages as $default_class ) {
			if ( class_exists( $default_class ) ) {
				$default_class::instance();
			}
		}
	}

	/**
	 * Hide unrelated admin notices on the plugin dashboard.
	 *
	 * Keeps only this plugin's own notices on the plugin page.
	 *
	 * @return void
	 */
	public function hide_unrelated_admin_notices() {

		// nonce verification is not required here because we are not using the nonce here.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : '';

		if ( strpos( $page, DRAGWYB_PREFIX . '-' ) === false ) {
			return;
		}

		$page = str_replace( DRAGWYB_PREFIX . '-', '', $page );

		if ( ! self::current_screen( $page ) ) {
			return;
		}

		global $wp_filter;

		$notice_hooks = array(
			'user_admin_notices',
			'admin_notices',
			'all_admin_notices',
		);

		foreach ( $notice_hooks as $hook_name ) {

			if (
			empty( $wp_filter[ $hook_name ] ) ||
			empty( $wp_filter[ $hook_name ]->callbacks ) ||
			! is_array( $wp_filter[ $hook_name ]->callbacks )
			) {
				continue;
			}

			foreach ( $wp_filter[ $hook_name ]->callbacks as $priority => $callbacks ) {

				foreach ( $callbacks as $callback_id => $callback_data ) {

					if ( empty( $callback_data['function'] ) ) {
						continue;
					}

					$callback = $callback_data['function'];

					unset(
						$wp_filter[ $hook_name ]->callbacks[ $priority ][ $callback_id ]
					);
				}
			}
		}

		// Remove delayed admin notices generated by WordPress.
		if (
		isset( $wp_filter['admin_footer']->callbacks ) &&
		is_array( $wp_filter['admin_footer']->callbacks )
		) {
			foreach ( $wp_filter['admin_footer']->callbacks as $priority => $callbacks ) {

				foreach ( $callbacks as $callback_id => $callback_data ) {

					if (
					isset( $callback_data['function'] ) &&
					'render_delayed_admin_notices' === $callback_data['function']
					) {
						unset(
							$wp_filter['admin_footer']->callbacks[ $priority ][ $callback_id ]
						);
					}
				}
			}
		}

		if ( 'onboarding' === $page ) {
			return;
		}

		// Register this plugin's notices after unrelated notices have been removed.
		add_action(
			'admin_notices',
			function () use ( $page ) {
				$this->display_admin_notices( $page );
			},
			PHP_INT_MAX
		);
	}

	/**
	 * Display plugin-specific admin notices.
	 *
	 * @return void
	 */
	public function display_admin_notices( $page = '' ) {
		$this->display_tracking_disabled_notice( $page );
		do_action( 'Dragwyb_Admin_Notices' );
	}

	/**
	 * Display an info notice when visitor tracking is disabled in settings.
	 *
	 * @return void
	 */
	public function display_tracking_disabled_notice( $page = '' ): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$tracking_enabled = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'tracking_enabled', false );

		if ( true === $tracking_enabled || 'yes' === $tracking_enabled || '1' === $tracking_enabled || 1 === $tracking_enabled ) {
			return;
		}

		$settings_url = admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-settings&tab=gdpr_privacy' );
		?>
		<div class="notice dragwyb-tracking-notice">
			<div class="dragwyb-tracking-notice-inner">
				<div class="dragwyb-tracking-notice-left">
					<div class="dragwyb-tracking-notice-icon">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<circle cx="12" cy="12" r="10"></circle>
							<line x1="12" y1="16" x2="12" y2="12"></line>
							<line x1="12" y1="8" x2="12.01" y2="8"></line>
						</svg>
					</div>
					<div class="dragwyb-tracking-notice-content">
						<div class="dragwyb-tracking-notice-header">
							<span class="dragwyb-tracking-notice-title"><?php esc_html_e( 'Visitor Tracking is Disabled', 'smart-form-builder-by-dragwyb' ); ?></span>
							<span class="dragwyb-tracking-notice-badge"><?php esc_html_e( 'Disabled', 'smart-form-builder-by-dragwyb' ); ?></span>
						</div>
						<p class="dragwyb-tracking-notice-desc">
							<?php esc_html_e( 'Visitor tracking is currently turned off in Settings. Form analytics, pageviews, and traffic source attribution will not be recorded.', 'smart-form-builder-by-dragwyb' ); ?>
						</p>
					</div>
				</div>
				<?php
				if ( 'settings' !== $page ) :
					?>
				<div class="dragwyb-tracking-notice-actions">
					<a href="<?php echo esc_url( $settings_url ); ?>" class="dragwyb-tracking-notice-btn dragwyb-btn-primary-add">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
							<circle cx="12" cy="12" r="3"/>
						</svg>
						<span><?php esc_html_e( 'Configure in Settings', 'smart-form-builder-by-dragwyb' ); ?></span>
					</a>
				</div>
					<?php
				endif;
				?>
			</div>
		</div>
		<?php
	}
}

