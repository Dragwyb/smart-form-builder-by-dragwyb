<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Entries;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Entries {

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
		// Only load on our entries page
		if ( strpos( $hook, DRAGWYB_PREFIX . '-entries' ) === false ) {
			return;
		}

		wp_enqueue_style(
			DRAGWYB_PREFIX . '-entries-style',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/css/entries.css' ),
			array(),
			esc_attr( DRAGWYB_FORM_BUILDER_VERSION )
		);

		wp_enqueue_script(
			DRAGWYB_PREFIX . '-entries-script',
			esc_url( DRAGWYB_FORM_BUILDER_URL . 'assets/js/entries.js' ),
			array(),
			esc_attr( DRAGWYB_FORM_BUILDER_VERSION ),
			true
		);

		wp_localize_script(
			DRAGWYB_PREFIX . '-entries-script',
			'DragwybEntriesApp',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'dragwyb_admin_nonce' ),
				'i18n'     => array(
					'loading'       => __( 'Loading...', 'smart-form-builder-by-dragwyb' ),
					'no_entries'    => __( 'No entries found.', 'smart-form-builder-by-dragwyb' ),
					'error_loading' => __( 'Error loading entries.', 'smart-form-builder-by-dragwyb' ),
					'all_forms'     => __( 'All Forms', 'smart-form-builder-by-dragwyb' ),
					'page'          => __( 'Page', 'smart-form-builder-by-dragwyb' ),
					'of'            => __( 'of', 'smart-form-builder-by-dragwyb' ),
				),
			)
		);
	}

	public function render_page( $screen ): void {
		if ( gettype( $screen ) === 'object' && $screen( 'entries' ) ) {
			$this->display_entries();
		}
	}

	public function display_entries(): void {
		?>
		<div class="wrap dragwyb-entries-wrap">
			<h1 class="wp-heading-inline"><?php esc_html_e( 'Form Entries', 'smart-form-builder-by-dragwyb' ); ?></h1>
			<hr class="wp-header-end">

			<div class="dragwyb-entries-controls">
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
					<input type="search" id="dragwyb-entry-search" placeholder="<?php esc_attr_e( 'Search IP or Data...', 'smart-form-builder-by-dragwyb' ); ?>">
					<button type="button" id="dragwyb-search-btn" class="button"><?php esc_html_e( 'Search', 'smart-form-builder-by-dragwyb' ); ?></button>
				</div>
			</div>

			<div class="dragwyb-entries-table-container">
				<table class="wp-list-table widefat fixed striped dragwyb-custom-table">
					<thead>
						<tr>
							<th scope="col" id="cb" class="manage-column column-cb check-column" style="width: 2.2em; vertical-align: middle;">
								<input id="dragwyb-select-all" type="checkbox">
							</th>
							<th scope="col" id="col-id" class="manage-column column-id sortable desc" data-orderby="id">
								<a href="#"><span><?php esc_html_e( 'ID', 'smart-form-builder-by-dragwyb' ); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="col-form_id" class="manage-column column-form_id sortable desc" data-orderby="form_id">
								<a href="#"><span><?php esc_html_e( 'Form ID', 'smart-form-builder-by-dragwyb' ); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="col-data" class="manage-column column-data">
								<?php esc_html_e( 'Submission Data', 'smart-form-builder-by-dragwyb' ); ?>
							</th>
							<th scope="col" id="col-ip" class="manage-column column-ip sortable desc" data-orderby="ip_address">
								<a href="#"><span><?php esc_html_e( 'IP Address', 'smart-form-builder-by-dragwyb' ); ?></span><span class="sorting-indicator"></span></a>
							</th>
							<th scope="col" id="col-date" class="manage-column column-date sortable desc sorted" data-orderby="created_at">
								<a href="#"><span><?php esc_html_e( 'Date', 'smart-form-builder-by-dragwyb' ); ?></span><span class="sorting-indicator"></span></a>
							</th>
						</tr>
					</thead>
					<tbody id="dragwyb-entries-body">
						<tr>
							<td colspan="5" class="dragwyb-loading-state"><?php esc_html_e( 'Loading...', 'smart-form-builder-by-dragwyb' ); ?></td>
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
			<div id="dragwyb-view-modal" class="dragwyb-modal dragwyb-entries-modal" style="display: none;">
				<div class="dragwyb-modal-header">
					<h2><?php esc_html_e( 'View Entry', 'smart-form-builder-by-dragwyb' ); ?></h2>
					<button type="button" class="dragwyb-modal-close"><span class="dashicons dashicons-no-alt"></span></button>
				</div>
				<div class="dragwyb-modal-body" id="dragwyb-view-modal-body">
					<!-- Dynamic Content -->
				</div>
			</div>

			<!-- Edit Modal -->
			<div id="dragwyb-edit-modal" class="dragwyb-modal" style="display: none;">
				<div class="dragwyb-modal-header">
					<h2><?php esc_html_e( 'Edit Entry', 'smart-form-builder-by-dragwyb' ); ?></h2>
					<button type="button" class="dragwyb-modal-close"><span class="dashicons dashicons-no-alt"></span></button>
				</div>
				<div class="dragwyb-modal-body">
					<form id="dragwyb-edit-entry-form">
						<input type="hidden" id="dragwyb-edit-entry-id" name="id" value="">
						<div id="dragwyb-edit-modal-body">
							<!-- Dynamic Inputs -->
						</div>
					</form>
				</div>
				<div class="dragwyb-modal-footer">
					<button type="button" class="button dragwyb-modal-close-btn"><?php esc_html_e( 'Cancel', 'smart-form-builder-by-dragwyb' ); ?></button>
					<button type="button" class="button button-primary" id="dragwyb-save-entry-btn"><?php esc_html_e( 'Save Changes', 'smart-form-builder-by-dragwyb' ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}
}
