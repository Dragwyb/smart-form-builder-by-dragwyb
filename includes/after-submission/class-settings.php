<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_SUbmission;

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Settings extends Register_Controls_Base
{
    private static $instance = null;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function init(): void {}

    protected function register_controls(): void
    {

        // Data Handling
        $this->start_section('data_handling', [
            'label' => __('Data Handling', 'dragwyb-form-builder'),
        ]);

        $this->add_control('save_to_db', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Save Submissions to Database', 'dragwyb-form-builder'),
            'default' => 'yes',
            'description' => __('View entries in WP Dashboard > Dragwyb > Submissions', 'dragwyb-form-builder'),
        ]);

        $this->end_section();

        // On Screen Actions
        $this->start_section('submission_actions', [
            'label' => __('Success / Redirect', 'dragwyb-form-builder'),
        ]);

        $this->add_control('success_message', [
            'type'    => Controls::TEXTAREA,
            'label'   => __('Success Message', 'dragwyb-form-builder'),
            'default' => __('Your form has been submitted successfully.', 'dragwyb-form-builder'),
            'rows'    => 3,
        ]);

        $this->add_control('error_message', [
            'type'    => Controls::TEXTAREA,
            'label'   => __('Error Message (Fallback)', 'dragwyb-form-builder'),
            'default' => __('Something went wrong. Please try again.', 'dragwyb-form-builder'),
            'rows'    => 2,
        ]);

        $this->add_control('redirect_enable', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Redirect After Submit', 'dragwyb-form-builder'),
            'default' => 'no',
        ]);

        $this->add_control('redirect_url', [
            'type'       => Controls::TEXT,
            'label'      => __('Redirect URL', 'dragwyb-form-builder'),
            'default'    => '',
            'placeholder' => 'https://example.com/thank-you',
            'conditions' => [
                'redirect_enable' => true,
            ]
        ]);

        $this->end_section();

        // Admin Notifications (Fully Unlocked)
        $this->start_section('admin_email_settings', [
            'label' => __('Admin Email Notification', 'dragwyb-form-builder'),
        ]);

        $this->add_control('send_admin_email', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Send Notification to Admin', 'dragwyb-form-builder'),
            'default' => 'yes',
        ]);

        $this->add_control('admin_email_to', [
            'type'       => Controls::TEXT,
            'label'      => __('Send To (Email)', 'dragwyb-form-builder'),
            'default'    => get_option('admin_email'),
            'description' => __('Separate multiple emails with commas.', 'dragwyb-form-builder'),
            'conditions' => ['send_admin_email' => true],
        ]);

        $this->add_control('admin_email_subject', [
            'type'       => Controls::TEXT,
            'label'      => __('Subject Line', 'dragwyb-form-builder'),
            'default'    => __('New Submission: [Form Name]', 'dragwyb-form-builder'),
            'conditions' => ['send_admin_email' => true],
        ]);

        $this->add_control('admin_email_body', [
            'type'       => Controls::TEXTAREA,
            'label'      => __('Message Body', 'dragwyb-form-builder'),
            'default'    => '{all_fields}', // Shortcode for all data
            'description' => __('Use {all_fields} to show all data, or use field IDs like {name}.', 'dragwyb-form-builder'),
            'conditions' => ['send_admin_email' => true],
        ]);

        $this->end_section();

        // User Confirmation (Auto-Responder)
        $this->start_section('user_email_settings', [
            'label' => __('User Confirmation Email', 'dragwyb-form-builder'),
        ]);

        $this->add_control('send_user_email', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Send Confirmation to User', 'dragwyb-form-builder'),
            'default' => 'no',
            'description' => __('Requires an Email Field in your form.', 'dragwyb-form-builder'),
        ]);

        $this->add_control('user_email_field_id', [
            'type'       => Controls::TEXT,
            'label'      => __('Email Field ID', 'dragwyb-form-builder'),
            'placeholder' => 'email_1',
            'description' => __('Enter the Field ID of the user\'s email input.', 'dragwyb-form-builder'),
            'conditions' => ['send_user_email' => true],
        ]);

        $this->add_control('user_email_subject', [
            'type'       => Controls::TEXT,
            'label'      => __('Subject Line', 'dragwyb-form-builder'),
            'default'    => __('We received your submission!', 'dragwyb-form-builder'),
            'conditions' => ['send_user_email' => true],
        ]);

        $this->add_control('user_email_body', [
            'type'       => Controls::TEXTAREA,
            'label'      => __('Message Body', 'dragwyb-form-builder'),
            'default'    => "Hi {name},\n\nThank you for contacting us. We will get back to you shortly.\n\nBest,\nTeam",
            'conditions' => ['send_user_email' => true],
        ]);

        $this->end_section();
    }
}
