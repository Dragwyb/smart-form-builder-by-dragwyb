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

		// Hook redirect to custom editor if core post.php or post-new.php is accessed
		add_action( 'admin_init', array( $this, 'redirect_to_custom_editor' ) );

		// Dynamically ensure administrators with manage_options have all form capabilities
		add_filter( 'user_has_cap', array( $this, 'filter_user_has_cap' ) );

		// Filter Polylang new translation link so + button points to Dragwyb Form Builder
		add_filter( 'pll_get_new_post_translation_link', array( $this, 'filter_polylang_new_translation_link' ), 10, 3 );

		// Filter WPML translation link so + and edit buttons point to Dragwyb Form Builder
		add_filter( 'wpml_link_to_translation', array( $this, 'filter_wpml_link_to_translation' ), 10, 5 );

		// Filter edit post link so it points to Dragwyb Form Builder
		add_filter( 'get_edit_post_link', array( $this, 'filter_form_edit_post_link' ), 10, 2 );
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
		$prefix = sanitize_text_field( DRAGWYB_PREFIX );
		$caps   = array(
			// Meta capabilities
			'edit_post'              => "edit_{$prefix}_form",
			'read_post'              => "read_{$prefix}_form",
			'delete_post'            => "delete_{$prefix}_form",

			// Primitive capabilities used outside of map_meta_cap
			'edit_posts'             => "edit_{$prefix}_forms",
			'edit_others_posts'      => "edit_others_{$prefix}_forms",
			'publish_posts'          => "publish_{$prefix}_forms",
			'read_private_posts'     => "read_private_{$prefix}_forms",

			// Primitive capabilities used inside of map_meta_cap
			'delete_posts'           => "delete_{$prefix}_forms",
			'delete_private_posts'   => "delete_private_{$prefix}_forms",
			'delete_published_posts' => "delete_published_{$prefix}_forms",
			'delete_others_posts'    => "delete_others_{$prefix}_forms",
			'edit_private_posts'     => "edit_private_{$prefix}_forms",
			'edit_published_posts'   => "edit_published_{$prefix}_forms",
			'create_posts'           => "edit_{$prefix}_forms",
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
				if ( ! $role->has_cap( $cap ) ) {
					$role->add_cap( $cap );
				}
			}
		}
	}

	/**
	 * Dynamically grant form capabilities to users who can manage options.
	 *
	 * @param array $allcaps All capabilities of the user.
	 * @return array
	 */
	public function filter_user_has_cap( $allcaps ) {
		if ( is_array( $allcaps ) && ! empty( $allcaps['manage_options'] ) ) {
			$caps = $this->capabilties();
			if ( is_array( $caps ) ) {
				foreach ( $caps as $cap ) {
					$allcaps[ $cap ] = true;
				}
			}
		}
		return $allcaps;
	}

	/**
	 * Redirect to custom editor if core post.php or post-new.php is accessed.
	 */
	public function redirect_to_custom_editor(): void {
		global $pagenow;

		// Require logged-in user
		if ( ! is_user_logged_in() ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$post_id   = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

		if ( $post_id && ! $post_type ) {
			$post_type = get_post_type( $post_id );
		}

		if ( $post_type !== self::POST_TYPE ) {
			return;
		}

		if ( 'post-new.php' === $pagenow ) {
			// Require permission to create/manage forms
			if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_dragwyb_forms' ) ) {
				return;
			}

			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$from_post = isset( $_GET['from_post'] ) ? absint( $_GET['from_post'] ) : 0;
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$new_lang  = isset( $_GET['new_lang'] ) ? sanitize_text_field( wp_unslash( $_GET['new_lang'] ) ) : '';

			// Support WPML parameters if from_post/new_lang not set directly
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( empty( $new_lang ) && isset( $_GET['lang'] ) ) {
				$new_lang = sanitize_text_field( wp_unslash( $_GET['lang'] ) );
			}
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$trid = isset( $_GET['trid'] ) ? absint( $_GET['trid'] ) : 0;
			if ( empty( $from_post ) && $trid && function_exists( 'apply_filters' ) ) {
				$translations = apply_filters( 'wpml_get_element_translations', null, $trid, 'post_' . self::POST_TYPE );
				if ( is_array( $translations ) ) {
					foreach ( $translations as $trans ) {
						if ( ! empty( $trans->original ) && ! empty( $trans->element_id ) ) {
							$from_post = (int) $trans->element_id;
							break;
						}
					}
				}
			}

			$args = array(
				'page' => DRAGWYB_PREFIX . '-form-builder',
			);
			if ( $from_post ) {
				$args['from_post'] = $from_post;
			}
			if ( ! empty( $new_lang ) ) {
				$args['new_lang'] = $new_lang;
			}
			if ( $trid ) {
				$args['trid'] = $trid;
			}

			wp_safe_redirect( add_query_arg( $args, admin_url( 'admin.php' ) ) );
			exit;
		}

		if ( 'post.php' === $pagenow && $post_id ) {
			// Require permission to edit this specific form
			if ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			wp_safe_redirect(
				add_query_arg(
					array(
						'page'    => DRAGWYB_PREFIX . '-form-builder',
						'form_id' => $post_id,
					),
					admin_url( 'admin.php' )
				)
			);
			exit;
		}
	}

	/**
	 * Filter Polylang new translation link so + button opens the Dragwyb Form Builder.
	 *
	 * @param string            $link     The new post translation link.
	 * @param object|array|null $language The language object.
	 * @param int               $post_id  The source post ID.
	 * @return string
	 */
	public function filter_polylang_new_translation_link( $link, $language, $post_id ) {
		if ( get_post_type( $post_id ) === self::POST_TYPE ) {
			// Block unauthenticated users or users without permission to create/manage forms
			if ( ! is_user_logged_in() || ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_dragwyb_forms' ) ) ) {
				return '';
			}

			$lang_slug = is_object( $language ) && isset( $language->slug ) ? $language->slug : (string) $language;
			return add_query_arg(
				array(
					'page'      => DRAGWYB_PREFIX . '-form-builder',
					'from_post' => (int) $post_id,
					'new_lang'  => sanitize_text_field( $lang_slug ),
				),
				admin_url( 'admin.php' )
			);
		}
		return $link;
	}

	/**
	 * Filter WPML translation link so + and edit buttons point to Dragwyb Form Builder.
	 *
	 * @param string      $link      Existing link.
	 * @param int         $post_id   Post ID.
	 * @param string      $lang      Language code.
	 * @param int         $trid      Translation group ID.
	 * @param string|null $css_class CSS class of the icon.
	 * @return string
	 */
	public function filter_wpml_link_to_translation( $link, $post_id, $lang, $trid, $css_class = null ) {
		if ( get_post_type( $post_id ) === self::POST_TYPE ) {
			if ( ! is_user_logged_in() || ( ! current_user_can( 'manage_options' ) && ! current_user_can( 'edit_dragwyb_forms' ) ) ) {
				return '';
			}

			// Adding a new translation in WPML
			if ( strpos( (string) $css_class, 'otgs-ico-add' ) !== false || strpos( (string) $link, 'post-new.php' ) !== false ) {
				return add_query_arg(
					array(
						'page'      => DRAGWYB_PREFIX . '-form-builder',
						'from_post' => (int) $post_id,
						'new_lang'  => sanitize_text_field( $lang ),
						'trid'      => (int) $trid,
					),
					admin_url( 'admin.php' )
				);
			}

			// Editing an existing translation in WPML
			if ( preg_match( '/[?&]post=([0-9]+)/', (string) $link, $matches ) ) {
				return add_query_arg(
					array(
						'page'    => DRAGWYB_PREFIX . '-form-builder',
						'form_id' => (int) $matches[1],
					),
					admin_url( 'admin.php' )
				);
			}
		}

		return $link;
	}

	/**
	 * Filter edit post link for forms so it points to the Dragwyb Form Builder.
	 *
	 * @param string $link    The edit link.
	 * @param int    $post_id Post ID.
	 * @return string
	 */
	public function filter_form_edit_post_link( $link, $post_id ) {
		if ( get_post_type( $post_id ) === self::POST_TYPE ) {
			return add_query_arg(
				array(
					'page'    => DRAGWYB_PREFIX . '-form-builder',
					'form_id' => (int) $post_id,
				),
				admin_url( 'admin.php' )
			);
		}
		return $link;
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
