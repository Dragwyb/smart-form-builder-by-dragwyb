<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Error_Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Error_Log {

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
	}

	public function enqueue_assets( $hook ): void {
		// Only load on our error log page
		if ( strpos( $hook, DRAGWYB_PREFIX . '-error-log' ) === false ) {
			return;
		}

		wp_enqueue_style(
			DRAGWYB_PREFIX . '-error-log-style',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/error-log.css' ),
			array(),
			esc_attr( DRAGWYB_FORM_BUILDER_VERSION )
		);

		wp_enqueue_script(
			DRAGWYB_PREFIX . '-error-log-script',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/error-log.js' ),
			array(),
			esc_attr( DRAGWYB_FORM_BUILDER_VERSION ),
			true
		);

		wp_localize_script(
			DRAGWYB_PREFIX . '-error-log-script',
			'DragwybErrorLogApp',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'dragwyb_admin_nonce' ),
				'i18n'     => array(
					'loading'        => __( 'Loading...', 'smart-form-builder-by-dragwyb' ),
					'no_errors'      => __( 'No validation errors logged.', 'smart-form-builder-by-dragwyb' ),
					'error_loading'  => __( 'Error loading error logs.', 'smart-form-builder-by-dragwyb' ),
					'all_forms'      => __( 'All Forms', 'smart-form-builder-by-dragwyb' ),
					'page'           => __( 'Page', 'smart-form-builder-by-dragwyb' ),
					'of'             => __( 'of', 'smart-form-builder-by-dragwyb' ),
					'clear_confirm'  => __( 'Are you sure you want to clear ALL error logs? This cannot be undone.', 'smart-form-builder-by-dragwyb' ),
					'delete_confirm' => __( 'Are you sure you want to delete this log entry?', 'smart-form-builder-by-dragwyb' ),
				),
			)
		);
	}

	public function render_page( $screen ): void {
		if ( gettype( $screen ) === 'object' && $screen( 'error-log' ) ) {
			$this->display_errors();
		}
	}

	public function display_errors(): void {
		?>
		<div class="wrap dragwyb-error-log-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Submission Error Log', 'smart-form-builder-by-dragwyb' ); ?></h1>
			<button type="button" id="dragwyb-clear-all-btn" class="button button-link-delete dragwyb-clear-all-btn">
				<span class="dashicons dashicons-trash"></span> <?php esc_html_e( 'Clear All Logs', 'smart-form-builder-by-dragwyb' ); ?>
			</button>
			<hr class="wp-header-end">

			<div class="dragwyb-error-log-controls">
				<div class="dragwyb-filters">
					<select id="dragwyb-form-filter" class="dragwyb-select">
						<option value="0"><?php esc_html_e( 'Loading forms...', 'smart-form-builder-by-dragwyb' ); ?></option>
					</select>
					<div class="dragwyb-bulk-actions actions bulkactions" style="display: flex; gap: 5px; align-items: center; margin-left: 10px;">
						<select id="dragwyb-bulk-action" class="dragwyb-select" style="min-width: 130px; padding: 3px 24px 3px 8px;">
							<option value="-1"><?php esc_html_e( 'Bulk actions', 'smart-form-builder-by-dragwyb' ); ?></option>
							<option value="delete"><?php esc_html_e( 'Delete', 'smart-form-builder-by-dragwyb' ); ?></option>
						</select>
						<button type="button" id="dragwyb-bulk-action-btn" class="button"><?php esc_html_e( 'Apply', 'smart-form-builder-by-dragwyb' ); ?></button>
					</div>
				</div>

				<div class="dragwyb-search-box">
					<input type="search" id="dragwyb-error-search" placeholder="<?php esc_attr_e( 'Search IP, Data, Errors...', 'smart-form-builder-by-dragwyb' ); ?>">
					<button type="button" id="dragwyb-search-btn" class="button"><?php esc_html_e( 'Search', 'smart-form-builder-by-dragwyb' ); ?></button>
				</div>
			</div>

			<div class="dragwyb-error-log-table-container">
				<table class="wp-list-table widefat fixed striped dragwyb-custom-table">
					<thead>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb check-column" style="width: 2.2em; vertical-align: middle;">
								<input id="dragwyb-select-all" type="checkbox">
							</th>
							<th scope="col" id="col-id" class="manage-column column-id sortable desc" data-orderby="id" style="width: 80px;">
								<a href="#"><span><?php esc_html_e( 'ID', 'smart-form-builder-by-dragwyb' ); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="col-form_id" class="manage-column column-form_id sortable desc" data-orderby="form_id" style="width: 150px;">
								<a href="#"><span><?php esc_html_e( 'Form', 'smart-form-builder-by-dragwyb' ); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="col-errors" class="manage-column column-errors">
								<?php esc_html_e( 'Validation Errors', 'smart-form-builder-by-dragwyb' ); ?>
							</th>
							<th scope="col" id="col-data" class="manage-column column-data">
								<?php esc_html_e( 'Submitted Data', 'smart-form-builder-by-dragwyb' ); ?>
							</th>
							<th scope="col" id="col-ip" class="manage-column column-ip sortable desc" data-orderby="ip_address" style="width: 130px;">
								<a href="#"><span><?php esc_html_e( 'IP Address', 'smart-form-builder-by-dragwyb' ); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="col-date" class="manage-column column-date sortable desc sorted" data-orderby="created_at" style="width: 160px;">
								<a href="#"><span><?php esc_html_e( 'Date', 'smart-form-builder-by-dragwyb' ); ?></span><span class="sorting-indicator"></span></a>
							</th>
						</tr>
					</thead>
					<tbody id="dragwyb-error-log-body">
						<tr>
							<td colspan="6" class="dragwyb-loading-state"><?php esc_html_e( 'Loading...', 'smart-form-builder-by-dragwyb' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>

			<div class="dragwyb-pagination tablenav bottom">
				<div class="tablenav-pages">
					<span class="displaying-num" id="dragwyb-total-items">0 items</span>
					<span class="pagination-links">
						<button class="button" id="dragwyb-prev-page" disabled>&lsaquo;</button>
						<span class="paging-input">
							<span class="tablenav-paging-text">
								<span id="dragwyb-current-page">1</span> <?php esc_html_e( 'of', 'smart-form-builder-by-dragwyb' ); ?> <span id="dragwyb-total-pages">1</span>
							</span>
						</span>
						<button class="button" id="dragwyb-next-page" disabled>&rsaquo;</button>
					</span>
				</div>
			</div>
		</div>

		<!-- Modals Container -->
		<div id="dragwyb-modal-overlay" class="dragwyb-modal-overlay">
			<!-- View Modal -->
			<div id="dragwyb-view-modal" class="dragwyb-modal dragwyb-error-modal">
				<div class="dragwyb-modal-header">
					<h2><?php esc_html_e( 'Submission Error Details', 'smart-form-builder-by-dragwyb' ); ?></h2>
					<button type="button" class="dragwyb-modal-close"><span class="dashicons dashicons-no-alt"></span></button>
				</div>
				<div class="dragwyb-modal-body" id="dragwyb-view-modal-body">
					<!-- Dynamic Content -->
				</div>
			</div>
		</div>
		<?php
	}
}
