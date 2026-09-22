<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Dragwyb_Pages;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Admin\Form_Overview\Form_Bulk_Actions_Handler;

class Dragwyb_Post {

	/**
	 * Post type name
	 */
	const POST_TYPE = DRAGWYB_PREFIX . '-forms';

	public static function post_type() {
		return self::POST_TYPE;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_post_type' ), 1 );

		add_action( 'init', array( $this, 'dragwyb_add_caps_to_admin' ) );

		add_action( 'admin_init', array( $this, 'dragwyb_bulk_actions_handler' ) );

		add_action( 'admin_notices', array( $this, 'bulk_action_notices' ) );

		// Register post type for Polylang custom post types settings
		add_filter( 'pll_get_post_types', array( $this, 'register_polylang_post_type' ), 99, 2 );

		// Register post type for WPML custom post types settings
		add_filter( 'wpml_get_translatable_types', array( $this, 'register_wpml_translatable_types' ) );
		add_filter( 'wpml_translatable_element_types', array( $this, 'register_wpml_translatable_types' ) );
		add_filter( 'wpml_get_post_types_for_translation', array( $this, 'register_wpml_post_types_for_translation' ) );
		add_filter( 'wpml_post_types_for_translation_table', array( $this, 'register_wpml_post_types_for_translation' ) );
	}

	/**
	 * Register the custom post type
	 */
	public function register_post_type(): void {
		$labels = array(
			'name'               => __( 'Smart Forms', 'smart-form-builder-by-dragwyb' ),
			'singular_name'      => __( 'Smart Form', 'smart-form-builder-by-dragwyb' ),
			'menu_name'          => __( 'Smart Forms', 'smart-form-builder-by-dragwyb' ),
			'name_admin_bar'     => __( 'Smart Form', 'smart-form-builder-by-dragwyb' ),
			'all_items'          => __( 'All Forms', 'smart-form-builder-by-dragwyb' ),
			'add_new'            => __( 'Add New', 'smart-form-builder-by-dragwyb' ),
			'add_new_item'       => __( 'Add New Form', 'smart-form-builder-by-dragwyb' ),
			'edit_item'          => __( 'Edit Form', 'smart-form-builder-by-dragwyb' ),
			'new_item'           => __( 'New Form', 'smart-form-builder-by-dragwyb' ),
			'view_item'          => __( 'View Form', 'smart-form-builder-by-dragwyb' ),
			'search_items'       => __( 'Search Forms', 'smart-form-builder-by-dragwyb' ),
			'not_found'          => __( 'No forms found', 'smart-form-builder-by-dragwyb' ),
			'not_found_in_trash' => __( 'No forms found in Trash', 'smart-form-builder-by-dragwyb' ),
		);

		$args = array(
			'label'               => __( 'Smart Forms', 'smart-form-builder-by-dragwyb' ),
			'labels'              => $labels,
			'public'              => false,
			'exclude_from_search' => true,
			'show_ui'             => false,
			'show_in_admin_bar'   => false,
			'rewrite'             => false,
			'query_var'           => false,
			'can_export'          => false,
			'supports'            => array( 'title', 'author', 'revisions' ),
			'capability_type'     => array( sanitize_text_field( DRAGWYB_PREFIX ) . '_forms', sanitize_text_field( DRAGWYB_PREFIX ) . '_form' ), // Not using 'capability_type' anywhere. It just has to be custom for security reasons.
			'capabilities'        => $this->capabilties(),
			'map_meta_cap'        => true,
		);

		register_post_type( self::POST_TYPE, $args );
	}

	public function capabilties() {
		$caps = array(
			'delete_post'            => 'delete_' . sanitize_text_field( DRAGWYB_PREFIX ) . '_form',
			'delete_posts'           => 'delete_' . sanitize_text_field( DRAGWYB_PREFIX ) . '_forms',
			'delete_published_posts' => 'delete_published_' . sanitize_text_field( DRAGWYB_PREFIX ) . '_forms',
		);

		$caps = apply_filters( 'Dragwyb/Forms/Capablitlies', $caps );

		return $caps;
	}

	public function dragwyb_add_caps_to_admin() {
		$role = get_role( 'administrator' );
		if ( ! $role ) {
			return;
		}

		$caps = $this->capabilties();

		if ( is_array( $caps ) && count( $caps ) > 0 ) {

			$caps = array_unique( array_values( $caps ) );

			foreach ( $caps as $cap ) {
				$role->add_cap( $cap );
			}
		}
	}

	/**
	 * Set custom columns for the forms list
	 */
	public function set_custom_columns( $columns ): array {
		$new_columns = array(
			'cb'        => $columns['cb'],
			'title'     => __( 'Form Name', 'smart-form-builder-by-dragwyb' ),
			'type'      => __( 'Form Type', 'smart-form-builder-by-dragwyb' ),
			'shortcode' => __( 'Shortcode', 'smart-form-builder-by-dragwyb' ),
			'entries'   => __( 'Entries', 'smart-form-builder-by-dragwyb' ),
			'date'      => $columns['date'],
		);
		return $new_columns;
	}

	/**
	 * Render custom column content
	 */
	public function render_custom_columns( $column, $post_id ): void {
		switch ( $column ) {
			case 'type':
				$form_type = get_post_meta( $post_id, '_Dragwyb_Page_type', true );
				echo esc_html( ucfirst( $form_type ?: 'Standard' ) );
				break;

			case 'shortcode':
				echo '<input type="text" readonly class="regular-text code" value="[Dragwyb_Page id=&quot;' . esc_attr( $post_id ) . '&quot;]" onclick="this.select()">';
				break;

			case 'entries':
				$entries_count = $this->get_form_entries_count( $post_id );
				echo esc_html( $entries_count );
				break;
		}
	}

	/**
	 * Get form entries count
	 */
	private function get_form_entries_count( $form_id ): int {
		// This will be implemented when we add form submissions functionality
		return 0;
	}

	/**
	 * Redirect to custom editor
	 */
	public function redirect_to_custom_editor(): void {
		global $post_type;

		if ( $post_type !== self::POST_TYPE ) {
			return;
		}

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No nonce is required for admin dashboard pages check
		$form_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

		if ( $form_id ) {
			wp_safe_redirect(
				add_query_arg(
					array(
						'page'    => DRAGWYB_PREFIX . '-form-overview',
						'form_id' => $form_id,
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}
	}

	/**
	 * Disable Gutenberg for forms
	 */
	public function disable_gutenberg( bool $use_block_editor, string $post_type ): bool {
		if ( $post_type === self::POST_TYPE ) {
			return false;
		}
		return $use_block_editor;
	}

	public function dragwyb_bulk_actions_handler() {
		if ( isset( $_GET['_wpnonce'] ) ) {
			$current_post_type = sanitize_text_field( self::POST_TYPE );
			$nonce             = isset( $_REQUEST['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ) : '';
			if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, 'bulk-' . $current_post_type ) ) {

				if ( function_exists( 'wp_get_referer' ) ) {
					$referal_url = wp_get_referer();

					if ( strpos( $referal_url, 'page=dragwyb-form-overview' ) !== false ) {
						Form_Bulk_Actions_Handler::instance( $current_post_type );
					}
				}
			}
		}
	}

	public function bulk_action_notices() {

		if ( ! function_exists( 'get_current_screen' ) || ! property_exists( get_current_screen(), 'id' ) || strpos( get_current_screen()->id, 'dragwyb-form-overview' ) === false ) {
			return;
		}

		if ( ! empty( $_GET['trashed'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bulk-trash-' . sanitize_text_field( self::POST_TYPE ) ) ) {
			$count = absint( $_GET['trashed'] );
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				// translators: %s is the number of forms moved to the trash
				sprintf( esc_html__( '%s form moved to the Trash.', 'smart-form-builder-by-dragwyb' ), absint( $count ) )
			);
		}

		if ( ! empty( $_GET['deleted'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bulk-delete-' . sanitize_text_field( self::POST_TYPE ) ) ) {
			$count = absint( $_GET['deleted'] );
			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				// translators: %s is the number of forms permanently deleted
				sprintf( esc_html__( '%s form permanently deleted.', 'smart-form-builder-by-dragwyb' ), absint( $count ) )
			);
		}

		if ( ! empty( $_GET['untrashed'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bulk-untrash-' . sanitize_text_field( self::POST_TYPE ) ) ) {
			$count = absint( $_GET['untrashed'] );

			printf(
				'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
				// translators: %s is the number of forms restored from trash
				sprintf( esc_html__( '%s form restored from Trash.', 'smart-form-builder-by-dragwyb' ), absint( $count ) )
			);
		}
	}

	/**
	 * Register post type in Polylang translatable post types settings.
	 *
	 * When $is_settings is true, adds dragwyb-forms so it displays in Polylang's
	 * "Custom post types and Taxonomies" settings screen for users to enable/disable.
	 *
	 * @param array $post_types Array of post type names.
	 * @param bool  $is_settings True when displaying the settings page.
	 * @return array
	 */
	public function register_polylang_post_type( $post_types, $is_settings = false ) {
		if ( ! is_array( $post_types ) ) {
			$post_types = array();
		}

		// When rendering the settings page, always include so administrator can toggle it.
		if ( $is_settings ) {
			$post_types[ self::POST_TYPE ] = self::POST_TYPE;
			return $post_types;
		}

		// Runtime: If admin saved settings in Polylang, include if enabled.
		$pll_options = get_option( 'polylang' );
		if ( is_array( $pll_options ) && isset( $pll_options['post_types'] ) && is_array( $pll_options['post_types'] ) ) {
			if ( in_array( self::POST_TYPE, $pll_options['post_types'], true ) ) {
				$post_types[ self::POST_TYPE ] = self::POST_TYPE;
			}
		}

		return $post_types;
	}

	/**
	 * Register post type in WPML translatable types.
	 *
	 * @param array $types Array of types.
	 * @return array
	 */
	public function register_wpml_translatable_types( $types ) {
		if ( is_array( $types ) ) {
			$types[ self::POST_TYPE ] = self::POST_TYPE;
		}
		return $types;
	}

	/**
	 * Register post type in WPML post types for translation settings table.
	 *
	 * @param array $types Array of post type names.
	 * @return array
	 */
	public function register_wpml_post_types_for_translation( $types ) {
		if ( is_array( $types ) && ! in_array( self::POST_TYPE, $types, true ) ) {
			$types[] = self::POST_TYPE;
		}
		return $types;
	}
}
