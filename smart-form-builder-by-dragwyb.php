<?php

/**
 * Plugin Name: Smart Form Builder
 * Description: Drag and drop form builder for WordPress
 * Version: 1.0.3
 * Author: dragwyb
 * Author URI:  https://dragwyb.com/
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

final class Dragwyb_Form_Builder {

	/**
	 * Plugin version
	 */
	const VERSION = '1.0.3';

	/**
	 * Plugin instance
	 */
	private static $instance = null;

	private $styling;

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
		define( 'DRAGWYB_FORM_BUILDER_VERSION', self::VERSION );
		define( 'DRAGWYB_FORM_BUILDER_PATH', plugin_dir_path( __FILE__ ) );
		define( 'DRAGWYB_FORM_BUILDER_URL', plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Initialize WordPress hooks
	 */
	private function init_hooks(): void {
		$this->init_plugin();
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
		}

		update_option( 'dragwyb_form_builder_activation_data', gmdate( 'Y-m-d H:i:s' ) );

		self::create_submission_db();
	}
}

register_activation_hook( __FILE__, array( 'Dragwyb_Form_Builder', 'activate' ) );

// Initialize plugin
function dragwyb_form_builder() {
	return Dragwyb_Form_Builder::instance();
}

// Start the plugin
dragwyb_form_builder();
