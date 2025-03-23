<?php
declare(strict_types=1);

class Dragwyb_Performance_Benchmarks {
    private const BENCHMARK_OPTION = 'dragwyb_performance_benchmarks';
    private const UPDATE_INTERVAL = 86400; // 24 hours

    private $metrics = [
        'submission_time' => [
            'good' => 3,    // seconds
            'fair' => 5,    // seconds
            'poor' => 8,    // seconds
        ],
        'error_rate' => [
            'good' => 0.02, // 2%
            'fair' => 0.05, // 5%
            'poor' => 0.10, // 10%
        ],
        'completion_rate' => [
            'good' => 0.80, // 80%
            'fair' => 0.60, // 60%
            'poor' => 0.40, // 40%
        ],
    ];

    /**
     * Initialize benchmarks
     */
    public function __construct() {
        add_action('dragwyb_daily_tasks', [$this, 'update_benchmarks']);
    }

    /**
     * Update performance benchmarks
     */
    public function update_benchmarks(): void {
        $forms = get_posts([
            'post_type' => 'dragwyb_form',
            'posts_per_page' => -1,
        ]);

        $benchmarks = [];

        foreach ($forms as $form) {
            $benchmarks[$form->ID] = $this->calculate_form_benchmarks($form->ID);
        }

        update_option(self::BENCHMARK_OPTION, [
            'last_updated' => current_time('timestamp'),
            'data' => $benchmarks,
        ]);
    }

    /**
     * Calculate benchmarks for a specific form
     */
    private function calculate_form_benchmarks(int $form_id): array {
        return [
            'submission_time' => $this->calculate_submission_time_benchmark($form_id),
            'error_rate' => $this->calculate_error_rate_benchmark($form_id),
            'completion_rate' => $this->calculate_completion_rate_benchmark($form_id),
            'performance_score' => $this->calculate_performance_score($form_id),
        ];
    }

    /**
     * Get performance rating
     */
    public function get_performance_rating(string $metric, float $value): string {
        if (!isset($this->metrics[$metric])) {
            return 'unknown';
        }

        $thresholds = $this->metrics[$metric];

        if ($value <= $thresholds['good']) {
            return 'good';
        } elseif ($value <= $thresholds['fair']) {
            return 'fair';
        } else {
            return 'poor';
        }
    }

    /**
     * Get benchmark data
     */
    public function get_benchmarks(int $form_id = null): array {
        $benchmarks = get_option(self::BENCHMARK_OPTION);

        if ($form_id !== null) {
            return $benchmarks['data'][$form_id] ?? [];
        }

        return $benchmarks['data'] ?? [];
    }

    /**
     * Calculate submission time benchmark
     */
    private function calculate_submission_time_benchmark(int $form_id): array {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT AVG(TIMESTAMPDIFF(SECOND, session_start, post_date)) as avg_time
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE post_type = 'dragwyb_submission'
            AND meta_key = '_form_id'
            AND meta_value = %d
            AND post_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            $form_id
        );

        $result = $wpdb->get_row($query);
        $avg_time = (float) $result->avg_time;

        return [
            'value' => $avg_time,
            'rating' => $this->get_performance_rating('submission_time', $avg_time),
        ];
    }

    /**
     * Calculate error rate benchmark
     */
    private function calculate_error_rate_benchmark(int $form_id): array {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT 
                COUNT(DISTINCT CASE WHEN pm2.meta_value IS NOT NULL THEN p.ID END) as error_count,
                COUNT(DISTINCT p.ID) as total_count
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_validation_errors'
            WHERE post_type = 'dragwyb_submission'
            AND pm1.meta_key = '_form_id'
            AND pm1.meta_value = %d
            AND post_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            $form_id
        );

        $result = $wpdb->get_row($query);
        $error_rate = $result->total_count > 0 ? 
            $result->error_count / $result->total_count : 0;

        return [
            'value' => $error_rate,
            'rating' => $this->get_performance_rating('error_rate', $error_rate),
        ];
    }

    /**
     * Calculate completion rate benchmark
     */
    private function calculate_completion_rate_benchmark(int $form_id): array {
        global $wpdb;

        $query = $wpdb->prepare(
            "SELECT 
                COUNT(DISTINCT p.ID) as completed_count,
                COUNT(DISTINCT s.session_id) as total_sessions
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            JOIN {$wpdb->prefix}dragwyb_sessions s ON s.form_id = pm.meta_value
            WHERE post_type = 'dragwyb_submission'
            AND pm.meta_key = '_form_id'
            AND pm.meta_value = %d
            AND post_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)",
            $form_id
        );

        $result = $wpdb->get_row($query);
        $completion_rate = $result->total_sessions > 0 ? 
            $result->completed_count / $result->total_sessions : 0;

        return [
            'value' => $completion_rate,
            'rating' => $this->get_performance_rating('completion_rate', $completion_rate),
        ];
    }

    /**
     * Calculate overall performance score
     */
    private function calculate_performance_score(int $form_id): float {
        $weights = [
            'submission_time' => 0.3,
            'error_rate' => 0.3,
            'completion_rate' => 0.4,
        ];

        $score = 0;
        $benchmarks = $this->calculate_form_benchmarks($form_id);

        foreach ($weights as $metric => $weight) {
            $value = $benchmarks[$metric]['value'];
            $normalized = $this->normalize_metric_value($metric, $value);
            $score += $normalized * $weight;
        }

        return round($score * 100, 2);
    }

    /**
     * Normalize metric value to 0-1 scale
     */
    private function normalize_metric_value(string $metric, float $value): float {
        $thresholds = $this->metrics[$metric];

        if ($value <= $thresholds['good']) {
            return 1.0;
        } elseif ($value <= $thresholds['fair']) {
            return 0.7;
        } elseif ($value <= $thresholds['poor']) {
            return 0.4;
        } else {
            return 0.0;
        }
    }
} 