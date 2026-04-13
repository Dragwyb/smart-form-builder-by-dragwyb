<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Advance_Settings;

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
        $form_id = absint($this->get_form_id());
        $form_title = sanitize_text_field(get_the_title($form_id));
        $form_status = sanitize_text_field(get_post_status($form_id));

        // 🔹 Form Identity
        $this->start_section('form_identity', [
            'label' => __('Form Identity', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('form_name', [
            'type'    => Controls::TEXT,
            'label'   => __('Form Name', 'smart-form-builder-by-dragwyb'),
            'default' => sanitize_text_field($form_title),
        ]);

        $this->add_control('form_id', [
            'type'    => Controls::TEXT,
            'label'   => __('Form ID', 'smart-form-builder-by-dragwyb'),
            'default' => sanitize_text_field($form_id),
        ]);

        $this->add_control('form_status', [
            'type'    => Controls::SELECT,
            'label'   => __('Form Status', 'smart-form-builder-by-dragwyb'),
            'options' => [
                'draft'   => __('Draft', 'smart-form-builder-by-dragwyb'),
                'publish'  => __('Published', 'smart-form-builder-by-dragwyb'),
            ],
            'default' => sanitize_text_field($form_status),
            'label_inline' => true,
        ]);

        $this->end_section();

        // Performance & Behavior
        $this->start_section('performance', [
            'label' => __('Performance & Behavior', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('ajax_submit', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Enable AJAX Submission', 'smart-form-builder-by-dragwyb'),
            'default' => 'yes',
            'description' => __('Submit form without reloading the page.', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('reset_after_submit', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Reset Form After Submit', 'smart-form-builder-by-dragwyb'),
            'default' => 'yes',
        ]);

        $this->add_control('save_progress', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Auto-Save Progress (Local)', 'smart-form-builder-by-dragwyb'),
            'default' => 'no',
            'description' => __('Saves inputs to browser storage so data isn\'t lost on refresh.', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->end_section();

        // Form Restrictions (Great for Contests)
        $this->start_section('restrictions', [
            'label' => __('Restrictions', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('limit_entries', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Limit Number of Entries', 'smart-form-builder-by-dragwyb'),
            'default' => 'no',
        ]);

        $this->add_control('max_entries', [
            'type'    => Controls::NUMBER,
            'label'   => __('Max Entries Allowed', 'smart-form-builder-by-dragwyb'),
            'default' => 100,
            'min'     => 1,
            'conditions' => [
                'limit_entries' => true
            ]
        ]);

        $this->add_control('limit_message', [
            'type'    => Controls::TEXTAREA,
            'label'   => __('Message when limit reached', 'smart-form-builder-by-dragwyb'),
            'default' => __('This form is no longer accepting submissions.', 'smart-form-builder-by-dragwyb'),
            'conditions' => [
                'limit_entries' => true
            ]
        ]);

        $this->end_section();

        // Security & Privacy
        $this->start_section('security', [
            'label' => __('Security & Privacy', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('honeypot', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Enable Honeypot (Anti-Spam)', 'smart-form-builder-by-dragwyb'),
            'default' => 'yes',
            'description' => __('Adds an invisible field to trap bots.', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('recaptcha', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Enable reCAPTCHA', 'smart-form-builder-by-dragwyb'),
            'default' => 'no',
            'description' => __('Requires API Keys in Global Settings.', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->end_section();
    }
}
