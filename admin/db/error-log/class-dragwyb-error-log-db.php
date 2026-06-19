<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Db\Error_Log;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dragwyb_Error_Log_Db {

	const VERSION = 'v1';

	/**
	 * Get the table name with the WP prefix.
	 */
	private static function get_table_name(): string {
		global $wpdb;
		return esc_sql( sanitize_text_field( $wpdb->prefix . 'dragwyb_error_logs' ) );
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
            ip_address varchar(45) NOT NULL,
            user_agent text NOT NULL,
            submission_data longtext NOT NULL,
            errors longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY form_id (form_id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Insert a new error log into the database.
	 *
	 * @param array $args The error data to insert.
	 * @return int|\WP_Error The ID of the inserted row or WP_Error on failure.
	 */
	public function insert( array $args ) {
		global $wpdb;

		$defaults = array(
			'form_id'         => 0,
			'ip_address'      => null,
			'user_agent'      => null,
			'submission_data' => '{}',
			'errors'          => '{}',
			'created_at'      => current_time( 'mysql' ),
		);

		$data = wp_parse_args( $args, $defaults );

		// Basic validation
		if ( empty( $data['form_id'] ) ) {
			return new \WP_Error( 'missing_form_id', __( 'Form ID is required for error logging.', 'smart-form-builder-by-dragwyb' ) );
		}

		// Ensure submission_data is properly encoded
		if ( is_array( $data['submission_data'] ) || is_object( $data['submission_data'] ) ) {
			$data['submission_data'] = wp_json_encode( $data['submission_data'] );
		}

		// Ensure errors is properly encoded
		if ( is_array( $data['errors'] ) || is_object( $data['errors'] ) ) {
			$data['errors'] = wp_json_encode( $data['errors'] );
		}

		$table_name = esc_sql( self::get_table_name() );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$inserted = $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"INSERT INTO $table_name (form_id, ip_address, user_agent, submission_data, errors, created_at) VALUES (%d, %s, %s, %s, %s, %s)",
				absint( $data['form_id'] ),
				sanitize_text_field( $data['ip_address'] ),
				sanitize_text_field( $data['user_agent'] ),
				$data['submission_data'],
				$data['errors'],
				sanitize_text_field( $data['created_at'] )
			)
		);

		if ( false === $inserted ) {
			if ( ! empty( $wpdb->last_error ) ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'Dragwyb error log insert failed: ' . $wpdb->last_error );
			}

			return new \WP_Error( 'db_insert_error', __( 'Could not save the error log.', 'smart-form-builder-by-dragwyb' ) );
		}

		return $wpdb->insert_id;
	}

	/**
	 * Get an error log by ID.
	 *
	 * @param int $id The error log ID.
	 * @return object|null The error log object or null if not found.
	 */
	public function get( int $id ) {
		global $wpdb;
		$table_name = esc_sql( self::get_table_name() );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM $table_name WHERE id = %d",
				$id
			)
		);
	}

	/**
	 * Get error logs with pagination and sorting.
	 *
	 * @param array $args Query arguments (limit, offset, orderby, order, search, form_id)
	 * @return array Array of error log objects.
	 */
	public function get_all( array $args = array() ): array {
		global $wpdb;
		$table_name = esc_sql( self::get_table_name() );

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

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				"SELECT * FROM $table_name $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d",
				...$query_params
			)
		);
	}

	/**
	 * Get the total count of error logs.
	 *
	 * @param array $args Query arguments (search, form_id)
	 * @return int
	 */
	public function get_total_count( array $args = array() ): int {
		global $wpdb;
		$table_name = esc_sql( self::get_table_name() );

		$query_params = array();
		$where_clause = $this->build_where_clause( $args, $query_params );

		$sql = "SELECT COUNT(id) FROM $table_name $where_clause";

		if ( empty( $query_params ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
			return (int) $wpdb->get_var( $sql );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
			return (int) $wpdb->get_var(
				$wpdb->prepare(
					// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
					$sql,
					...$query_params
				)
			);
		}
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
			$where[]        = '(ip_address LIKE %s OR submission_data LIKE %s OR errors LIKE %s)';
			$query_params[] = $search;
			$query_params[] = $search;
			$query_params[] = $search;
		}

		return ! empty( $where ) ? 'WHERE ' . implode( ' AND ', $where ) : '';
	}

	/**
	 * Delete a single error log.
	 *
	 * @param int $id The error log ID.
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function delete( int $id ) {
		global $wpdb;
		$table_name = esc_sql( self::get_table_name() );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->query(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"DELETE FROM $table_name WHERE id = %d",
				$id
			)
		);
	}

	/**
	 * Clear all error logs.
	 *
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function clear_all() {
		global $wpdb;
		$table_name = esc_sql( self::get_table_name() );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
		$result = $wpdb->query( "TRUNCATE TABLE $table_name" );
		if ( $result === false ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
			$result = $wpdb->query( "DELETE FROM $table_name" );
		}

		return $result !== false;
	}
}
