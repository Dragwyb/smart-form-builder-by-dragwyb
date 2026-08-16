<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;
use Dragwyb\Form_Builder\Admin\Db\Submission\Dragwyb_Submission_Db;
use Dragwyb\Form_Builder\Admin\Settings\Settings_Manager;

class Save_Submissions_Action extends Action_Base {

	public function get_id(): string {
		return 'save_submissions';
	}

	public function get_name(): string {
		return __( 'Save Submissions', 'smart-form-builder-by-dragwyb' );
	}

	protected function register_settings(): void {
		$this->start_section(
			'database_section_save_submissions',
			array(
				'label'      => __( 'Save Submissions', 'smart-form-builder-by-dragwyb' ),
				'conditions' => array(
					'after_submissions' => 'save_submissions',
				),
			)
		);

		$this->add_control(
			'save_to_db_save_submissions',
			array(
				'type'        => Controls::SWITCHER,
				'label'       => __( 'Save Submissions to Database', 'smart-form-builder-by-dragwyb' ),
				'default'     => 'no',
				'description' => __( 'View entries in WP Dashboard > Dragwyb > Submissions', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->end_section();
	}

	/**
	 * Get IP address safely.
	 *
	 * @return string
	 */
	private function get_ip_address(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '127.0.0.1';

		// Handle multiple IPs if present
		$ip_array = explode( ',', $ip );
		$ip       = trim( $ip_array[0] );

		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '127.0.0.1';
	}

	public function process_submission( $form_id, $form_data, $form_config, Form_Submission_Handler $form_submission ) {
		$settings   = $form_config['after-submission'] ?? array();
		$save_to_db = $settings['save_to_db_save_submissions'] ?? 'no';

		if ( $save_to_db === 'yes' ) {
			$db = new Dragwyb_Submission_Db();

			$insert_data = array(
				'form_id'         => $form_id,
				'submission_data' => $this->get_form_data( $form_data, $form_config ),
			);

			$disable_details    = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'gdpr_disable_user_details', false );
			$is_disable_details = true === $disable_details || 'yes' === $disable_details;

			if ( ! $is_disable_details ) {
				$insert_data['ip_address'] = $this->get_ip_address();
				$insert_data['user_agent'] = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
			}

			$insert_id = $db->insert( $insert_data );

			if ( is_wp_error( $insert_id ) ) {
				$form_submission->add_error( 'save_submissions', $insert_id->get_error_message() );
			}
		}
	}
}
