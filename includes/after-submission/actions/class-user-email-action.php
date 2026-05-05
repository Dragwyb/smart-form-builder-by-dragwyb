<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\After_Submission\Actions\Email_Action_Base;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class User_Email_Action extends Email_Action_Base
{
    public function get_id(): string
    {
        return 'user_email';
    }

    public function get_name(): string
    {
        return __('Send User Email', 'smart-form-builder-by-dragwyb');
    }

    protected function section_label(): string
    {
        return __('User Confirmation Email', 'smart-form-builder-by-dragwyb');
    }

    protected function register_settings(): void
    {
        $this->email_action_settings();
    }

    public function process_submission($form_id, $form_data, $form_config, Form_Submission_Handler $form_submission)
    {
        $settings = $form_config['after-submission'] ?? [];
        
        $to = $settings['email_to_user_email'] ?? '';
        $subject = $settings['email_subject_user_email'] ?? __('New Submission: [Form Name]', 'smart-form-builder-by-dragwyb');
        $message = $settings['email_message_body_user_email'] ?? '{all_fields}';

        $to = $this->replace_shortcodes($to, $form_data, $form_config);
        
        if (!is_email($to)) {
            return; // Target is not a valid email after parsing
        }

        $subject = $this->replace_shortcodes($subject, $form_data, $form_config);
        $message = $this->replace_shortcodes($message, $form_data, $form_config);

        $headers = ['Content-Type: text/html; charset=UTF-8'];

        $this->send_email($to, $subject, $message, $headers, $form_submission);
    }
}
