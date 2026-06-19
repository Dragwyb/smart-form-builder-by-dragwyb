<?php

namespace Dragwyb\Form_Builder\Includes\Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;

class Helper {

	public static function directory_separator() {
		if ( defined( 'DIRECTORY_SEPARATOR' ) ) {
			return DIRECTORY_SEPARATOR;
		}

		$dir = ( strpos( DRAGWYB_FORM_BUILDER_PATH, '\\' ) !== false ) ? '\\' : '/';
		return $dir;
	}

	public static function namespace_into_dir_path( $namespace ) {
		$seperator = self::directory_separator();
		if ( ! strpos( $namespace, $seperator ) ) {
			$namespace = str_replace( '\\', $seperator, $namespace );
		}
		return $namespace;
	}

	public static function dir_path_into_namespace( $dir ) {
		$seperator = self::directory_separator();

		if ( strpos( $dir, $seperator ) ) {
			$dir = str_replace( $seperator, '\\', $dir );
		}

		return $dir;
	}

	final public static function is_preview_mode(): bool {
		$form_preview_id = isset( $_GET['preview_id'] ) ? sanitize_text_field( wp_unslash( $_GET['preview_id'] ) ) : '';
		$form_id         = isset( $_GET['p'] ) ? absint( $_GET['p'] ) : 0;
		$post_type       = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : '';

		if ( $form_preview_id && $form_id && $post_type === Dragwyb_Post::POST_TYPE && self::current_user_can_preview( $form_id ) && wp_verify_nonce( $form_preview_id, self::preview_private_key_name( $form_id ) ) && is_user_logged_in() ) {
			return true;
		}

		return false;
	}

	private static function current_user_can_preview( int $form_id ): bool {
		return current_user_can( 'manage_options' ) || current_user_can( 'edit_post', $form_id );
	}

	private static function preview_private_key_name( int $form_id ): string {
		$post_type = Dragwyb_Post::POST_TYPE;
		return $post_type . '-' . $form_id;
	}

	final public static function is_editor_preview_mode(): bool {
		if ( self::is_preview_mode() ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce verification is done in self::is_preview_mode() which is called first.
			$iframe_mode = isset( $_GET['dragwyb_iframe_mode'] ) ? sanitize_key( wp_unslash( $_GET['dragwyb_iframe_mode'] ) ) : '';

			if ( 'true' === $iframe_mode ) {
				return true;
			}
		}

		return false;
	}
}
