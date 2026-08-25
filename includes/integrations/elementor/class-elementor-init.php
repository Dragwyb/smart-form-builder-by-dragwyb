<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Integrations\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Includes\Integrations\Elementor\Widgets\Form_Widget;

/**
 * Class Elementor_Init
 *
 * Handles Elementor widget registration and asset hooks.
 */
class Elementor_Init {

	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init(): void {
		// Register Elementor Widget
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
		// Fallback for older Elementor versions
		add_action( 'elementor/widgets/widgets_registered', array( $this, 'register_widgets_legacy' ) );

		// Enqueue frontend & editor styles for Elementor
		add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue_preview_styles' ) );
		add_action( 'elementor/preview/enqueue_scripts', array( $this, 'enqueue_preview_scripts' ) );
	}

	/**
	 * Register widgets with Elementor (Elementor 3.5.0+).
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( $widgets_manager ): void {
		if ( class_exists( '\Elementor\Widget_Base' ) ) {
			require_once DRAGWYB_FORM_BUILDER_PATH . 'includes/integrations/elementor/widgets/class-form-widget.php';
			$widgets_manager->register( new Form_Widget() );
		}
	}

	/**
	 * Fallback widget registration for older Elementor versions (< 3.5.0).
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets_legacy( $widgets_manager ): void {
		if ( class_exists( '\Elementor\Widget_Base' ) ) {
			require_once DRAGWYB_FORM_BUILDER_PATH . 'includes/integrations/elementor/widgets/class-form-widget.php';
			$widgets_manager->register_widget_type( new Form_Widget() );
		}
	}

	/**
	 * Enqueue form styles in Elementor editor preview iframe.
	 */
	public function enqueue_preview_styles(): void {
		wp_enqueue_style(
			'dragwyb-form-frontend',
			DRAGWYB_FORM_BUILDER_URL . 'assets/css/form-frontend.css',
			array(),
			DRAGWYB_FORM_BUILDER_VERSION
		);
	}

	/**
	 * Enqueue interactive preview helper script in Elementor editor preview iframe.
	 */
	public function enqueue_preview_scripts(): void {
		wp_add_inline_script(
			'jquery',
			'
			jQuery(document).ready(function($) {
				$(document).on("change", ".dragwyb-elementor-preview-select", function() {
					var formId = $(this).val();
					var $widget = $(this).closest(".elementor-element");
					var modelId = $widget.data("id");
					if (window.parent && window.parent.$e && modelId) {
						var container = window.parent.elementor.getContainer(modelId);
						if (container) {
							container.settings.setExternalChange("form_id", formId);
						}
					}
				});
			});
			'
		);
	}
}
