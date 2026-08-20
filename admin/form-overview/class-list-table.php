<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Form_Overview;

use WP_List_Table;
use WP_Post;
use WP_Screen;
use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;
use Dragwyb\Form_Builder\Includes\Frontend\Form_Preview;

/**
 * Generate the table on the plugin overview page.
 *
 * @since 1.8.6
 */
class List_Table extends WP_List_Table {


	public $per_page;
	private $count;
	private $view;
	/**
	 * Upload directory.
	 *
	 * @var string
	 */
	private $upload_dir;

	/**
	 * Cached submission counts indexed by form ID.
	 *
	 * @var array
	 */
	private $submission_counts = array();

	/**
	 * Primary class constructor.
	 *
	 * @since 1.8.6
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'dragwyb-form',
				'plural'   => 'dragwyb-forms',
				'ajax'     => false,
			)
		);

		$this->per_page = (int) apply_filters( DRAGWYB_PREFIX . '_overview_per_page', 20 );

		$upload_info = wp_upload_dir();
		// Create a specific folder for your plugin's CSS
		$this->upload_dir = $upload_info['basedir'] . '/dragwyb-forms/css/';
	}

	/**
	 * Get the instance of a class and store it in itself.
	 *
	 * @since 1.8.6
	 */
	public static function get_instance() {
		static $instance;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 1.8.6
	 *
	 * @return array
	 */
	public function get_columns() {
		$columns = array(
			'cb'              => '<input type="checkbox" />', // Required for bulk actions
			'name'            => __( 'Name', 'smart-form-builder-by-dragwyb' ),
			'shortcode'       => __( 'Shortcode', 'smart-form-builder-by-dragwyb' ),
			'views'           => __( 'Views', 'smart-form-builder-by-dragwyb' ) . ' <span class="dragwyb-info-icon" title="' . esc_attr__( 'Form preview count', 'smart-form-builder-by-dragwyb' ) . '">ⓘ</span>',
			'submissions'     => __( 'Submissions', 'smart-form-builder-by-dragwyb' ) . ' <span class="dragwyb-info-icon" title="' . esc_attr__( 'Form submission count', 'smart-form-builder-by-dragwyb' ) . '">ⓘ</span>',
			'conversion_rate' => __( 'Conversion Rate', 'smart-form-builder-by-dragwyb' ) . ' <span class="dragwyb-info-icon" title="' . esc_attr__( 'Submission / View ratio', 'smart-form-builder-by-dragwyb' ) . '">ⓘ</span>',
			'clean_cache'     => __( 'Clean Cache', 'smart-form-builder-by-dragwyb' ),
			'date'            => __( 'Date', 'smart-form-builder-by-dragwyb' ),
		);

		// Modify columns via a filter
		$columns = apply_filters( 'dragwyb_form_overview_columns', $columns );

		return $columns;
	}

	/**
	 * Render the checkbox column.
	 *
	 * @since 1.8.6
	 *
	 * @param WP_Post $form Form.
	 *
	 * @return string
	 */
	public function column_cb( $form ) {
		return sprintf(
			'<input type="checkbox" name="post[]" value="%d" />',
			$form->ID
		);
	}

	/**
	 * Render the columns.
	 *
	 * @since 1.8.6
	 *
	 * @param WP_Post $form        CPT object as a form representation.
	 * @param string  $column_name Column Name.
	 *
	 * @return string
	 */
	public function column_default( $form, $column_name ) {
		switch ( $column_name ) {
			case 'shortcode':
				$shortcode = '[' . DRAGWYB_PREFIX . '-form id="' . $form->ID . '"]';
				$value     = sprintf(
					'<div class="dragwyb-shortcode-box dragwyb-shortcode dragwyb-shortcode-value" data-shortcode="%s" title="%s"><span class="dragwyb-shortcode-text">%s</span><span class="dashicons dashicons-admin-page dragwyb-copy-icon"></span></div>',
					esc_attr( $shortcode ),
					esc_attr__( 'Click to copy shortcode', 'smart-form-builder-by-dragwyb' ),
					esc_html( $shortcode )
				);
				break;

			case 'views':
				$login_views    = (int) get_post_meta( $form->ID, '_dragwyb_form_login_preview_count', true );
				$frontend_views = (int) get_post_meta( $form->ID, '_dragwyb_form_frontend_preview_count', true );
				$total_views    = $login_views + $frontend_views;
				$value          = sprintf(
					'<div class="dragwyb-views-card"><div class="dragwyb-views-header"><span class="dashicons dashicons-visibility"></span> <strong class="dragwyb-views-count">%d</strong></div><div class="dragwyb-views-sub"><div class="dragwyb-view-subrow">%s: %d</div><div class="dragwyb-view-subrow">%s: %d</div></div></div>',
					$total_views,
					esc_html__( 'Frontend', 'smart-form-builder-by-dragwyb' ),
					$frontend_views,
					esc_html__( 'Login', 'smart-form-builder-by-dragwyb' ),
					$login_views
				);
				break;

			case 'submissions':
			case 'entries':
				$submission_count = isset( $this->submission_counts[ $form->ID ] ) ? $this->submission_counts[ $form->ID ] : 0;
				$value            = sprintf(
					'<div class="dragwyb-submissions-card"><strong class="dragwyb-submissions-count">%d</strong></div>',
					$submission_count
				);
				break;

			case 'conversion_rate':
				$login_views      = (int) get_post_meta( $form->ID, '_dragwyb_form_login_preview_count', true );
				$frontend_views   = (int) get_post_meta( $form->ID, '_dragwyb_form_frontend_preview_count', true );
				$total_views      = $login_views + $frontend_views;
				$submission_count = isset( $this->submission_counts[ $form->ID ] ) ? $this->submission_counts[ $form->ID ] : 0;

				$rate           = $total_views > 0 ? ( $submission_count / $total_views ) * 100 : 0;
				$rate_formatted = number_format( $rate, 1 ) . '%';
				$progress_width = min( 100, max( 0, $rate ) );

				$value = sprintf(
					'<div class="dragwyb-conversion-wrap"><div class="dragwyb-conversion-value">%s</div><div class="dragwyb-progress-track"><div class="dragwyb-progress-fill" style="width: %f%%;"></div></div></div>',
					esc_html( $rate_formatted ),
					$progress_width
				);
				break;

			case 'created':
				$value = get_the_date( 'Y-m-d', $form );
				break;

			case 'date':
				$date_str = get_the_modified_date( 'Y-m-d', $form );
				$time_str = get_the_modified_date( 'h:i A', $form );
				$value    = sprintf(
					'<div class="dragwyb-date-cell"><div class="dragwyb-date-wrap"><span class="dragwyb-date-main">%s</span><span class="dragwyb-date-time">%s</span></div></div>',
					esc_html( $date_str ),
					esc_html( $time_str )
				);
				break;

			case 'clean_cache':
				if ( $this->css_cache_exist( $form->ID ) ) {
					$value = sprintf(
						'<button type="button" id="%s" data-key="%s" data-clean-key="%s" class="dragwyb-clean-btn active"><span class="dashicons dashicons-update"></span> %s</button>',
						esc_attr( 'clean-cache-' . (int) $form->ID ),
						esc_attr( wp_create_nonce( sanitize_text_field( $form->post_type ) . (int) $form->ID . '-clean-cache' ) ),
						esc_attr( wp_create_nonce( 'delete_cache_nonce' ) ),
						esc_html__( 'Clean Cache', 'smart-form-builder-by-dragwyb' )
					);
				} else {
					$value = sprintf(
						'<button type="button" id="%s" disabled class="dragwyb-clean-btn disabled"><span class="dashicons dashicons-update"></span> %s</button>',
						esc_attr( 'clean-cache-' . (int) $form->ID ),
						esc_html__( 'Clean Cache', 'smart-form-builder-by-dragwyb' )
					);
				}
				break;

			default:
				$value = '';
		}

		return apply_filters( 'dragwyb_form_overview_column_value', $value, $form, $column_name );
	}

	private function css_cache_exist( $form_id ) {
		$unique    = get_post_meta( $form_id, '_dragwyb_form_assets_id', true ) ?: 0;
		$file_name = 'form-' . $form_id . '-' . $unique . '.css';

		$file_path = $this->upload_dir . $file_name;
		return file_exists( $file_path );
	}

	/**
	 * Render the form name column with action links.
	 *
	 * @since 1.8.6
	 *
	 * @param WP_Post $form Form.
	 *
	 * @return string
	 */
	public function column_name( $form ) {
		$title        = ! empty( $form->post_title ) ? $form->post_title : $form->post_name;
		$status       = 'publish' === $form->post_status ? __( 'Published', 'smart-form-builder-by-dragwyb' ) : ucfirst( (string) $form->post_status );
		$status_class = 'publish' === $form->post_status ? 'dragwyb-status-published' : 'dragwyb-status-draft';

		if ( current_user_can( 'edit_post', $form->ID ) ) {
			$title_html = sprintf(
				'<a href="%s" class="dragwyb-form-title-link">%s</a>',
				esc_url( '?page=dragwyb-form-builder&form_id=' . (int) $form->ID ),
				esc_html( $title )
			);
		} else {
			$title_html = sprintf( '<span class="dragwyb-form-title-text">%s</span>', esc_html( $title ) );
		}

		$actions = $this->get_column_name_row_actions( $form );

		$colors = array(
			array(
				'bg'    => '#fff0f5',
				'color' => '#e11d48',
			),
			array(
				'bg'    => '#faf5ff',
				'color' => '#9333ea',
			),
			array(
				'bg'    => '#eff6ff',
				'color' => '#2563eb',
			),
			array(
				'bg'    => '#ecfeff',
				'color' => '#0891b2',
			),
		);

		return sprintf(
			'<div class="dragwyb-name-cell"><div class="dragwyb-name-meta">%s<span class="dragwyb-status-badge %s">%s</span>%s</div></div>',
			$title_html,
			esc_attr( $status_class ),
			esc_html( $status ),
			$actions
		);
	}

	/**
	 * Get the form name HTML for the form name column.
	 *
	 * @since 1.8.6
	 *
	 * @param WP_Post $form Form object.
	 *
	 * @return string
	 */
	protected function get_column_name_title( $form ) {
		$title = ! empty( $form->post_title ) ? $form->post_title : $form->post_name;

		if ( $this->view === 'trash' ) {
			return esc_html( $title );
		}

		// Generate preview and edit links for users with appropriate permissions
		$edit_url = get_edit_post_link( $form->ID );
		$value    = sprintf(
			'<a href="%s" target="_blank">%s</a>',
			esc_url( $this->get_preview_url( $form->ID ) ),
			esc_html( $title )
		);

		if ( current_user_can( 'edit_post', $form->ID ) ) {
			$value = sprintf(
				'<a href="%s" target="_blank">%s</a>',
				esc_url( $edit_url ),
				esc_html( $title )
			);
		}

		return $value;
	}

	private function get_preview_url( int $id ) {
		return home_url( '/?post_type=' . sanitize_text_field( Dragwyb_Post::POST_TYPE ) . '&p=' . $id . '&preview_id=' . Form_Preview::generate_key( $id ) );
	}

	/**
	 * Get the row actions HTML for the form name column.
	 *
	 * @since 1.8.6
	 *
	 * @param WP_Post $form Form object.
	 *
	 * @return string
	 */
	protected function get_column_name_row_actions( $form ) {
		$actions         = array();
		$confirm_message = sprintf(
			/* translators: %s is the form title and ID. */
			__( 'Are you sure you want to delete %s form?', 'smart-form-builder-by-dragwyb' ),
			$form->post_title . '(' . $form->ID . ')'
		);
		$confirm_attr = esc_attr( 'return confirm("' . esc_js( $confirm_message ) . '");' );

		if ( 'trash' === $form->post_status ) {
			$actions['untrash'] = sprintf(
				'<a href="%s" class="submitdelete" onclick="%s">%s</a>',
				esc_url( wp_nonce_url( "post.php?action=untrash&post={$form->ID}", 'untrash-post_' . $form->ID ) ),
				$confirm_attr,
				esc_html__( 'Restore', 'smart-form-builder-by-dragwyb' )
			);
			$actions['delete']  = sprintf(
				'<a href="%s" class="submitdelete" onclick="%s">%s</a>',
				esc_url( wp_nonce_url( "post.php?action=delete&post={$form->ID}", 'delete-post_' . $form->ID ) ),
				$confirm_attr,
				esc_html__( 'Delete', 'smart-form-builder-by-dragwyb' )
			);
		} else {
			$actions['edit']  = '<a href="?page=dragwyb-form-builder&form_id=' . (int) esc_attr( $form->ID ) . '">' . esc_html__( 'Edit', 'smart-form-builder-by-dragwyb' ) . '</a>';
			$actions['view']  = '<a href="' . esc_url( $this->get_preview_url( $form->ID ) ) . '" target="_blank">' . esc_html__( 'View', 'smart-form-builder-by-dragwyb' ) . '</a>';
			$actions['trash'] = sprintf(
				'<a href="%s" class="submitdelete" onclick="%s">%s</a>',
				esc_url( wp_nonce_url( "post.php?action=trash&post={$form->ID}", 'trash-post_' . $form->ID ) ),
				$confirm_attr,
				esc_html__( 'Trash', 'smart-form-builder-by-dragwyb' )
			);
		}

		// Add more actions if necessary, such as delete, etc.

		return $this->row_actions( $actions );
	}

	/**
	 * Define bulk actions available for our table listing.
	 *
	 * @since 1.8.6
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- for post status check
		if ( isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] === 'trash' ) {
			return array(
				'delete'  => __( 'Delete Permanently', 'smart-form-builder-by-dragwyb' ),
				'untrash' => __( 'Restore', 'smart-form-builder-by-dragwyb' ),
			);
		}

		return array(
			'trash' => __( 'Move to Trash', 'smart-form-builder-by-dragwyb' ),
		);
	}


	public function get_views() {
		$statuses = array(
			'all'     => array( 'label' => 'All' ),
			'publish' => array( 'label' => 'Published' ),
			'draft'   => array( 'label' => 'Draft' ),
			'trash'   => array( 'label' => 'Trash' ),
		);

		$views = array();
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- for post status check
		$current = isset( $_REQUEST['post_status'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['post_status'] ) ) : 'all';

		// Get post counts
		$post_counts = wp_count_posts( Dragwyb_Post::POST_TYPE );

		// Build each view link
		foreach ( $statuses as $key => $val ) {
			// Get the count for the status
			if ( $key === 'all' ) {
				$count = ( $post_counts->publish ?? 0 ) + ( $post_counts->draft ?? 0 );
				$url   = remove_query_arg( array( 'post_status', 'paged', 's' ) );
			} else {
				$count = $post_counts->{$key} ?? 0;
				$url   = add_query_arg( 'post_status', $key );
				$url   = remove_query_arg( array( 'paged', 's' ), $url );
			}

			if ( $count > 0 ) {
				$views[ $key ] = sprintf(
					'<a href="%s"%s>%s <span class="count">(%d)</span></a>',
					esc_url( $url ),
					$current === $key ? ' class="current"' : '',
					esc_html( $val['label'] ),
					absint( $count )
				);
			}
		}

		return $views;
	}

	/**
	 * Fetch and set up the final data for the table.
	 *
	 * @since 1.8.6
	 */
	public function prepare_items() {
		// 1. Setup columns
		$columns               = $this->get_columns();
		$hidden                = get_hidden_columns( $this->screen );
		$sortable              = array(
			'id'      => array( 'ID', false ),
			'name'    => array( 'title', false ),
			'created' => array( 'date', false ),
		);
		$this->_column_headers = array( $columns, $hidden, $sortable );

		// 2. Setup pagination, sorting, and status filters
		$current_page = $this->get_pagenum();
		$per_page     = $this->get_items_per_page( 'dragwyb_forms_per_page', $this->per_page );

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- for order check
		$orderby = sanitize_key( wp_unslash( $_GET['orderby'] ?? 'ID' ) );

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- for order check
		$order = strtoupper( sanitize_text_field( wp_unslash( $_GET['order'] ?? 'DESC' ) ) );
		$order = in_array( $order, array( 'ASC', 'DESC' ), true ) ? $order : 'DESC';

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- for post status check
		$status = isset( $_GET['post_status'] ) ? sanitize_key( wp_unslash( $_GET['post_status'] ) ) : 'all';

		switch ( $status ) {
			case 'publish':
			case 'draft':
			case 'trash':
				$post_status = $status;
				break;

			default:
				$post_status = array( 'publish', 'draft' ); // ✅ Compatible syntax for all versions
				break;
		}

		// 4. Query the forms
		$args = array(
			'post_type'      => Dragwyb_Post::POST_TYPE,
			'post_status'    => $post_status,
			'orderby'        => $orderby,
			'order'          => $order,
			'paged'          => $current_page,
			'posts_per_page' => $per_page,
			'no_found_rows'  => false, // needed for pagination
		);

		$this->items = get_posts( $args );

		$this->submission_counts = array();
		if ( ! empty( $this->items ) && is_array( $this->items ) ) {
			$form_ids = wp_list_pluck( $this->items, 'ID' );
			$form_ids = array_map( 'absint', array_filter( $form_ids ) );
			if ( ! empty( $form_ids ) ) {
				global $wpdb;
				$table_name   = $wpdb->prefix . 'dragwyb_submissions';
				$placeholders = implode( ',', array_fill( 0, count( $form_ids ), '%d' ) );
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
				$results = $wpdb->get_results(
					// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$wpdb->prepare(
						"SELECT form_id, COUNT(id) as total_count FROM {$table_name} WHERE form_id IN ({$placeholders}) GROUP BY form_id",
						...$form_ids
					)
				);
				if ( ! empty( $results ) ) {
					foreach ( $results as $row ) {
						$this->submission_counts[ (int) $row->form_id ] = (int) $row->total_count;
					}
				}
			}
		}

		$post_counts = wp_count_posts( Dragwyb_Post::POST_TYPE );

		$total_items = 0;

		switch ( $status ) {
			case 'all':
				$total_items = ( $post_counts->publish ?? 0 ) + ( $post_counts->draft ?? 0 );
				break;
			default:
				$total_items = $post_counts->{$status} ?? 0;
				break;
		}

		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}

	/**
	 * Display the pagination.
	 *
	 * @since 1.8.6
	 *
	 * @param string $which The location of the table pagination: 'top' or 'bottom'.
	 */
	protected function pagination( $which ) {
		if ( $this->has_items() ) {
			parent::pagination( $which );
		} else {
			echo '<div class="tablenav-pages one-page"><span class="displaying-num">0 items</span></div>';
		}
	}

	/**
	 * Message to be displayed when there are no forms.
	 *
	 * @since 1.8.6
	 */
	public function no_items() {
		esc_html_e( 'No forms found.', 'smart-form-builder-by-dragwyb' );
	}
}
