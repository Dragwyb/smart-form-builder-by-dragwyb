<?php
declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_SUbmission;
use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Settings extends Register_Controls_Base {
    private static $instance = null;

    public static function instance(): self {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function init(): void {}

    protected function register_controls(): void {
        // 🔹 Submission Actions
        $this->start_section('submission_actions', [
            'label' => __('Submission Settings', 'dragwyb-form-builder'),
        ]);

        $this->add_control('success_message', [
            'type'    => Controls::TEXTAREA,
            'label'   => __('Success Message', 'dragwyb-form-builder'),
            'default' => __('Your form has been submitted successfully.', 'dragwyb-form-builder'),
        ]);

        $this->add_control('error_message', [
            'type'    => Controls::TEXTAREA,
            'label'   => __('Error Message', 'dragwyb-form-builder'),
            'default' => __('Something went wrong. Please try again.', 'dragwyb-form-builder'),
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
            'conditions' => [
                'redirect_enable' => true,
            ]
        ]);

        $this->add_control('redirect_delay', [
            'type'       => Controls::NUMBER,
            'label'      => __('Redirect Delay (seconds)', 'dragwyb-form-builder'),
            'default'    => 0,
            'conditions' => [
                'redirect_enable' => true,
            ]
        ]);

        $this->end_section();

        // 🔹 Notifications (later expandable)
        $this->start_section('notifications', [
            'label' => __('Notifications', 'dragwyb-form-builder'),
        ]);

        $this->add_control('send_email', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Send Email Notification', 'dragwyb-form-builder'),
            'default' => 'no',
        ]);

        // $this->add_control('admin_email', [
        //     'type'       => Controls::EMAIL,
        //     'label'      => __('Notification Email To', 'dragwyb-form-builder'),
        //     'default'    => get_option('admin_email'),
        //     'conditions' => [
        //         'send_email' => true,
        //     ]
        // ]);

        $this->add_control('user_confirmation', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Send Confirmation Email To User', 'dragwyb-form-builder'),
            'default' => 'no',
        ]);

        $this->end_section();
    }
}
