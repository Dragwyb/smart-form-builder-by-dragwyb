<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Validation {
    private $errors = [];
    private $messages = [];
    private $conditional_validator;
    private $error_templates;
    private $groups;
    private $cache;
    private $dependencies;
    private $optimizer;

    public function __construct() {
        $this->init_validation_messages();
        $this->conditional_validator = new Dragwyb_Form_Builder_Conditional_Validation();
        $this->error_templates = new Dragwyb_Form_Builder_Error_Templates();
        $this->groups = new Dragwyb_Form_Builder_Validation_Groups();
        $this->cache = new Dragwyb_Form_Builder_Validation_Cache();
        $this->dependencies = new Dragwyb_Form_Builder_Validation_Dependencies();
        $this->optimizer = new Dragwyb_Form_Builder_Validation_Optimizer();
    }

    /**
     * Initialize default validation messages
     */
    private function init_validation_messages(): void {
        $this->messages = [
            'required' => __('This field is required.', 'dragwyb-form-builder'),
            'email' => __('Please enter a valid email address.', 'dragwyb-form-builder'),
            'number' => __('Please enter a valid number.', 'dragwyb-form-builder'),
            'url' => __('Please enter a valid URL.', 'dragwyb-form-builder'),
            'tel' => __('Please enter a valid phone number.', 'dragwyb-form-builder'),
            'minlength' => __('Please enter at least {0} characters.', 'dragwyb-form-builder'),
            'maxlength' => __('Please enter no more than {0} characters.', 'dragwyb-form-builder'),
            'min' => __('Please enter a value greater than or equal to {0}.', 'dragwyb-form-builder'),
            'max' => __('Please enter a value less than or equal to {0}.', 'dragwyb-form-builder'),
            'pattern' => __('Please match the requested format.', 'dragwyb-form-builder'),
            'file_size' => __('File size must not exceed {0}MB.', 'dragwyb-form-builder'),
            'file_type' => __('File type not allowed. Allowed types: {0}', 'dragwyb-form-builder'),
        ];
    }

    /**
     * Validate form fields
     */
    public function validate_fields(array $fields): array {
        return $this->optimizer->process_batch($fields);
    }

    /**
     * Validate individual field
     */
    public function validate_field(array $field, $value): ?string {
        // Check cache first
        $cache_key = $this->cache->generate_cache_key($field, $value);
        $cached_result = $this->cache->get_cached_result($cache_key);
        
        if ($cached_result !== null) {
            return $cached_result['error'] ?? null;
        }

        // Get active validation rules and optimize their order
        $active_rules = $this->groups->get_active_rules();
        $optimized_rules = $this->optimizer->optimize_rules($active_rules);

        // Filter out rules with unmet dependencies
        $valid_rules = array_filter($optimized_rules, function($rule) use ($active_rules) {
            return $this->dependencies->are_dependencies_met($rule, $active_rules);
        });

        // Perform validation
        $result = $this->validate_with_rules($field, $value, $valid_rules);

        // Record validation result for optimization
        foreach ($valid_rules as $rule) {
            $this->optimizer->record_validation($rule, $result['valid']);
        }

        // Cache the result
        $this->cache->cache_result($cache_key, $result);

        return $result['error'] ?? null;
    }

    private function validate_with_rules(array $field, $value, array $active_rules): array {
        $result = ['valid' => true];

        foreach ($active_rules as $rule) {
            if (!$this->should_apply_rule($rule, $field)) {
                continue;
            }

            $validation_result = $this->apply_rule($rule, $field, $value);
            if (!$validation_result['valid']) {
                $result = $validation_result;
                break;
            }
        }

        return $result;
    }

    private function should_apply_rule(string $rule, array $field): bool {
        // Skip rules that don't apply to the field type
        $rule_field_types = $this->get_rule_field_types($rule);
        return empty($rule_field_types) || in_array($field['type'], $rule_field_types);
    }

    private function get_rule_field_types(string $rule): array {
        $rule_types = [
            'email' => ['email'],
            'number' => ['number'],
            'url' => ['url'],
            'file_size' => ['file'],
            'file_type' => ['file'],
            'password_strength' => ['password'],
            'word_count' => ['textarea', 'text'],
        ];

        return $rule_types[$rule] ?? [];
    }

    private function apply_rule(string $rule, array $field, $value): array {
        switch ($rule) {
            case 'required':
                return $this->validate_required($field, $value);

            case 'email':
                return $this->validate_email($value);

            case 'number':
                return $this->validate_number($field, $value);

            case 'url':
                return $this->validate_url($value);

            case 'password_strength':
                return $this->validate_password_strength($field, $value);

            case 'word_count':
                return $this->validate_word_count($field, $value);

            case 'file_size':
                return $this->validate_file_size($field, $value);

            case 'file_type':
                return $this->validate_file_type($field, $value);

            case 'custom_regex':
                return $this->validate_custom_regex($field, $value);

            case 'unique_value':
                return $this->validate_unique_value($field, $value);

            default:
                return ['valid' => true];
        }
    }

    private function validate_required(array $field, $value): array {
        if (empty($field['required'])) {
            return ['valid' => true];
        }

        return [
            'valid' => !$this->is_empty_value($value),
            'error' => $this->error_templates->get_message('required'),
        ];
    }

    private function validate_password_strength(array $field, string $value): array {
        $min_length = $field['password_min_length'] ?? 8;
        $require_uppercase = $field['password_require_uppercase'] ?? true;
        $require_number = $field['password_require_number'] ?? true;
        $require_special = $field['password_require_special'] ?? true;

        $errors = [];

        if (strlen($value) < $min_length) {
            $errors[] = sprintf(
                $this->error_templates->get_message('password_min_length'),
                $min_length
            );
        }

        if ($require_uppercase && !preg_match('/[A-Z]/', $value)) {
            $errors[] = $this->error_templates->get_message('password_uppercase');
        }

        if ($require_number && !preg_match('/[0-9]/', $value)) {
            $errors[] = $this->error_templates->get_message('password_number');
        }

        if ($require_special && !preg_match('/[^A-Za-z0-9]/', $value)) {
            $errors[] = $this->error_templates->get_message('password_special');
        }

        return [
            'valid' => empty($errors),
            'error' => implode(' ', $errors),
        ];
    }

    private function validate_word_count(array $field, string $value): array {
        $min_words = $field['min_words'] ?? null;
        $max_words = $field['max_words'] ?? null;
        $word_count = str_word_count($value);

        if ($min_words && $word_count < $min_words) {
            return [
                'valid' => false,
                'error' => sprintf(
                    $this->error_templates->get_message('min_words'),
                    $min_words
                ),
            ];
        }

        if ($max_words && $word_count > $max_words) {
            return [
                'valid' => false,
                'error' => sprintf(
                    $this->error_templates->get_message('max_words'),
                    $max_words
                ),
            ];
        }

        return ['valid' => true];
    }

    private function validate_email($value): array {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => $this->error_templates->get_message('email')];
        }
        return ['valid' => true];
    }

    private function validate_number($field, $value): array {
        if (!is_numeric($value)) {
            return ['valid' => false, 'error' => $this->error_templates->get_message('number')];
        }
        if (isset($field['min']) && $value < $field['min']) {
            return ['valid' => false, 'error' => $this->error_templates->get_message('min', ['min' => $field['min']])];
        }
        if (isset($field['max']) && $value > $field['max']) {
            return ['valid' => false, 'error' => $this->error_templates->get_message('max', ['max' => $field['max']])];
        }
        return ['valid' => true];
    }

    private function validate_url($value): array {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return ['valid' => false, 'error' => $this->error_templates->get_message('url')];
        }
        return ['valid' => true];
    }

    private function validate_file_size($field, $value): array {
        if (!isset($_FILES[$field['id']])) {
            return ['valid' => true];
        }

        $file = $_FILES[$field['id']];

        // Check file size
        $max_size = (!empty($field['max_size'])) ? $field['max_size'] * 1024 * 1024 : wp_max_upload_size();
        if ($file['size'] > $max_size) {
            return ['valid' => false, 'error' => $this->error_templates->get_message('file_size', [size_format($max_size)])];
        }

        return ['valid' => true];
    }

    private function validate_file_type($field, $value): array {
        if (!isset($_FILES[$field['id']])) {
            return ['valid' => true];
        }

        $file = $_FILES[$field['id']];

        // Check file type
        if (!empty($field['allowed_types'])) {
            $file_type = wp_check_filetype($file['name']);
            $allowed_types = array_map('trim', explode(',', $field['allowed_types']));
            
            if (!in_array($file_type['ext'], $allowed_types)) {
                return ['valid' => false, 'error' => $this->error_templates->get_message('file_type', [implode(', ', $allowed_types)])];
            }
        }

        return ['valid' => true];
    }

    private function validate_custom_regex($field, $value): array {
        if (!empty($field['pattern'])) {
            if (!preg_match("/$field['pattern']/", $value)) {
                return ['valid' => false, 'error' => $this->error_templates->get_message('pattern')];
            }
        }
        return ['valid' => true];
    }

    private function validate_unique_value($field, $value): array {
        // Implementation of unique_value validation
        return ['valid' => true];
    }

    /**
     * Check if value is empty
     */
    private function is_empty_value($value): bool {
        if (is_array($value)) {
            return empty($value);
        }
        return $value === null || trim($value) === '';
    }

    /**
     * Get validation errors
     */
    public function get_errors(): array {
        return $this->errors;
    }

    /**
     * Check if there are any errors
     */
    public function has_errors(): bool {
        return !empty($this->errors);
    }
} 
} 