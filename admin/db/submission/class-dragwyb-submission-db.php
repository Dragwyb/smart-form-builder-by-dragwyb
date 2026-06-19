<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Db\Submission;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dragwyb_Submission_Db {


	const VERSION = 'v1';

	/**
	 * Get the table name with the WP prefix.
	 */
	private static function get_table_name(): string {
		global $wpdb;
		return esc_sql( sanitize_text_field( $wpdb->prefix . 'dragwyb_submissions' ) );
	}

	/**
	 * Create the database table.
	 */
	public static function create_table(): void {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            form_id bigint(20) unsigned NOT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            ip_address varchar(45) NOT NULL,
            user_agent text NOT NULL,
            submission_data longtext NOT NULL,
            status varchar(20) DEFAULT 'publish' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY form_id (form_id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Insert a new form submission into the database.
	 *
	 * @param array $args The submission data to insert.
	 * @return int|\WP_Error The ID of the inserted row or WP_Error on failure.
	 */
	public function insert( array $args ) {
		global $wpdb;

		$defaults = array(
			'form_id'         => 0,
			'user_id'         => get_current_user_id() ?: null,
			'ip_address'      => null,
			'user_agent'      => null,
			'submission_data' => '{}',
			'status'          => 'publish',
			'created_at'      => current_time( 'mysql' ),
			'updated_at'      => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $args, $defaults );

		// Basic validation
		if ( empty( $data['form_id'] ) ) {
			return new \WP_Error( 'missing_form_id', __( 'Form ID is required for submission.', 'smart-form-builder-by-dragwyb' ) );
		}

		// Ensure submission_data is properly encoded
		if ( is_array( $data['submission_data'] ) || is_object( $data['submission_data'] ) ) {
			$data['submission_data'] = wp_json_encode( $data['submission_data'] );
		}

		$table_name = self::get_table_name();

		$sql = $wpdb->prepare(
			"INSERT INTO $table_name (form_id, user_id, ip_address, user_agent, submission_data, status, created_at, updated_at) VALUES (%d, %d, %s, %s, %s, %s, %s, %s)",
			absint( $data['form_id'] ),
			absint( $data['user_id'] ),
			sanitize_text_field( $data['ip_address'] ),
			sanitize_text_field( $data['user_agent'] ),
			$data['submission_data'],
			sanitize_text_field( $data['status'] ),
			sanitize_text_field( $data['created_at'] ),
			sanitize_text_field( $data['updated_at'] )
		);

		$inserted = $wpdb->query( $sql );

		if ( false === $inserted ) {
			if ( ! empty( $wpdb->last_error ) ) {
				error_log( 'Dragwyb submission insert failed: ' . $wpdb->last_error );
			}

			return new \WP_Error( 'db_insert_error', __( 'Could not save the submission.', 'smart-form-builder-by-dragwyb' ) );
		}

		return $wpdb->insert_id;
	}

	/**
	 * Get a submission by ID.
	 *
	 * @param int $id The submission ID.
	 * @return object|null The submission object or null if not found.
	 */
	public function get( int $id ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$sql = $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $id );
		return $wpdb->get_row( $sql );
	}

	/**
	 * Get submissions with pagination and sorting.
	 *
	 * @param array $args Query arguments (limit, offset, orderby, order, search, form_id)
	 * @return array Array of submission objects.
	 */
	public function get_all( array $args = array() ): array {
		global $wpdb;
		$table_name = self::get_table_name();

		$defaults = array(
			'limit'   => 20,
			'offset'  => 0,
			'orderby' => 'created_at',
			'order'   => 'DESC',
			'search'  => '',
			'form_id' => 0,
		);

		$args = wp_parse_args( $args, $defaults );

		$allowed_orderby = array( 'id', 'form_id', 'ip_address', 'created_at' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order           = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		$query_params = array();
		$where_clause = $this->build_where_clause( $args, $query_params );

		$query_params[] = absint( $args['limit'] );
		$query_params[] = absint( $args['offset'] );

		$sql = "SELECT * FROM $table_name $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d";

		$sql = $wpdb->prepare( $sql, ...$query_params );

		return $wpdb->get_results( $sql );
	}

	/**
	 * Get the total count of submissions.
	 *
	 * @param array $args Query arguments (search, form_id)
	 * @return int
	 */
	public function get_total_count( array $args = array() ): int {
		global $wpdb;
		$table_name = self::get_table_name();

		$query_params = array();
		$where_clause = $this->build_where_clause( $args, $query_params );

		$sql = "SELECT COUNT(id) FROM $table_name $where_clause";

		if ( ! empty( $query_params ) ) {
			$sql = $wpdb->prepare( $sql, ...$query_params );
		}

		return (int) $wpdb->get_var( $sql );
	}

	/**
	 * Helper to build WHERE clause
	 */
	private function build_where_clause( array $args, array &$query_params ): string {
		global $wpdb;
		$where = array();

		if ( ! empty( $args['form_id'] ) ) {
			$where[]        = 'form_id = %d';
			$query_params[] = absint( $args['form_id'] );
		}

		if ( ! empty( $args['search'] ) ) {
			$search         = '%' . $wpdb->esc_like( sanitize_text_field( $args['search'] ) ) . '%';
			$where[]        = '(ip_address LIKE %s OR submission_data LIKE %s)';
			$query_params[] = $search;
			$query_params[] = $search;
		}

		return ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';
	}

	/**
	 * Update an existing submission.
	 *
	 * @param int   $id The submission ID.
	 * @param array $data The data to update.
	 * @return int|false The number of rows updated, or false on error.
	 */
	public function update( int $id, array $data ) {
		global $wpdb;
		$table_name = self::get_table_name();

		if ( empty( $data ) ) {
			return false;
		}

		$set_parts = array();
		$values    = array();

		$allowed_columns = array( 'form_id', 'user_id', 'ip_address', 'user_agent', 'submission_data', 'status', 'created_at', 'updated_at' );

		foreach ( $data as $key => $value ) {
			if ( ! in_array( $key, $allowed_columns, true ) ) {
				continue;
			}
			$format      = in_array( $key, array( 'form_id', 'user_id' ), true ) ? '%d' : '%s';
			$set_parts[] = '`' . sanitize_key( $key ) . '` = ' . $format;
			$values[]    = $value;
		}

		$values[] = $id;

		$sql = $wpdb->prepare(
			"UPDATE $table_name SET " . implode( ', ', $set_parts ) . ' WHERE id = %d',
			...$values
		);

		return $wpdb->query( $sql );
	}

	/**
	 * Delete a submission.
	 *
	 * @param int $id The submission ID.
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function delete( int $id ) {
		global $wpdb;
		$table_name = self::get_table_name();

		$sql = $wpdb->prepare( "DELETE FROM $table_name WHERE id = %d", $id );

		return $wpdb->query( $sql );
	}
}
