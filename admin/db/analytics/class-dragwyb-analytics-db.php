<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Db\Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dragwyb_Analytics_Db {

	const VERSION = 'v1';

	public static function table_name( string $name ): string {
		global $wpdb;
		return $wpdb->prefix . 'dragwyb_' . $name;
	}

	public static function create_tables(): void {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$visitors_table  = self::table_name( 'visitors' );
		$sessions_table  = self::table_name( 'sessions' );
		$pageviews_table = self::table_name( 'pageviews' );
		$events_table    = self::table_name( 'events' );
		$journey_table   = self::table_name( 'journey' );

		$sql_visitors = "CREATE TABLE $visitors_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			visitor_uid varchar(64) NOT NULL,
			first_referrer text DEFAULT NULL,
			first_utm_source varchar(100) DEFAULT NULL,
			first_utm_medium varchar(100) DEFAULT NULL,
			first_utm_campaign varchar(100) DEFAULT NULL,
			first_landing_page text DEFAULT NULL,
			device_type varchar(20) DEFAULT 'desktop',
			browser varchar(50) DEFAULT NULL,
			os varchar(50) DEFAULT NULL,
			screen_resolution varchar(30) DEFAULT NULL,
			language varchar(20) DEFAULT NULL,
			total_sessions int(11) DEFAULT 1,
			total_pageviews int(11) DEFAULT 0,
			total_events int(11) DEFAULT 0,
			first_seen datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			last_seen datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (id),
			UNIQUE KEY visitor_uid (visitor_uid)
		) $charset_collate;";

		$sql_sessions = "CREATE TABLE $sessions_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			visitor_id bigint(20) unsigned NOT NULL,
			session_uid varchar(64) NOT NULL,
			entry_page text DEFAULT NULL,
			exit_page text DEFAULT NULL,
			referrer text DEFAULT NULL,
			referrer_domain varchar(150) DEFAULT NULL,
			utm_source varchar(100) DEFAULT NULL,
			utm_medium varchar(100) DEFAULT NULL,
			utm_campaign varchar(100) DEFAULT NULL,
			utm_term varchar(100) DEFAULT NULL,
			utm_content varchar(100) DEFAULT NULL,
			traffic_source varchar(50) DEFAULT 'direct',
			device_type varchar(20) DEFAULT 'desktop',
			browser varchar(50) DEFAULT NULL,
			os varchar(50) DEFAULT NULL,
			ip_address varchar(45) DEFAULT NULL,
			pageview_count int(11) DEFAULT 0,
			event_count int(11) DEFAULT 0,
			is_bounce tinyint(1) DEFAULT 1,
			duration_seconds int(11) DEFAULT 0,
			started_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			ended_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (id),
			KEY visitor_id (visitor_id),
			KEY session_uid (session_uid),
			KEY started_at (started_at)
		) $charset_collate;";

		$sql_pageviews = "CREATE TABLE $pageviews_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			visitor_id bigint(20) unsigned NOT NULL,
			session_id bigint(20) unsigned NOT NULL,
			page_url text NOT NULL,
			page_path varchar(255) NOT NULL,
			page_title varchar(255) DEFAULT NULL,
			referrer text DEFAULT NULL,
			is_entry_page tinyint(1) DEFAULT 0,
			scroll_depth_percent int(11) DEFAULT 0,
			time_on_page_seconds int(11) DEFAULT 0,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY visitor_id (visitor_id),
			KEY page_path (page_path(191))
		) $charset_collate;";

		$sql_events = "CREATE TABLE $events_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			visitor_id bigint(20) unsigned NOT NULL,
			session_id bigint(20) unsigned NOT NULL,
			event_name varchar(100) NOT NULL,
			event_category varchar(100) DEFAULT NULL,
			event_action varchar(100) DEFAULT NULL,
			event_label varchar(255) DEFAULT NULL,
			event_value int(11) DEFAULT NULL,
			page_url text DEFAULT NULL,
			element_id varchar(100) DEFAULT NULL,
			element_class varchar(255) DEFAULT NULL,
			element_text varchar(255) DEFAULT NULL,
			metadata longtext DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY event_name (event_name)
		) $charset_collate;";

		$sql_journey = "CREATE TABLE $journey_table (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			visitor_id bigint(20) unsigned NOT NULL,
			session_id bigint(20) unsigned NOT NULL,
			action_type varchar(50) NOT NULL,
			action_detail text DEFAULT NULL,
			page_url text DEFAULT NULL,
			page_title varchar(255) DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
			PRIMARY KEY (id),
			KEY session_id (session_id),
			KEY visitor_id (visitor_id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_visitors );
		dbDelta( $sql_sessions );
		dbDelta( $sql_pageviews );
		dbDelta( $sql_events );
		dbDelta( $sql_journey );
	}
}
