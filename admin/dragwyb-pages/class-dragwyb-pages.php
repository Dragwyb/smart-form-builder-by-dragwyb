<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Dragwyb_Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Includes\Helper\Helper;
use Dragwyb\Form_Builder\Admin\Dragwyb_Editor\Dragwyb_Builder_Editor;
use Dragwyb\Form_Builder\Admin\Form_Overview\Form_Overview;

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
		do_action( 'Dragwyb_Current_Screen', $this->current_page() );
	}

	/**
	 * Add main menu page
	 */
	public function add_main_menu_page(): void {
		add_menu_page(
			__( 'Dragwyb Form', 'smart-form-builder-by-dragwyb' ),
			__( 'Dragwyb Form', 'smart-form-builder-by-dragwyb' ),
			'manage_options',
			DRAGWYB_PREFIX . '-form-overview',
			array( $this, 'dragwyb_render_page' ),
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/img/menu-logo.svg' ),
			58
		);

		// Add submenu page for adding a form
		add_submenu_page(
			DRAGWYB_PREFIX . '-form-overview',
			__( 'Add Form', 'smart-form-builder-by-dragwyb' ),
			__( 'Add Form', 'smart-form-builder-by-dragwyb' ),
			'manage_options',
			DRAGWYB_PREFIX . '-form-builder',
			array( $this, 'dragwyb_render_page' )
		);

		// Add submenu page for entries
		add_submenu_page(
			DRAGWYB_PREFIX . '-form-overview',
			__( 'Entries', 'smart-form-builder-by-dragwyb' ),
			__( 'Entries', 'smart-form-builder-by-dragwyb' ),
			'manage_options',
			DRAGWYB_PREFIX . '-entries',
			array( $this, 'dragwyb_render_page' )
		);
	}

	/**
	 * Render main menu page
	 */
	public function dragwyb_render_page(): void {
		do_action( 'Dragwyb_Menu_Page', $this->current_page() );
	}

	private function form_admin_page(): void {
		$default_pages_names = array( 'form-overview', 'add-form', 'entries' );
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
		$default_pages = array( 'form-overview', 'entries' );

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
}
