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
            'label' => __('Form Identity', 'dragwyb-form-builder'),
        ]);

        $this->add_control('form_name', [
            'type'    => Controls::TEXT,
            'label'   => __('Form Name', 'dragwyb-form-builder'),
            'default' => sanitize_text_field($form_title),
        ]);

        $this->add_control('form_id', [
            'type'    => Controls::TEXT,
            'label'   => __('Form ID', 'dragwyb-form-builder'),
            'default' => sanitize_text_field($form_id),
            'description' => __('Unique identifier (auto-generated). Change only if required.', 'dragwyb-form-builder'),
        ]);

        $this->add_control('form_status', [
            'type'    => Controls::SELECT,
            'label'   => __('Form Status', 'dragwyb-form-builder'),
            'options' => [
                'draft'   => __('Draft', 'dragwyb-form-builder'),
                'publish'  => __('Published', 'dragwyb-form-builder'),
            ],
            'default' => sanitize_text_field($form_status),
            'label_inline' => true,
        ]);

        $this->end_section();

        // 🔹 Display Options
        $this->start_section('display_options', [
            'label' => __('Display Options', 'dragwyb-form-builder'),
        ]);

        $this->add_control('enable_popup', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Open in Popup', 'dragwyb-form-builder'),
            'default' => 'no',
        ]);

        $this->add_control('multi_step', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Enable Multi-Step Form', 'dragwyb-form-builder'),
            'default' => 'no',
        ]);

        $this->end_section();

        // 🔹 Performance & Behavior
        $this->start_section('performance', [
            'label' => __('Performance & Behavior', 'dragwyb-form-builder'),
        ]);

        $this->add_control('ajax_submit', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Enable AJAX Submission', 'dragwyb-form-builder'),
            'default' => 'no',
        ]);

        $this->add_control('save_progress', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Save & Continue Later', 'dragwyb-form-builder'),
            'default' => 'no',
        ]);

        $this->end_section();

        // 🔹 Security & Privacy
        $this->start_section('security', [
            'label' => __('Security & Privacy', 'dragwyb-form-builder'),
        ]);

        $this->add_control('gdpr_consent', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Enable GDPR Consent Field', 'dragwyb-form-builder'),
            'default' => false,
        ]);

        $this->add_control('honeypot', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Enable Honeypot Protection', 'dragwyb-form-builder'),
            'default' => 'no',
        ]);

        $this->add_control('recaptcha', [
            'type'    => Controls::SWITCHER,
            'label'   => __('Enable reCAPTCHA (if configured)', 'dragwyb-form-builder'),
            'default' => 'no',
        ]);

        $this->end_section();
    }
}
