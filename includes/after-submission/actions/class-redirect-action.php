<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Redirect_Action extends Action_Base {

	public function get_id(): string {
		return 'redirect';
	}

	public function get_name(): string {
		return __( 'Redirect', 'smart-form-builder-by-dragwyb' );
	}

	protected function register_settings(): void {
		$this->start_section(
			'url_section_redirect',
			array(
				'label'      => __( 'Redirect After Submit', 'smart-form-builder-by-dragwyb' ),
				'conditions' => array(
					'after_submissions' => 'redirect',
				),
			)
		);

		$this->add_control(
			'url_redirect',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Redirect URL', 'smart-form-builder-by-dragwyb' ),
				'default'     => '',
				'placeholder' => 'https://example.com/thank-you',
				'required'    => true,
			)
		);

		$this->end_section();
	}

	public function process_submission( $form_id, $form_data, $form_config, Form_Submission_Handler $form_submission ) {
		$settings = $form_config['after-submission'] ?? array();
		$url      = $settings['url_redirect'] ?? '';

		if ( ! empty( $url ) ) {
			$form_submission->set_form_return_data( 'redirect', esc_url_raw( $url ) );
		}
	}
}
