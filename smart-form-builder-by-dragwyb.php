<?php

/**
 * Plugin Name: Dragwyb Forms – Contact Forms, Conditional Form, MultiStep Form, Analytics, SMTP
 * Description: A Contact form plugin. Create custom forms, multi-step layouts, and lead generation forms with a visual React editor, built-in SMTP, and GDPR compliance.
 * Version: 1.3.0
 * Author: dragwyb
 * Author URI:  https://dragwyb.com/?utm_source=wpplugin&utm_medium=author_uri&utm_campaign=form_builder_demo
 * Plugin URI: https://dragwyb.com/product/form/?utm_source=wpplugin&utm_medium=plugin_uri&utm_campaign=form_builder_demo
 * Text Domain: smart-form-builder-by-dragwyb
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Dragwyb_Form_Builder_Autoload;
use Dragwyb\Form_Builder\Includes\Dragwyb_Init;
use Dragwyb\Form_Builder\Admin\Db\Submission\Dragwyb_Submission_Db;
use Dragwyb\Form_Builder\Admin\Db\Error_Log\Dragwyb_Error_Log_Db;
use Dragwyb\Form_Builder\Admin\Db\Analytics\Dragwyb_Analytics_Db;
use Dragwyb\Form_Builder\Admin\Settings\Settings_Manager;

final class Dragwyb_Form_Builder {

	/**
	 * Plugin version
	 */
	const VERSION = '1.3.0';

	/**
	 * Plugin instance
	 */
	private static $instance = null;

	/**
	 * Get plugin instance
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->define_constants();

		// Load autoloader
		require_once DRAGWYB_FORM_BUILDER_PATH . 'class-dragwyb-autoload.php';
		Dragwyb_Form_Builder_Autoload::instance();

		$this->init_hooks();
	}

	/**
	 * Define plugin constants
	 */
	private function define_constants(): void {
		define( 'DRAGWYB_PREFIX', 'dragwyb' );
		define( 'DRAGWYB_TEXT_DOMAIN', 'smart-form-builder-by-dragwyb' );
		define( 'DRAGWYB_FORM_BUILDER_VERSION', self::VERSION );
		define( 'DRAGWYB_FORM_BUILDER_FILE', __FILE__ );
		define( 'DRAGWYB_FORM_BUILDER_PATH', plugin_dir_path( DRAGWYB_FORM_BUILDER_FILE ) );
		define( 'DRAGWYB_FORM_BUILDER_URL', plugin_dir_url( DRAGWYB_FORM_BUILDER_FILE ) );
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks(): void {
		self::create_submission_db();
		$this->init_plugin();
		$this->init_gdpr_cron();
	}

	/**
	 * Initialize GDPR data retention cron hooks and schedule event.
	 */
	private function init_gdpr_cron(): void {
		add_action( 'dragwyb_gdpr_data_retention_cron', array( $this, 'run_gdpr_data_retention_cleanup' ) );

		if ( ! wp_next_scheduled( 'dragwyb_gdpr_data_retention_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'dragwyb_gdpr_data_retention_cron' );
		}
	}

	/**
	 * WP Cron handler to purge tracking data older than gdpr_data_retention_days if > 0.
	 */
	public function run_gdpr_data_retention_cleanup(): void {
		$days = (int) Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'gdpr_data_retention_days', 0 );
		if ( $days > 0 ) {
			$retain_entries = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'gdpr_retain_entries', true );
			$is_retain      = filter_var( $retain_entries, FILTER_VALIDATE_BOOLEAN );
			Dragwyb_Analytics_Db::purge_old_tracking_data( $days, $is_retain );
		}
	}

	/**
	 * Check if plugin version changed, and update DB if needed.
	 */
	private static function create_submission_db(): void {
		$db_version = get_option( 'dragwyb_submission_db_version', false );
		if ( ! $db_version || $db_version !== Dragwyb_Submission_Db::VERSION ) {
			Dragwyb_Submission_Db::create_table();
			update_option( 'dragwyb_submission_db_version', Dragwyb_Submission_Db::VERSION );
		}

		$error_log_db_version = get_option( 'dragwyb_error_log_db_version', false );
		if ( ! $error_log_db_version || $error_log_db_version !== Dragwyb_Error_Log_Db::VERSION ) {
			Dragwyb_Error_Log_Db::create_table();
			update_option( 'dragwyb_error_log_db_version', Dragwyb_Error_Log_Db::VERSION );
		}

		$analytics_db_version = get_option( 'dragwyb_analytics_db_version', false );
		if ( ! $analytics_db_version || $analytics_db_version !== Dragwyb_Analytics_Db::VERSION ) {
			Dragwyb_Analytics_Db::create_tables();
			update_option( 'dragwyb_analytics_db_version', Dragwyb_Analytics_Db::VERSION );
		}
	}

	/**
	 * Initialize plugin components
	 */
	public function init_plugin(): void {

		$dragwyb = Dragwyb_Init::instance();
		$dragwyb->init();
	}

	/**
	 * Plugin activation hook
	 */
	public static function activate(): void {
		require_once plugin_dir_path( __FILE__ ) . 'class-dragwyb-autoload.php';
		Dragwyb_Form_Builder_Autoload::instance();

		if ( ! get_option( 'dragwyb_form_builder_install_data', false ) ) {
			update_option( 'dragwyb_form_builder_install_data', gmdate( 'Y-m-d H:i:s' ) );
			update_option( 'dragwyb_form_initial_version', DRAGWYB_FORM_BUILDER_VERSION );
		}

		update_option( 'dragwyb_form_builder_activation_data', gmdate( 'Y-m-d H:i:s' ) );
		update_option( 'dragwyb_form_active_version', DRAGWYB_FORM_BUILDER_VERSION );

		self::create_submission_db();

		if ( ! wp_next_scheduled( 'dragwyb_gdpr_data_retention_cron' ) ) {
			wp_schedule_event( time(), 'daily', 'dragwyb_gdpr_data_retention_cron' );
		}

		set_transient( 'dragwyb_activation_redirect', true, 600 );
	}

	/**
	 * Plugin deactivation hook
	 */
	public static function deactivate(): void {
		wp_clear_scheduled_hook( 'dragwyb_gdpr_data_retention_cron' );
	}
}

register_activation_hook( __FILE__, array( 'Dragwyb_Form_Builder', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Dragwyb_Form_Builder', 'deactivate' ) );

// Initialize plugin
function dragwyb_form_builder() {
	return Dragwyb_Form_Builder::instance();
}

// Start the plugin
dragwyb_form_builder();
