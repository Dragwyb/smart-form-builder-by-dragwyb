<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\After_Submission\Actions;

use Dragwyb\Form_Builder\Includes\After_Submission\Action_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;
use Dragwyb\Form_Builder\Admin\Db\Submission\Dragwyb_Submission_Db;
use Dragwyb\Form_Builder\Admin\Settings\Settings_Manager;
use Dragwyb\Form_Builder\Admin\Db\Analytics\Dragwyb_Analytics_Db;

class Save_Submissions_Action extends Action_Base {

	public function get_id(): string {
		return 'save_submissions';
	}

	public function get_name(): string {
		return __( 'Save Entry', 'smart-form-builder-by-dragwyb' );
	}

	protected function register_settings(): void {
		$this->start_section(
			'database_section_save_submissions',
			array(
				'label'      => __( 'Save Submissions', 'smart-form-builder-by-dragwyb' ),
				'conditions' => array(
					'after_submissions' => 'save_submissions',
				),
			)
		);

		$this->add_control(
			'save_to_db_save_submissions',
			array(
				'type'        => Controls::SWITCHER,
				'label'       => __( 'Save Entry to Database', 'smart-form-builder-by-dragwyb' ),
				'default'     => 'yes',
				'description' => __( 'View entries in WP Dashboard > Dragwyb > Submissions', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->end_section();
	}

	/**
	 * Get IP address safely.
	 *
	 * @return string
	 */
	private function get_ip_address(): string {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '127.0.0.1';

		// Handle multiple IPs if present
		$ip_array = explode( ',', $ip );
		$ip       = trim( $ip_array[0] );

		return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '127.0.0.1';
	}

	/**
	 * Anonymize IP address according to Privacy & Data Anonymization settings.
	 */
	private function anonymize_ip( string $ip ): string {
		if ( empty( $ip ) || '-' === $ip ) {
			return '-';
		}

		if ( function_exists( 'wp_privacy_anonymize_data' ) ) {
			$anonymized = wp_privacy_anonymize_data( 'ip', $ip );
			if ( ! empty( $anonymized ) ) {
				return $anonymized;
			}
		}

		// Fallback IPv4 mask
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return preg_replace( '/\.\d+$/', '.0', $ip );
		}

		// Fallback IPv6 mask
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			return preg_replace( '/:[^:]+$/', ':0000', $ip );
		}

		return $ip;
	}

	/**
	 * Parse device type from User-Agent.
	 */
	private function parse_device( string $ua ): string {
		if ( empty( $ua ) || '-' === $ua ) {
			return '-';
		}
		if ( preg_match( '/(tablet|ipad|playbook|silk)|(android(?!.*mobi))/i', $ua ) ) {
			return 'Tablet';
		}
		if ( preg_match( '/(mobile|ipod|iphone|blackberry|opera mini|windows phone|palm|iemobile)/i', $ua ) ) {
			return 'Mobile';
		}
		return 'Desktop';
	}

	/**
	 * Parse browser name from User-Agent.
	 */
	private function parse_browser( string $ua ): string {
		if ( empty( $ua ) || '-' === $ua ) {
			return '-';
		}
		if ( preg_match( '/Edg/i', $ua ) ) {
			return 'Edge';
		}
		if ( preg_match( '/Chrome/i', $ua ) ) {
			return 'Chrome';
		}
		if ( preg_match( '/Firefox/i', $ua ) ) {
			return 'Firefox';
		}
		if ( preg_match( '/Safari/i', $ua ) ) {
			return 'Safari';
		}
		if ( preg_match( '/MSIE|Trident/i', $ua ) ) {
			return 'Internet Explorer';
		}
		return 'Other';
	}

	/**
	 * Parse OS name from User-Agent.
	 */
	private function parse_os( string $ua ): string {
		if ( empty( $ua ) || '-' === $ua ) {
			return '-';
		}
		if ( preg_match( '/Windows/i', $ua ) ) {
			return 'Windows';
		}
		if ( preg_match( '/Macintosh|Mac OS X/i', $ua ) ) {
			return 'macOS';
		}
		if ( preg_match( '/Linux/i', $ua ) ) {
			return 'Linux';
		}
		if ( preg_match( '/iPhone|iPad|iPod/i', $ua ) ) {
			return 'iOS';
		}
		if ( preg_match( '/Android/i', $ua ) ) {
			return 'Android';
		}
		return 'Other';
	}

	/**
	 * Parse traffic source from referrer string.
	 */
	private function parse_traffic_source( string $referrer ): string {
		if ( empty( $referrer ) || 'Direct' === $referrer || '-' === $referrer ) {
			return 'Direct';
		}
		$host = wp_parse_url( $referrer, PHP_URL_HOST );
		if ( ! $host ) {
			return 'Direct';
		}
		if ( preg_match( '/(google|bing|yahoo|duckduckgo|baidu|yandex)/i', $host ) ) {
			return 'Organic Search';
		}
		if ( preg_match( '/(facebook|instagram|twitter|t.co|linkedin|pinterest|tiktok|reddit)/i', $host ) ) {
			return 'Social';
		}
		return 'Referral';
	}

	public function process_submission( $form_id, $form_data, $form_config, Form_Submission_Handler $form_submission ) {
		$settings   = $form_config['after-submission'] ?? array();
		$save_to_db = $settings['save_to_db_save_submissions'] ?? 'yes';

		if ( $save_to_db === 'yes' ) {
			$db = new Dragwyb_Submission_Db();

			// Read GDPR & Privacy Anonymization Settings
			$disable_details = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'gdpr_disable_user_details', false );
			$is_disable_det  = true === $disable_details || 'yes' === $disable_details;

			$anonymize_ip    = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'gdpr_anonymize_ip', false );
			$is_anonymize_ip = true === $anonymize_ip || 'yes' === $anonymize_ip;

			$respect_dnt    = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'gdpr_respect_dnt', false );
			$is_respect_dnt = true === $respect_dnt || 'yes' === $respect_dnt;
			$has_dnt_header = isset( $_SERVER['HTTP_DNT'] ) && '1' === trim( (string) $_SERVER['HTTP_DNT'] );

			$tracking_enabled = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'tracking_enabled', true );
			$is_tracking_off  = false === $tracking_enabled || 'no' === $tracking_enabled;

			$disable_cookies = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'gdpr_disable_user_cookies', false );
			$is_disable_cook = true === $disable_cookies || 'yes' === $disable_cookies;

			// If DNT is enabled and DNT header is sent by visitor's browser, disable details collection and tracking
			if ( $is_respect_dnt && $has_dnt_header ) {
				$is_disable_det  = true;
				$is_tracking_off = true;
			}

			$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_textarea_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
			$raw_ip     = $this->get_ip_address();

			if ( $is_disable_det || $is_tracking_off ) {
				$ip_address = '-';
				$user_agent = '-';
				$device     = '-';
				$browser    = '-';
				$os         = '-';
				$screen     = '-';
				$language   = '-';
				$user_id    = null;
			} else {
				$ip_address = $is_anonymize_ip ? $this->anonymize_ip( $raw_ip ) : $raw_ip;
				$device     = $this->parse_device( $user_agent );
				$browser    = $this->parse_browser( $user_agent );
				$os         = $this->parse_os( $user_agent );
				$screen     = isset( $_REQUEST['screen_resolution'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['screen_resolution'] ) ) : ( isset( $_POST['screen_resolution'] ) ? sanitize_text_field( wp_unslash( $_POST['screen_resolution'] ) ) : '-' );
				$language   = isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) : '-';
				$language   = explode( ',', $language )[0] ?? '-';
				$user_id    = get_current_user_id() ?: null;
			}

			$landing = isset( $_REQUEST['current_page_url'] ) && ! empty( $_REQUEST['current_page_url'] )
				? sanitize_text_field( wp_unslash( $_REQUEST['current_page_url'] ) )
				: ( isset( $_POST['current_page_url'] ) ? sanitize_text_field( wp_unslash( $_POST['current_page_url'] ) ) : ( isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '-' ) );

			$raw_referrer = isset( $_REQUEST['referrer_url'] ) && ! empty( $_REQUEST['referrer_url'] )
				? sanitize_text_field( wp_unslash( $_REQUEST['referrer_url'] ) )
				: ( isset( $_POST['referrer_url'] ) ? sanitize_text_field( wp_unslash( $_POST['referrer_url'] ) ) : 'Direct' );

			$site_host     = wp_parse_url( home_url(), PHP_URL_HOST );
			$referrer_host = wp_parse_url( $raw_referrer, PHP_URL_HOST );

			if ( empty( $raw_referrer ) || 'Direct' === $raw_referrer || ( $referrer_host && $site_host && strtolower( $referrer_host ) === strtolower( $site_host ) ) ) {
				$referrer = 'Direct';
			} else {
				$referrer = $raw_referrer;
			}

			$time_taken = isset( $_REQUEST['time_to_submit'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['time_to_submit'] ) ) : ( isset( $_POST['time_to_submit'] ) ? sanitize_text_field( wp_unslash( $_POST['time_to_submit'] ) ) : '-' );

			$visitor_info = array(
				'user_id'        => $user_id,
				'ip_address'     => $ip_address,
				'user_agent'     => $user_agent,
				'device'         => $device,
				'browser'        => $browser,
				'os'             => $os,
				'screen'         => $screen,
				'language'       => $language,
				'time_to_submit' => $time_taken,
			);

			if ( $is_tracking_off || $is_disable_det ) {
				$lead_attributes = array();
				$visitor_journey = array();
			} else {
				$lead_attributes = array(
					'traffic_source' => $this->parse_traffic_source( $referrer ),
					'referrer'       => $referrer,
					'landing_page'   => $landing,
					'utm_source'     => isset( $_REQUEST['utm_source'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['utm_source'] ) ) : '-',
					'utm_medium'     => isset( $_REQUEST['utm_medium'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['utm_medium'] ) ) : '-',
					'utm_campaign'   => isset( $_REQUEST['utm_campaign'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['utm_campaign'] ) ) : '-',
					'utm_term'       => isset( $_REQUEST['utm_term'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['utm_term'] ) ) : '-',
					'utm_content'    => isset( $_REQUEST['utm_content'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['utm_content'] ) ) : '-',
				);
			}

			global $wpdb;
			$session_uid = isset( $_POST['session_uid'] ) ? sanitize_text_field( wp_unslash( $_POST['session_uid'] ) ) : ( isset( $_POST['user_session'] ) ? sanitize_text_field( wp_unslash( $_POST['user_session'] ) ) : ( isset( $_COOKIE['dragwyb_sid'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['dragwyb_sid'] ) ) : '' ) );
			$visitor_uid = isset( $_POST['user_id'] ) ? sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : ( isset( $_COOKIE['dragwyb_uid'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['dragwyb_uid'] ) ) : '' );

			$db_visitor_id = null;
			$db_session_id = null;

			if ( ! empty( $visitor_uid ) ) {
				$visitors_table = Dragwyb_Analytics_Db::table_name( 'visitors' );
				$db_visitor_id  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$visitors_table} WHERE visitor_uid = %s", $visitor_uid ) );
			}

			if ( ! empty( $session_uid ) && $db_visitor_id ) {
				$sessions_table = Dragwyb_Analytics_Db::table_name( 'sessions' );
				$db_session_id  = (int) $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$sessions_table} WHERE visitor_id = %d AND session_uid = %s ORDER BY id DESC LIMIT 1", $db_visitor_id, $session_uid ) );
			}

			if ( ! $is_tracking_off && ! $is_disable_det ) {
				$form_title    = get_the_title( $form_id ) ?: '#' . $form_id;
				$journey_table = Dragwyb_Analytics_Db::table_name( 'journey' );

				if ( $db_session_id && $db_visitor_id ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
					$wpdb->insert(
						$journey_table,
						array(
							'visitor_id'    => $db_visitor_id,
							'session_id'    => $db_session_id,
							'action_type'   => 'submission',
							'action_detail' => sprintf( __( 'Form submitted: %s', 'smart-form-builder-by-dragwyb' ), $form_title ),
							'page_url'      => $landing,
							'page_title'    => sprintf( __( 'Form submitted: %s', 'smart-form-builder-by-dragwyb' ), $form_title ),
							'created_at'    => current_time( 'mysql' ),
						),
						array( '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
					);
				}
			}

			$extra_data = array(
				'visitor_info'    => $visitor_info,
				'lead_attributes' => $lead_attributes,
			);

			if ( ! $is_tracking_off && ! $is_disable_det && ! $is_disable_cook ) {
				$extra_data['session_uid'] = $session_uid;
				$extra_data['session_id']  = $db_session_id ? (string) $db_session_id : '';
				$extra_data['user_id']     = $visitor_uid;
			}

			unset( $form_data['session_uid'], $form_data['session_id'], $form_data['user_id'], $form_data['user_session'] );

			$insert_data = array(
				'form_id'         => $form_id,
				'submission_data' => $this->get_form_data( $form_data, $form_config ),
				'extra_data'      => wp_json_encode( $extra_data ),
			);

			$insert_id = $db->insert( $insert_data );

			if ( is_wp_error( $insert_id ) ) {
				$form_submission->add_error( 'save_submissions', $insert_id->get_error_message() );
			}
		}
	}
}
