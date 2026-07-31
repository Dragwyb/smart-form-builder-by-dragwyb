<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Includes\Dragwyb_Form_Builder_Ajax;
use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;
use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Pages;
use Dragwyb\Form_Builder\Admin\Settings\Dragwyb_Settings;
use Dragwyb\Form_Builder\Admin\Dragwyb_Editor\Dragwyb_Builder_Editor;
use Dragwyb\Form_Builder\Includes\Frontend\Shortcode\Shortcode_Handler;
use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;
use Dragwyb\Form_Builder\Includes\Frontend\Form_Preview;
use Dragwyb\Form_Builder\Includes\Frontend\Managers\CSS_Manager;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Dragwyb_Frontend_Route;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Dragwyb_Settings_Route;
use Dragwyb\Form_Builder\Admin\Feedback\SMFBD_Feedback_Form;
use Dragwyb\Form_Builder\Admin\Review\Dragwyb_Review_Notice;

class Dragwyb_Init {

	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function init(): void {
		new Dragwyb_Post();

		// Initialize admin
		if ( is_admin() ) {
			new Dragwyb_Builder_Editor();
			new Dragwyb_Pages();
			new Dragwyb_Form_Builder_Ajax();
			Dragwyb_Settings::instance();
			Frontend_Render::instance();
			SMFBD_Feedback_Form::get_instance();

			$this->review_notice();
		}

		new Dragwyb_Frontend_Route();
		new Dragwyb_Settings_Route();
		Form_Preview::instance();
		Shortcode_Handler::instance();

		add_action( 'admin_init', array( $this, 'initial_files' ) );

		// Enqueue admin assets
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );
	}

	public function initial_files() {
		CSS_Manager::instance();
	}

	public static function core_script() {

		$thisObj = self::instance();

		$js_assets_info = array(
			'version'      => DRAGWYB_FORM_BUILDER_VERSION,
			'dependencies' => array( 'jquery' ),
		);

		if ( file_exists( DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/core/core.asset.php' ) ) {
			$dragwyb_js_assets_info = require_once DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/core/core.asset.php';

			if ( isset( $dragwyb_js_assets_info['dependencies'] ) ) {
				$js_assets_info['dependencies'] = array_merge( $js_assets_info['dependencies'], $dragwyb_js_assets_info['dependencies'] );
			}

			if ( isset( $dragwyb_js_assets_info['version'] ) ) {
				$js_assets_info['version'] = $dragwyb_js_assets_info['version'];
			}
		}

		// Enqueue React and dependencies
		wp_register_script(
			'dragwyb-form-core',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/dist/core/core.js' ),
			$js_assets_info['dependencies'],
			esc_attr( $js_assets_info['version'] ),
			true
		);

		$thisObj->localize_script();
	}

	private function localize_script() {
		global $post;

		$form_id = $post->ID ?? 0;

		$data = array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'i18n'    => $this->get_translations(),
		);

		$data = apply_filters( 'Dragwyb_Localize_Core_Script', $data );
		// Localize data
		wp_localize_script( 'dragwyb-form-core', 'DragwybBuilder', $data );
	}

	public function admin_assets() {
		// Enqueue admin styles
		wp_enqueue_style(
			'dragwyb-form-admin',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/admin.css' ),
			array(),
			esc_attr( DRAGWYB_FORM_BUILDER_VERSION ),
			'all'
		);
	}

	private function review_notice() {
		$already_rated = get_option( 'dragwyb_form_builder_already_reviewd', false );
		if ( ! $already_rated && class_exists( Dragwyb_Review_Notice::class ) ) {
			Dragwyb_Review_Notice::instance();
		}
	}

	private function get_translations() {
		$localize_strings = array();

		return apply_filters( 'Dragwyb_i18n', $localize_strings );
	}
}
