<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Integrations;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;
use Dragwyb\Form_Builder\Includes\Integrations\Gutenberg\Gutenberg_Block;
use Dragwyb\Form_Builder\Includes\Integrations\Elementor\Elementor_Init;

/**
 * Class Integrations_Manager
 *
 * Central manager for third-party page builder and block editor integrations.
 */
class Integrations_Manager {

	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init(): void {
		// Initialize Gutenberg block integration
		Gutenberg_Block::instance()->init();

		// Initialize Elementor integration
		Elementor_Init::instance()->init();
	}

	/**
	 * Retrieve all available published/draft forms as an associative array [ form_id => "Title (#ID)" ].
	 *
	 * @return array<int|string, string>
	 */
	public static function get_forms_options(): array {
		$options = array();

		$posts = get_posts(
			array(
				'post_type'      => Dragwyb_Post::POST_TYPE,
				'post_status'    => array( 'publish', 'draft' ),
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		if ( ! empty( $posts ) && is_array( $posts ) ) {
			foreach ( $posts as $post ) {
				$title = ! empty( $post->post_title ) ? $post->post_title : sprintf( __( 'Form #%d (No Title)', 'smart-form-builder-by-dragwyb' ), $post->ID );
				$suffix = ( 'draft' === $post->post_status ) ? ' - [' . __( 'Draft', 'smart-form-builder-by-dragwyb' ) . ']' : '';
				$options[ (string) $post->ID ] = sprintf( '%1$s (#%2$d)%3$s', $title, $post->ID, $suffix );
			}
		}

		return $options;
	}

	/**
	 * Retrieve all available forms formatted for JavaScript select options [ [ 'label' => '...', 'value' => '...' ], ... ].
	 *
	 * @return array<int, array{label: string, value: string}>
	 */
	public static function get_forms_for_js(): array {
		$options = self::get_forms_options();
		$list    = array();

		foreach ( $options as $id => $label ) {
			$list[] = array(
				'label' => $label,
				'value' => (string) $id,
			);
		}

		return $list;
	}
}
