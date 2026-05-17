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
        $this->category = 'advanced-fields';
        $this->keywords = array('security', 'spam', 'recaptcha', 'hcaptcha', 'bot');
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
            'description' => __('API Keys are configured in Global Settings > Integrations.', 'smart-form-builder-by-dragwyb'),
            'label_inline' => true
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
        $classes      = $this->field_key_exist($settings, 'css_classes', '');

        $settings_manager = \Dragwyb\Form_Builder\Admin\Settings\Settings_Manager::instance();

        if ($captcha_type === 'recaptcha_v2') {
            $site_key = $settings_manager->get_api_key('recaptcha_v2_site_key');
        } elseif ($captcha_type === 'recaptcha_v3') {
            $site_key = $settings_manager->get_api_key('recaptcha_v3_site_key');
        } elseif ($captcha_type === 'hcaptcha') {
            $site_key = $settings_manager->get_api_key('hcaptcha_site_key');
        } else {
            $site_key = '';
        }

        if ($captcha_type === 'recaptcha_v2' && !empty($site_key)) {
            wp_enqueue_script('google-recaptcha-v2', 'https://www.google.com/recaptcha/api.js', array(), null, true);
        } elseif ($captcha_type === 'recaptcha_v3' && !empty($site_key)) {
            wp_enqueue_script('google-recaptcha-v3', 'https://www.google.com/recaptcha/api.js?render=' . esc_attr($site_key), array(), null, true);
        } elseif ($captcha_type === 'hcaptcha' && !empty($site_key)) {
            wp_enqueue_script('hcaptcha', 'https://js.hcaptcha.com/1/api.js', array(), null, true);
        }
?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?>">
            <div class="dragwyb-captcha-container" data-type="<?php echo esc_attr($captcha_type); ?>" data-sitekey="<?php echo esc_attr($site_key); ?>" id="<?php echo esc_attr($field_id); ?>_container">
                <?php if (empty($site_key)) : ?>
                    <div style="padding:10px; border:1px dashed red; color:red;">
                        <?php esc_html_e('Captcha Error: Site Key is missing. Please configure it in the Global Settings.', 'smart-form-builder-by-dragwyb'); ?>
                    </div>
                <?php else : ?>
                    <?php if ($captcha_type === 'recaptcha_v2') : ?>
                        <div class="g-recaptcha" data-sitekey="<?php echo esc_attr($site_key); ?>" data-callback="dragwyb_recaptcha_callback_<?php echo esc_js($field_id); ?>"></div>
                        <input type="hidden" name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>_input" class="dragwyb-captcha-input" value="">
                        <script>
                            function dragwyb_recaptcha_callback_<?php echo esc_js($field_id); ?>(response) {
                                var input = document.getElementById('<?php echo esc_js($field_id); ?>_input');
                                if (input) {
                                    input.value = response;
                                }
                            }
                        </script>
                    <?php elseif ($captcha_type === 'hcaptcha') : ?>
                        <div class="h-captcha" data-sitekey="<?php echo esc_attr($site_key); ?>" data-callback="dragwyb_hcaptcha_callback_<?php echo esc_js($field_id); ?>"></div>
                        <input type="hidden" name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>_input" class="dragwyb-captcha-input" value="">
                        <script>
                            function dragwyb_hcaptcha_callback_<?php echo esc_js($field_id); ?>(response) {
                                var input = document.getElementById('<?php echo esc_js($field_id); ?>_input');
                                if (input) {
                                    input.value = response;
                                }
                            }
                        </script>
                    <?php elseif ($captcha_type === 'recaptcha_v3') : ?>
                        <input type="hidden" name="<?php echo esc_attr($field_id); ?>" id="<?php echo esc_attr($field_id); ?>_input" class="dragwyb-captcha-input" value="">
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                if (typeof grecaptcha !== 'undefined') {
                                    grecaptcha.ready(function() {
                                        grecaptcha.execute('<?php echo esc_js($site_key); ?>', {
                                            action: 'submit'
                                        }).then(function(token) {
                                            var input = document.getElementById('<?php echo esc_js($field_id); ?>_input');
                                            if (input) {
                                                input.value = token;
                                            }
                                        });
                                    });
                                }
                            });
                        </script>
                    <?php else : ?>
                        <div class="dragwyb-captcha-placeholder" style="background:#f9f9f9; border:1px solid #ddd; padding:15px; display:inline-block;">
                            [ <?php echo esc_html($captcha_type); ?> Placeholder ]
                        </div>
                    <?php endif; ?>
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
        $captcha_type = isset($field_attr['captcha_type']) ? $field_attr['captcha_type'] : 'recaptcha_v2';

        $settings_manager = \Dragwyb\Form_Builder\Admin\Settings\Settings_Manager::instance();

        if ($captcha_type === 'recaptcha_v2') {
            $secret_key = $settings_manager->get_api_key('recaptcha_v2_secret_key');
        } elseif ($captcha_type === 'recaptcha_v3') {
            $secret_key = $settings_manager->get_api_key('recaptcha_v3_secret_key');
        } elseif ($captcha_type === 'hcaptcha') {
            $secret_key = $settings_manager->get_api_key('hcaptcha_secret_key');
        } else {
            $secret_key = '';
        }

        if (empty($secret_key)) {
            // Can't validate without secret key.
            return;
        }

        if (empty($value)) {
            $error_handler->add_error($field_id, __('Please complete the captcha verification.', 'smart-form-builder-by-dragwyb'));
            return;
        }

        switch ($captcha_type) {
            case 'recaptcha_v2':
            case 'recaptcha_v3':
                $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
                $response = wp_remote_post($verify_url, [
                    'body' => [
                        'secret'   => $secret_key,
                        'response' => sanitize_text_field($value),
                        'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
                    ]
                ]);

                if (is_wp_error($response)) {
                    $error_handler->add_error($field_id, __('Unable to connect to Captcha server. Please try again later.', 'smart-form-builder-by-dragwyb'));
                    return;
                }

                $body = wp_remote_retrieve_body($response);
                $result = json_decode($body);

                if (!$result || empty($result->success)) {
                    $error_handler->add_error($field_id, __('Captcha verification failed. Please try again.', 'smart-form-builder-by-dragwyb'));
                } elseif ($captcha_type === 'recaptcha_v3') {
                    if (isset($result->score) && $result->score < 0.5) {
                        $error_handler->add_error($field_id, __('Captcha verification failed. Suspicious activity detected.', 'smart-form-builder-by-dragwyb'));
                    }
                }
                break;

            case 'hcaptcha':
                $verify_url = 'https://hcaptcha.com/siteverify';
                $response = wp_remote_post($verify_url, [
                    'body' => [
                        'secret'   => $secret_key,
                        'response' => sanitize_text_field($value),
                        'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '',
                    ]
                ]);

                if (is_wp_error($response)) {
                    $error_handler->add_error($field_id, __('Unable to connect to Captcha server. Please try again later.', 'smart-form-builder-by-dragwyb'));
                    return;
                }

                $body = wp_remote_retrieve_body($response);
                $result = json_decode($body);

                if (!$result || empty($result->success)) {
                    $error_handler->add_error($field_id, __('Captcha verification failed. Please try again.', 'smart-form-builder-by-dragwyb'));
                }
                break;

            default:
                // Other captchas are not implemented yet.
                break;
        }
    }

    public function sanitize($default = '', $value = null)
    {
        if ($value && is_string($value)) {
            return sanitize_text_field($value);
        }
        return sanitize_text_field($default);
    }
}
