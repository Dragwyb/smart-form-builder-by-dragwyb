<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Rest_Routes;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Dragwyb_Settings_Route
 *
 * Handles the REST API routes for plugin settings dashboard operations.
 */
class Dragwyb_Settings_Route
{
    /**
     * Default settings structure
     */
    private array $default_settings = [
        'integrations' => [
            'recaptcha_v2_site_key'   => '',
            'recaptcha_v2_secret_key' => '',
            'recaptcha_v3_site_key'   => '',
            'recaptcha_v3_secret_key' => '',
            'hcaptcha_site_key'       => '',
            'hcaptcha_secret_key'     => '',
        ],
        'performance' => [
            'load_font_awesome' => 'yes',
            'load_svg_icons'    => 'no',
            'load_default_css'  => 'yes',
        ],
        'fields_manager' => [
            'field_text'     => true,
            'field_email'    => true,
            'field_textarea' => true,
            'field_select'   => true,
            'field_radio'    => true,
            'field_checkbox' => true,
            'field_number'   => true,
            'field_hidden'   => true,
            'field_date'     => true,
            'field_time'     => true,
            'field_phone'    => true,
            'field_url'      => true,
            'field_name'     => true,
            'field_address'  => true,
            'field_range'    => true,
            'field_file'     => true,
            'field_captcha'  => true,
            'field_row'      => true,
            'field_section'  => true,
            'field_html'     => true,
            'field_button'   => true,
        ]
    ];

    /**
     * Dragwyb_Settings_Route constructor.
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes.
     */
    public function register_routes(): void
    {
        register_rest_route('dragwyb/v1', '/settings', [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_settings'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'update_settings'],
                'permission_callback' => [$this, 'permissions_check'],
            ],
        ]);
    }

    /**
     * Check permissions for the settings endpoints.
     *
     * @return bool|\WP_Error
     */
    public function permissions_check()
    {
        if (!current_user_can('manage_options')) {
            return new \WP_Error('rest_forbidden', __('You do not have permissions to manage settings.', 'smart-form-builder-by-dragwyb'), ['status' => rest_authorization_required_code()]);
        }
        return true;
    }

    /**
     * Retrieve settings.
     *
     * @return \WP_REST_Response
     */
    public function get_settings(): \WP_REST_Response
    {
        $settings = get_option('dragwyb_form_settings', []);

        // Merge with defaults to ensure structure is intact
        $merged_settings = wp_parse_args($settings, $this->default_settings);
        foreach ($this->default_settings as $tab => $keys) {
            if (isset($settings[$tab]) && is_array($settings[$tab])) {
                $merged_settings[$tab] = wp_parse_args($settings[$tab], $keys);
            }
        }

        return rest_ensure_response([
            'status' => 'success',
            'data'   => $merged_settings,
        ]);
    }

    /**
     * Update settings.
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response|\WP_Error
     */
    public function update_settings(\WP_REST_Request $request)
    {
        $params = $request->get_json_params();

        if (empty($params) || !is_array($params)) {
            return new \WP_Error('rest_invalid_data', __('Invalid data provided.', 'smart-form-builder-by-dragwyb'), ['status' => 400]);
        }

        $sanitized_settings = [];

        // Sanitize Integrations Tab
        if (isset($params['integrations']) && is_array($params['integrations'])) {
            $sanitized_settings['integrations'] = [];
            foreach ($this->default_settings['integrations'] as $key => $default) {
                $sanitized_settings['integrations'][sanitize_key($key)] = isset($params['integrations'][$key]) ? sanitize_text_field($params['integrations'][$key]) : $default;
            }
        }

        // Sanitize Performance Tab
        if (isset($params['performance']) && is_array($params['performance'])) {
            $sanitized_settings['performance'] = [];
            foreach ($this->default_settings['performance'] as $key => $default) {
                // Ensure only allowed values for performance settings, or fallback to default
                $val = isset($params['performance'][$key]) ? sanitize_text_field($params['performance'][$key]) : $default;
                $sanitized_settings['performance'][sanitize_key($key)] = in_array($val, ['yes', 'no', 'svg'], true) ? $val : $default;
            }
        }

        // Sanitize Fields Manager Tab
        if (isset($params['fields_manager']) && is_array($params['fields_manager'])) {
            $sanitized_settings['fields_manager'] = [];
            foreach ($this->default_settings['fields_manager'] as $key => $default) {
                $sanitized_settings['fields_manager'][sanitize_key($key)] = isset($params['fields_manager'][$key]) ? filter_var($params['fields_manager'][$key], FILTER_VALIDATE_BOOLEAN) : $default;
            }
        }

        // Save to wp_options
        update_option('dragwyb_form_settings', $sanitized_settings);

        return rest_ensure_response([
            'status'  => 'success',
            'message' => __('Settings saved successfully.', 'smart-form-builder-by-dragwyb'),
            'data'    => $sanitized_settings,
        ]);
    }
}
