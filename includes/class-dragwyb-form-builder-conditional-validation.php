<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Conditional_Validation {
    private $conditions = [];
    private $field_values = [];

    /**
     * Set up conditional validation rules
     */
    public function add_condition(array $condition): void {
        $this->conditions[] = $condition;
    }

    /**
     * Set field values for evaluation
     */
    public function set_field_values(array $values): void {
        $this->field_values = $values;
    }

    /**
     * Evaluate conditions for a field
     */
    public function should_validate_field(string $field_id): bool {
        foreach ($this->conditions as $condition) {
            if ($condition['target_field'] === $field_id) {
                return $this->evaluate_condition($condition);
            }
        }
        return true; // Validate by default if no conditions
    }

    /**
     * Evaluate a single condition
     */
    private function evaluate_condition(array $condition): bool {
        $source_value = $this->field_values[$condition['source_field']] ?? null;
        
        switch ($condition['operator']) {
            case 'equals':
                return $source_value == $condition['value'];
            
            case 'not_equals':
                return $source_value != $condition['value'];
            
            case 'contains':
                return is_string($source_value) && 
                       strpos($source_value, $condition['value']) !== false;
            
            case 'not_contains':
                return is_string($source_value) && 
                       strpos($source_value, $condition['value']) === false;
            
            case 'greater_than':
                return is_numeric($source_value) && 
                       floatval($source_value) > floatval($condition['value']);
            
            case 'less_than':
                return is_numeric($source_value) && 
                       floatval($source_value) < floatval($condition['value']);
            
            case 'is_empty':
                return empty($source_value);
            
            case 'is_not_empty':
                return !empty($source_value);
            
            case 'matches_regex':
                return is_string($source_value) && 
                       preg_match('/' . $condition['value'] . '/', $source_value);
            
            default:
                return false;
        }
    }
} 