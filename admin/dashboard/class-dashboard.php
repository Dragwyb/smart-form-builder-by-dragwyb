<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Dashboard;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Admin\Db\Submission\Dragwyb_Submission_Db;
use Dragwyb\Form_Builder\Admin\Db\Analytics\Dragwyb_Analytics_Db;
use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;

class Dashboard {

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
		add_action( 'wp_ajax_dragwyb_get_dashboard_data', array( $this, 'ajax_get_dashboard_data' ) );
	}

	public function enqueue_assets( string $hook ): void {
		if ( strpos( $hook, DRAGWYB_PREFIX . '-dashboard' ) === false ) {
			return;
		}

		wp_enqueue_style(
			DRAGWYB_PREFIX . '-editor-global',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/editor-global.css' ),
			array(),
			DRAGWYB_FORM_BUILDER_VERSION
		);

		wp_enqueue_style(
			DRAGWYB_PREFIX . '-dashboard-style',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/dashboard.css' ),
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
			DRAGWYB_PREFIX . '-dashboard-script',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/dashboard.js' ),
			array( 'jquery', DRAGWYB_PREFIX . '-chartjs' ),
			DRAGWYB_FORM_BUILDER_VERSION,
			true
		);

		wp_localize_script(
			DRAGWYB_PREFIX . '-dashboard-script',
			'DragwybDashboardApp',
			array(
				'ajax_url'    => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'dragwyb_admin_nonce' ),
				'builder_url' => admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-form-builder' ),
				'entries_url' => admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-entries' ),
				'i18n'        => array(
					'loading' => __( 'Loading dashboard...', 'smart-form-builder-by-dragwyb' ),
					'error'   => __( 'Failed to load dashboard data.', 'smart-form-builder-by-dragwyb' ),
				),
			)
		);
	}

	public function render_page( $screen ): void {
		if ( ! is_callable( $screen ) || ( ! $screen( 'dashboard' ) && ! $screen( DRAGWYB_PREFIX . '-dashboard' ) ) ) {
			return;
		}

		$builder_url = admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-form-builder' );
		$entries_url = admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-entries' );
		$logo_url    = DRAGWYB_FORM_BUILDER_URL . 'assets/img/menu-logo.svg';
		?>
		<!-- Top Header Bar -->
		<div class="dragwyb-dashboard-header">
			<div class="dragwyb-db-brand">
				<div class="dragwyb-db-logo">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="Smart Form Builder Logo" />
				</div>
				<div class="dragwyb-db-title-group">
					<h1 class="dragwyb-db-brand-name"><?php esc_html_e( 'Smart Form Builder', 'smart-form-builder-by-dragwyb' ); ?></h1>
					<span class="dragwyb-db-sub-title"><?php esc_html_e( 'Dashboard', 'smart-form-builder-by-dragwyb' ); ?></span>
				</div>
			</div>
			<div class="dragwyb-db-header-actions">
				<a href="<?php echo esc_url( $builder_url ); ?>" class="dragwyb-btn-primary-add">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
						<line x1="12" y1="5" x2="12" y2="19"></line>
						<line x1="5" y1="12" x2="19" y2="12"></line>
					</svg>
					<?php esc_html_e( 'Add Form', 'smart-form-builder-by-dragwyb' ); ?>
				</a>
			</div>
		</div>

		<div class="dragwyb-dashboard-wrap">
			<!-- Navigation Bar Tabs -->
			<div class="dragwyb-db-nav-tabs">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-dashboard' ) ); ?>" class="dragwyb-db-nav-item active">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
					<?php esc_html_e( 'Dashboard', 'smart-form-builder-by-dragwyb' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-form-analytics' ) ); ?>" class="dragwyb-db-nav-item">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
					<?php esc_html_e( 'Analytics', 'smart-form-builder-by-dragwyb' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-form-overview' ) ); ?>" class="dragwyb-db-nav-item">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
					<?php esc_html_e( 'View Forms', 'smart-form-builder-by-dragwyb' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-entries' ) ); ?>" class="dragwyb-db-nav-item">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
					<?php esc_html_e( 'Entries', 'smart-form-builder-by-dragwyb' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-settings' ) ); ?>" class="dragwyb-db-nav-item">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
					<?php esc_html_e( 'Settings', 'smart-form-builder-by-dragwyb' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-settings&tab=smtp' ) ); ?>" class="dragwyb-db-nav-item">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
					<?php esc_html_e( 'SMTP', 'smart-form-builder-by-dragwyb' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-settings&tab=gdpr_privacy' ) ); ?>" class="dragwyb-db-nav-item">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
					<?php esc_html_e( 'GDPR / Privacy', 'smart-form-builder-by-dragwyb' ); ?>
				</a>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-settings&tab=import_export' ) ); ?>" class="dragwyb-db-nav-item">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
					<?php esc_html_e( 'Import / Export', 'smart-form-builder-by-dragwyb' ); ?>
				</a>
			</div>

			<!-- Main Content Dashboard Grid -->
			<div class="dragwyb-db-grid">

				<!-- Left Column: Submission Chart & Recent Submissions Table -->
				<div class="dragwyb-db-col-left">

					<!-- Submissions Over Time Chart Card -->
					<div class="dragwyb-db-card dragwyb-chart-card">
						<div class="dragwyb-db-card-header">
							<div class="dragwyb-db-card-title">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
								<h2><?php esc_html_e( 'Submission Over Time', 'smart-form-builder-by-dragwyb' ); ?></h2>
							</div>
							<div class="dragwyb-db-card-controls">
								<select id="dragwyb-db-chart-days" class="dragwyb-db-select">
									<option value="7"><?php esc_html_e( 'Last 7 Days', 'smart-form-builder-by-dragwyb' ); ?></option>
									<option value="14"><?php esc_html_e( 'Last 14 Days', 'smart-form-builder-by-dragwyb' ); ?></option>
									<option value="30"><?php esc_html_e( 'Last 30 Days', 'smart-form-builder-by-dragwyb' ); ?></option>
								</select>
								<button type="button" class="dragwyb-db-icon-btn" title="<?php esc_attr_e( 'Select Date Range', 'smart-form-builder-by-dragwyb' ); ?>">
									<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
								</button>
							</div>
						</div>
						<div class="dragwyb-db-chart-container">
							<canvas id="dragwyb-db-submissions-chart"></canvas>
						</div>
					</div>

					<!-- Unread Submissions Table Card -->
					<div class="dragwyb-db-card dragwyb-table-card">
						<div class="dragwyb-db-card-header">
							<div class="dragwyb-db-card-title">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
								<h2><?php esc_html_e( 'Unread Submissions', 'smart-form-builder-by-dragwyb' ); ?></h2>
							</div>
							<a href="<?php echo esc_url( $entries_url ); ?>" class="dragwyb-btn-secondary-link">
								<?php esc_html_e( 'View All Entries', 'smart-form-builder-by-dragwyb' ); ?>
							</a>
						</div>
						<div class="dragwyb-db-table-responsive">
							<table class="dragwyb-db-table" id="dragwyb-db-recent-table">
								<thead>
									<tr>
										<th><?php esc_html_e( 'Name', 'smart-form-builder-by-dragwyb' ); ?></th>
										<th><?php esc_html_e( 'Form Name', 'smart-form-builder-by-dragwyb' ); ?></th>
										<th><?php esc_html_e( 'Email', 'smart-form-builder-by-dragwyb' ); ?></th>
										<th><?php esc_html_e( 'Submitted On', 'smart-form-builder-by-dragwyb' ); ?></th>
										<th><?php esc_html_e( 'IP Address', 'smart-form-builder-by-dragwyb' ); ?></th>
										<th><?php esc_html_e( 'Actions', 'smart-form-builder-by-dragwyb' ); ?></th>
									</tr>
								</thead>
								<tbody id="dragwyb-db-table-body">
									<tr>
										<td colspan="6" class="dragwyb-loading-cell">
											<?php esc_html_e( 'Loading recent submissions...', 'smart-form-builder-by-dragwyb' ); ?>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>

				</div>

				<!-- Right Column: Analytics Overview & Sidebar Cards -->
				<div class="dragwyb-db-col-right">

					<!-- Form Analytics Overview Card -->
					<div class="dragwyb-db-card dragwyb-analytics-card">
						<div class="dragwyb-db-card-header">
							<div class="dragwyb-db-card-title">
								<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f43f5e" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
								<h2><?php esc_html_e( 'Analytics Overview', 'smart-form-builder-by-dragwyb' ); ?></h2>
							</div>
							<select id="dragwyb-db-stats-timeframe" class="dragwyb-db-select-sm">
								<option value="today"><?php esc_html_e( 'Today', 'smart-form-builder-by-dragwyb' ); ?></option>
								<option value="7"><?php esc_html_e( 'Last 7 Days', 'smart-form-builder-by-dragwyb' ); ?></option>
								<option value="30"><?php esc_html_e( 'Last 30 Days', 'smart-form-builder-by-dragwyb' ); ?></option>
								<option value="all" selected><?php esc_html_e( 'All Time', 'smart-form-builder-by-dragwyb' ); ?></option>
							</select>
						</div>
						<div class="dragwyb-db-stats-grid">
							<!-- Card 1: Total Forms -->
							<div class="dragwyb-db-stat-tile tile-pink">
								<div class="stat-icon">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
								</div>
								<div class="stat-content">
									<div class="stat-number" id="db-stat-forms">0</div>
									<div class="stat-label"><?php esc_html_e( 'Total Forms', 'smart-form-builder-by-dragwyb' ); ?></div>
								</div>
							</div>

							<!-- Card 2: Total Form Submissions -->
							<div class="dragwyb-db-stat-tile tile-green">
								<div class="stat-icon">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
								</div>
								<div class="stat-content">
									<div class="stat-number" id="db-stat-submissions">0</div>
									<div class="stat-label"><?php esc_html_e( 'Total Form Submissions', 'smart-form-builder-by-dragwyb' ); ?></div>
								</div>
							</div>

							<!-- Card 3: Unique Visitors -->
							<div class="dragwyb-db-stat-tile tile-purple">
								<div class="stat-icon">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
								</div>
								<div class="stat-content">
									<div class="stat-number" id="db-stat-visitors">0</div>
									<div class="stat-label"><?php esc_html_e( 'Unique Visitors', 'smart-form-builder-by-dragwyb' ); ?></div>
								</div>
							</div>

							<!-- Card 4: Total Sessions -->
							<div class="dragwyb-db-stat-tile tile-blue">
								<div class="stat-icon">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
								</div>
								<div class="stat-content">
									<div class="stat-number" id="db-stat-sessions">0</div>
									<div class="stat-label"><?php esc_html_e( 'Total Sessions', 'smart-form-builder-by-dragwyb' ); ?></div>
								</div>
							</div>

							<!-- Card 5: Total PageViews -->
							<div class="dragwyb-db-stat-tile tile-orange">
								<div class="stat-icon">
									<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
								</div>
								<div class="stat-content">
									<div class="stat-number" id="db-stat-pageviews">0</div>
									<div class="stat-label"><?php esc_html_e( 'Total PageViews', 'smart-form-builder-by-dragwyb' ); ?></div>
								</div>
							</div>
						</div>
					</div>

					<!-- Support Card -->
					<div class="dragwyb-db-card dragwyb-support-card">
						<div class="dragwyb-support-icon">
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640"><path d="M320 128C241 128 175.3 185.3 162.3 260.7C171.6 257.7 181.6 256 192 256L208 256C234.5 256 256 277.5 256 304L256 400C256 426.5 234.5 448 208 448L192 448C139 448 96 405 96 352L96 288C96 164.3 196.3 64 320 64C443.7 64 544 164.3 544 288L544 456.1C544 522.4 490.2 576.1 423.9 576.1L336 576L304 576C277.5 576 256 554.5 256 528C256 501.5 277.5 480 304 480L336 480C362.5 480 384 501.5 384 528L384 528L424 528C463.8 528 496 495.8 496 456L496 435.1C481.9 443.3 465.5 447.9 448 447.9L432 447.9C405.5 447.9 384 426.4 384 399.9L384 303.9C384 277.4 405.5 255.9 432 255.9L448 255.9C458.4 255.9 468.3 257.5 477.7 260.6C464.7 185.3 399.1 127.9 320 127.9z"/></svg>
						</div>
						<h3 class="dragwyb-support-title"><?php esc_html_e( 'Support', 'smart-form-builder-by-dragwyb' ); ?></h3>
						<p class="dragwyb-support-desc"><?php esc_html_e( "Need help? We're here for you.", 'smart-form-builder-by-dragwyb' ); ?></p>
						<div class="dragwyb-support-actions">
							<a href="https://wordpress.org/support/plugin/smart-form-builder-by-dragwyb/" target="_blank" rel="noopener noreferrer" class="dragwyb-btn-doc">
								<?php esc_html_e( 'Free Support', 'smart-form-builder-by-dragwyb' ); ?>
							</a>
							<a href="https://dragwyb.com/contact/?utm_source=dashboard&utm_medium=contact&utm_campaign=form-builder" target="_blank" rel="noopener noreferrer" class="dragwyb-btn-premium">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 18v-6a9 9 0 0 1 18 0v6"></path><path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3zM3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"></path></svg>
								<?php esc_html_e( 'Premium Support', 'smart-form-builder-by-dragwyb' ); ?>
							</a>
						</div>
					</div>

					<!-- More Plugins Card -->
					<div class="dragwyb-db-card dragwyb-promo-card">
						<div class="dragwyb-promo-header">
							<div class="dragwyb-star-badge">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="#f43f5e" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
							</div>
							<div>
								<h3 class="dragwyb-promo-title"><?php esc_html_e( 'More Plugins by Dragwyb', 'smart-form-builder-by-dragwyb' ); ?></h3>
								<p class="dragwyb-promo-subtitle"><?php esc_html_e( 'Powerful plugins to extend your website.', 'smart-form-builder-by-dragwyb' ); ?></p>
							</div>
						</div>

						<div class="dragwyb-plugin-feature-box">
							<div class="plugin-icon-bubble">
								<img src="<?php echo esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/images/chatbot-ai.png' ); ?>" loading="lazy" alt=""/>
							</div>
							<div class="plugin-info">
								<h4 class="plugin-name"><?php esc_html_e( 'AI Chatbot & Floating widget', 'smart-form-builder-by-dragwyb' ); ?></h4>
								<p class="plugin-desc"><?php esc_html_e( 'Add AI Chatbot & Floating chat widgets to your website.', 'smart-form-builder-by-dragwyb' ); ?></p>
							</div>
							<div class="dragwyb-plugin-btn-wrapper">
								<a href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=dragwyb-click-to-chat' ) ); ?>" class="dragwyb-btn-install" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Install Now', 'smart-form-builder-by-dragwyb' ); ?>
								</a>
							</div>
						</div>

						<div class="dragwyb-promo-footer">
							<a href="https://dragwyb.com/products/?utm_source=dashboard&utm_medium=plugin&utm_campaign=form-builder" target="_blank" rel="noopener noreferrer" class="dragwyb-link-arrow">
								<?php esc_html_e( 'View All Plugins', 'smart-form-builder-by-dragwyb' ); ?> &rarr;
							</a>
						</div>
					</div>

				</div>

			</div>
		</div>
		<?php
	}

	public function ajax_get_dashboard_data(): void {
		check_ajax_referer( 'dragwyb_admin_nonce', 'nonce' );
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Unauthorized', 'smart-form-builder-by-dragwyb' ) ) );
		}

		global $wpdb;

		// 1. Chart Submissions Data
		$chart_days = absint( $_POST['chart_days'] ?? 7 );
		if ( $chart_days <= 0 ) {
			$chart_days = 7;
		}

		$submissions_table = Dragwyb_Submission_Db::table_name();

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$raw_chart_data = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DATE(created_at) as date_val, COUNT(id) as total FROM {$submissions_table} WHERE created_at >= DATE_SUB(NOW(), INTERVAL %d DAY) GROUP BY DATE(created_at) ORDER BY date_val ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$chart_days
			),
			ARRAY_A
		);

		// Map dates for continuous timeline
		$chart_labels = array();
		$chart_values = array();

		$start_time = strtotime( "-{$chart_days} days" );
		$end_time   = time();

		$db_map = array();
		if ( ! empty( $raw_chart_data ) ) {
			foreach ( $raw_chart_data as $row ) {
				$db_map[ $row['date_val'] ] = (int) $row['total'];
			}
		}

		for ( $t = $start_time; $t <= $end_time; $t += 86400 ) {
			$d_key          = gmdate( 'Y-m-d', $t );
			$chart_labels[] = gmdate( 'M d', $t );
			$chart_values[] = $db_map[ $d_key ] ?? 0;
		}

		// 2. Overview Stat Tiles
		$timeframe = sanitize_text_field( wp_unslash( $_POST['stats_timeframe'] ?? 'all' ) );

		// Forms count
		$forms_obj   = wp_count_posts( Dragwyb_Post::POST_TYPE );
		$total_forms = isset( $forms_obj->publish ) ? (int) $forms_obj->publish : 0;

		// Submissions count
		$sub_db            = new Dragwyb_Submission_Db();
		$total_submissions = $sub_db->get_total_count();

		// Unique visitors, sessions, pageviews
		$visitors_table  = Dragwyb_Analytics_Db::table_name( 'visitors' );
		$sessions_table  = Dragwyb_Analytics_Db::table_name( 'sessions' );
		$pageviews_table = Dragwyb_Analytics_Db::table_name( 'pageviews' );

		$range = 1;
		$days  = 0;

		if ( 'all' === $timeframe ) {
			$range = -1;
			$days  = 1;
		} elseif ( '7' === $timeframe ) {
			$range = 7;
			$days  = 7;
		} elseif ( '30' === $timeframe ) {
			$range = 30;
			$days  = 30;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$unique_visitors = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $visitors_table" . $this->get_date_where_clause( $range, 'first_seen' ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				$days
			)
		);
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$total_sessions = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $sessions_table" . $this->get_date_where_clause( $range, 'started_at' ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				$days
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		$total_pageviews = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(id) FROM $pageviews_table" . $this->get_date_where_clause( $range, 'created_at' ), // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
				$days
			)
		);

		// 3. Recent Submissions Table
		$page   = max( 1, absint( $_POST['submissions_page'] ?? 1 ) );
		$limit  = 6;
		$offset = ( $page - 1 ) * $limit;

		$recent_rows = $sub_db->get_all(
			array(
				'limit'   => $limit,
				'offset'  => $offset,
				'orderby' => 'created_at',
				'order'   => 'DESC',
				'status'  => 'unread',
			)
		);

		$formatted_submissions = array();
		$avatar_colors         = array(
			array(
				'bg'    => '#ffe4e6',
				'color' => '#e11d48',
			), // Red/Pink
			array(
				'bg'    => '#dcfce7',
				'color' => '#16a34a',
			), // Green
			array(
				'bg'    => '#f3e8ff',
				'color' => '#9333ea',
			), // Purple
			array(
				'bg'    => '#ffedd5',
				'color' => '#ea580c',
			), // Orange
			array(
				'bg'    => '#e0f2fe',
				'color' => '#0284c7',
			), // Blue
		);

		if ( ! empty( $recent_rows ) ) {
			foreach ( $recent_rows as $idx => $row ) {
				$sub_data   = json_decode( $row->submission_data ?? '{}', true );
				$extra_data = json_decode( $row->extra_data ?? '{}', true );

				$name  = $this->extract_field_value( $sub_data, array( 'name', 'full_name', 'first_name', 'your_name' ) ) ?: __( 'Null', 'smart-form-builder-by-dragwyb' );
				$email = $this->extract_field_value( $sub_data, array( 'email', 'your_email', 'user_email' ) ) ?: __( 'Null', 'smart-form-builder-by-dragwyb' );

				// Form title
				$form_post = get_post( (int) $row->form_id );
				// translators: %d is the form id
				$form_name = $form_post ? $form_post->post_title : sprintf( __( 'Form #%d', 'smart-form-builder-by-dragwyb' ), $row->form_id );

				// Formatted date
				$submitted_on = gmdate( 'M d, Y h:i A', strtotime( $row->created_at ) );

				// IP Address
				$ip_address = $extra_data['visitor_info']['ip_address'] ?? '192.168.1.1';
				if ( '-' === $ip_address || empty( $ip_address ) ) {
					$ip_address = '127.0.0.1';
				}

				// Initials
				$initials = $this->get_initials( $name );
				$color    = $avatar_colors[ $idx % count( $avatar_colors ) ];

				$formatted_submissions[] = array(
					'id'           => (int) $row->id,
					'name'         => $name,
					'initials'     => $initials,
					'avatar_bg'    => $color['bg'],
					'avatar_color' => $color['color'],
					'form_id'      => (int) $row->form_id,
					'form_name'    => $form_name,
					'email'        => $email,
					'submitted_on' => $submitted_on,
					'ip_address'   => $ip_address,
					'view_url'     => admin_url( 'admin.php?page=' . DRAGWYB_PREFIX . '-entries&entry_id=' . $row->id ),
				);
			}
		}

		$total_recent_submissions = $total_submissions;
		$total_pages              = ceil( $total_recent_submissions / $limit );

		wp_send_json_success(
			array(
				'chart'       => array(
					'labels' => $chart_labels,
					'values' => $chart_values,
				),
				'stats'       => array(
					'total_forms'       => $total_forms,
					'total_submissions' => $total_submissions,
					'unique_visitors'   => $unique_visitors,
					'total_sessions'    => $total_sessions,
					'total_pageviews'   => $total_pageviews,
				),
				'submissions' => $formatted_submissions,
				'pagination'  => array(
					'total' => $total_recent_submissions,
					'page'  => $page,
					'limit' => $limit,
					'pages' => max( 1, (int) $total_pages ),
				),
			)
		);
	}

	private function extract_field_value( array $sub_data, array $keys ): string {
		foreach ( $keys as $key ) {
			if ( isset( $sub_data[ $key ] ) && ! empty( $sub_data[ $key ] ) ) {
				if ( isset( $sub_data[ $key ]['type'] ) ) {
					unset( $sub_data[ $key ]['type'] );
					unset( $sub_data[ $key ]['label'] );
				}
				return is_array( $sub_data[ $key ] ) ? implode( ', ', $sub_data[ $key ] ) : (string) $sub_data[ $key ];
			}
		}

		foreach ( $sub_data as $field_key => $field_val ) {
			if ( empty( $field_val ) ) {
				continue;
			}
			$lower_key = strtolower( (string) $field_key );
			foreach ( $keys as $k ) {
				if ( strpos( $lower_key, $k ) !== false ) {
					return is_array( $field_val ) ? implode( ', ', $field_val ) : (string) $field_val;
				}
			}
		}

		return '';
	}

	private function get_initials( string $name ): string {
		$name  = trim( $name );
		$words = explode( ' ', $name );
		if ( count( $words ) >= 2 ) {
			return strtoupper( substr( $words[0], 0, 1 ) . substr( $words[ count( $words ) - 1 ], 0, 1 ) );
		}
		return strtoupper( substr( $name, 0, 2 ) );
	}

	private function get_date_where_clause( int $range, string $from = 'first_seen' ) {
		if ( $range === -1 ) {
			return ' WHERE 1 = %s';
		}

		if ( ! in_array( $range, array( 1, 7, 30 ), true ) ) {
			return '';
		}

		if ( ! in_array( $from, array( 'first_seen', 'created_at', 'started_at' ), true ) ) {
			return '';
		}

		if ( $range === 1 ) {
			return " WHERE DATE($from) = CURDATE()";
		}

		return " WHERE $from >= DATE_SUB(NOW(), INTERVAL %s DAY)";
	}
}
