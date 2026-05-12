<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Captcha extends Field_Base
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function init(): void
    {
        $this->type = 'captcha';
        $this->name = __('Captcha', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-shield-alt';
    }

    protected function register_field_controls(): void
    {
        $this->start_section('section_content_general', [
            'label' => __('Integration Settings', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_control('captcha_type', [
            'type'    => Controls::SELECT,
            'label'   => __('Captcha Provider', 'smart-form-builder-by-dragwyb'),
            'options' => [
                'recaptcha_v2' => __('Google reCAPTCHA v2', 'smart-form-builder-by-dragwyb'),
                'recaptcha_v3' => __('Google reCAPTCHA v3', 'smart-form-builder-by-dragwyb'),
                'hcaptcha'     => __('hCaptcha', 'smart-form-builder-by-dragwyb'),
            ],
            'default' => 'recaptcha_v2',
        ]);

        $this->add_control('site_key', [
            'type'        => Controls::TEXT,
            'label'       => __('Site Key', 'smart-form-builder-by-dragwyb'),
            'description' => __('Enter the Site Key provided by your captcha provider.', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->add_control('secret_key', [
            'type'        => Controls::TEXT,
            'label'       => __('Secret Key', 'smart-form-builder-by-dragwyb'),
            'description' => __('Enter the Secret Key. This is used for backend validation.', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->end_section();

        $this->start_section('section_style_container', [
            'label' => __('Container Style', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('container_margin', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Margin', 'smart-form-builder-by-dragwyb'),
            'size_units' => ['px', 'em', '%'],
            'selectors'  => ['{{WRAPPER}}' => '--dragwyb-captcha-mt: {{TOP}}{{UNIT}}; --dragwyb-captcha-mr: {{RIGHT}}{{UNIT}}; --dragwyb-captcha-mb: {{BOTTOM}}{{UNIT}}; --dragwyb-captcha-ml: {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_control('container_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'smart-form-builder-by-dragwyb'),
            'size_units' => ['px', 'em', '%'],
            'selectors'  => ['{{WRAPPER}}' => '--dragwyb-captcha-pt: {{TOP}}{{UNIT}}; --dragwyb-captcha-pr: {{RIGHT}}{{UNIT}}; --dragwyb-captcha-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-captcha-pl: {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_section();
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id = $this->get_the_id();
        $field_id     = $this->field_key_exist($settings, 'field_id', uniqid('field_'));
        $captcha_type = $this->field_key_exist($settings, 'captcha_type', 'recaptcha_v2');
        $site_key     = $this->field_key_exist($settings, 'site_key', '');
        $classes      = $this->field_key_exist($settings, 'css_classes', '');
?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?>">
            <div class="dragwyb-captcha-container" data-type="<?php echo esc_attr($captcha_type); ?>" data-sitekey="<?php echo esc_attr($site_key); ?>" id="<?php echo esc_attr($field_id); ?>">
                <?php if (empty($site_key)) : ?>
                    <div style="padding:10px; border:1px dashed red; color:red;">
                        <?php esc_html_e('Captcha Error: Site Key is missing. Please configure it in the field settings.', 'smart-form-builder-by-dragwyb'); ?>
                    </div>
                <?php else : ?>
                    <!-- Captcha will be rendered here via JS based on type and site_key -->
                    <div class="dragwyb-captcha-placeholder" style="background:#f9f9f9; border:1px solid #ddd; padding:15px; display:inline-block;">
                        [ <?php echo esc_html($captcha_type); ?> Placeholder ]
                    </div>
                <?php endif; ?>
            </div>
        </div>
<?php
    }

    public function validate($value, $field_id, $form_config, Form_Submission_Handler $error_handler): void
    {
        if (!isset($form_config['fields'][$field_id])) {
            $error_handler->add_error($field_id, __('Invalid field.', 'smart-form-builder-by-dragwyb'));
            return;
        }

        $field_attr = isset($form_config['fields'][$field_id]['attributes']) ? $form_config['fields'][$field_id]['attributes'] : array();
        $secret_key = isset($field_attr['secret_key']) ? $field_attr['secret_key'] : '';

        if (empty($secret_key)) {
            // Can't validate without secret key. You could return an error or skip.
            // Skipping for now, assuming if it's empty, validation is disabled.
            return;
        }

        if (empty($value)) {
            $error_handler->add_error($field_id, __('Please complete the captcha verification.', 'smart-form-builder-by-dragwyb'));
            return;
        }

        // The actual API call to Google/hCaptcha would go here.
        // As per the implementation plan, this is stubbed for phase 1.
    }

    public function sanitize($default = '', $value = null)
    {
        if ($value && is_string($value)) {
            return sanitize_text_field($value);
        }
        return sanitize_text_field($default);
    }
}
