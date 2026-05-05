<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Error_Message_Action extends Action_Base
{
    public function get_id(): string
    {
        return 'error_message';
    }

    public function get_name(): string
    {
        return __('Error Message', 'smart-form-builder-by-dragwyb');
    }

    protected function register_settings(): void
    {
        $this->start_section('message_section_error_message', [
            'label' => __('Error Message (Fallback)', 'smart-form-builder-by-dragwyb'),
            'conditions' => [
                'after_submissions' => ['error_message']
            ]
        ]);

        $this->add_control('message_text_error_message', [
            'type'    => Controls::TEXTAREA,
            'label'   => __('Message', 'smart-form-builder-by-dragwyb'),
            'default' => __('Something went wrong. Please try again.', 'smart-form-builder-by-dragwyb'),
            'rows'    => 2,
            'required' => true,
        ]);

        $this->end_section();
    }

    public function process_submission($form_id, $form_data, $form_config, Form_Submission_Handler $form_submission)
    {
        $settings = $form_config['after-submission'] ?? [];
        $message = $settings['message_text_error_message'] ?? __('Something went wrong. Please try again.', 'smart-form-builder-by-dragwyb');
        
        $form_submission->set_form_return_data('error_message', sanitize_textarea_field($message));
    }
}
