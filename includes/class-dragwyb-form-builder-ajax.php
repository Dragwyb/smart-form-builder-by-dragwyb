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
		add_action( 'wp_ajax_dragwyb_clear_errors', array( $this, 'clear_errors' ) );
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
		$sorted_fields = array();
		foreach ( $root_containers as $root_container ) {
			$sorted_fields[ $root_container ] = $fields[ $root_container ] ?? array();

			if ( isset( $fields[ $root_container ]['is_root_container'] ) && true === $fields[ $root_container ]['is_root_container'] && isset( $fields[ $root_container ]['children'] ) && count( $fields[ $root_container ]['children'] ) > 0 ) {
				$this->sorting_child_fields( $fields[ $root_container ]['children'], $fields, $sorted_fields );
			}
		}

		return $sorted_fields;
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
				'post_type'      => 'Dragwyb_Page', // Assuming Dragwyb_Page is the CPT based on class-dragwyb-pages.php
				'posts_per_page' => -1,
				'post_status'    => 'publish',
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

		// Decode JSON safely for frontend usage
		$entry->submission_data_decoded = json_decode( $entry->submission_data, true );
		if ( json_last_error() !== JSON_ERROR_NONE || ! is_array( $entry->submission_data_decoded ) ) {
			$entry->submission_data_decoded = array();
		}

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

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- IDs are sanitized via array_map and absint
		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
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

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- IDs are sanitized via array_map and absint
		$ids = isset( $_POST['ids'] ) ? array_map( 'absint', (array) $_POST['ids'] ) : array();
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
	 * Clear all error logs via AJAX
	 */
	public function clear_errors(): void {
		check_ajax_referer( 'dragwyb_admin_nonce' );

		if ( ! $this->current_user_can_manage_entries() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied', 'smart-form-builder-by-dragwyb' ) ) );
		}

		$db      = new Dragwyb_Error_Log_Db();
		$cleared = $db->clear_all();

		if ( $cleared === false ) {
			wp_send_json_error( array( 'message' => __( 'Failed to clear error logs.', 'smart-form-builder-by-dragwyb' ) ) );
		}

		wp_send_json_success( array( 'message' => __( 'All error logs cleared successfully.', 'smart-form-builder-by-dragwyb' ) ) );
	}
}
