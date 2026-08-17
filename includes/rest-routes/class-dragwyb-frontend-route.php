<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Rest_Routes;

use Dragwyb\Form_Builder\Includes\Core\Helpers;
use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbars;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Dragwyb_Frontend_Route
 *
 * Handles the REST API routes for frontend operations, such as form submissions.
 *
 * @package Dragwyb\Form_Builder\Includes\Rest_Routes
 */
class Dragwyb_Frontend_Route {


	/**
	 * Dragwyb_Frontend_Route constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers the REST API routes.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			'dragwyb-form-builder/v1',
			'/submit',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'submit_form' ),
				'permission_callback' => array( $this, 'verify_submission_permission' ),
				'args'                => $this->get_endpoint_args(),
			)
		);
	}

	/**
	 * Verify frontend submission requests.
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 * @return bool|\WP_Error
	 */
	public function verify_submission_permission( \WP_REST_Request $request ) {
		$nonce   = $request->get_param( 'nonce' );
		$form_id = absint( $request->get_param( 'form_id' ) );
		$action  = Frontend_Render::get_submission_key( $form_id );

		if ( empty( $nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), $action ) ) {
			return new \WP_Error(
				'rest_forbidden',
				__( 'Invalid submission token.', 'smart-form-builder-by-dragwyb' ),
				array( 'status' => 403 )
			);
		}

		$post = get_post( $form_id );
		if ( ! $post || $post->post_type !== \Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post::POST_TYPE || $post->post_status !== 'publish' ) {
			return new \WP_Error(
				'invalid_form',
				__( 'Invalid form ID.', 'smart-form-builder-by-dragwyb' ),
				array( 'status' => 400 )
			);
		}

		return true;
	}

	/**
	 * Retrieves the endpoint arguments for the form submission route.
	 *
	 * @return array The endpoint arguments.
	 */
	public function get_endpoint_args(): array {
		return array(
			'form_id' => array(
				'description'       => __( 'The ID of the form being submitted.', 'smart-form-builder-by-dragwyb' ),
				'type'              => 'integer',
				'required'          => true,
				'sanitize_callback' => 'absint',
				'validate_callback' => function ( $param, $request, $key ) {
					return is_numeric( $param ) && $param > 0;
				},
			),
			'fields'  => array(
				'description'       => __( 'The submitted form fields data.', 'smart-form-builder-by-dragwyb' ),
				'type'              => 'object',
				'required'          => true,
				'validate_callback' => function ( $param, $request, $key ) {
					return is_array( $param );
				},
			),
		);
	}

	/**
	 * Handles the form submission REST API request.
	 *
	 * @param \WP_REST_Request $request The REST API request object.
	 *
	 * @return \WP_REST_Response|\WP_Error The response or error object.
	 */
	public function submit_form( \WP_REST_Request $request ) {
		$form_id = $request->get_param( 'form_id' );
		$fields  = $request->get_param( 'fields' );

		if ( ! $form_id ) {
			return new \WP_Error( 'invalid_form', __( 'Invalid form ID.', 'smart-form-builder-by-dragwyb' ), array( 'status' => 400 ) );
		}

		if ( defined( 'DRAGWYB_FORM_SUBMISSION_REQUEST' ) ) {
			return new \WP_Error( 'form_submission_already_running', __( 'Form submission is already running.', 'smart-form-builder-by-dragwyb' ), array( 'status' => 400 ) );
		}

		define( 'DRAGWYB_FORM_SUBMISSION_REQUEST', true );

		$form_id = intval( $form_id );

		if ( $request->get_param( 'screen_resolution' ) ) {
			$_POST['screen_resolution'] = sanitize_text_field( (string) $request->get_param( 'screen_resolution' ) );
		}
		if ( $request->get_param( 'current_page_url' ) ) {
			$_POST['current_page_url'] = sanitize_text_field( (string) $request->get_param( 'current_page_url' ) );
		}
		if ( $request->get_param( 'referrer_url' ) ) {
			$_POST['referrer_url'] = sanitize_text_field( (string) $request->get_param( 'referrer_url' ) );
		}
		if ( $request->get_param( 'time_to_submit' ) ) {
			$_POST['time_to_submit'] = sanitize_text_field( (string) $request->get_param( 'time_to_submit' ) );
		}
		if ( $request->get_param( 'visitor_journey' ) ) {
			$_POST['visitor_journey'] = $request->get_param( 'visitor_journey' );
		}

		if ( is_array( $fields ) ) {
			foreach ( $fields as $field_item ) {
				if ( is_array( $field_item ) && isset( $field_item['name'], $field_item['value'] ) ) {
					if ( 'session_uid' === $field_item['name'] ) {
						$_POST['session_uid'] = sanitize_text_field( (string) $field_item['value'] );
					} elseif ( 'session_id' === $field_item['name'] ) {
						$_POST['session_id'] = sanitize_text_field( (string) $field_item['value'] );
					} elseif ( 'user_id' === $field_item['name'] ) {
						$_POST['user_id'] = sanitize_text_field( (string) $field_item['value'] );
					} elseif ( 'user_session' === $field_item['name'] ) {
						$_POST['user_session'] = sanitize_text_field( (string) $field_item['value'] );
					}
				}
			}
		}

		// Instantiate the submission handler
		$handler = new Form_Submission_Handler(
			$form_id,
			(array) $fields,
			Frontend_Render::instance(),
			Toolbars::instance()
		);
		$handler->handle();

		$handler_errors = $handler->get_errors();
		// If there are validation errors, return them as a JSON response
		if ( $handler->has_errors() ) {
			// Convert WP_Error to a flat array to preserve the API response format.
			$errors_array = array();
			foreach ( $handler_errors->get_error_codes() as $code ) {
				$errors_array[ $code ] = $handler_errors->get_error_message( $code );
			}

			return rest_ensure_response(
				array(
					'success' => false,
					'message' => $handler_errors->get_error_message( 'honeypot' ) ? $handler_errors->get_error_message( 'honeypot' ) : __( 'Form submission failed due to validation errors.', 'smart-form-builder-by-dragwyb' ),
					'errors'  => $errors_array,
				)
			);
		}

		// Return a successful JSON response
		return rest_ensure_response(
			array(
				'success' => true,
				'message' => __( 'Form submitted successfully.', 'smart-form-builder-by-dragwyb' ),
				'data'    => array(
					'form_id'      => $form_id,
					'actions_data' => $handler->get_form_return_data(),
				),
			)
		);
	}
}
