<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Dragwyb\Form_Builder\Includes\Toolbars\Toolbars;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;
use Dragwyb\Form_Builder\Includes\Frontend\Managers\CSS_Manager;
use Dragwyb\Form_Builder\Admin\Db\Submission\Dragwyb_Submission_Db;
use Dragwyb\Form_Builder\Admin\Db\Error_Log\Dragwyb_Error_Log_Db;
use Dragwyb\Form_Builder\Includes\Modules\Modules;
use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;
use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;

class Dragwyb_Form_Builder_Ajax {

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'wp_ajax_dragwyb_save_form', array( $this, 'save_form' ) );
		add_action( 'wp_ajax_dragwyb_get_entries', array( $this, 'get_entries' ) );
		add_action( 'wp_ajax_dragwyb_get_forms', array( $this, 'get_forms' ) );
		add_action( 'wp_ajax_dragwyb_get_entry', array( $this, 'get_entry' ) );
		add_action( 'wp_ajax_dragwyb_update_entry', array( $this, 'update_entry' ) );
		add_action( 'wp_ajax_dragwyb_delete_entry', array( $this, 'delete_entry' ) );
		add_action( 'wp_ajax_dragwyb_delete_entries', array( $this, 'delete_entries' ) );
		add_action( 'wp_ajax_dragwyb_get_errors', array( $this, 'get_errors' ) );
		add_action( 'wp_ajax_dragwyb_delete_error', array( $this, 'delete_error' ) );
		add_action( 'wp_ajax_dragwyb_delete_errors', array( $this, 'delete_errors' ) );
		add_action( 'wp_ajax_dragwyb_get_export_data', array( $this, 'get_export_data' ) );
		add_action( 'wp_ajax_dragwyb_export_forms', array( $this, 'export_forms' ) );
		add_action( 'wp_ajax_dragwyb_import_forms', array( $this, 'import_forms' ) );
		add_action( 'wp_ajax_dragwyb_onboard_setup_complete', array( $this, 'onboard_setup_complete' ) );
	}

	/**
	 * Onboarding setup complete status update.
	 */
	public function onboard_setup_complete(): void {
		check_ajax_referer( 'dragwyb_setup_complete' );

		if ( ! $this->current_user_can_manage_form( 0 ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		update_option( 'dragwyb_onboarding_setup_complete', '1' );

		wp_send_json_success( array( 'message' => __( 'Onboarding setup complete', 'smart-form-builder-by-dragwyb' ) ) );
	}

	/**
	 * Save form data
	 */
	public function save_form(): void {

		check_ajax_referer( 'dragwyb_editor' );

		$form_id = isset( $_POST['form_id'] ) ? absint( wp_unslash( $_POST['form_id'] ) ) : 0;

		if ( ! $this->current_user_can_manage_form( $form_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- data properly sanitized in sanitize_form_data
		$form_data = json_decode( wp_unslash( $_POST['form_data'] ?? '' ), true );

		if ( ! $form_id || ! is_array( $form_data ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid form data', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$sanitized_data = $this->sanitize_form_data( $form_data, $form_id );
		$initial_load   = isset( $_POST['isInitialLoad'] ) ? absint( wp_unslash( $_POST['isInitialLoad'] ) ) : 0;

		if ( $initial_load && 1 === $initial_load ) {
			delete_post_meta( $form_id, '_dragwyb_initial_form_data_save' );
		}

		// Update form meta
		update_post_meta( $form_id, '_dragwyb_form_data', $sanitized_data );

		$css_manager = CSS_Manager::instance();
		$css_manager->clean_cache( $form_id );

		wp_send_json_success(
			array(
				'message' => __( 'Form saved successfully', 'smart-form-builder-by-dragwyb' ),
			)
		);
	}

	/**
	 * Sanitize form data
	 */
	private function sanitize_form_data( array $data, int $form_id ): array {

		defined( 'DRAGWYB_EDITOR_SAVE_AJAX' ) || define( 'DRAGWYB_EDITOR_SAVE_AJAX', true );
		$sanitize_data  = array();
		$toolbar_obj    = Toolbars::instance();
		$toolbars       = $toolbar_obj->get_toolbars();
		$toolbars_cache = array();

		foreach ( $data as $key => $value ) {
			if ( $key === 'id' ) {
				continue;
			}

			if ( count( $toolbars ) > 0 && isset( $toolbars[ $key ] ) && $toolbars[ $key ] instanceof Toolbar_Base ) {
				$toolbar = $toolbars[ $key ];
				$toolbar->set_form_id( $form_id );
				$toolbar->set_toolbar_data( $value );
				$toolbar_data = $toolbar->get_toolbar_data();

				if ( $toolbar_data ) {
					if ( $key === 'fields' ) {
						$toolbar_data = $this->sorting_fields( $toolbar_data, $data['rootContainers'] ?? array() );
					}
					$sanitize_data[ $key ]  = $toolbar_data;
					$toolbars_cache[ $key ] = $toolbar;
				}
			}
		}

		if ( count( $toolbars_cache ) > 0 ) {
			foreach ( $toolbars_cache as $toolbar ) {
				$toolbar->settings_updated();
			}
		}

		return $sanitize_data;
	}

	/**
	 * Sort fields
	 */
	private function sorting_fields( array $fields, $root_containers ): array {
		if ( empty( $root_containers ) || ! is_array( $root_containers ) ) {
			return $fields;
		}

		$sorted_fields = array();
		foreach ( $root_containers as $root_container ) {
			$sorted_fields[ $root_container ] = $fields[ $root_container ] ?? array();

			if ( isset( $fields[ $root_container ]['is_root_container'] ) && true === $fields[ $root_container ]['is_root_container'] && isset( $fields[ $root_container ]['children'] ) && count( $fields[ $root_container ]['children'] ) > 0 ) {
				$this->sorting_child_fields( $fields[ $root_container ]['children'], $fields, $sorted_fields );
			}
		}

		return ! empty( $sorted_fields ) ? $sorted_fields : $fields;
	}

	/**
	 * Sort child fields
	 */
	private function sorting_child_fields( array $childrens, array $fields, array &$sorted_fields ): void {
		foreach ( $childrens as $field_id ) {
			if ( isset( $field_id ) && isset( $fields[ $field_id ] ) ) {
				$sorted_fields[ $field_id ] = $fields[ $field_id ];

				if ( isset( $fields[ $field_id ]['children'] ) && count( $fields[ $field_id ]['children'] ) > 0 ) {
					$this->sorting_child_fields( $fields[ $field_id ]['children'], $fields, $sorted_fields );
				}
			}
		}
	}

	/**
	 * Get entries via AJAX
	 */
	public function get_entries(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$db              = new Dragwyb_Submission_Db();
		$allowed_orderby = array( 'id', 'form_id', 'ip_address', 'created_at' );
		$orderby         = isset( $_POST['orderby'] ) ? sanitize_key( wp_unslash( $_POST['orderby'] ) ) : 'created_at';
		$orderby         = in_array( $orderby, $allowed_orderby, true ) ? $orderby : 'created_at';

		$order = isset( $_POST['order'] ) ? sanitize_key( wp_unslash( $_POST['order'] ) ) : 'DESC';
		$order = in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $order ) : 'DESC';

		$args = array(
			'limit'   => isset( $_POST['limit'] ) ? absint( wp_unslash( $_POST['limit'] ) ) : 20,
			'offset'  => isset( $_POST['offset'] ) ? absint( wp_unslash( $_POST['offset'] ) ) : 0,
			'orderby' => $orderby,
			'order'   => $order,
			'search'  => isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '',
			'form_id' => isset( $_POST['form_id'] ) ? absint( wp_unslash( $_POST['form_id'] ) ) : 0,
		);

		$entries = $db->get_all( $args );
		$total   = $db->get_total_count( $args );

		// Process data for UI
		foreach ( $entries as &$entry ) {
			$data = json_decode( $entry->submission_data, true );
			if ( json_last_error() === JSON_ERROR_NONE && is_array( $data ) ) {
				$summary      = array();
				$summary_text = array();
				$count        = 0;
				foreach ( $data as $key => $value ) {
					if ( $count >= 3 ) {
						break;
					}

					$raw_val     = ( is_array( $value ) && isset( $value['value'] ) ) ? $value['value'] : $value;
					$display_val = is_array( $raw_val ) ? implode( ', ', $raw_val ) : (string) $raw_val;

					$summary[]      = isset( $value['label'] ) ? sprintf( '<strong>%s:</strong> %s', esc_html( (string) $value['label'] ), esc_html( $display_val ) ) : esc_html( $display_val );
					$summary_text[] = isset( $value['label'] ) ? sprintf( '%s: %s', sanitize_text_field( (string) $value['label'] ), sanitize_text_field( $display_val ) ) : sanitize_text_field( $display_val );
					++$count;
				}
				$entry->submission_data_summary      = implode( '<br>', $summary ) . ( count( $data ) > 3 ? '<br><em>...and more</em>' : '' );
				$entry->submission_data_summary_text = implode( '; ', $summary_text ) . ( count( $data ) > 3 ? ' ...and more' : '' );
			} else {
				$entry->submission_data_summary      = esc_html( wp_trim_words( $entry->submission_data, 10, '...' ) );
				$entry->submission_data_summary_text = sanitize_text_field( wp_trim_words( $entry->submission_data, 10, '...' ) );
			}
			$extra                       = ! empty( $entry->extra_data ) ? json_decode( $entry->extra_data, true ) : array();
			$entry->ip_address           = $extra['visitor_info']['ip_address'] ?? ( $entry->ip_address ?? '-' );
			$entry->created_at_formatted = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $entry->created_at ) );
		}

		wp_send_json_success(
			array(
				'entries'     => $entries,
				'total_items' => $total,
				'total_pages' => ceil( $total / max( 1, $args['limit'] ) ),
			)
		);
	}

	/**
	 * Get forms list via AJAX
	 */
	public function get_forms(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$forms = get_posts(
			array(
				'post_type'      => Dragwyb_Post::post_type(),
				'posts_per_page' => -1,
				'post_status'    => array( 'publish', 'draft' ),
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		$options = array();
		foreach ( $forms as $form ) {
			$options[] = array(
				'id'    => $form->ID,
				'title' => $form->post_title,
			);
		}

		wp_send_json_success( array( 'forms' => $options ) );
	}

	/**
	 * Get single entry
	 */
	public function get_entry(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ID', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$db    = new Dragwyb_Submission_Db();
		$entry = $db->get( $id );

		if ( ! $entry ) {
			wp_send_json_error( array( 'message' => __( 'Entry not found', 'smart-form-builder-by-dragwyb' ) ) );
		}

		if ( ! $this->current_user_can_manage_entry( $entry ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		if ( 'unread' === $entry->status ) {
			$db->update( $id, array( 'status' => 'publish' ) );
			$entry->status = 'publish';
		}

		// Decode JSON safely for frontend usage
		$entry->submission_data_decoded = json_decode( $entry->submission_data, true );
		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $entry->submission_data_decoded ) ) {
			$entry->submission_data_decoded = array();
		}

		$extra_data = ! empty( $entry->extra_data ) ? json_decode( $entry->extra_data, true ) : array();
		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $extra_data ) ) {
			$extra_data = array();
		}

		$visitor_info = isset( $extra_data['visitor_info'] ) && is_array( $extra_data['visitor_info'] ) ? $extra_data['visitor_info'] : array();
		$lead_attrs   = isset( $extra_data['lead_attributes'] ) && is_array( $extra_data['lead_attributes'] ) ? $extra_data['lead_attributes'] : array();

		$entry->visitor_info = wp_parse_args(
			$visitor_info,
			array(
				'user_id'        => isset( $entry->user_id ) ? $entry->user_id : null,
				'ip_address'     => isset( $entry->ip_address ) ? $entry->ip_address : '-',
				'user_agent'     => isset( $entry->user_agent ) ? $entry->user_agent : '-',
				'device'         => '-',
				'browser'        => '-',
				'os'             => '-',
				'screen'         => '-',
				'language'       => '-',
				'time_to_submit' => '-',
			)
		);

		$entry->lead_attributes = wp_parse_args(
			$lead_attrs,
			array(
				'traffic_source' => 'Direct',
				'referrer'       => 'Direct',
				'landing_page'   => '-',
				'utm_source'     => '-',
				'utm_medium'     => '-',
				'utm_campaign'   => '-',
				'utm_term'       => '-',
				'utm_content'    => '-',
			)
		);

		$visitor_journey = array();
		$db_session_id   = ! empty( $extra_data['session_id'] ) ? absint( $extra_data['session_id'] ) : 0;
		$db_session_uid  = ! empty( $extra_data['session_uid'] ) ? sanitize_text_field( $extra_data['session_uid'] ) : '';

		global $wpdb;
		$journey_table = \Dragwyb\Form_Builder\Admin\Db\Analytics\Dragwyb_Analytics_Db::table_name( 'journey' );
		$journey_rows  = array();

		if ( $db_session_id > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$journey_rows = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$journey_table} WHERE session_id = %d ORDER BY id ASC",
					$db_session_id
				)
			);
		} elseif ( ! empty( $db_session_uid ) ) {
			$sessions_table = \Dragwyb\Form_Builder\Admin\Db\Analytics\Dragwyb_Analytics_Db::table_name( 'sessions' );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			$found_session_id = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT id FROM {$sessions_table} WHERE session_uid = %s ORDER BY id DESC LIMIT 1",
					$db_session_uid
				)
			);

			if ( $found_session_id > 0 ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
				$journey_rows = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM {$journey_table} WHERE session_id = %d ORDER BY id ASC",
						$found_session_id
					)
				);
			}
		}

		if ( ! empty( $journey_rows ) && is_array( $journey_rows ) ) {
			foreach ( $journey_rows as $j_row ) {
				$visitor_journey[] = array(
					'title' => ! empty( $j_row->page_title ) ? $j_row->page_title : ( ! empty( $j_row->action_detail ) ? $j_row->action_detail : $j_row->page_url ),
					'url'   => $j_row->page_url,
					'time'  => wp_date( 'g:i:s A', strtotime( $j_row->created_at ) ),
					'type'  => 'submission' === $j_row->action_type ? 'submission' : 'pageview',
				);
			}
		} elseif ( isset( $extra_data['visitor_journey'] ) && is_array( $extra_data['visitor_journey'] ) && ! empty( $extra_data['visitor_journey'] ) ) {
			$visitor_journey = $extra_data['visitor_journey'];
		}

		if ( empty( $visitor_journey ) ) {
			$visitor_journey = array(
				array(
					'title' => sprintf( __( 'Form submitted: %s', 'smart-form-builder-by-dragwyb' ), $entry->form_title ),
					'url'   => $entry->lead_attributes['landing_page'] ?? '-',
					'time'  => wp_date( 'g:i:s A', strtotime( $entry->created_at ) ),
					'type'  => 'submission',
				),
			);
		}

		$entry->visitor_journey      = $visitor_journey;
		$entry->form_title           = get_the_title( $entry->form_id ) ?: '#' . $entry->form_id;
		$entry->created_at_formatted = wp_date( 'F j, Y g:i:s A', strtotime( $entry->created_at ) );

		wp_send_json_success( array( 'entry' => $entry ) );
	}

	/**
	 * Update an entry (Quick Edit or Full Edit)
	 */
	public function update_entry(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ID', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$db    = new Dragwyb_Submission_Db();
		$entry = $db->get( $id );

		if ( ! $entry || ! $this->current_user_can_manage_entry( $entry ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		if ( ! property_exists( $entry, 'submission_data' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid data', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$entry_submission_data = json_decode( $entry->submission_data, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $entry_submission_data ) ) {
			$entry_submission_data = array();
		}

		$is_data_update = false;
		$dragwyb_module = null;

		// Check if quick edit data exists
		if ( isset( $_POST['status'] ) ) {
			$is_data_update = true;
			$data['status'] = sanitize_text_field( wp_unslash( $_POST['status'] ) );
		}
		if ( isset( $_POST['created_at'] ) ) {
			$is_data_update     = true;
			$data['created_at'] = sanitize_text_field( wp_unslash( $_POST['created_at'] ) );
		}

		// Check if full edit data exists (JSON payload)
		if ( isset( $_POST['submission_data'] ) ) {
			// Data is JSON string from JS, decode it, sanitize it, and encode it back to ensure validity
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Data is properly sanitized later in the foreach loop
			$submission_data = json_decode( wp_unslash( $_POST['submission_data'] ), true );

			if ( json_last_error() === JSON_ERROR_NONE && is_array( $submission_data ) ) {
				foreach ( $entry_submission_data as $key => $val ) {
					if ( ! isset( $submission_data[ $key ] ) || ! isset( $val['value'] ) || $val['value'] === $submission_data[ $key ] || ! isset( $val['type'] ) ) {
						continue;
					}

					$is_data_update = true;

					if ( ! isset( $dragwyb_module ) ) {
						$dragwyb_module = Modules::instance();
					}

					$field_type = $val['type'];

					$dragwyb_field = $dragwyb_module->get_field( $field_type );

					if ( ! $dragwyb_field instanceof Field_Base ) {
						continue;
					}

					$entry_submission_data[ $key ]['value'] = $dragwyb_field->sanitize( $submission_data[ $key ] );
				}

				$data['submission_data'] = wp_json_encode( $entry_submission_data );
			}
		}

		if ( ! $is_data_update ) {
			wp_send_json_error( array( 'message' => __( 'No data provided for update.', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$updated = $db->update( $id, $data );

		if ( $updated === false ) {
			wp_send_json_error( array( 'message' => __( 'Failed to update entry.', 'smart-form-builder-by-dragwyb' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Entry updated successfully.', 'smart-form-builder-by-dragwyb' ) ) );
	}

	/**
	 * Delete an entry
	 */
	public function delete_entry(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ID', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$db    = new Dragwyb_Submission_Db();
		$entry = $db->get( $id );

		if ( ! $entry || ! $this->current_user_can_manage_entry( $entry, 'delete_post' ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$deleted = $db->delete( $id );

		if ( $deleted === false ) {
			wp_send_json_error( array( 'message' => __( 'Failed to delete entry.', 'smart-form-builder-by-dragwyb' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Entry deleted successfully.', 'smart-form-builder-by-dragwyb' ) ) );
	}

	/**
	 * Delete multiple entries (submissions) via AJAX
	 */
	public function delete_entries(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- IDs are unslashed and sanitized via absint
		$raw_ids = isset( $_POST['ids'] ) ? wp_unslash( $_POST['ids'] ) : array();
		$ids     = array_map( 'absint', (array) $raw_ids );
		if ( empty( $ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No IDs provided', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$db          = new Dragwyb_Submission_Db();
		$allowed_ids = array();

		foreach ( $ids as $id ) {
			$entry = $db->get( $id );
			if ( $entry && $this->current_user_can_manage_entry( $entry, 'delete_post' ) ) {
				$allowed_ids[] = $id;
			}
		}

		if ( empty( $allowed_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied or invalid IDs', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$deleted = $db->delete_multiple( $allowed_ids );

		if ( $deleted === false ) {
			wp_send_json_error( array( 'message' => __( 'Failed to delete entries.', 'smart-form-builder-by-dragwyb' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Selected entries deleted successfully.', 'smart-form-builder-by-dragwyb' ) ) );
	}

	private function current_user_can_manage_form( int $form_id, string $capability = 'edit_post' ): bool {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return $form_id > 0 && current_user_can( $capability, $form_id );
	}

	private function current_user_can_manage_entries(): bool {
		return current_user_can( 'manage_options' );
	}

	private function current_user_can_manage_entry( $entry, string $capability = 'edit_post' ): bool {
		if ( ! $entry || empty( $entry->form_id ) ) {
			return false;
		}

		return $this->current_user_can_manage_form( absint( $entry->form_id ), $capability );
	}

	/**
	 * Get error logs via AJAX
	 */
	public function get_errors(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$db              = new Dragwyb_Error_Log_Db();
		$allowed_orderby = array( 'id', 'form_id', 'ip_address', 'created_at' );
		$orderby         = isset( $_POST['orderby'] ) ? sanitize_key( wp_unslash( $_POST['orderby'] ) ) : 'created_at';
		$orderby         = in_array( $orderby, $allowed_orderby, true ) ? $orderby : 'created_at';

		$order = isset( $_POST['order'] ) ? sanitize_key( wp_unslash( $_POST['order'] ) ) : 'DESC';
		$order = in_array( strtoupper( $order ), array( 'ASC', 'DESC' ), true ) ? strtoupper( $order ) : 'DESC';

		$args = array(
			'limit'   => isset( $_POST['limit'] ) ? absint( wp_unslash( $_POST['limit'] ) ) : 20,
			'offset'  => isset( $_POST['offset'] ) ? absint( wp_unslash( $_POST['offset'] ) ) : 0,
			'orderby' => $orderby,
			'order'   => $order,
			'search'  => isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '',
			'form_id' => isset( $_POST['form_id'] ) ? absint( wp_unslash( $_POST['form_id'] ) ) : 0,
		);

		$entries = $db->get_all( $args );
		$total   = $db->get_total_count( $args );

		// Process data for UI
		foreach ( $entries as &$entry ) {
			$entry->form_title = get_the_title( $entry->form_id ) ?: '#' . $entry->form_id;

			$data = json_decode( $entry->submission_data, true );
			if ( json_last_error() === JSON_ERROR_NONE && is_array( $data ) ) {
				$summary = array();
				$count   = 0;
				foreach ( $data as $key => $value ) {
					if ( $count >= 3 ) {
						break;
					}
					$display_val = is_array( $value ) ? implode( ', ', $value ) : (string) $value;
					$summary[]   = sprintf( '<strong>%s:</strong> %s', esc_html( (string) $key ), esc_html( $display_val ) );
					++$count;
				}
				$entry->submission_data_summary = implode( '<br>', $summary ) . ( count( $data ) > 3 ? '<br><em>...and more</em>' : '' );
			} else {
				$entry->submission_data_summary = esc_html( wp_trim_words( $entry->submission_data, 10, '...' ) );
			}

			// Format validation errors summary
			$errors = json_decode( $entry->errors, true );
			if ( json_last_error() === JSON_ERROR_NONE && is_array( $errors ) ) {
				$err_summary = array();
				foreach ( $errors as $field_key => $err_msg ) {
					$err_summary[] = sprintf( '<span class="dragwyb-error-badge"><strong>%s:</strong> %s</span>', esc_html( (string) $field_key ), esc_html( (string) $err_msg ) );
				}
				$entry->errors_summary = implode( ' ', $err_summary );
			} else {
				$entry->errors_summary = esc_html( $entry->errors );
			}

			$entry->created_at_formatted = wp_date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $entry->created_at ) );
		}

		wp_send_json_success(
			array(
				'entries'     => $entries,
				'total_items' => $total,
				'total_pages' => ceil( $total / max( 1, $args['limit'] ) ),
			)
		);
	}

	/**
	 * Delete a single error log via AJAX
	 */
	public function delete_error(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$id = isset( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : 0;
		if ( ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid ID', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$db      = new Dragwyb_Error_Log_Db();
		$deleted = $db->delete( $id );

		if ( $deleted === false ) {
			wp_send_json_error( array( 'message' => __( 'Failed to delete error log.', 'smart-form-builder-by-dragwyb' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Error log deleted successfully.', 'smart-form-builder-by-dragwyb' ) ) );
	}

	/**
	 * Delete multiple error logs via AJAX
	 */
	public function delete_errors(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- IDs are unslashed and sanitized via absint
		$raw_ids = isset( $_POST['ids'] ) ? wp_unslash( $_POST['ids'] ) : array();
		$ids     = array_map( 'absint', (array) $raw_ids );
		if ( empty( $ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No IDs provided', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$db      = new Dragwyb_Error_Log_Db();
		$deleted = $db->delete_multiple( $ids );

		if ( $deleted === false ) {
			wp_send_json_error( array( 'message' => __( 'Failed to delete error logs.', 'smart-form-builder-by-dragwyb' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'Selected error logs deleted successfully.', 'smart-form-builder-by-dragwyb' ) ) );
	}

	/**
	 * Get raw export data in batches or specific IDs via AJAX
	 */
	public function get_export_data(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		if ( is_user_logged_in() && ! is_admin() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$type    = isset( $_POST['type'] ) ? sanitize_key( wp_unslash( $_POST['type'] ) ) : 'entries';
		$raw_ids = isset( $_POST['ids'] ) ? wp_unslash( $_POST['ids'] ) : array();
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Sanitized below via array_map and absint
		$ids = array_map( 'absint', (array) $raw_ids );

		if ( ! empty( $ids ) ) {
			// Fetch specific IDs
			if ( $type === 'errors' ) {
				$db      = new Dragwyb_Error_Log_Db();
				$results = array();
				foreach ( $ids as $id ) {
					$entry = $db->get( $id );
					if ( $entry ) {
						$results[] = $entry;
					}
				}
			} else {
				$db      = new Dragwyb_Submission_Db();
				$results = array();
				foreach ( $ids as $id ) {
					$entry = $db->get( $id );
					if ( $entry && $this->current_user_can_manage_entry( $entry ) ) {
						$results[] = $entry;
					}
				}
			}
			wp_send_json_success(
				array(
					'items' => $results,
					'total' => count( $results ),
				)
			);
		} else {
			// Fetch by filter with limit and offset (for batching)
			$limit   = isset( $_POST['limit'] ) ? absint( wp_unslash( $_POST['limit'] ) ) : 100;
			$offset  = isset( $_POST['offset'] ) ? absint( wp_unslash( $_POST['offset'] ) ) : 0;
			$form_id = isset( $_POST['form_id'] ) ? absint( wp_unslash( $_POST['form_id'] ) ) : 0;
			$search  = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';

			$args = array(
				'limit'   => $limit,
				'offset'  => $offset,
				'form_id' => $form_id,
				'search'  => $search,
			);

			if ( $type === 'errors' ) {
				$db    = new Dragwyb_Error_Log_Db();
				$items = $db->get_all( $args );
				$total = $db->get_total_count( $args );
			} else {
				$db    = new Dragwyb_Submission_Db();
				$items = $db->get_all( $args );
				// Filter out entries user doesn't have permission for
				$filtered_items = array();
				foreach ( $items as $item ) {
					if ( $this->current_user_can_manage_entry( $item ) ) {
						$filtered_items[] = $item;
					}
				}
				$items = $filtered_items;
				$total = $db->get_total_count( $args );
			}

			wp_send_json_success(
				array(
					'items' => $items,
					'total' => $total,
				)
			);
		}
	}

	/**
	 * Export forms via AJAX
	 */
	public function export_forms(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$form_ids    = array();
		$export_type = isset( $_POST['export_type'] ) ? sanitize_key( wp_unslash( $_POST['export_type'] ) ) : 'all';

		if ( 'selected' === $export_type ) {
			if ( isset( $_POST['form_id'] ) ) {
				$form_ids[] = absint( wp_unslash( $_POST['form_id'] ) );
			} elseif ( isset( $_POST['form_ids'] ) ) {
				$raw_form_ids = wp_unslash( $_POST['form_ids'] );
				// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized via array_map and absint
				$form_ids = array_map( 'absint', (array) $raw_form_ids );
			}
			$form_ids = array_filter( $form_ids );
		} else {
			$forms    = get_posts(
				array(
					'post_type'      => Dragwyb_Post::post_type(),
					'posts_per_page' => -1,
					'post_status'    => array( 'publish', 'draft' ),
					'fields'         => 'ids',
				)
			);
			$form_ids = array_map( 'absint', $forms );
		}

		if ( empty( $form_ids ) ) {
			wp_send_json_error( array( 'message' => __( 'No forms found to export.', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$exported_forms = array();

		foreach ( $form_ids as $form_id ) {
			$post = get_post( $form_id );
			if ( ! $post || Dragwyb_Post::post_type() !== $post->post_type ) {
				continue;
			}

			$raw_form_data = get_post_meta( $form_id, '_dragwyb_form_data', true );
			if ( ! is_array( $raw_form_data ) ) {
				$raw_form_data = array();
			}

			$sanitized_form_data = $this->sanitize_form_data( $raw_form_data, $form_id );

			$exported_forms[] = array(
				'id'        => $form_id,
				'title'     => $post->post_title,
				'form_data' => $sanitized_form_data,
			);
		}

		wp_send_json_success( array( 'forms' => $exported_forms ) );
	}

	/**
	 * Import forms via AJAX
	 */
	public function import_forms(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in sanitize_form_data
		$raw_input   = wp_unslash( $_POST['import_data'] ?? '' );
		$import_data = json_decode( $raw_input, true );

		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $import_data ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid JSON data provided.', 'smart-form-builder-by-dragwyb' ) ) );
		}

		// Handle single object or array of forms
		if ( isset( $import_data['forms'] ) && is_array( $import_data['forms'] ) ) {
			$forms_list = $import_data['forms'];
		} elseif ( isset( $import_data['form_data'] ) || isset( $import_data['title'] ) ) {
			$forms_list = array( $import_data );
		} elseif ( isset( $import_data['fields'] ) || isset( $import_data['rootContainers'] ) ) {
			$forms_list = array( array( 'form_data' => $import_data ) );
		} else {
			$forms_list = $import_data;
		}

		$imported_count = 0;
		$imported_forms = array();

		foreach ( $forms_list as $item ) {
			if ( ! is_array( $item ) ) {
				continue;
			}

			$form_data = $item['form_data'] ?? ( ( isset( $item['fields'] ) || isset( $item['rootContainers'] ) ) ? $item : array() );

			if ( empty( $form_data ) || ! is_array( $form_data ) ) {
				continue;
			}

			$form_title = ! empty( $item['title'] ) ? sanitize_text_field( $item['title'] ) : __( 'Imported Form', 'smart-form-builder-by-dragwyb' );

			// Create form post
			$new_form_id = wp_insert_post(
				array(
					'post_title'  => $form_title,
					'post_type'   => Dragwyb_Post::post_type(),
					'post_status' => 'publish',
				)
			);

			if ( is_wp_error( $new_form_id ) || ! $new_form_id ) {
				continue;
			}

			// Sanitize with newly created form ID
			$final_sanitized = $this->sanitize_form_data( $form_data, (int) $new_form_id );
			if ( empty( $final_sanitized ) ) {
				wp_delete_post( $new_form_id, true );
				continue;
			}

			update_post_meta( $new_form_id, '_dragwyb_form_data', $final_sanitized );
			$css_manager = CSS_Manager::instance();
			$css_manager->clean_cache( (int) $new_form_id );

			++$imported_count;
			$imported_forms[] = array(
				'id'    => $new_form_id,
				'title' => $form_title,
			);
		}

		if ( 0 === $imported_count ) {
			wp_send_json_error( array( 'message' => __( 'No valid form data could be imported.', 'smart-form-builder-by-dragwyb' ) ) );
		}

		wp_send_json_success(
			array(
				'message'  => sprintf(
					/* translators: %d: number of imported forms */
					_n( '%d form imported successfully.', '%d forms imported successfully.', $imported_count, 'smart-form-builder-by-dragwyb' ),
					$imported_count
				),
				'imported' => $imported_forms,
			)
		);
	}
}
