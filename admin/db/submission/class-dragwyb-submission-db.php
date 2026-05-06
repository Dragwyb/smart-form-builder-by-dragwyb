<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Db\Submission;

if (!defined('ABSPATH')) {
    exit;
}

class Dragwyb_Submission_Db
{

    const VERSION = 'v1';

    /**
     * Get the table name with the WP prefix.
     */
    public static function get_table_name(): string
    {
        global $wpdb;
        return $wpdb->prefix . 'dragwyb_submissions';
    }

    /**
     * Create the database table.
     */
    public static function create_table(): void
    {
        global $wpdb;

        $table_name = self::get_table_name();
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
        dbDelta($sql);
    }

    /**
     * Insert a new form submission into the database.
     *
     * @param array $args The submission data to insert.
     * @return int|\WP_Error The ID of the inserted row or WP_Error on failure.
     */
    public function insert(array $args)
    {
        global $wpdb;

        $defaults = [
            'form_id'         => 0,
            'user_id'         => get_current_user_id() ?: null,
            'ip_address'      => $this->get_ip_address(),
            'user_agent'      => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_textarea_field($_SERVER['HTTP_USER_AGENT']) : '',
            'submission_data' => '{}',
            'status'          => 'publish',
            'created_at'      => current_time('mysql'),
            'updated_at'      => current_time('mysql'),
        ];

        $data = wp_parse_args($args, $defaults);

        // Basic validation
        if (empty($data['form_id'])) {
            return new \WP_Error('missing_form_id', __('Form ID is required for submission.', 'smart-form-builder-by-dragwyb'));
        }

        // Ensure submission_data is properly encoded
        if (is_array($data['submission_data']) || is_object($data['submission_data'])) {
            $data['submission_data'] = wp_json_encode($data['submission_data']);
        }

        $table_name = self::get_table_name();

        $sql = $wpdb->prepare(
            "INSERT INTO $table_name (form_id, user_id, ip_address, user_agent, submission_data, status, created_at, updated_at) VALUES (%d, %d, %s, %s, %s, %s, %s, %s)",
            absint($data['form_id']),
            absint($data['user_id']),
            sanitize_text_field($data['ip_address']),
            sanitize_text_field($data['user_agent']),
            $data['submission_data'],
            sanitize_text_field($data['status']),
            sanitize_text_field($data['created_at']),
            sanitize_text_field($data['updated_at'])
        );

        $inserted = $wpdb->query($sql);

        if (false === $inserted) {
            return new \WP_Error('db_insert_error', $wpdb->last_error);
        }

        return $wpdb->insert_id;
    }

    /**
     * Get a submission by ID.
     *
     * @param int $id The submission ID.
     * @return object|null The submission object or null if not found.
     */
    public function get(int $id)
    {
        global $wpdb;
        $table_name = self::get_table_name();

        $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id);
        return $wpdb->get_row($sql);
    }

    /**
     * Get IP address safely.
     *
     * @return string
     */
    private function get_ip_address(): string
    {
        $ip = '127.0.0.1';

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        // Handle multiple IPs in X-Forwarded-For
        $ip_array = explode(',', $ip);
        $ip = trim($ip_array[0]);

        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
    }
}
