<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\After_Submission\Actions\Email_Action_Base;

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

    public function process_submission($form_data, $settings)
    {
        // Handle sending email to user
    }
}
