<?php
declare(strict_types=1);

class Dragwyb_Analytics {
    private const ANALYTICS_VERSION = '1.0.0';
    private const EVENTS_TABLE = 'dragwyb_form_events';

    public function __construct() {
        add_action('init', [$this, 'init_analytics']);
        add_action('wp_ajax_dragwyb_get_analytics', [$this, 'get_analytics_data']);
        add_action('dragwyb_form_viewed', [$this, 'track_form_view']);
        add_action('dragwyb_form_submitted', [$this, 'track_form_submission']);
        add_action('dragwyb_form_abandoned', [$this, 'track_form_abandonment']);
    }

    /**
     * Initialize analytics system
     */
    public function init_analytics(): void {
        $this->create_tables();
        $this->schedule_cleanup();
    }

    /**
     * Create necessary database tables
     */
    private function create_tables(): void {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . self::EVENTS_TABLE;

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            form_id bigint(20) NOT NULL,
            event_type varchar(50) NOT NULL,
            user_id bigint(20) DEFAULT NULL,
            session_id varchar(50) NOT NULL,
            page_url varchar(255) NOT NULL,
            device_type varchar(20) NOT NULL,
            browser varchar(50) NOT NULL,
            country varchar(2) DEFAULT NULL,
            duration int DEFAULT NULL,
            fields_completed text DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY form_id (form_id),
            KEY event_type (event_type),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Track form view event
     */
    public function track_form_view(int $form_id): void {
        $this->track_event($form_id, 'view');
    }

    /**
     * Track form submission event
     */
    public function track_form_submission(int $form_id, array $data = []): void {
        $this->track_event($form_id, 'submission', $data);
    }

    /**
     * Track form abandonment
     */
    public function track_form_abandonment(int $form_id, array $data = []): void {
        $this->track_event($form_id, 'abandonment', $data);
    }

    /**
     * Track analytics event
     */
    private function track_event(int $form_id, string $event_type, array $data = []): void {
        global $wpdb;

        $event_data = [
            'form_id' => $form_id,
            'event_type' => $event_type,
            'user_id' => get_current_user_id() ?: null,
            'session_id' => $this->get_session_id(),
            'page_url' => $_SERVER['HTTP_REFERER'] ?? '',
            'device_type' => $this->get_device_type(),
            'browser' => $this->get_browser_info(),
            'country' => $this->get_country_code(),
            'duration' => $data['duration'] ?? null,
            'fields_completed' => isset($data['fields_completed']) ? 
                json_encode($data['fields_completed']) : null
        ];

        $wpdb->insert(
            $wpdb->prefix . self::EVENTS_TABLE,
            $event_data,
            ['%d', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s']
        );
    }

    /**
     * Get analytics data
     */
    public function get_analytics_data(): void {
        try {
            check_ajax_referer('dragwyb_analytics');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $form_id = isset($_POST['form_id']) ? absint($_POST['form_id']) : 0;
            $date_range = sanitize_text_field($_POST['date_range'] ?? '30days');
            $start_date = sanitize_text_field($_POST['start_date'] ?? '');
            $end_date = sanitize_text_field($_POST['end_date'] ?? '');

            $data = [
                'overview' => $this->get_overview_stats($form_id, $date_range, $start_date, $end_date),
                'conversion' => $this->get_conversion_stats($form_id, $date_range, $start_date, $end_date),
                'fields' => $this->get_field_stats($form_id, $date_range, $start_date, $end_date),
                'devices' => $this->get_device_stats($form_id, $date_range, $start_date, $end_date),
                'timeline' => $this->get_timeline_data($form_id, $date_range, $start_date, $end_date)
            ];

            wp_send_json_success($data);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get overview statistics
     */
    private function get_overview_stats(
        int $form_id, 
        string $date_range, 
        string $start_date, 
        string $end_date
    ): array {
        global $wpdb;
        $table_name = $wpdb->prefix . self::EVENTS_TABLE;
        $where_clause = $this->build_date_where_clause($date_range, $start_date, $end_date);

        if ($form_id) {
            $where_clause .= $wpdb->prepare(" AND form_id = %d", $form_id);
        }

        $stats = $wpdb->get_results("
            SELECT 
                event_type,
                COUNT(*) as count,
                AVG(duration) as avg_duration
            FROM $table_name
            WHERE 1=1 $where_clause
            GROUP BY event_type
        ");

        $overview = [
            'views' => 0,
            'submissions' => 0,
            'abandonment_rate' => 0,
            'avg_completion_time' => 0
        ];

        foreach ($stats as $stat) {
            switch ($stat->event_type) {
                case 'view':
                    $overview['views'] = (int)$stat->count;
                    break;
                case 'submission':
                    $overview['submissions'] = (int)$stat->count;
                    $overview['avg_completion_time'] = round($stat->avg_duration);
                    break;
                case 'abandonment':
                    $overview['abandonment_rate'] = $overview['views'] ? 
                        round(($stat->count / $overview['views']) * 100, 2) : 0;
                    break;
            }
        }

        return $overview;
    }

    /**
     * Get conversion statistics
     */
    private function get_conversion_stats(
        int $form_id, 
        string $date_range, 
        string $start_date, 
        string $end_date
    ): array {
        global $wpdb;
        $table_name = $wpdb->prefix . self::EVENTS_TABLE;
        $where_clause = $this->build_date_where_clause($date_range, $start_date, $end_date);

        if ($form_id) {
            $where_clause .= $wpdb->prepare(" AND form_id = %d", $form_id);
        }

        return $wpdb->get_results("
            SELECT 
                DATE(created_at) as date,
                COUNT(CASE WHEN event_type = 'view' THEN 1 END) as views,
                COUNT(CASE WHEN event_type = 'submission' THEN 1 END) as submissions,
                ROUND(
                    (COUNT(CASE WHEN event_type = 'submission' THEN 1 END) / 
                    COUNT(CASE WHEN event_type = 'view' THEN 1 END)) * 100,
                    2
                ) as conversion_rate
            FROM $table_name
            WHERE 1=1 $where_clause
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ");
    }

    /**
     * Get field statistics
     */
    private function get_field_stats(
        int $form_id, 
        string $date_range, 
        string $start_date, 
        string $end_date
    ): array {
        global $wpdb;
        $table_name = $wpdb->prefix . self::EVENTS_TABLE;
        $where_clause = $this->build_date_where_clause($date_range, $start_date, $end_date);

        if ($form_id) {
            $where_clause .= $wpdb->prepare(" AND form_id = %d", $form_id);
        }

        $results = $wpdb->get_results("
            SELECT 
                fields_completed,
                COUNT(*) as count
            FROM $table_name
            WHERE event_type = 'submission' $where_clause
            GROUP BY fields_completed
        ");

        $field_stats = [];
        foreach ($results as $result) {
            $fields = json_decode($result->fields_completed, true);
            foreach ($fields as $field => $value) {
                if (!isset($field_stats[$field])) {
                    $field_stats[$field] = [
                        'completion_rate' => 0,
                        'error_rate' => 0,
                        'avg_time' => 0
                    ];
                }
                $field_stats[$field]['completion_rate'] += $result->count;
            }
        }

        return $field_stats;
    }

    /**
     * Get device statistics
     */
    private function get_device_stats(
        int $form_id, 
        string $date_range, 
        string $start_date, 
        string $end_date
    ): array {
        global $wpdb;
        $table_name = $wpdb->prefix . self::EVENTS_TABLE;
        $where_clause = $this->build_date_where_clause($date_range, $start_date, $end_date);

        if ($form_id) {
            $where_clause .= $wpdb->prepare(" AND form_id = %d", $form_id);
        }

        return $wpdb->get_results("
            SELECT 
                device_type,
                COUNT(*) as total,
                COUNT(CASE WHEN event_type = 'submission' THEN 1 END) as submissions,
                ROUND(
                    (COUNT(CASE WHEN event_type = 'submission' THEN 1 END) / 
                    COUNT(*)) * 100,
                    2
                ) as conversion_rate
            FROM $table_name
            WHERE 1=1 $where_clause
            GROUP BY device_type
        ");
    }

    /**
     * Get timeline data
     */
    private function get_timeline_data(
        int $form_id, 
        string $date_range, 
        string $start_date, 
        string $end_date
    ): array {
        global $wpdb;
        $table_name = $wpdb->prefix . self::EVENTS_TABLE;
        $where_clause = $this->build_date_where_clause($date_range, $start_date, $end_date);

        if ($form_id) {
            $where_clause .= $wpdb->prepare(" AND form_id = %d", $form_id);
        }

        return $wpdb->get_results("
            SELECT 
                DATE(created_at) as date,
                HOUR(created_at) as hour,
                COUNT(*) as total,
                COUNT(CASE WHEN event_type = 'submission' THEN 1 END) as submissions
            FROM $table_name
            WHERE 1=1 $where_clause
            GROUP BY DATE(created_at), HOUR(created_at)
            ORDER BY date ASC, hour ASC
        ");
    }

    /**
     * Build WHERE clause for date filtering
     */
    private function build_date_where_clause(
        string $date_range, 
        string $start_date, 
        string $end_date
    ): string {
        global $wpdb;
        
        if ($start_date && $end_date) {
            return $wpdb->prepare(
                " AND DATE(created_at) BETWEEN %s AND %s",
                $start_date,
                $end_date
            );
        }

        $interval = match ($date_range) {
            '7days' => '7 DAY',
            '30days' => '30 DAY',
            '90days' => '90 DAY',
            'year' => '1 YEAR',
            default => '30 DAY'
        };

        return $wpdb->prepare(
            " AND created_at >= DATE_SUB(NOW(), INTERVAL %s)",
            $interval
        );
    }

    /**
     * Get session ID
     */
    private function get_session_id(): string {
        if (!session_id()) {
            session_start();
        }
        return session_id();
    }

    /**
     * Get device type
     */
    private function get_device_type(): string {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $user_agent)) {
            return 'tablet';
        }
        
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $user_agent)) {
            return 'mobile';
        }
        
        return 'desktop';
    }

    /**
     * Get browser information
     */
    private function get_browser_info(): string {
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        if (preg_match('/MSIE/i', $user_agent)) {
            return 'Internet Explorer';
        }
        if (preg_match('/Firefox/i', $user_agent)) {
            return 'Firefox';
        }
        if (preg_match('/Chrome/i', $user_agent)) {
            return 'Chrome';
        }
        if (preg_match('/Safari/i', $user_agent)) {
            return 'Safari';
        }
        if (preg_match('/Opera/i', $user_agent)) {
            return 'Opera';
        }
        
        return 'Unknown';
    }

    /**
     * Get country code from IP
     */
    private function get_country_code(): ?string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        if (!$ip) {
            return null;
        }

        $response = wp_remote_get("http://ip-api.com/json/{$ip}");
        if (is_wp_error($response)) {
            return null;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        return $data['countryCode'] ?? null;
    }

    /**
     * Schedule cleanup of old analytics data
     */
    private function schedule_cleanup(): void {
        if (!wp_next_scheduled('dragwyb_cleanup_analytics')) {
            wp_schedule_event(time(), 'daily', 'dragwyb_cleanup_analytics');
        }

        add_action('dragwyb_cleanup_analytics', [$this, 'cleanup_old_data']);
    }

    /**
     * Cleanup old analytics data
     */
    public function cleanup_old_data(): void {
        global $wpdb;
        $table_name = $wpdb->prefix . self::EVENTS_TABLE;

        $wpdb->query("
            DELETE FROM $table_name 
            WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
        ");
    }
} 