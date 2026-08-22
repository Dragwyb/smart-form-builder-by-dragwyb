<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Form_Analytics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Admin\Db\Analytics\Dragwyb_Analytics_Db;
use Dragwyb\Form_Builder\Admin\Db\Submission\Dragwyb_Submission_Db;

class Form_Analytics {

	private static $instance = null;

	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function __construct() {
		add_action( 'Dragwyb_Menu_Page', array( $this, 'render_page' ), 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
		add_action( 'wp_ajax_dragwyb_get_analytics_data', array( $this, 'ajax_get_analytics_data' ) );
	}

	public function enqueue_assets( $hook ): void {
		if ( strpos( $hook, DRAGWYB_PREFIX . '-form-analytics' ) === false ) {
			return;
		}

		wp_enqueue_style(
			DRAGWYB_PREFIX . '-editor-global',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/editor-global.css' ),
			array(),
			DRAGWYB_FORM_BUILDER_VERSION
		);

		wp_enqueue_style(
			DRAGWYB_PREFIX . '-analytics-style',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/analytics.css' ),
			array( DRAGWYB_PREFIX . '-editor-global' ),
			DRAGWYB_FORM_BUILDER_VERSION
		);

		wp_enqueue_script(
			DRAGWYB_PREFIX . '-chartjs',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/lib/chartjs/chart.umd.min.js' ),
			array(),
			'4.5.1',
			true
		);

		wp_enqueue_script(
			DRAGWYB_PREFIX . '-analytics-script',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/analytics.js' ),
			array( 'jquery', DRAGWYB_PREFIX . '-chartjs' ),
			DRAGWYB_FORM_BUILDER_VERSION,
			true
		);

		$days       = isset( $_GET['days'] ) ? absint( wp_unslash( $_GET['days'] ) ) : 30; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$valid_tabs = array( 'traffic', 'pages', 'sources', 'devices', 'live' );
		$tab_param  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_tab = in_array( $tab_param, $valid_tabs, true ) ? $tab_param : 'traffic';

		wp_localize_script(
			DRAGWYB_PREFIX . '-analytics-script',
			'DragwybAnalyticsApp',
			array(
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'dragwyb_admin_nonce' ),
				'days'       => $days > 0 ? $days : 30,
				'active_tab' => $active_tab,
				'i18n'       => array(
					'loading' => __( 'Loading data...', 'smart-form-builder-by-dragwyb' ),
					'error'   => __( 'Failed to load analytics.', 'smart-form-builder-by-dragwyb' ),
				),
			)
		);
	}

	public function render_page( $current_page ): void {
		if ( ! is_callable( $current_page ) || ( ! $current_page( 'form-analytics' ) && ! $current_page( DRAGWYB_PREFIX . '-form-analytics' ) ) ) {
			return;
		}

		$days       = isset( $_GET['days'] ) ? absint( wp_unslash( $_GET['days'] ) ) : 30; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$valid_tabs = array( 'traffic', 'pages', 'sources', 'devices', 'live' );
		$tab_param  = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$active_tab = in_array( $tab_param, $valid_tabs, true ) ? $tab_param : 'traffic';
		?>
		<div class="dragwyb-dashboard-header">
			<div class="dragwyb-db-brand">
				<div class="dragwyb-db-logo">
					<?php
					$logo_url  = DRAGWYB_FORM_BUILDER_URL . 'assets/img/menu-logo.svg';
					$logo_html = '<img src="' . esc_url( $logo_url ) . '"/>';
					echo wp_kses_post( $logo_html );
					?>
				</div>
				<div class="dragwyb-header-title-meta">
					<h1 class="dragwyb-db-brand-name"><?php esc_html_e( 'Analytics & Traffic', 'smart-form-builder-by-dragwyb' ); ?></h1>
					<p class="dragwyb-db-sub-title"><?php esc_html_e( 'Monitor your visitor engagement, session trends, and conversions in real-time.', 'smart-form-builder-by-dragwyb' ); ?></p>
				</div>
			</div>
			<div class="dragwyb-date-filter">
				<select id="dragwyb-analytics-days" class="dragwyb-select-sm">
					<option value="7" <?php selected( $days, 7 ); ?>><?php esc_html_e( 'Last 7 Days', 'smart-form-builder-by-dragwyb' ); ?></option>
					<option value="14" <?php selected( $days, 14 ); ?>><?php esc_html_e( 'Last 14 Days', 'smart-form-builder-by-dragwyb' ); ?></option>
					<option value="30" <?php selected( $days, 30 ); ?>><?php esc_html_e( 'Last 30 Days', 'smart-form-builder-by-dragwyb' ); ?></option>
					<option value="90" <?php selected( $days, 90 ); ?>><?php esc_html_e( 'Last 90 Days', 'smart-form-builder-by-dragwyb' ); ?></option>
				</select>
			</div>
		</div>

		<!-- Quick Overview Stats -->
		<div class="dragwyb-stats-grid">
			<div class="dragwyb-stat-card">
				<div class="dragwyb-stat-label"><?php esc_html_e( 'Form Submissions', 'smart-form-builder-by-dragwyb' ); ?></div>
				<div class="dragwyb-stat-value" id="stat-submissions">-</div>
			</div>
			<div class="dragwyb-stat-card">
				<div class="dragwyb-stat-label"><?php esc_html_e( 'Unique Visitors', 'smart-form-builder-by-dragwyb' ); ?></div>
				<div class="dragwyb-stat-value" id="stat-visitors">-</div>
			</div>
			<div class="dragwyb-stat-card">
				<div class="dragwyb-stat-label"><?php esc_html_e( 'Total Sessions', 'smart-form-builder-by-dragwyb' ); ?></div>
				<div class="dragwyb-stat-value" id="stat-sessions">-</div>
			</div>
			<div class="dragwyb-stat-card">
				<div class="dragwyb-stat-label"><?php esc_html_e( 'Total Pageviews', 'smart-form-builder-by-dragwyb' ); ?></div>
				<div class="dragwyb-stat-value" id="stat-pageviews">-</div>
			</div>
			<div class="dragwyb-stat-card">
				<div class="dragwyb-stat-label"><?php esc_html_e( 'Avg Session Duration', 'smart-form-builder-by-dragwyb' ); ?></div>
				<div class="dragwyb-stat-value" id="stat-duration">-</div>
			</div>
			<div class="dragwyb-stat-card">
				<div class="dragwyb-stat-label"><?php esc_html_e( 'Bounce Rate', 'smart-form-builder-by-dragwyb' ); ?></div>
				<div class="dragwyb-stat-value" id="stat-bounce">-</div>
			</div>
		</div>

		<div class="dragwyb-analytics-wrap">
			<!-- Analytics Tabs -->
			<div class="dragwyb-analytics-tabs">
				<button class="dragwyb-atab <?php echo 'traffic' === $active_tab ? 'active' : ''; ?>" data-tab="traffic"><?php esc_html_e( 'Traffic Overview', 'smart-form-builder-by-dragwyb' ); ?></button>
				<button class="dragwyb-atab <?php echo 'pages' === $active_tab ? 'active' : ''; ?>" data-tab="pages"><?php esc_html_e( 'Top Pages', 'smart-form-builder-by-dragwyb' ); ?></button>
				<button class="dragwyb-atab <?php echo 'sources' === $active_tab ? 'active' : ''; ?>" data-tab="sources"><?php esc_html_e( 'Traffic Sources', 'smart-form-builder-by-dragwyb' ); ?></button>
				<button class="dragwyb-atab <?php echo 'devices' === $active_tab ? 'active' : ''; ?>" data-tab="devices"><?php esc_html_e( 'Devices', 'smart-form-builder-by-dragwyb' ); ?></button>
				<button class="dragwyb-atab <?php echo 'live' === $active_tab ? 'active' : ''; ?>" data-tab="live"><?php esc_html_e( 'Live Visitors', 'smart-form-builder-by-dragwyb' ); ?></button>
			</div>

			<div class="dragwyb-atab-content-container">
				<!-- Tab 1: Traffic Overview -->
			<div class="dragwyb-atab-content <?php echo 'traffic' === $active_tab ? 'active' : ''; ?>" id="atab-traffic">
				<!-- Combined Overview Chart -->
				<div class="dragwyb-card dragwyb-chart-card" style="margin-bottom:24px;">
					<div class="dragwyb-card-header">
						<h3 class="dragwyb-card-title">
							<i class="fas fa-chart-area" style="color:#4361ee;margin-right:8px;"></i>
							<?php esc_html_e( 'Traffic Overview (Visitors & Sessions)', 'smart-form-builder-by-dragwyb' ); ?>
						</h3>
						<div class="dragwyb-chart-legend">
							<span class="dragwyb-legend-pill legend-visitors"><span class="pill-dot"></span> <?php esc_html_e( 'Unique Visitors', 'smart-form-builder-by-dragwyb' ); ?></span>
							<span class="dragwyb-legend-pill legend-sessions"><span class="pill-dot"></span> <?php esc_html_e( 'Total Sessions', 'smart-form-builder-by-dragwyb' ); ?></span>
						</div>
					</div>
					<div class="dragwyb-chart-canvas-wrap dragwyb-chart-lg">
						<canvas id="analytics-combined-chart"></canvas>
					</div>
				</div>

				<div class="dragwyb-grid-2">
					<div class="dragwyb-card dragwyb-chart-card">
						<h3 class="dragwyb-card-title">
							<i class="fas fa-user-friends" style="color:#4361ee;margin-right:8px;"></i>
							<?php esc_html_e( 'Visitors Over Time', 'smart-form-builder-by-dragwyb' ); ?>
						</h3>
						<div class="dragwyb-chart-canvas-wrap">
							<canvas id="analytics-visitors-chart"></canvas>
						</div>
					</div>
					<div class="dragwyb-card dragwyb-chart-card">
						<h3 class="dragwyb-card-title">
							<i class="fas fa-layer-group" style="color:#06b6d4;margin-right:8px;"></i>
							<?php esc_html_e( 'Sessions Over Time', 'smart-form-builder-by-dragwyb' ); ?>
						</h3>
						<div class="dragwyb-chart-canvas-wrap">
							<canvas id="analytics-sessions-chart"></canvas>
						</div>
					</div>
				</div>
			</div>

			<!-- Tab 2: Top Pages -->
			<div class="dragwyb-atab-content" id="atab-pages">
				<div class="dragwyb-card">
					<h3 class="dragwyb-card-title"><?php esc_html_e( 'Top Pages', 'smart-form-builder-by-dragwyb' ); ?></h3>
					<div id="analytics-pages-table"><p class="dragwyb-loading"><?php esc_html_e( 'Loading...', 'smart-form-builder-by-dragwyb' ); ?></p></div>
				</div>
			</div>

			<!-- Tab 3: Traffic Sources -->
			<div class="dragwyb-atab-content <?php echo 'sources' === $active_tab ? 'active' : ''; ?>" id="atab-sources">
				<div class="dragwyb-grid-2">
					<div class="dragwyb-card">
						<h3 class="dragwyb-card-title">
							<i class="fas fa-bullseye" style="color:#4361ee;margin-right:8px;"></i>
							<?php esc_html_e( 'Traffic Sources Breakdown', 'smart-form-builder-by-dragwyb' ); ?>
						</h3>
						<div id="analytics-sources-chart" class="dragwyb-donut-container"></div>
					</div>
					<div class="dragwyb-card">
						<h3 class="dragwyb-card-title">
							<i class="fas fa-list-ul" style="color:#4361ee;margin-right:8px;"></i>
							<?php esc_html_e( 'Source Details', 'smart-form-builder-by-dragwyb' ); ?>
						</h3>
						<div id="analytics-sources-table"><p class="dragwyb-loading"><?php esc_html_e( 'Loading...', 'smart-form-builder-by-dragwyb' ); ?></p></div>
					</div>
				</div>
			</div>

			<!-- Tab 4: Devices -->
			<div class="dragwyb-atab-content <?php echo 'devices' === $active_tab ? 'active' : ''; ?>" id="atab-devices">
				<div class="dragwyb-card" style="max-width:650px;margin:0 auto;">
					<h3 class="dragwyb-card-title">
						<i class="fas fa-laptop" style="color:#4361ee;margin-right:8px;"></i>
						<?php esc_html_e( 'Device Breakdown', 'smart-form-builder-by-dragwyb' ); ?>
					</h3>
					<div id="analytics-devices-chart" class="dragwyb-donut-container"></div>
				</div>
			</div>

			<!-- Tab 5: Live Visitors -->
			<div class="dragwyb-atab-content" id="atab-live">
				<div class="dragwyb-card">
					<h3 class="dragwyb-card-title">
						<span class="dragwyb-live-dot"></span>
						<?php esc_html_e( 'Live Visitors', 'smart-form-builder-by-dragwyb' ); ?>
						<span id="live-count" class="dragwyb-live-count">0</span>
					</h3>
					<div id="analytics-live-table"><p class="dragwyb-loading"><?php esc_html_e( 'Loading...', 'smart-form-builder-by-dragwyb' ); ?></p></div>
				</div>
			</div>
			</div>
		</div>
		<?php
	}

	public function ajax_get_analytics_data(): void {
		check_ajax_referer( 'dragwyb_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-form-builder-by-dragwyb' ) ) );
		}

		global $wpdb;
		$days = isset( $_POST['days'] ) ? absint( wp_unslash( $_POST['days'] ) ) : 30;
		if ( $days <= 0 ) {
			$days = 30;
		}

		$visitors_table  = Dragwyb_Analytics_Db::table_name( 'visitors' );
		$sessions_table  = Dragwyb_Analytics_Db::table_name( 'sessions' );
		$pageviews_table = Dragwyb_Analytics_Db::table_name( 'pageviews' );
		$submissions_db  = new Dragwyb_Submission_Db();

		$submissions_count = $submissions_db->get_total_count();

		// Unique visitors
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$visitors_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $visitors_table WHERE last_seen >= DATE_SUB(NOW(), INTERVAL %d DAY)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			)
		);

		// Total sessions
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$sessions_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $sessions_table WHERE started_at >= DATE_SUB(NOW(), INTERVAL %d DAY)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			)
		);

		// Total pageviews
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$pageviews_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $pageviews_table WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			)
		);

		// Avg duration & Bounce rate
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$session_stats = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT AVG(duration_seconds) as avg_duration, (SUM(is_bounce) / COUNT(id)) * 100 as bounce_rate FROM $sessions_table WHERE started_at >= DATE_SUB(NOW(), INTERVAL %d DAY)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			)
		);

		$avg_duration_sec = (int) ( $session_stats->avg_duration ?? 0 );
		$bounce_rate      = round( (float) ( $session_stats->bounce_rate ?? 0 ), 1 );

		// Visitors & Sessions Over Time
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$raw_daily_stats = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE_FORMAT(started_at, '%%m-%%d-%%y') as date_val, COUNT(DISTINCT visitor_id) as visitors, COUNT(id) as sessions FROM $sessions_table WHERE started_at >= DATE_SUB(NOW(), INTERVAL %d DAY) GROUP BY DATE(started_at) ORDER BY date_val ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			),
			ARRAY_A
		);

		// Find earliest date data exists in the database within requested period
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$min_date = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT MIN(DATE(started_at)) FROM $sessions_table WHERE started_at >= DATE_SUB(NOW(), INTERVAL %d DAY)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			)
		);

		$daily_map = array();

		if ( $min_date ) {
			$start_time = strtotime( $min_date );
			$end_time   = strtotime( gmdate( 'Y-m-d' ) );

			// Generate entries starting ONLY from the date analytics first recorded data
			for ( $time = $start_time; $time <= $end_time; $time += 86400 ) {
				$date_key               = gmdate( 'm-d-y', $time );
				$daily_map[ $date_key ] = array(
					'date_val' => $date_key,
					'visitors' => 0,
					'sessions' => 0,
				);
			}

			if ( ! empty( $raw_daily_stats ) ) {
				foreach ( $raw_daily_stats as $row ) {
					$d = $row['date_val'] ?? '';
					if ( isset( $daily_map[ $d ] ) ) {
						$daily_map[ $d ]['visitors'] = (int) ( $row['visitors'] ?? 0 );
						$daily_map[ $d ]['sessions'] = (int) ( $row['sessions'] ?? 0 );
					}
				}
			}
		}

		$daily_stats = array_values( $daily_map );

		// Top Pages
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$top_pages = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT page_path, page_title, COUNT(id) as views, COUNT(DISTINCT visitor_id) as visitors, AVG(time_on_page_seconds) as avg_time, AVG(scroll_depth_percent) as avg_scroll FROM $pageviews_table WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) GROUP BY page_path ORDER BY views DESC LIMIT 10", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			),
			ARRAY_A
		);

		// Traffic Sources
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$traffic_sources = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT traffic_source, COUNT(id) as sessions, COUNT(DISTINCT visitor_id) as visitors, (SUM(is_bounce) / COUNT(id)) * 100 as bounce_rate FROM $sessions_table WHERE started_at >= DATE_SUB(NOW(), INTERVAL %d DAY) GROUP BY traffic_source ORDER BY sessions DESC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			),
			ARRAY_A
		);

		// Devices
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$devices = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT device_type, COUNT(id) as total FROM $sessions_table WHERE started_at >= DATE_SUB(NOW(), INTERVAL %d DAY) GROUP BY device_type", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$days
			),
			ARRAY_A
		);

		// Live Visitors (Active in last 5 minutes)
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$live_visitors = $wpdb->get_results(
			'SELECT s.session_uid, v.visitor_uid, s.entry_page, s.exit_page, s.device_type, s.browser, s.ip_address, s.ended_at FROM ' . esc_sql( Dragwyb_Analytics_Db::table_name( 'sessions' ) ) . ' s JOIN ' . esc_sql( Dragwyb_Analytics_Db::table_name( 'visitors' ) ) . ' v ON s.visitor_id = v.id WHERE s.ended_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY s.ended_at DESC LIMIT 20', // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			ARRAY_A
		);

		wp_send_json_success(
			array(
				'stats'           => array(
					'submissions'  => $submissions_count,
					'visitors'     => $visitors_count,
					'sessions'     => $sessions_count,
					'pageviews'    => $pageviews_count,
					'avg_duration' => self::format_duration( $avg_duration_sec ),
					'bounce_rate'  => $bounce_rate . '%',
				),
				'daily_stats'     => $daily_stats,
				'top_pages'       => $top_pages,
				'traffic_sources' => $traffic_sources,
				'devices'         => $devices,
				'live_visitors'   => $live_visitors,
			)
		);
	}

	public static function format_duration( int $seconds ): string {
		if ( $seconds <= 0 ) {
			return '0s';
		}

		if ( $seconds > 1000000 ) {
			$seconds = (int) round( $seconds / 1000 );
		}

		$hours   = (int) floor( $seconds / 3600 );
		$minutes = (int) floor( ( $seconds % 3600 ) / 60 );
		$secs    = (int) ( $seconds % 60 );

		if ( $hours > 0 ) {
			return sprintf( '%dh %dm %ds', $hours, $minutes, $secs );
		}
		if ( $minutes > 0 ) {
			return sprintf( '%dm %ds', $minutes, $secs );
		}
		return sprintf( '%ds', $secs );
	}
}
