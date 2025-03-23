<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Conditional_Logic {
    public function __construct() {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }

    public function enqueue_scripts(): void {
        wp_enqueue_script(
            'dragwyb-conditional-logic',
            DRAGWYB_FORM_BUILDER_URL . 'assets/js/dragwyb-conditional-logic.js',
            ['jquery'],
            DRAGWYB_FORM_BUILDER_VERSION,
            true
        );
    }

    public function get_field_value($field_id, $submitted_data): mixed {
        return $submitted_data[$field_id] ?? null;
    }

    public function evaluate_conditions(array $conditions, array $field_values): bool {
        $logic = $conditions['conditional_logic'] ?? 'all';
        $rules = $conditions['conditional_rules'] ?? [];

        if (empty($rules)) {
            return true;
        }

        $results = array_map(function($rule) use ($field_values) {
            return $this->evaluate_rule($rule, $field_values);
        }, $rules);

        return $logic === 'all' ? !in_array(false, $results, true) : in_array(true, $results, true);
    }

    private function evaluate_rule(array $rule, array $field_values): bool {
        $field = $rule['field'] ?? '';
        $operator = $rule['operator'] ?? '';
        $value = $rule['value'] ?? '';
        $field_value = $field_values[$field] ?? '';

        switch ($operator) {
            case 'equals':
                return $field_value == $value;

            case 'not_equals':
                return $field_value != $value;

            case 'contains':
                return is_string($field_value) && strpos($field_value, $value) !== false;

            case 'not_contains':
                return is_string($field_value) && strpos($field_value, $value) === false;

            case 'greater_than':
                return is_numeric($field_value) && is_numeric($value) && 
                       floatval($field_value) > floatval($value);

            case 'less_than':
                return is_numeric($field_value) && is_numeric($value) && 
                       floatval($field_value) < floatval($value);

            default:
                return false;
        }
    }
} 