<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Validation_Rules {
    /**
     * Get advanced validation rules
     */
    public static function get_rules(): array {
        return [
            'password_strength' => [
                'name' => __('Password Strength', 'dragwyb-form-builder'),
                'callback' => [self::class, 'validate_password_strength'],
                'params' => [
                    'min_length' => [
                        'type' => 'number',
                        'label' => __('Minimum Length', 'dragwyb-form-builder'),
                        'default' => 8,
                    ],
                    'require_uppercase' => [
                        'type' => 'checkbox',
                        'label' => __('Require Uppercase', 'dragwyb-form-builder'),
                        'default' => true,
                    ],
                    'require_number' => [
                        'type' => 'checkbox',
                        'label' => __('Require Number', 'dragwyb-form-builder'),
                        'default' => true,
                    ],
                    'require_special' => [
                        'type' => 'checkbox',
                        'label' => __('Require Special Character', 'dragwyb-form-builder'),
                        'default' => true,
                    ],
                ],
            ],
            'date_range' => [
                'name' => __('Date Range', 'dragwyb-form-builder'),
                'callback' => [self::class, 'validate_date_range'],
                'params' => [
                    'min_date' => [
                        'type' => 'date',
                        'label' => __('Minimum Date', 'dragwyb-form-builder'),
                    ],
                    'max_date' => [
                        'type' => 'date',
                        'label' => __('Maximum Date', 'dragwyb-form-builder'),
                    ],
                ],
            ],
            'word_count' => [
                'name' => __('Word Count', 'dragwyb-form-builder'),
                'callback' => [self::class, 'validate_word_count'],
                'params' => [
                    'min_words' => [
                        'type' => 'number',
                        'label' => __('Minimum Words', 'dragwyb-form-builder'),
                    ],
                    'max_words' => [
                        'type' => 'number',
                        'label' => __('Maximum Words', 'dragwyb-form-builder'),
                    ],
                ],
            ],
            'unique_value' => [
                'name' => __('Unique Value', 'dragwyb-form-builder'),
                'callback' => [self::class, 'validate_unique_value'],
                'params' => [
                    'scope' => [
                        'type' => 'select',
                        'label' => __('Scope', 'dragwyb-form-builder'),
                        'options' => [
                            'form' => __('This Form', 'dragwyb-form-builder'),
                            'global' => __('All Forms', 'dragwyb-form-builder'),
                        ],
                    ],
                ],
            ],
            'custom_regex' => [
                'name' => __('Custom Regex', 'dragwyb-form-builder'),
                'callback' => [self::class, 'validate_regex'],
                'params' => [
                    'pattern' => [
                        'type' => 'text',
                        'label' => __('Regex Pattern', 'dragwyb-form-builder'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Validate password strength
     */
    public static function validate_password_strength($value, array $params): array {
        $errors = [];
        $min_length = $params['min_length'] ?? 8;

        if (strlen($value) < $min_length) {
            $errors[] = sprintf(
                __('Password must be at least %d characters long', 'dragwyb-form-builder'),
                $min_length
            );
        }

        if (!empty($params['require_uppercase']) && !preg_match('/[A-Z]/', $value)) {
            $errors[] = __('Password must contain at least one uppercase letter', 'dragwyb-form-builder');
        }

        if (!empty($params['require_number']) && !preg_match('/[0-9]/', $value)) {
            $errors[] = __('Password must contain at least one number', 'dragwyb-form-builder');
        }

        if (!empty($params['require_special']) && !preg_match('/[^A-Za-z0-9]/', $value)) {
            $errors[] = __('Password must contain at least one special character', 'dragwyb-form-builder');
        }

        return $errors;
    }

    /**
     * Validate date range
     */
    public static function validate_date_range($value, array $params): array {
        $errors = [];
        $date = strtotime($value);

        if ($date === false) {
            return [__('Invalid date format', 'dragwyb-form-builder')];
        }

        if (!empty($params['min_date'])) {
            $min_date = strtotime($params['min_date']);
            if ($date < $min_date) {
                $errors[] = sprintf(
                    __('Date must be after %s', 'dragwyb-form-builder'),
                    date_i18n(get_option('date_format'), $min_date)
                );
            }
        }

        if (!empty($params['max_date'])) {
            $max_date = strtotime($params['max_date']);
            if ($date > $max_date) {
                $errors[] = sprintf(
                    __('Date must be before %s', 'dragwyb-form-builder'),
                    date_i18n(get_option('date_format'), $max_date)
                );
            }
        }

        return $errors;
    }

    /**
     * Validate word count
     */
    public static function validate_word_count($value, array $params): array {
        $errors = [];
        $words = str_word_count($value);

        if (isset($params['min_words']) && $words < $params['min_words']) {
            $errors[] = sprintf(
                __('Text must contain at least %d words', 'dragwyb-form-builder'),
                $params['min_words']
            );
        }

        if (isset($params['max_words']) && $words > $params['max_words']) {
            $errors[] = sprintf(
                __('Text must contain no more than %d words', 'dragwyb-form-builder'),
                $params['max_words']
            );
        }

        return $errors;
    }

    /**
     * Validate unique value
     */
    public static function validate_unique_value($value, array $params): array {
        global $wpdb;

        $scope = $params['scope'] ?? 'form';
        $form_id = $params['form_id'] ?? 0;

        $query = $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}dragwyb_submissions 
            WHERE field_value = %s",
            $value
        );

        if ($scope === 'form') {
            $query .= $wpdb->prepare(" AND form_id = %d", $form_id);
        }

        $count = $wpdb->get_var($query);

        return $count > 0 ? 
            [__('This value has already been used', 'dragwyb-form-builder')] : 
            [];
    }

    /**
     * Validate custom regex
     */
    public static function validate_regex($value, array $params): array {
        if (empty($params['pattern'])) {
            return [];
        }

        return preg_match('/' . $params['pattern'] . '/', $value) ? 
            [] : 
            [__('Value does not match the required pattern', 'dragwyb-form-builder')];
    }
} 