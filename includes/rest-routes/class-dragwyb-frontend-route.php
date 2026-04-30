<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Rest_Routes;

use Dragwyb\Form_Builder\Includes\Core\Helpers;
use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Dragwyb_Frontend_Route
 *
 * Handles the REST API routes for frontend operations, such as form submissions.
 *
 * @package Dragwyb\Form_Builder\Includes\Rest_Routes
 */
class Dragwyb_Frontend_Route
{

    /**
     * Dragwyb_Frontend_Route constructor.
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Registers the REST API routes.
     *
     * @return void
     */
    public function register_routes(): void
    {
        register_rest_route(
            'dragwyb-form-builder/v1',
            '/submit',
            [
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'submit_form'],
                'permission_callback' => '__return_true', // @todo: Update this based on specific security requirements.
                'args'                => $this->get_endpoint_args(),
            ]
        );
    }

    /**
     * Retrieves the endpoint arguments for the form submission route.
     *
     * @return array The endpoint arguments.
     */
    public function get_endpoint_args(): array
    {
        return [
            'form_id' => [
                'description'       => __('The ID of the form being submitted.', 'smart-form-builder-by-dragwyb'),
                'type'              => 'integer',
                'required'          => true,
                'sanitize_callback' => 'absint',
                'validate_callback' => function ($param, $request, $key) {
                    return is_numeric($param) && $param > 0;
                },
            ],
            'fields'  => [
                'description' => __('The submitted form fields data.', 'smart-form-builder-by-dragwyb'),
                'type'        => 'object',
                'required'    => true,
                'validate_callback' => function ($param, $request, $key) {
                    return is_array($param);
                },
            ],
        ];
    }

    /**
     * Handles the form submission REST API request.
     *
     * @param \WP_REST_Request $request The REST API request object.
     *
     * @return \WP_REST_Response|\WP_Error The response or error object.
     */
    public function submit_form(\WP_REST_Request $request)
    {
        $form_id = $request->get_param('form_id');
        $fields  = $request->get_param('fields');

        // Add basic security checks like nonce validation if needed
        // check_ajax_referer('dragwyb_frontend', 'nonce');

        if (!$form_id) {
            wp_send_json_error(['message' => __('Invalid form ID.', 'smart-form-builder-by-dragwyb')]);
        }

        $form_id = intval($form_id);

        $form_data = Frontend_Render::instance();
        $form_data->init($form_id);

        $fields_settings = $form_data->get_fields_values();

        var_dump($fields_settings);

        // Allow other plugins/extensions to hook into submission
        do_action('dragwyb/frontend/form/before_submit_processing', $form_id);

        // TODO: Extract values, validate, send emails, save to DB etc.
        // For now, return a placeholder success message.

        $response_message = apply_filters(
            'dragwyb/frontend/form/success_message',
            __('Form submitted successfully!', 'smart-form-builder-by-dragwyb'),
            $form_id
        );

        wp_send_json_success([
            'message' => $response_message
        ]);

        // @todo: Implement the actual form submission logic, e.g., saving data, sending emails, etc.

        return rest_ensure_response(
            [
                'success' => true,
                'message' => __('Form submitted successfully.', 'smart-form-builder-by-dragwyb'),
                'data'    => [
                    'form_id' => $form_id,
                    'fields'  => $fields,
                ],
            ]
        );
    }
}
