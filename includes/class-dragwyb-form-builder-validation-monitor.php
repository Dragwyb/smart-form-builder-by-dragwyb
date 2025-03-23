<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Validation_Monitor {
    private const LOG_OPTION = 'dragwyb_validation_performance_log';
    private $start_time;
    private $metrics = [];

    public function start_monitoring(): void {
        $this->start_time = microtime(true);
    }

    public function record_metric(string $rule, float $duration, bool $cached = false): void {
        if (!isset($this->metrics[$rule])) {
            $this->metrics[$rule] = [
                'count' => 0,
                'total_time' => 0,
                'cached_count' => 0,
            ];
        }

        $this->metrics[$rule]['count']++;
        $this->metrics[$rule]['total_time'] += $duration;
        
        if ($cached) {
            $this->metrics[$rule]['cached_count']++;
        }
    }

    public function end_monitoring(): array {
        $total_duration = microtime(true) - $this->start_time;
        $this->save_metrics($total_duration);

        return [
            'total_duration' => $total_duration,
            'metrics' => $this->metrics,
        ];
    }

    private function save_metrics(float $total_duration): void {
        $log = get_option(self::LOG_OPTION, []);
        $log[date('Y-m-d H:i:s')] = [
            'total_duration' => $total_duration,
            'metrics' => $this->metrics,
        ];

        // Keep only last 100 entries
        if (count($log) > 100) {
            $log = array_slice($log, -100, 100, true);
        }

        update_option(self::LOG_OPTION, $log);
    }

    public function get_performance_report(): array {
        $log = get_option(self::LOG_OPTION, []);
        
        $report = [
            'average_duration' => 0,
            'rule_stats' => [],
            'cache_effectiveness' => 0,
        ];

        if (empty($log)) {
            return $report;
        }

        $total_entries = count($log);
        $total_duration = 0;
        $rule_metrics = [];
        $cache_hits = 0;
        $total_validations = 0;

        foreach ($log as $entry) {
            $total_duration += $entry['total_duration'];
            
            foreach ($entry['metrics'] as $rule => $metrics) {
                if (!isset($rule_metrics[$rule])) {
                    $rule_metrics[$rule] = [
                        'count' => 0,
                        'total_time' => 0,
                        'cached_count' => 0,
                    ];
                }
                
                $rule_metrics[$rule]['count'] += $metrics['count'];
                $rule_metrics[$rule]['total_time'] += $metrics['total_time'];
                $rule_metrics[$rule]['cached_count'] += $metrics['cached_count'];
                
                $total_validations += $metrics['count'];
                $cache_hits += $metrics['cached_count'];
            }
        }

        $report['average_duration'] = $total_duration / $total_entries;
        $report['cache_effectiveness'] = $total_validations > 0 ? 
            ($cache_hits / $total_validations) * 100 : 0;

        foreach ($rule_metrics as $rule => $metrics) {
            $report['rule_stats'][$rule] = [
                'average_time' => $metrics['count'] > 0 ? 
                    $metrics['total_time'] / $metrics['count'] : 0,
                'usage_count' => $metrics['count'],
                'cache_hit_rate' => $metrics['count'] > 0 ? 
                    ($metrics['cached_count'] / $metrics['count']) * 100 : 0,
            ];
        }

        return $report;
    }
} 