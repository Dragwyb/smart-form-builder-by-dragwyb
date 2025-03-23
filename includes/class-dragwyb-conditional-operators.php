<?php
declare(strict_types=1);

class Dragwyb_Conditional_Operators {
    private const CUSTOM_OPERATORS_OPTION = 'dragwyb_custom_operators';

    /**
     * Get all available operators
     */
    public function get_operators(): array {
        return array_merge(
            $this->get_text_operators(),
            $this->get_numeric_operators(),
            $this->get_date_operators(),
            $this->get_array_operators(),
            $this->get_regex_operators(),
            $this->get_custom_operators()
        );
    }

    /**
     * Text comparison operators
     */
    private function get_text_operators(): array {
        return [
            'text_length' => [
                'label' => __('Text Length', 'dragwyb-form-builder'),
                'operators' => [
                    'length_equals' => __('Length Equals', 'dragwyb-form-builder'),
                    'length_greater_than' => __('Length Greater Than', 'dragwyb-form-builder'),
                    'length_less_than' => __('Length Less Than', 'dragwyb-form-builder'),
                    'length_between' => __('Length Between', 'dragwyb-form-builder'),
                ],
                'value_type' => 'number',
                'callback' => [$this, 'evaluate_text_length'],
            ],
            'text_pattern' => [
                'label' => __('Text Pattern', 'dragwyb-form-builder'),
                'operators' => [
                    'matches_pattern' => __('Matches Pattern', 'dragwyb-form-builder'),
                    'not_matches_pattern' => __('Does Not Match Pattern', 'dragwyb-form-builder'),
                    'word_count' => __('Word Count', 'dragwyb-form-builder'),
                    'contains_word' => __('Contains Word', 'dragwyb-form-builder'),
                ],
                'value_type' => 'text',
                'callback' => [$this, 'evaluate_text_pattern'],
            ],
        ];
    }

    /**
     * Numeric comparison operators
     */
    private function get_numeric_operators(): array {
        return [
            'numeric_comparison' => [
                'label' => __('Numeric Comparison', 'dragwyb-form-builder'),
                'operators' => [
                    'is_multiple_of' => __('Is Multiple Of', 'dragwyb-form-builder'),
                    'is_not_multiple_of' => __('Is Not Multiple Of', 'dragwyb-form-builder'),
                    'is_divisible_by' => __('Is Divisible By', 'dragwyb-form-builder'),
                    'remainder_equals' => __('Remainder Equals', 'dragwyb-form-builder'),
                ],
                'value_type' => 'number',
                'callback' => [$this, 'evaluate_numeric_comparison'],
            ],
            'numeric_range' => [
                'label' => __('Numeric Range', 'dragwyb-form-builder'),
                'operators' => [
                    'in_range' => __('In Range', 'dragwyb-form-builder'),
                    'not_in_range' => __('Not In Range', 'dragwyb-form-builder'),
                    'is_within_percentage' => __('Is Within Percentage', 'dragwyb-form-builder'),
                    'differs_by_percentage' => __('Differs By Percentage', 'dragwyb-form-builder'),
                ],
                'value_type' => 'range',
                'callback' => [$this, 'evaluate_numeric_range'],
            ],
        ];
    }

    /**
     * Date comparison operators
     */
    private function get_date_operators(): array {
        return [
            'date_comparison' => [
                'label' => __('Date Comparison', 'dragwyb-form-builder'),
                'operators' => [
                    'is_future_date' => __('Is Future Date', 'dragwyb-form-builder'),
                    'is_past_date' => __('Is Past Date', 'dragwyb-form-builder'),
                    'is_today' => __('Is Today', 'dragwyb-form-builder'),
                    'is_weekday' => __('Is Weekday', 'dragwyb-form-builder'),
                    'is_weekend' => __('Is Weekend', 'dragwyb-form-builder'),
                ],
                'value_type' => 'none',
                'callback' => [$this, 'evaluate_date_comparison'],
            ],
            'date_range' => [
                'label' => __('Date Range', 'dragwyb-form-builder'),
                'operators' => [
                    'days_from_now' => __('Days From Now', 'dragwyb-form-builder'),
                    'months_from_now' => __('Months From Now', 'dragwyb-form-builder'),
                    'is_within_days' => __('Is Within Days', 'dragwyb-form-builder'),
                    'is_within_months' => __('Is Within Months', 'dragwyb-form-builder'),
                ],
                'value_type' => 'number',
                'callback' => [$this, 'evaluate_date_range'],
            ],
        ];
    }

    /**
     * Array comparison operators
     */
    private function get_array_operators(): array {
        return [
            'array_comparison' => [
                'label' => __('Array Comparison', 'dragwyb-form-builder'),
                'operators' => [
                    'contains_all' => __('Contains All', 'dragwyb-form-builder'),
                    'contains_any' => __('Contains Any', 'dragwyb-form-builder'),
                    'contains_none' => __('Contains None', 'dragwyb-form-builder'),
                    'contains_exactly' => __('Contains Exactly', 'dragwyb-form-builder'),
                ],
                'value_type' => 'array',
                'callback' => [$this, 'evaluate_array_comparison'],
            ],
            'array_count' => [
                'label' => __('Array Count', 'dragwyb-form-builder'),
                'operators' => [
                    'count_equals' => __('Count Equals', 'dragwyb-form-builder'),
                    'count_greater_than' => __('Count Greater Than', 'dragwyb-form-builder'),
                    'count_less_than' => __('Count Less Than', 'dragwyb-form-builder'),
                    'count_between' => __('Count Between', 'dragwyb-form-builder'),
                ],
                'value_type' => 'number',
                'callback' => [$this, 'evaluate_array_count'],
            ],
        ];
    }

    /**
     * Regular expression operators
     */
    private function get_regex_operators(): array {
        return [
            'regex_pattern' => [
                'label' => __('Regular Expression', 'dragwyb-form-builder'),
                'operators' => [
                    'matches_regex' => __('Matches Regex', 'dragwyb-form-builder'),
                    'not_matches_regex' => __('Does Not Match Regex', 'dragwyb-form-builder'),
                    'extract_matches' => __('Extract Matches', 'dragwyb-form-builder'),
                    'replace_pattern' => __('Replace Pattern', 'dragwyb-form-builder'),
                ],
                'value_type' => 'regex',
                'callback' => [$this, 'evaluate_regex_pattern'],
            ],
        ];
    }

    /**
     * Get custom operators
     */
    private function get_custom_operators(): array {
        return get_option(self::CUSTOM_OPERATORS_OPTION, []);
    }

    /**
     * Evaluate text length operators
     */
    public function evaluate_text_length($value, $operator, $compare_value): bool {
        $length = mb_strlen($value);

        switch ($operator) {
            case 'length_equals':
                return $length === (int) $compare_value;

            case 'length_greater_than':
                return $length > (int) $compare_value;

            case 'length_less_than':
                return $length < (int) $compare_value;

            case 'length_between':
                if (!is_array($compare_value)) return false;
                return $length >= (int) $compare_value[0] && 
                       $length <= (int) $compare_value[1];

            default:
                return false;
        }
    }

    /**
     * Evaluate text pattern operators
     */
    public function evaluate_text_pattern($value, $operator, $compare_value): bool {
        switch ($operator) {
            case 'matches_pattern':
                return fnmatch($compare_value, $value);

            case 'not_matches_pattern':
                return !fnmatch($compare_value, $value);

            case 'word_count':
                $word_count = str_word_count($value);
                return $word_count === (int) $compare_value;

            case 'contains_word':
                return in_array($compare_value, str_word_count($value, 1));

            default:
                return false;
        }
    }

    /**
     * Evaluate numeric comparison operators
     */
    public function evaluate_numeric_comparison($value, $operator, $compare_value): bool {
        if (!is_numeric($value)) return false;

        switch ($operator) {
            case 'is_multiple_of':
                return $value % $compare_value === 0;

            case 'is_not_multiple_of':
                return $value % $compare_value !== 0;

            case 'is_divisible_by':
                return $compare_value !== 0 && $value % $compare_value === 0;

            case 'remainder_equals':
                if (!is_array($compare_value)) return false;
                return $value % $compare_value[0] === (int) $compare_value[1];

            default:
                return false;
        }
    }

    /**
     * Evaluate numeric range operators
     */
    public function evaluate_numeric_range($value, $operator, $compare_value): bool {
        if (!is_numeric($value)) return false;

        switch ($operator) {
            case 'in_range':
                if (!is_array($compare_value)) return false;
                return $value >= $compare_value[0] && $value <= $compare_value[1];

            case 'not_in_range':
                if (!is_array($compare_value)) return false;
                return $value < $compare_value[0] || $value > $compare_value[1];

            case 'is_within_percentage':
                if (!is_array($compare_value)) return false;
                $target = $compare_value[0];
                $percentage = $compare_value[1];
                $difference = abs($value - $target);
                return ($difference / $target * 100) <= $percentage;

            case 'differs_by_percentage':
                if (!is_array($compare_value)) return false;
                $target = $compare_value[0];
                $percentage = $compare_value[1];
                $difference = abs($value - $target);
                return ($difference / $target * 100) >= $percentage;

            default:
                return false;
        }
    }

    /**
     * Evaluate date comparison operators
     */
    public function evaluate_date_comparison($value, $operator, $compare_value): bool {
        $date = strtotime($value);
        if ($date === false) return false;

        $today = strtotime('today');
        $now = current_time('timestamp');

        switch ($operator) {
            case 'is_future_date':
                return $date > $now;

            case 'is_past_date':
                return $date < $today;

            case 'is_today':
                return date('Y-m-d', $date) === date('Y-m-d', $today);

            case 'is_weekday':
                return !in_array(date('N', $date), ['6', '7']);

            case 'is_weekend':
                return in_array(date('N', $date), ['6', '7']);

            default:
                return false;
        }
    }

    /**
     * Evaluate date range operators
     */
    public function evaluate_date_range($value, $operator, $compare_value): bool {
        $date = strtotime($value);
        if ($date === false) return false;

        $now = current_time('timestamp');

        switch ($operator) {
            case 'days_from_now':
                $days_diff = round(($date - $now) / DAY_IN_SECONDS);
                return $days_diff === (int) $compare_value;

            case 'months_from_now':
                $months_diff = (date('Y', $date) - date('Y', $now)) * 12 
                             + (date('m', $date) - date('m', $now));
                return $months_diff === (int) $compare_value;

            case 'is_within_days':
                $days_diff = abs(round(($date - $now) / DAY_IN_SECONDS));
                return $days_diff <= (int) $compare_value;

            case 'is_within_months':
                $months_diff = abs((date('Y', $date) - date('Y', $now)) * 12 
                                 + (date('m', $date) - date('m', $now)));
                return $months_diff <= (int) $compare_value;

            default:
                return false;
        }
    }

    /**
     * Evaluate array comparison operators
     */
    public function evaluate_array_comparison($value, $operator, $compare_value): bool {
        if (!is_array($value)) {
            $value = [$value];
        }

        if (!is_array($compare_value)) {
            $compare_value = [$compare_value];
        }

        switch ($operator) {
            case 'contains_all':
                return count(array_intersect($value, $compare_value)) === count($compare_value);

            case 'contains_any':
                return count(array_intersect($value, $compare_value)) > 0;

            case 'contains_none':
                return count(array_intersect($value, $compare_value)) === 0;

            case 'contains_exactly':
                sort($value);
                sort($compare_value);
                return $value === $compare_value;

            default:
                return false;
        }
    }

    /**
     * Evaluate array count operators
     */
    public function evaluate_array_count($value, $operator, $compare_value): bool {
        if (!is_array($value)) {
            $value = [$value];
        }

        $count = count($value);

        switch ($operator) {
            case 'count_equals':
                return $count === (int) $compare_value;

            case 'count_greater_than':
                return $count > (int) $compare_value;

            case 'count_less_than':
                return $count < (int) $compare_value;

            case 'count_between':
                if (!is_array($compare_value)) return false;
                return $count >= (int) $compare_value[0] && 
                       $count <= (int) $compare_value[1];

            default:
                return false;
        }
    }

    /**
     * Evaluate regex pattern operators
     */
    public function evaluate_regex_pattern($value, $operator, $compare_value): bool {
        switch ($operator) {
            case 'matches_regex':
                return preg_match($compare_value, $value) === 1;

            case 'not_matches_regex':
                return preg_match($compare_value, $value) === 0;

            case 'extract_matches':
                $matches = [];
                preg_match_all($compare_value, $value, $matches);
                return !empty($matches[0]);

            case 'replace_pattern':
                if (!is_array($compare_value)) return false;
                $pattern = $compare_value[0];
                $replacement = $compare_value[1];
                return preg_replace($pattern, $replacement, $value) !== $value;

            default:
                return false;
        }
    }
} 