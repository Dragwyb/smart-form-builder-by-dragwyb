<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Error_Logs_Action extends Action_Base {

	public function get_id(): string {
		return 'error_logs';
	}

	public function get_name(): string {
		return __( 'Error Logs', 'smart-form-builder-by-dragwyb' );
	}

	protected function register_settings(): void {
		$this->start_section(
			'database_section_error_logs',
			array(
				'label'      => __( 'Error Logs', 'smart-form-builder-by-dragwyb' ),
				'conditions' => array(
					'after_submissions' => 'error_logs',
				),
			)
		);

		$this->add_control(
			'enable_error_logs',
			array(
				'type'        => Controls::SWITCHER,
				'label'       => __( 'Enable Error Logging', 'smart-form-builder-by-dragwyb' ),
				'default'     => 'yes',
				'description' => __( 'Log validation failures to WordPress Dashboard > Smart Forms > Error Log', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->end_section();
	}

	public function process_submission( $form_id, $form_data, $form_config, Form_Submission_Handler $form_submission ) {
		$settings       = $form_config['after-submission'] ?? array();
		$enable_logging = $settings['enable_error_logs'] ?? 'yes';

		if ( $enable_logging === 'yes' && $form_submission->has_errors() ) {
			$submission_data = $this->get_form_data( $form_data, $form_config );
			$handler_errors  = $form_submission->get_errors();
			$errors_array    = array();
			foreach ( $handler_errors->get_error_codes() as $code ) {
				$errors_array[ $code ] = $handler_errors->get_error_message( $code );
			}

			$error_db = new \Dragwyb\Form_Builder\Admin\Db\Error_Log\Dragwyb_Error_Log_Db();
			$error_db->insert(
				array(
					'form_id'         => $form_id,
					'ip_address'      => sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ),
					'user_agent'      => sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ),
					'submission_data' => $submission_data,
					'errors'          => $errors_array,
				)
			);
		}
	}
}
