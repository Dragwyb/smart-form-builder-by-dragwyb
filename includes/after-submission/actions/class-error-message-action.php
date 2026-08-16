<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;
use Dragwyb\Form_Builder\Includes\Controls\Icons\Icons_Manager;

class Error_Message_Action extends Action_Base {

	public function get_id(): string {
		return 'error_message';
	}

	public function get_name(): string {
		return __( 'Error Message', 'smart-form-builder-by-dragwyb' );
	}

	protected function register_settings(): void {
		$this->start_section(
			'message_section_error_message',
			array(
				'label'      => __( 'Error Message (Fallback)', 'smart-form-builder-by-dragwyb' ),
				'conditions' => array(
					'after_submissions' => array( 'error_message' ),
				),
			)
		);

		$this->add_control(
			'message_position_error_message',
			array(
				'type'         => Controls::SELECT,
				'label'        => __( 'Message Position', 'smart-form-builder-by-dragwyb' ),
				'options'      => array(
					'form_bottom' => __( 'Form Bottom', 'smart-form-builder-by-dragwyb' ),
					'form_place'  => __( 'In Form Place', 'smart-form-builder-by-dragwyb' ),
					'modal'       => __( 'Modal Popup', 'smart-form-builder-by-dragwyb' ),
				),
				'default'      => 'form_bottom',
				'label_inline' => true,
			)
		);

		$this->add_control(
			'message_title_error_message',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Title', 'smart-form-builder-by-dragwyb' ),
				'default' => __( 'Error', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'message_icon_error_message',
			array(
				'type'    => Controls::ICON,
				'label'   => __( 'Icon', 'smart-form-builder-by-dragwyb' ),
				'default' => array(
					'icon' => 'exclamation-circle',
					'type' => 'solid',
				),
			)
		);

		$this->add_control(
			'message_text_error_message',
			array(
				'type'     => Controls::TEXTAREA,
				'label'    => __( 'Message', 'smart-form-builder-by-dragwyb' ),
				'default'  => __( 'Something went wrong. Please try again.', 'smart-form-builder-by-dragwyb' ),
				'rows'     => 2,
				'required' => true,
			)
		);

		$this->add_control(
			'message_bg_color_error_message',
			array(
				'type'    => Controls::COLOR,
				'label'   => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'default' => '#ffffff',
			)
		);

		$this->add_control(
			'message_text_color_error_message',
			array(
				'type'    => Controls::COLOR,
				'label'   => __( 'Text Color', 'smart-form-builder-by-dragwyb' ),
				'default' => '#b91c1c',
			)
		);

		$this->end_section();
	}

	public function process_submission( $form_id, $form_data, $form_config, Form_Submission_Handler $form_submission ) {
		if ( $form_submission->has_errors() ) {
			$settings = $form_config['after-submission'] ?? array();

			$position = $settings['message_position_error_message'] ?? 'form_bottom';
			$title    = $settings['message_title_error_message'] ?? __( 'Error', 'smart-form-builder-by-dragwyb' );
			$message  = $settings['message_text_error_message'] ?? __( 'Something went wrong. Please try again.', 'smart-form-builder-by-dragwyb' );

			$icon_data = array(
				'icon' => 'exclamation-circle',
				'type' => 'solid',
			);

			if ( isset( $settings['message_icon_error_message'] ) && ! empty( $settings['message_icon_error_message']['icon'] ) ) {
				$icon_data = $settings['message_icon_error_message'];
			}

			$bg_color   = $settings['message_bg_color_error_message'] ?? '#ffffff';
			$text_color = $settings['message_text_color_error_message'] ?? '#b91c1c';

			$data = array(
				'position'   => sanitize_text_field( $position ),
				'title'      => sanitize_text_field( $title ),
				'message'    => sanitize_textarea_field( $message ),
				'icon'       => Icons_Manager::get_icon_html( $icon_data ),
				'bg_color'   => sanitize_text_field( $bg_color ),
				'text_color' => sanitize_text_field( $text_color ),
			);

			$form_submission->set_form_return_data( 'error_message', $data );
		}
	}
}
