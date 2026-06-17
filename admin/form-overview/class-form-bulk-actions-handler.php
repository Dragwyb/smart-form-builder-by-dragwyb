<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Form_Overview;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Form_Bulk_Actions_Handler {


	protected $post_type;

	private static ?self $instance = null;

	private static $trashed   = 0;
	private static $locked    = 0;
	private static $untrashed = 0;
	private static $deleted   = 0;

	public static function instance( $post_type ): self {
		if ( null === self::$instance ) {
			self::$instance = new self( $post_type );
		}

		return self::$instance;
	}

	public function __construct( $post_type ) {
		$this->post_type = sanitize_text_field( $post_type );
		$this->handle_actions();
	}

	public function handle_actions() {
		if ( ! current_user_can( 'delete_posts' ) ) {
			return;
		}

		$action = $this->get_current_action();

		if ( ! $action ) {
			return;
		}

		check_admin_referer( 'bulk-' . sanitize_text_field( $this->post_type ) );

		$post_ids = isset( $_REQUEST['post'] ) ? array_map( 'absint', (array) wp_unslash( $_REQUEST['post'] ) ) : array();

		if ( empty( $post_ids ) ) {
			return;
		}
		foreach ( $post_ids as $post_id ) {
			if ( get_post_type( $post_id ) !== $this->post_type ) {
				continue;
			}

			$query_args = array();

			switch ( $action ) {
				case 'trash':
					$this->trash_post( $post_id );
					break;
				case 'delete':
					$this->delete_post( $post_id );
					break;
				case 'untrash':
					$this->untrash_post( $post_id );
					break;
			}
		}

		$query_args = array();

		switch ( $action ) {
			case 'trash':
				$query_args = array(
					'trashed'  => sanitize_text_field( $this->trashed ),
					'ids'      => implode( ',', $post_ids ),
					'locked'   => sanitize_text_field( $this->locked ),
					'_wpnonce' => wp_create_nonce( 'bulk-trash-' . sanitize_text_field( $this->post_type ) ),
				);
				break;
			case 'delete':
				$query_args = array(
					'deleted'  => sanitize_text_field( $this->deleted ),
					'_wpnonce' => wp_create_nonce( 'bulk-delete-' . sanitize_text_field( $this->post_type ) ),
				);
				break;
			case 'untrash':
				$query_args = array(
					'untrashed' => sanitize_text_field( $this->untrashed ),
					'_wpnonce'  => wp_create_nonce( 'bulk-untrash-' . sanitize_text_field( $this->post_type ) ),
				);
				break;
		}

		$this->redirect_after_action( $query_args );
	}

	protected function get_current_action() {
		check_admin_referer( 'bulk-' . sanitize_text_field( $this->post_type ) );
		return isset( $_REQUEST['action'] ) && $_REQUEST['action'] !== '-1' ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : ( isset( $_REQUEST['action2'] ) ? sanitize_key( wp_unslash( $_REQUEST['action2'] ) ) : '' );
	}

	protected function trash_post( $post_id ) {
		if ( ! current_user_can( 'delete_post', $post_id ) ) {
			++$this->locked;
			return;
		}

		if ( wp_check_post_lock( $post_id ) ) {
			++$this->locked;
			return;
		}

		if ( ! wp_trash_post( $post_id ) ) {
			wp_die( esc_html__( 'Error in moving the item to Trash.', 'smart-form-builder-by-dragwyb' ) );
		}

		++$this->trashed;
	}

	protected function delete_post( $post_id ) {
		if ( ! current_user_can( 'delete_post', $post_id ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to delete this item.', 'smart-form-builder-by-dragwyb' ) );
		}

		if ( ! wp_delete_post( $post_id ) ) {
			wp_die( esc_html__( 'Error in deleting the item.', 'smart-form-builder-by-dragwyb' ) );
		}

		++$this->deleted;
	}

	protected function untrash_post( $post_id ) {
		if ( ! current_user_can( 'delete_post', $post_id ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to restore this item from the Trash.', 'smart-form-builder-by-dragwyb' ) );
		}

		if ( ! wp_untrash_post( $post_id ) ) {
			wp_die( esc_html__( 'Error in restoring the item from Trash.', 'smart-form-builder-by-dragwyb' ) );
		}

		++$this->untrashed;
	}

	protected function redirect_after_action( $query_args ) {
		$action         = $this->get_current_action();
		$current_status = $action === 'untrash' ? 'trash' : 'all';

		$sendback = remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'locked', 'ids' ), wp_get_referer() );

		if ( $current_status === 'trash' ) {
			$trash_count = wp_count_posts( sanitize_text_field( $this->post_type ) )->trash ?? 0;
			if ( $trash_count === 0 ) {
				$sendback = remove_query_arg( 'post_status', $sendback );
			}
		}

		if ( $current_status === 'all' ) {
			$all_post            = wp_count_posts( sanitize_text_field( $this->post_type ) );
			$publish_posts_count = ( $all_post->publish ?? 0 ) + ( $all_post->draft ?? 0 );
			$trash_count         = $all_post->trash ?? 0;

			if ( $publish_posts_count === 0 && $trash_count > 0 ) {
				$sendback = admin_url( 'admin.php?page=dragwyb-form-overview' );
				$sendback = add_query_arg( 'post_status', 'trash', $sendback );
			}
		}

		if ( ! $sendback ) {
			return;
		}

		$sendback = add_query_arg( $query_args, $sendback );

		$this->trashed   = 0;
		$this->locked    = 0;
		$this->untrashed = 0;
		$this->deleted   = 0;

		wp_safe_redirect( $sendback );
		exit;
	}
}
