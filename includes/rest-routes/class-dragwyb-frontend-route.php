<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Rest_Routes;

use Dragwyb\Form_Builder\Includes\Core\Helpers;
use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

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
            return new \WP_Error('invalid_form', __('Invalid form ID.', 'smart-form-builder-by-dragwyb'), ['status' => 400]);
        }

        if (defined('DRAGWYB_FORM_SUBMISSION_REQUEST')) {
            return new \WP_Error('form_submission_already_running', __('Form submission is already running.', 'smart-form-builder-by-dragwyb'), ['status' => 400]);
        }

        define('DRAGWYB_FORM_SUBMISSION_REQUEST', true);

        $form_id = intval($form_id);

        // Instantiate the submission handler
        $handler = new Form_Submission_Handler($form_id, (array) $fields);

        // If there are validation errors, return them as a JSON response
        if ($handler->has_errors()) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Form submission failed due to validation errors.', 'smart-form-builder-by-dragwyb'),
                'errors'  => $handler->get_errors(),
            ]);
        }

        // Return a successful JSON response
        return rest_ensure_response(
            [
                'success' => true,
                'message' => __('Form submitted successfully.', 'smart-form-builder-by-dragwyb'),
                'data'    => [
                    'form_id'      => $form_id,
                    'actions_data' => $handler->get_form_return_data(),
                ],
            ]
        );
    }
}
