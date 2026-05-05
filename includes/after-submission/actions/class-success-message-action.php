<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Success_Message_Action extends Action_Base
{
    public function get_id(): string
    {
        return 'success_message';
    }

    public function get_name(): string
    {
        return __('Success Message', 'smart-form-builder-by-dragwyb');
    }

    protected function register_settings(): void
    {
        $this->start_section('message_section_success_message', [
            'label' => __('Success Message', 'smart-form-builder-by-dragwyb'),
            'conditions' => [
                'after_submissions' => 'success_message'
            ]
        ]);

        $this->add_control('message_text_success_message', [
            'type'    => Controls::TEXTAREA,
            'label'   => __('Message', 'smart-form-builder-by-dragwyb'),
            'default' => __('Your form has been submitted successfully.', 'smart-form-builder-by-dragwyb'),
            'rows'    => 3,
            'required' => true,
        ]);

        $this->end_section();
    }

    public function process_submission($form_id, $form_data, $form_config, Form_Submission_Handler $form_submission)
    {
        $settings = $form_config['after-submission'] ?? [];
        $message = $settings['message_text_success_message'] ?? __('Your form has been submitted successfully.', 'smart-form-builder-by-dragwyb');

        $form_submission->set_form_return_data('success_message', sanitize_textarea_field($message));
    }
}
