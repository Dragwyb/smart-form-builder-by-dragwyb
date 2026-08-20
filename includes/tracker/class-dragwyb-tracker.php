<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Tracker;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Admin\Settings\Settings_Manager;
use Dragwyb\Form_Builder\Admin\Db\Analytics\Dragwyb_Analytics_Db;

class Dragwyb_Tracker {

	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$enabled = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'tracking_enabled', true );
		if ( false === $enabled || 'no' === $enabled ) {
			return;
		}

		$respect_dnt = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'gdpr_respect_dnt', false );
		if ( ( true === $respect_dnt || 'yes' === $respect_dnt ) && isset( $_SERVER['HTTP_DNT'] ) && '1' === $_SERVER['HTTP_DNT'] ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_tracker' ) );
		add_action( 'wp_ajax_dragwyb_track', array( $this, 'handle_tracking' ) );
		add_action( 'wp_ajax_nopriv_dragwyb_track', array( $this, 'handle_tracking' ) );
	}

	public function enqueue_tracker(): void {
		if ( is_admin() ) {
			return;
		}

		$track_admins = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'track_admins', false );
		if ( current_user_can( 'manage_options' ) && true !== $track_admins && 'yes' !== $track_admins ) {
			return;
		}

		$timeout         = absint( Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'session_timeout', 30 ) );
		$cookie_duration = absint( Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'cookie_duration', 730 ) );
		$disable_cookies = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'gdpr_disable_user_cookies', false );
		$disable_details = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'gdpr_disable_user_details', false );

		wp_enqueue_script(
			'dragwyb-tracker',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/tracker.js' ),
			array(),
			DRAGWYB_FORM_BUILDER_VERSION,
			true
		);

		wp_localize_script(
			'dragwyb-tracker',
			'dragwybTrackerCfg',
			array(
				'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
				'nonce'          => wp_create_nonce( 'dragwyb_track_nonce' ),
				'sessionTimeout' => $timeout > 0 ? $timeout : 30,
				'cookieDuration' => $cookie_duration > 0 ? $cookie_duration : 730,
				'disableCookies' => ( true === $disable_cookies || 'yes' === $disable_cookies ) ? '1' : '0',
				'disableDetails' => ( true === $disable_details || 'yes' === $disable_details ) ? '1' : '0',
			)
		);
	}

	public function handle_tracking(): void {
		check_ajax_referer( 'dragwyb_track_nonce', 'nonce' );

		$event_type = isset( $_POST['event_type'] ) ? sanitize_text_field( wp_unslash( $_POST['event_type'] ) ) : '';
		$data       = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : array();

		if ( is_string( $data ) ) {
			$data = json_decode( stripslashes( $data ), true );
		}

		if ( ! is_array( $data ) ) {
			$data = array();
		}

		$data = $this->sanitize_tracking_data( $data );

		$disable_details    = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'gdpr_disable_user_details', false );
		$is_disable_details = true === $disable_details || 'yes' === $disable_details;

		$data['ip_address'] = $is_disable_details ? '' : $this->get_client_ip();
		$data['user_agent'] = $is_disable_details ? '' : ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '' );

		try {
			switch ( $event_type ) {
				case 'pageview':
					$this->record_pageview( $data );
					break;
				case 'event':
					$this->record_event( $data );
					break;
				case 'scroll':
					$this->update_scroll( $data );
					break;
				case 'heartbeat':
					$this->update_heartbeat( $data );
					break;
			}
			wp_send_json_success( array( 'status' => 'ok' ) );
		} catch ( \Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}
	}

	private function record_pageview( array $data ): void {
		global $wpdb;

		$visitor_uid = sanitize_text_field( $data['visitor_uid'] ?? '' );
		$session_uid = sanitize_text_field( $data['session_uid'] ?? '' );

		if ( empty( $visitor_uid ) || empty( $session_uid ) ) {
			return;
		}

		$visitors_table  = Dragwyb_Analytics_Db::table_name( 'visitors' );
		$sessions_table  = Dragwyb_Analytics_Db::table_name( 'sessions' );
		$pageviews_table = Dragwyb_Analytics_Db::table_name( 'pageviews' );
		$journey_table   = Dragwyb_Analytics_Db::table_name( 'journey' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$visitor = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM $visitors_table WHERE visitor_uid = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$visitor_uid
			)
		);

		if ( $visitor ) {
			$visitor_id = (int) $visitor->id;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->update(
				$visitors_table,
				array( 'last_seen' => current_time( 'mysql' ) ),
				array( 'id' => $visitor_id ),
				array( '%s' ),
				array( '%d' )
			);
		} else {
			$referrer        = esc_url_raw( $data['referrer'] ?? '' );
			$referrer_domain = '';
			if ( $referrer ) {
				$parsed          = wp_parse_url( $referrer );
				$referrer_domain = $parsed['host'] ?? '';
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$visitors_table,
				array(
					'visitor_uid'        => sanitize_text_field( $visitor_uid ),
					'first_referrer'     => $referrer,
					'first_utm_source'   => sanitize_text_field( $data['utm_source'] ?? '' ),
					'first_utm_medium'   => sanitize_text_field( $data['utm_medium'] ?? '' ),
					'first_utm_campaign' => sanitize_text_field( $data['utm_campaign'] ?? '' ),
					'first_landing_page' => esc_url_raw( $data['page_url'] ?? '' ),
					'device_type'        => sanitize_text_field( $data['device_type'] ?? 'desktop' ),
					'browser'            => sanitize_text_field( $data['browser'] ?? '' ),
					'os'                 => sanitize_text_field( $data['os'] ?? '' ),
					'screen_resolution'  => sanitize_text_field( $data['screen_resolution'] ?? '' ),
					'language'           => sanitize_text_field( $data['language'] ?? '' ),
					'total_sessions'     => 1,
					'total_pageviews'    => 0,
					'first_seen'         => current_time( 'mysql' ),
					'last_seen'          => current_time( 'mysql' ),
				),
				array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
			);
			$visitor_id = (int) $wpdb->insert_id;
		}

		$timeout = absint( Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'session_timeout', 30 ) );
		if ( $timeout <= 0 ) {
			$timeout = 30;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$session = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id FROM $sessions_table WHERE visitor_id = %d AND session_uid = %s AND (ended_at > DATE_SUB(NOW(), INTERVAL %d MINUTE) OR started_at > DATE_SUB(NOW(), INTERVAL %d MINUTE)) ORDER BY id DESC LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$visitor_id,
				$session_uid,
				$timeout,
				$timeout
			)
		);

		if ( $session ) {
			$session_id = (int) $session->id;
		} else {
			$traffic_source = self::classify_traffic_source(
				$data['referrer'] ?? '',
				$data['utm_source'] ?? '',
				$data['utm_medium'] ?? ''
			);

			$referrer        = esc_url_raw( $data['referrer'] ?? '' );
			$referrer_domain = '';
			if ( $referrer ) {
				$parsed          = wp_parse_url( $referrer );
				$referrer_domain = $parsed['host'] ?? '';
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$sessions_table,
				array(
					'visitor_id'      => $visitor_id,
					'session_uid'     => sanitize_text_field( $session_uid ),
					'entry_page'      => esc_url_raw( $data['page_url'] ?? '' ),
					'referrer'        => $referrer,
					'referrer_domain' => sanitize_text_field( $referrer_domain ),
					'utm_source'      => sanitize_text_field( $data['utm_source'] ?? '' ),
					'utm_medium'      => sanitize_text_field( $data['utm_medium'] ?? '' ),
					'utm_campaign'    => sanitize_text_field( $data['utm_campaign'] ?? '' ),
					'utm_term'        => sanitize_text_field( $data['utm_term'] ?? '' ),
					'utm_content'     => sanitize_text_field( $data['utm_content'] ?? '' ),
					'traffic_source'  => sanitize_text_field( $traffic_source ),
					'device_type'     => sanitize_text_field( $data['device_type'] ?? 'desktop' ),
					'browser'         => sanitize_text_field( $data['browser'] ?? '' ),
					'os'              => sanitize_text_field( $data['os'] ?? '' ),
					'ip_address'      => sanitize_text_field( $data['ip_address'] ?? '' ),
				),
				array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
			);
			$session_id = (int) $wpdb->insert_id;

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->query(
				$wpdb->prepare(
					"UPDATE $visitors_table SET total_sessions = total_sessions + 1 WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$visitor_id
				)
			);
		}

		$page_url  = esc_url_raw( $data['page_url'] ?? '' );
		$page_path = '/';
		if ( $page_url ) {
			$parsed    = wp_parse_url( $page_url );
			$page_path = $parsed['path'] ?? '/';
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$pv_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM $pageviews_table WHERE session_id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$session_id
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$pageviews_table,
			array(
				'visitor_id'    => $visitor_id,
				'session_id'    => $session_id,
				'page_url'      => $page_url,
				'page_path'     => sanitize_text_field( $page_path ),
				'page_title'    => sanitize_text_field( $data['page_title'] ?? '' ),
				'referrer'      => esc_url_raw( $data['referrer'] ?? '' ),
				'is_entry_page' => 0 === $pv_count ? 1 : 0,
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s', '%d', '%s' )
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $sessions_table SET pageview_count = pageview_count + 1, exit_page = %s, is_bounce = IF(pageview_count >= 1, 0, 1), ended_at = NOW(), duration_seconds = TIMESTAMPDIFF(SECOND, started_at, NOW()) WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$page_url,
				$session_id
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $visitors_table SET total_pageviews = total_pageviews + 1 WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$visitor_id
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$journey_table,
			array(
				'visitor_id'    => $visitor_id,
				'session_id'    => $session_id,
				'action_type'   => 'pageview',
				'action_detail' => 'Viewed: ' . sanitize_text_field( $data['page_title'] ?? $page_path ),
				'page_url'      => $page_url,
				'page_title'    => sanitize_text_field( $data['page_title'] ?? '' ),
				'created_at'    => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s', '%s' )
		);
	}

	private function record_event( array $data ): void {
		global $wpdb;
		$visitor_id = $this->resolve_visitor_id( $data['visitor_uid'] ?? '' );
		$session_id = $this->resolve_session_id( $visitor_id, $data['session_uid'] ?? '' );

		if ( ! $visitor_id || ! $session_id ) {
			return;
		}

		$events_table   = Dragwyb_Analytics_Db::table_name( 'events' );
		$sessions_table = Dragwyb_Analytics_Db::table_name( 'sessions' );
		$visitors_table = Dragwyb_Analytics_Db::table_name( 'visitors' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$events_table,
			array(
				'visitor_id'     => $visitor_id,
				'session_id'     => $session_id,
				'event_name'     => sanitize_text_field( $data['event_name'] ?? '' ),
				'event_category' => sanitize_text_field( $data['event_category'] ?? '' ),
				'event_action'   => sanitize_text_field( $data['event_action'] ?? '' ),
				'event_label'    => sanitize_text_field( $data['event_label'] ?? '' ),
				'event_value'    => isset( $data['event_value'] ) ? sanitize_text_field( (string) $data['event_value'] ) : null,
				'page_url'       => esc_url_raw( $data['page_url'] ?? '' ),
				'element_id'     => sanitize_text_field( $data['element_id'] ?? '' ),
				'element_class'  => sanitize_text_field( $data['element_class'] ?? '' ),
				'element_text'   => sanitize_text_field( $data['element_text'] ?? '' ),
				'metadata'       => wp_json_encode( $data['metadata'] ?? array() ),
				'created_at'     => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $sessions_table SET event_count = event_count + 1, ended_at = NOW() WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$session_id
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $visitors_table SET total_events = total_events + 1 WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$visitor_id
			)
		);
	}

	private function update_scroll( array $data ): void {
		global $wpdb;
		$visitor_id = $this->resolve_visitor_id( $data['visitor_uid'] ?? '' );
		$session_id = $this->resolve_session_id( $visitor_id, $data['session_uid'] ?? '' );
		if ( ! $session_id ) {
			return;
		}

		$pageviews_table = Dragwyb_Analytics_Db::table_name( 'pageviews' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $pageviews_table SET scroll_depth_percent = GREATEST(scroll_depth_percent, %d) WHERE session_id = %d AND page_url = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $data['scroll_depth'] ?? 0 ),
				$session_id,
				$data['page_url'] ?? ''
			)
		);
	}

	private function update_heartbeat( array $data ): void {
		global $wpdb;
		$visitor_id = $this->resolve_visitor_id( $data['visitor_uid'] ?? '' );
		$session_id = $this->resolve_session_id( $visitor_id, $data['session_uid'] ?? '' );
		if ( ! $session_id ) {
			return;
		}

		$pageviews_table = Dragwyb_Analytics_Db::table_name( 'pageviews' );
		$sessions_table  = Dragwyb_Analytics_Db::table_name( 'sessions' );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $pageviews_table SET time_on_page_seconds = %d WHERE session_id = %d AND page_url = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $data['time_on_page'] ?? 0 ),
				$session_id,
				$data['page_url'] ?? ''
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE $sessions_table SET ended_at = NOW(), duration_seconds = TIMESTAMPDIFF(SECOND, started_at, NOW()) WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$session_id
			)
		);
	}

	private function resolve_visitor_id( string $visitor_uid ): ?int {
		if ( empty( $visitor_uid ) ) {
			return null;
		}
		global $wpdb;
		$visitors_table = Dragwyb_Analytics_Db::table_name( 'visitors' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM $visitors_table WHERE visitor_uid = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$visitor_uid
			)
		);
		return $id ? (int) $id : null;
	}

	private function resolve_session_id( ?int $visitor_id, string $session_uid ): ?int {
		if ( ! $visitor_id || empty( $session_uid ) ) {
			return null;
		}
		global $wpdb;
		$timeout        = absint( Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'session_timeout', 30 ) );
		$sessions_table = Dragwyb_Analytics_Db::table_name( 'sessions' );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$id = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM $sessions_table WHERE visitor_id = %d AND session_uid = %s AND (ended_at > DATE_SUB(NOW(), INTERVAL %d MINUTE) OR started_at > DATE_SUB(NOW(), INTERVAL %d MINUTE)) ORDER BY id DESC LIMIT 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$visitor_id,
				$session_uid,
				$timeout > 0 ? $timeout : 30,
				$timeout > 0 ? $timeout : 30
			)
		);
		return $id ? (int) $id : null;
	}

	public static function classify_traffic_source( string $referrer, string $utm_source, string $utm_medium ): string {
		if ( $utm_medium ) {
			$m = strtolower( $utm_medium );
			if ( in_array( $m, array( 'cpc', 'ppc', 'paid', 'paidsearch' ), true ) ) {
				return 'paid_search';
			}
			if ( in_array( $m, array( 'cpm', 'display', 'banner', 'paid_social' ), true ) ) {
				return 'paid_social';
			}
			if ( in_array( $m, array( 'social', 'social-media' ), true ) ) {
				return 'organic_social';
			}
			if ( in_array( $m, array( 'email', 'newsletter' ), true ) ) {
				return 'email';
			}
			if ( in_array( $m, array( 'referral', 'affiliate' ), true ) ) {
				return 'referral';
			}
			if ( in_array( $m, array( 'organic', 'seo' ), true ) ) {
				return 'organic_search';
			}
		}
		if ( $utm_source ) {
			$s = strtolower( $utm_source );
			if ( in_array( $s, array( 'google', 'bing', 'yahoo', 'duckduckgo' ), true ) ) {
				return 'organic_search';
			}
			if ( in_array( $s, array( 'facebook', 'instagram', 'twitter', 'linkedin', 'tiktok' ), true ) ) {
				return 'organic_social';
			}
		}
		if ( $referrer ) {
			$domain = strtolower( wp_parse_url( $referrer, PHP_URL_HOST ) ?: '' );
			foreach ( array( 'google.', 'bing.', 'yahoo.', 'duckduckgo.' ) as $se ) {
				if ( false !== strpos( $domain, $se ) ) {
					return 'organic_search';
				}
			}
			foreach ( array( 'facebook.', 'instagram.', 'twitter.', 'linkedin.', 'tiktok.', 't.co', 'x.com' ) as $sn ) {
				if ( false !== strpos( $domain, $sn ) ) {
					return 'organic_social';
				}
			}
			$site_domain = wp_parse_url( home_url(), PHP_URL_HOST );
			if ( $domain && $domain !== $site_domain ) {
				return 'referral';
			}
		}
		return empty( $referrer ) ? 'direct' : 'other';
	}

	private function sanitize_tracking_data( array $data ): array {
		$sanitized = array();
		$url_keys  = array( 'page_url', 'referrer', 'first_landing_page', 'first_referrer', 'entry_page', 'exit_page' );
		$int_keys  = array( 'scroll_depth', 'time_on_page', 'total_sessions', 'total_pageviews', 'event_value' );

		foreach ( $data as $key => $value ) {
			$clean_key = sanitize_key( (string) $key );
			if ( is_array( $value ) ) {
				$sanitized[ $clean_key ] = $this->sanitize_tracking_data( $value );
			} elseif ( in_array( $clean_key, $url_keys, true ) ) {
				$sanitized[ $clean_key ] = esc_url_raw( (string) $value );
			} elseif ( in_array( $clean_key, $int_keys, true ) ) {
				$sanitized[ $clean_key ] = absint( $value );
			} elseif ( in_array( $clean_key, array( 'visitor_uid', 'session_uid' ), true ) ) {
				$sanitized[ $clean_key ] = preg_replace( '/[^a-zA-Z0-9_\-]/', '', (string) $value );
			} else {
				$sanitized[ $clean_key ] = sanitize_text_field( (string) $value );
			}
		}
		return $sanitized;
	}

	private function get_client_ip(): string {
		$headers = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' );
		foreach ( $headers as $header ) {
			if ( ! empty( $_SERVER[ $header ] ) ) {
				$ip = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $header ] ) ) );
				$ip = trim( $ip[0] );
				if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
					$anonymize = Settings_Manager::instance()->get_setting( 'gdpr_privacy', 'gdpr_anonymize_ip', false );
					if ( true === $anonymize || 'yes' === $anonymize ) {
						return self::anonymize_ip( $ip );
					}
					return $ip;
				}
			}
		}
		return '0.0.0.0';
	}

	public static function anonymize_ip( string $ip ): string {
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 ) ) {
			return preg_replace( '/\.\d+$/', '.0', $ip );
		}
		if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			return inet_ntop( inet_pton( $ip ) & inet_pton( 'ffff:ffff:ffff:ffff::' ) );
		}
		return $ip;
	}
}
