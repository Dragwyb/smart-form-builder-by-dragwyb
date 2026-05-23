<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission;

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

abstract class Action_Base extends Register_Controls_Base {

	abstract public function get_id(): string;
	abstract public function get_name(): string;

	protected function register_scripts() {
		return array();
	}

	protected function register_style() {
		return array();
	}

	public function enqueue_assets() {
		$scripts = $this->register_scripts();
		$styles  = $this->register_style();

		if ( is_array( $scripts ) ) {
			foreach ( $scripts as $script ) {
				if ( ! wp_script_is( $script, 'enqueued' ) ) {
					wp_enqueue_script( $script );
				}
			}
		}

		if ( is_array( $styles ) ) {
			foreach ( $styles as $style ) {
				if ( ! wp_style_is( $style, 'enqueued' ) ) {
					wp_enqueue_style( $style );
				}
			}
		}
	}

	/**
	 * Define the specific controls for this action.
	 */
	abstract protected function register_settings(): void;

	/**
	 * Process the submission for this specific action.
	 */
	abstract public function process_submission( $form_id, $form_data, $form_config, Form_Submission_Handler $form_submission );

	protected function register_controls(): void {
		$this->register_settings();
	}
}
