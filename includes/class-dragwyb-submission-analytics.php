<?php
declare(strict_types=1);

class Dragwyb_Submission_Analytics {
    private const TRANSIENT_PREFIX = 'dragwyb_analytics_';
    private const CACHE_DURATION = 3600; // 1 hour

    /**
     * Initialize analytics
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_analytics_page']);
        add_action('wp_ajax_dragwyb_get_analytics', [$this, 'get_analytics_data']);
    }

    /**
     * Add analytics page
     */
    public function add_analytics_page(): void {
        add_submenu_page(
            'dragwyb-forms',
            __('Form Analytics', 'dragwyb-form-builder'),
            __('Analytics', 'dragwyb-form-builder'),
            'manage_options',
            'dragwyb-analytics',
            [$this, 'render_analytics_page']
        );
    }

    /**
     * Render analytics page
     */
    public function render_analytics_page(): void {
        wp_enqueue_script('dragwyb-analytics', DRAGWYB_URL . 'assets/js/dragwyb-analytics.js', ['jquery', 'chart-js'], DRAGWYB_VERSION, true);
        wp_localize_script('dragwyb-analytics', 'dragwybAnalytics', [
            'nonce' => wp_create_nonce('dragwyb_analytics'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);

        include DRAGWYB_PATH . 'templates/admin/analytics.php';
    }

    /**
     * Get analytics data
     */
    public function get_analytics_data(): void {
        check_ajax_referer('dragwyb_analytics');

        $form_id = absint($_POST['form_id'] ?? 0);
        $date_range = sanitize_text_field($_POST['date_range'] ?? '30days');
        
        $cache_key = self::TRANSIENT_PREFIX . $form_id . '_' . $date_range;
        $data = get_transient($cache_key);

        if ($data === false) {
            $data = [
                'submission_trends' => $this->get_submission_trends($form_id, $date_range),
                'conversion_rate' => $this->get_conversion_rate($form_id, $date_range),
                'field_analytics' => $this->get_field_analytics($form_id, $date_range),
                'error_rates' => $this->get_error_rates($form_id, $date_range),
                'device_breakdown' => $this->get_device_breakdown($form_id, $date_range),
            ];

            set_transient($cache_key, $data, self::CACHE_DURATION);
        }

        wp_send_json_success($data);
    }

    /**
     * Get submission trends
     */
    private function get_submission_trends(int $form_id, string $date_range): array {
        global $wpdb;

        $date_query = $this->get_date_query($date_range);
        
        $query = $wpdb->prepare(
            "SELECT DATE(post_date) as date, COUNT(*) as count
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'dragwyb_submission'
            AND pm.meta_key = '_form_id'
            AND pm.meta_value = %d
            AND post_date >= %s
            AND post_date <= %s
            GROUP BY DATE(post_date)
            ORDER BY date ASC",
            $form_id,
            $date_query['start'],
            $date_query['end']
        );

        $results = $wpdb->get_results($query);
        
        return array_map(function($row) {
            return [
                'date' => $row->date,
                'count' => (int) $row->count,
            ];
        }, $results);
    }

    /**
     * Get conversion rate
     */
    private function get_conversion_rate(int $form_id, string $date_range): array {
        $date_query = $this->get_date_query($date_range);
        
        $views = get_post_meta($form_id, '_form_views', true) ?: 0;
        
        $submissions = wp_count_posts('dragwyb_submission');
        $total_submissions = $submissions->publish ?? 0;

        return [
            'views' => $views,
            'submissions' => $total_submissions,
            'rate' => $views > 0 ? ($total_submissions / $views) * 100 : 0,
        ];
    }

    /**
     * Get field analytics
     */
    private function get_field_analytics(int $form_id, string $date_range): array {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_submission_data'
            AND post_id IN (
                SELECT ID FROM {$wpdb->posts}
                WHERE post_type = 'dragwyb_submission'
                AND post_date >= %s
                AND post_date <= %s
            )",
            $this->get_date_query($date_range)['start'],
            $this->get_date_query($date_range)['end']
        );

        $results = $wpdb->get_col($query);
        $field_stats = [];

        foreach ($results as $submission_data) {
            $data = maybe_unserialize($submission_data);
            foreach ($data as $field_id => $value) {
                if (!isset($field_stats[$field_id])) {
                    $field_stats[$field_id] = [
                        'total' => 0,
                        'filled' => 0,
                    ];
                }

                $field_stats[$field_id]['total']++;
                if (!empty($value)) {
                    $field_stats[$field_id]['filled']++;
                }
            }
        }

        return $field_stats;
    }

    /**
     * Get error rates
     */
    private function get_error_rates(int $form_id, string $date_range): array {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT meta_value
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_validation_errors'
            AND post_id IN (
                SELECT ID FROM {$wpdb->posts}
                WHERE post_type = 'dragwyb_submission'
                AND post_date >= %s
                AND post_date <= %s
            )",
            $this->get_date_query($date_range)['start'],
            $this->get_date_query($date_range)['end']
        );

        $results = $wpdb->get_col($query);
        $error_stats = [];

        foreach ($results as $errors) {
            $error_data = maybe_unserialize($errors);
            foreach ($error_data as $field_id => $error) {
                if (!isset($error_stats[$field_id])) {
                    $error_stats[$field_id] = 0;
                }
                $error_stats[$field_id]++;
            }
        }

        return $error_stats;
    }

    /**
     * Get device breakdown
     */
    private function get_device_breakdown(int $form_id, string $date_range): array {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT meta_value, COUNT(*) as count
            FROM {$wpdb->postmeta}
            WHERE meta_key = '_user_agent'
            AND post_id IN (
                SELECT ID FROM {$wpdb->posts}
                WHERE post_type = 'dragwyb_submission'
                AND post_date >= %s
                AND post_date <= %s
            )
            GROUP BY meta_value",
            $this->get_date_query($date_range)['start'],
            $this->get_date_query($date_range)['end']
        );

        $results = $wpdb->get_results($query);
        $device_stats = [
            'desktop' => 0,
            'mobile' => 0,
            'tablet' => 0,
        ];

        foreach ($results as $row) {
            $device_type = $this->detect_device_type($row->meta_value);
            $device_stats[$device_type] += (int) $row->count;
        }

        return $device_stats;
    }

    /**
     * Get date query based on range
     */
    private function get_date_query(string $range): array {
        $end = current_time('Y-m-d H:i:s');
        
        switch ($range) {
            case '7days':
                $start = date('Y-m-d H:i:s', strtotime('-7 days'));
                break;
            case '30days':
                $start = date('Y-m-d H:i:s', strtotime('-30 days'));
                break;
            case '90days':
                $start = date('Y-m-d H:i:s', strtotime('-90 days'));
                break;
            default:
                $start = date('Y-m-d H:i:s', strtotime('-30 days'));
        }

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Detect device type from user agent
     */
    private function detect_device_type(string $user_agent): string {
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $user_agent)) {
            return 'tablet';
        }
        
        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $user_agent)) {
            return 'mobile';
        }
        
        return 'desktop';
    }
} 