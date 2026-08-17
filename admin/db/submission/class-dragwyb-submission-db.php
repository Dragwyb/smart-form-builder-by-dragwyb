<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Db\Submission;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Dragwyb_Submission_Db {

	const VERSION = '1.1';

	/**
	 * Get the table name with the WP prefix.
	 */
	private static function get_table_name(): string {
		global $wpdb;
		return esc_sql( sanitize_text_field( $wpdb->prefix . 'dragwyb_submissions' ) );
	}

	/**
	 * Create the database table & run migration if needed.
	 */
	public static function create_table(): void {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            form_id bigint(20) unsigned NOT NULL,
            submission_data longtext NOT NULL,
            extra_data longtext DEFAULT NULL,
            status varchar(20) DEFAULT 'unread' NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY form_id (form_id)
        ) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		self::migrate_v1_to_v2();
	}

	/**
	 * Migrate v1 data to v2 extra_data column structure.
	 */
	public static function migrate_v1_to_v2(): void {
		global $wpdb;
		$table_name = self::get_table_name();

		// Check if extra_data column exists
		$column_exists = $wpdb->get_results(
			$wpdb->prepare( "SHOW COLUMNS FROM $table_name LIKE %s", 'extra_data' )
		);

		if ( empty( $column_exists ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query( "ALTER TABLE $table_name ADD COLUMN extra_data longtext DEFAULT NULL" );
		}

		// Query rows where extra_data is NULL or empty
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results( "SELECT * FROM $table_name WHERE extra_data IS NULL OR extra_data = '' OR extra_data = '{}'" );

		if ( ! empty( $rows ) ) {
			foreach ( $rows as $row ) {
				$extra_data = array(
					'visitor_info'    => array(
						'user_id'        => isset( $row->user_id ) ? (int) $row->user_id : null,
						'ip_address'     => ! empty( $row->ip_address ) ? (string) $row->ip_address : '-',
						'user_agent'     => ! empty( $row->user_agent ) ? (string) $row->user_agent : '-',
						'device'         => '-',
						'browser'        => '-',
						'os'             => '-',
						'screen'         => '-',
						'language'       => '-',
						'time_to_submit' => '-',
					),
					'lead_attributes' => array(
						'traffic_source' => 'Direct',
						'referrer'       => 'Direct',
						'landing_page'   => '-',
						'utm_source'     => '-',
						'utm_medium'     => '-',
						'utm_campaign'   => '-',
						'utm_term'       => '-',
						'utm_content'    => '-',
					),
				);

				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->update(
					$table_name,
					array( 'extra_data' => wp_json_encode( $extra_data ) ),
					array( 'id' => $row->id )
				);
			}
		}

		// Drop old v1 columns: user_id, ip_address, user_agent
		$old_columns = array( 'user_id', 'ip_address', 'user_agent' );
		foreach ( $old_columns as $col ) {
			$has_col = $wpdb->get_results(
				$wpdb->prepare( "SHOW COLUMNS FROM $table_name LIKE %s", $col )
			);

			if ( ! empty( $has_col ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->query( "ALTER TABLE $table_name DROP COLUMN `$col`" );
			}
		}
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
			'submission_data' => '{}',
			'extra_data'      => '{}',
			'status'          => 'unread',
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

		if ( is_array( $data['extra_data'] ) || is_object( $data['extra_data'] ) ) {
			$data['extra_data'] = wp_json_encode( $data['extra_data'] );
		}

		$table_name = esc_sql( self::get_table_name() );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		$inserted = $wpdb->query(
			$wpdb->prepare(
				"INSERT INTO $table_name (form_id, submission_data, extra_data, status, created_at, updated_at) VALUES (%d, %s, %s, %s, %s, %s)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $data['form_id'] ),
				$data['submission_data'],
				$data['extra_data'],
				sanitize_text_field( $data['status'] ),
				sanitize_text_field( $data['created_at'] ),
				sanitize_text_field( $data['updated_at'] )
			)
		);

		if ( false === $inserted ) {
			if ( ! empty( $wpdb->last_error ) ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
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
		$table_name = esc_sql( self::get_table_name() );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM $table_name WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$id
			)
		);
	}

	/**
	 * Get submissions with pagination and sorting.
	 *
	 * @param array $args Query arguments (limit, offset, orderby, order, search, form_id)
	 * @return array Array of submission objects.
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

		$allowed_orderby = array( 'id', 'form_id', 'created_at' );
		$orderby         = in_array( $args['orderby'], $allowed_orderby, true ) ? $args['orderby'] : 'created_at';
		$order           = strtoupper( $args['order'] ) === 'ASC' ? 'ASC' : 'DESC';

		$query_params = array();
		$where_clause = $this->build_where_clause( $args, $query_params );

		$query_params[] = absint( $args['limit'] );
		$query_params[] = absint( $args['offset'] );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return $wpdb->get_results(
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->prepare(
				"SELECT * FROM $table_name $where_clause ORDER BY $orderby $order LIMIT %d OFFSET %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				...$query_params
			)
		);
	}

	/**
	 * Get the total count of submissions.
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
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
			return (int) $wpdb->get_var( $sql );
		} else {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
			return (int) $wpdb->get_var(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
				$wpdb->prepare(
					$sql, // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
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
			$where[]        = '(submission_data LIKE %s OR extra_data LIKE %s)';
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
		$table_name = esc_sql( self::get_table_name() );

		if ( empty( $data ) ) {
			return false;
		}

		$set_parts = array();
		$values    = array();

		$allowed_columns = array( 'form_id', 'submission_data', 'extra_data', 'status', 'created_at', 'updated_at' );

		foreach ( $data as $key => $value ) {
			if ( ! in_array( $key, $allowed_columns, true ) ) {
				continue;
			}
			$format      = 'form_id' === $key ? '%d' : '%s';
			$set_parts[] = '`' . sanitize_key( $key ) . '` = ' . $format;
			$values[]    = is_array( $value ) || is_object( $value ) ? wp_json_encode( $value ) : $value;
		}

		$values[] = $id;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery, PluginCheck.Security.DirectDB.UnescapedDBParameter
		return $wpdb->query(
			// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
			$wpdb->prepare(
				"UPDATE $table_name SET " . implode( ', ', $set_parts ) . ' WHERE id = %d', // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQL.NotPrepared
				...$values
			)
		);
	}

	/**
	 * Delete a submission.
	 *
	 * @param int $id The submission ID.
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function delete( int $id ) {
		global $wpdb;
		$table_name = esc_sql( self::get_table_name() );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $table_name WHERE id = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$id
			)
		);
	}

	/**
	 * Delete multiple submissions.
	 *
	 * @param array $ids The submission IDs to delete.
	 * @return int|false The number of rows deleted, or false on error.
	 */
	public function delete_multiple( array $ids ) {
		global $wpdb;
		$table_name = esc_sql( self::get_table_name() );

		if ( empty( $ids ) ) {
			return 0;
		}

		$ids    = array_map( 'absint', $ids );
		$format = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM $table_name WHERE id IN ($format)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				...$ids
			)
		);
	}
}
