<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Advance_Settings;

use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;

class Advance_Settings extends Toolbar_Base {

	private static $instance    = null;
	protected $toolbar_settings = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	protected function get_id(): string {
		return 'advance';
	}

	protected function get_name(): string {
		return __( 'Advance', 'smart-form-builder-by-dragwyb' );
	}

	protected function get_icon(): string {
		return 'fas fa-sliders-h';
	}

	public function __construct() {
		parent::__construct();
		$this->init();
	}

	private function init(): void {
		$this->toolbar_settings = new Settings();
	}

	protected function update_toolbar(): void {
		$settings = $this->get_display_settings();

		$form_id = $this->get_form_id();

		$post_update = array();

		$post_status = get_post_status( $form_id );

		if ( isset( $settings['form_name'] ) ) {
			$post_update['post_title'] = sanitize_text_field( $settings['form_name'] );
		}
		if ( isset( $settings['form_status'] ) ) {
			$post_update['post_status'] = sanitize_text_field( $settings['form_status'] );
		} elseif ( 'draft' !== $post_status ) {
			$post_update['post_status'] = 'draft';
		}

		if ( ! empty( $post_update ) ) {
			$post_update['ID'] = $form_id;
			wp_update_post( $post_update );
		}
	}

	protected function get_setting_instance(): string {
		return Settings::class;
	}
}
