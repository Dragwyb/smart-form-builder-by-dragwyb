<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Validation_Optimizer {
    private const BATCH_SIZE = 50;
    private const CACHE_TTL = 3600;
    private $validation_stats = [];
    private $rule_priorities = [];

    public function __construct() {
        $this->init_rule_priorities();
        $this->load_validation_stats();
    }

    /**
     * Initialize rule priorities based on complexity
     */
    private function init_rule_priorities(): void {
        $this->rule_priorities = [
            'required' => 1,
            'email' => 2,
            'number' => 2,
            'url' => 2,
            'date_format' => 3,
            'word_count' => 3,
            'file_type' => 4,
            'file_size' => 4,
            'password_strength' => 5,
            'custom_regex' => 5,
            'unique_value' => 6,
            'recaptcha' => 7,
        ];
    }

    /**
     * Load validation statistics from cache
     */
    private function load_validation_stats(): void {
        $this->validation_stats = get_transient('dragwyb_validation_stats') ?: [];
    }

    /**
     * Optimize validation rules order based on statistics and priorities
     */
    public function optimize_rules(array $rules): array {
        $weighted_rules = [];

        foreach ($rules as $rule) {
            $weight = $this->calculate_rule_weight($rule);
            $weighted_rules[$rule] = $weight;
        }

        arsort($weighted_rules);
        return array_keys($weighted_rules);
    }

    /**
     * Calculate rule weight based on priority and success rate
     */
    private function calculate_rule_weight(string $rule): float {
        $priority = $this->rule_priorities[$rule] ?? 5;
        $stats = $this->validation_stats[$rule] ?? ['success' => 0, 'total' => 0];
        
        if ($stats['total'] === 0) {
            return 10 - $priority;
        }

        $success_rate = $stats['success'] / $stats['total'];
        return (10 - $priority) * $success_rate;
    }

    /**
     * Record validation result for optimization
     */
    public function record_validation(string $rule, bool $success): void {
        if (!isset($this->validation_stats[$rule])) {
            $this->validation_stats[$rule] = ['success' => 0, 'total' => 0];
        }

        $this->validation_stats[$rule]['total']++;
        if ($success) {
            $this->validation_stats[$rule]['success']++;
        }

        $this->save_validation_stats();
    }

    /**
     * Save validation statistics to cache
     */
    private function save_validation_stats(): void {
        set_transient('dragwyb_validation_stats', $this->validation_stats, self::CACHE_TTL);
    }

    /**
     * Process validation in batches
     */
    public function process_batch(array $fields): array {
        $results = [];
        $batches = array_chunk($fields, self::BATCH_SIZE);

        foreach ($batches as $batch) {
            $results = array_merge($results, $this->validate_batch($batch));
        }

        return $results;
    }

    /**
     * Validate a batch of fields
     */
    private function validate_batch(array $batch): array {
        $results = [];
        
        // Group fields by validation rules to minimize rule initialization
        $grouped_fields = $this->group_fields_by_rules($batch);
        
        foreach ($grouped_fields as $rule => $fields) {
            $validator = $this->get_validator_for_rule($rule);
            foreach ($fields as $field) {
                $results[$field['id']] = $validator->validate($field);
            }
        }

        return $results;
    }

    /**
     * Group fields by their validation rules
     */
    private function group_fields_by_rules(array $fields): array {
        $grouped = [];
        
        foreach ($fields as $field) {
            foreach ($field['validation_rules'] as $rule) {
                if (!isset($grouped[$rule])) {
                    $grouped[$rule] = [];
                }
                $grouped[$rule][] = $field;
            }
        }

        return $grouped;
    }
} 