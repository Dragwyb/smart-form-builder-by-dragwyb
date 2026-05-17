<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Rest_Routes;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use Dragwyb\Form_Builder\Admin\Settings\Settings_Manager;

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
    private array $default_settings = array();

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
        if (!isset($this->default_settings) || empty($this->default_settings)) {
            $this->default_settings = Settings_Manager::instance()->get_all_settings();
        }

        return rest_ensure_response([
            'status' => 'success',
            'data'   => $this->default_settings,
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

        if (!isset($this->default_settings) || empty($this->default_settings)) {
            $this->default_settings = Settings_Manager::instance()->get_all_settings();
        }

        $default_settings = $this->default_settings;
        $existing_settings = get_option('dragwyb_form_settings', []);

        $settings_types = ['integrations', 'performance', 'fields_manager'];
        // Sanitize Integrations Tab

        foreach ($settings_types as $setting_type) {
            if (isset($params[$setting_type]) && is_array($params[$setting_type])) {
                $sanitized_settings[$setting_type] = [];
                foreach ($this->default_settings[$setting_type] as $key => $default) {
                    $current_default_settings = &$default_settings[$setting_type][$key];
                    $current_param_settings = isset($params[$setting_type][$key]) ? $params[$setting_type][$key] : [];

                    if (!empty($current_param_settings)) {
                        if (isset($current_default_settings['value']) && isset($current_param_settings['value']) && $current_default_settings['value'] === $current_param_settings['value'] && isset($existing_settings[$setting_type][$key])) {
                            $current_param_settings['value'] = $existing_settings[$setting_type][$key];
                        }

                        $this->set_sanitized_value($key, $current_param_settings, $current_default_settings, $sanitized_settings[$setting_type]);
                    }
                }
            }
        }

        // Save to wp_options
        update_option('dragwyb_form_settings', $sanitized_settings);

        return rest_ensure_response([
            'status'  => 'success',
            'message' => __('Settings saved successfully.', 'smart-form-builder-by-dragwyb'),
            'data'    => $default_settings,
        ]);
    }

    private function set_sanitized_value($key, $value, &$data, &$settings)
    {
        if (!isset($data['type']) || !isset($value['value'])) {
            return;
        }

        $value = $value['value'];

        if (isset($data['valid_values'])) {
            if (in_array($value, $data['valid_values'])) {
                $settings[$key] = $value;
            }
        } else {
            $value_type = $data['type'];
            $sanitized_value = $this->get_sanitized_value($value_type, $value);
            $settings[$key] = $sanitized_value;

            if (isset($data['mask']) && $data['mask'] === true) {
                $data['value'] = Settings_Manager::mask_api_key($sanitized_value);
            } else {
                $data['value'] = $sanitized_value;
            }
        }
    }

    private function get_sanitized_value($type, $value)
    {
        if ($type === 'bool') {
            return (bool) $value;
        } elseif ($type === 'number') {
            return (int) $value;
        } else {
            return sanitize_text_field($value);
        }
    }
}
