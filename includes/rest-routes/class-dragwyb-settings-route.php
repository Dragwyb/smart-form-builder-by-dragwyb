<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Rest_Routes;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Dragwyb\Form_Builder\Admin\Settings\Settings_Manager;
use Dragwyb\Form_Builder\Includes\Mailer\Dragwyb_Mailer;
use Dragwyb\Form_Builder\Admin\Db\Analytics\Dragwyb_Analytics_Db;
use Dragwyb\Form_Builder\Admin\Db\Submission\Dragwyb_Submission_Db;

/**
 * Class Dragwyb_Settings_Route
 *
 * Handles the REST API routes for plugin settings dashboard operations.
 */
class Dragwyb_Settings_Route {

	/**
	 * Default settings structure
	 */
	private array $default_settings = array();

	/**
	 * Dragwyb_Settings_Route constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register REST API routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			'dragwyb/v1',
			'/settings',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'update_settings' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			'dragwyb/v1',
			'/settings/test-email',
			array(
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'send_test_email' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);

		register_rest_route(
			'dragwyb/v1',
			'/templates',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_templates' ),
					'permission_callback' => array( $this, 'permissions_check_templates' ),
				),
			)
		);

		register_rest_route(
			'dragwyb/v1',
			'/analytics',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_analytics' ),
					'permission_callback' => array( $this, 'permissions_check' ),
				),
			)
		);
	}

	/**
	 * Check permissions for the settings endpoints.
	 *
	 * @return bool|\WP_Error
	 */
	public function permissions_check() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You do not have permissions to manage settings.', 'smart-form-builder-by-dragwyb' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Check permissions for template library endpoint.
	 *
	 * @return bool|\WP_Error
	 */
	public function permissions_check_templates() {
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'manage_options' ) ) {
			return new \WP_Error( 'rest_forbidden', __( 'You do not have permissions to view templates.', 'smart-form-builder-by-dragwyb' ), array( 'status' => rest_authorization_required_code() ) );
		}
		return true;
	}

	/**
	 * Retrieve settings.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_settings(): \WP_REST_Response {
		if ( ! isset( $this->default_settings ) || empty( $this->default_settings ) ) {
			$this->default_settings = Settings_Manager::instance()->get_all_settings();
		}

		return rest_ensure_response(
			array(
				'status' => 'success',
				'data'   => $this->default_settings,
			)
		);
	}

	/**
	 * Update settings.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function update_settings( \WP_REST_Request $request ) {
		$params = $request->get_json_params();

		if ( empty( $params ) || ! is_array( $params ) ) {
			return new \WP_Error( 'rest_invalid_data', __( 'Invalid data provided.', 'smart-form-builder-by-dragwyb' ), array( 'status' => 400 ) );
		}

		$sanitized_settings = array();

		if ( ! isset( $this->default_settings ) || empty( $this->default_settings ) ) {
			$this->default_settings = Settings_Manager::instance()->get_all_settings();
		}

		$default_settings  = $this->default_settings;
		$existing_settings = get_option( 'dragwyb_form_settings', array() );

		$settings_types = array( 'integrations', 'performance', 'fields_manager', 'smtp', 'gdpr_privacy' );
		// Sanitize Integrations Tab

		foreach ( $settings_types as $setting_type ) {
			if ( isset( $params[ $setting_type ] ) && is_array( $params[ $setting_type ] ) ) {
				$sanitized_settings[ $setting_type ] = array();
				foreach ( $this->default_settings[ $setting_type ] as $key => $default ) {
					$current_default_settings = &$default_settings[ $setting_type ][ $key ];
					$current_param_settings   = isset( $params[ $setting_type ][ $key ] ) ? $params[ $setting_type ][ $key ] : array();

					if ( ! empty( $current_param_settings ) ) {
						if ( isset( $current_default_settings['value'] ) && isset( $current_param_settings['value'] ) && $current_default_settings['value'] === $current_param_settings['value'] && isset( $existing_settings[ $setting_type ][ $key ] ) ) {
							$current_param_settings['value'] = $existing_settings[ $setting_type ][ $key ];
						}

						$this->set_sanitized_value( $key, $current_param_settings, $current_default_settings, $sanitized_settings[ $setting_type ] );
					}
				}
			}
		}

		// Save to wp_options
		update_option( 'dragwyb_form_settings', $sanitized_settings );

		return rest_ensure_response(
			array(
				'status'  => 'success',
				'message' => __( 'Settings saved successfully.', 'smart-form-builder-by-dragwyb' ),
				'data'    => $default_settings,
			)
		);
	}

	private function set_sanitized_value( $key, $value, &$data, &$settings ) {
		if ( ! isset( $data['type'] ) || ! isset( $value['value'] ) ) {
			return;
		}

		$value = $value['value'];

		if ( isset( $data['valid_values'] ) ) {
			if ( in_array( $value, $data['valid_values'], true ) ) {
				$settings[ $key ] = $value;
			}
		} else {
			$value_type       = $data['type'];
			$sanitized_value  = $this->get_sanitized_value( $value_type, $value );
			$settings[ $key ] = $sanitized_value;

			if ( isset( $data['mask'] ) && $data['mask'] === true ) {
				$data['value'] = Settings_Manager::mask_api_key( $sanitized_value );
			} else {
				$data['value'] = $sanitized_value;
			}
		}
	}

	private function get_sanitized_value( $type, $value ) {
		if ( $type === 'bool' ) {
			return (bool) $value;
		} elseif ( $type === 'number' ) {
			return (int) $value;
		} else {
			return sanitize_text_field( $value );
		}
	}

	/**
	 * Load and return prebuilt form templates from JSON files.
	 *
	 * @return \WP_REST_Response
	 */
	public function get_templates(): \WP_REST_Response {
		$base_path = DRAGWYB_FORM_BUILDER_PATH . 'admin/templates/';

		$templates = array(
			'contact'   => array(),
			'bussiness' => array(),
			'marketing' => array(),
			'feedback'  => array(),
		);

		foreach ( array_keys( $templates ) as $type ) {
			$file_path = $base_path . $type . '.json';
			if ( file_exists( $file_path ) ) {
				$content   = file_get_contents( $file_path );
				$json_data = json_decode( $content, true );
				if ( is_array( $json_data ) ) {
					$templates[ $type ] = $json_data;
				}
			}
		}

		return rest_ensure_response(
			array(
				'status' => 'success',
				'data'   => $templates,
			)
		);
	}

	/**
	 * Send test email via REST API.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function send_test_email( \WP_REST_Request $request ): \WP_REST_Response {
		$params = $request->get_json_params();
		$email  = sanitize_email( (string) ( $params['email'] ?? '' ) );

		if ( empty( $email ) || ! is_email( $email ) ) {
			return rest_ensure_response(
				array(
					'status'  => 'error',
					'message' => __( 'Invalid email address provided.', 'smart-form-builder-by-dragwyb' ),
				)
			);
		}

		$result = Dragwyb_Mailer::send_test_email( $email );

		if ( ! empty( $result['success'] ) ) {
			return rest_ensure_response(
				array(
					'status'  => 'success',
					'message' => __( 'Test email sent successfully.', 'smart-form-builder-by-dragwyb' ),
				)
			);
		}

		if ( ! empty( $result['message'] ) ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( 'Dragwyb send test email error: ' . $result['message'] );
		}

		return rest_ensure_response(
			array(
				'status'  => 'error',
				'message' => __( 'Failed to send test email. Please verify your SMTP settings and server logs.', 'smart-form-builder-by-dragwyb' ),
			)
		);
	}

	/**
	 * Retrieve analytics statistics based on filter range.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get_analytics( \WP_REST_Request $request ): \WP_REST_Response {
		$range = sanitize_key( (string) ( $request->get_param( 'range' ) ?: '1' ) );

		if ( ! in_array( $range, array( '1', '7', '30', '-1' ), true ) ) {
			return rest_ensure_response(
				array(
					'status'  => 'error',
					'message' => __( 'Invalid range provided.', 'smart-form-builder-by-dragwyb' ),
				)
			);
		}

		global $wpdb;

		$date_clause = '';
		$days        = 0;

		switch ( $range ) {
			case '1':
				$date_clause = 'WHERE DATE(created_at) = CURDATE()';
				$days        = 0;
				break;
			case '7':
				$date_clause = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)';
				$days        = 7;
				break;
			case '30':
				$date_clause = 'WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';
				$days        = 30;
				break;
			case '-1':
			default:
				$date_clause = '';
				$days        = 1;
				break;
		}

		// Check submissions table count
		$sub_count         = 0;
		$submissions_table = Dragwyb_Submission_Db::table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$sub_count = (int) $wpdb->get_var( "SELECT COUNT(id) FROM $submissions_table $date_clause" );

		$base_submissions = $sub_count > 0 ? $sub_count : 0;

		$visitors_table  = Dragwyb_Analytics_Db::table_name( 'visitors' );
		$sessions_table  = Dragwyb_Analytics_Db::table_name( 'sessions' );
		$pageviews_table = Dragwyb_Analytics_Db::table_name( 'pageviews' );

		// Unique visitors
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$visitors_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $visitors_table" . $this->get_date_where_clause( $range, 'first_seen' ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			)
		);

		// Total sessions
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$sessions_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $sessions_table" . $this->get_date_where_clause( $range, 'started_at' ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			)
		);

		// Total pageviews
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$pageviews_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $pageviews_table" . $this->get_date_where_clause( $range, 'created_at' ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			)
		);

		return rest_ensure_response(
			array(
				'status' => 'success',
				'data'   => array(
					'range'            => $range,
					'totalSubmissions' => number_format( $base_submissions ),
					'uniqueVisitors'   => number_format( $visitors_count ),
					'totalSessions'    => number_format( $sessions_count ),
					'totalPageViews'   => number_format( $pageviews_count ),
				),
			)
		);
	}

	private function get_date_where_clause( string $range, string $from = 'first_seen' ) {
		if ( $range === '-1' ) {
			return ' WHERE 1 = %s';
		}

		if ( ! in_array( $range, array( '1', '7', '30' ), true ) ) {
			return '';
		}

		if ( ! in_array( $from, array( 'first_seen', 'created_at', 'started_at' ), true ) ) {
			return '';
		}

		if ( $range === '1' ) {
			return " WHERE DATE($from) = CURDATE()";
		}

		return " WHERE $from >= DATE_SUB(NOW(), INTERVAL %s DAY)";
	}
}
