<?php
declare(strict_types=1);

class Dragwyb_Conditional_Logic {
    private const RULES_META_KEY = '_dragwyb_conditional_rules';
    private $field_types;

    public function __construct() {
        $this->init_hooks();
        $this->init_field_types();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks(): void {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_filter('dragwyb_form_field_settings', [$this, 'add_conditional_settings']);
        add_action('wp_ajax_dragwyb_save_conditional_rules', [$this, 'save_conditional_rules']);
    }

    /**
     * Initialize supported field types
     */
    private function init_field_types(): void {
        $this->field_types = [
            'text' => [
                'operators' => ['equals', 'not_equals', 'contains', 'not_contains', 'starts_with', 'ends_with'],
            ],
            'textarea' => [
                'operators' => ['contains', 'not_contains', 'is_empty', 'is_not_empty'],
            ],
            'select' => [
                'operators' => ['equals', 'not_equals', 'is_empty', 'is_not_empty'],
            ],
            'radio' => [
                'operators' => ['equals', 'not_equals'],
            ],
            'checkbox' => [
                'operators' => ['checked', 'unchecked', 'contains'],
            ],
            'number' => [
                'operators' => ['equals', 'not_equals', 'greater_than', 'less_than', 'between'],
            ],
            'date' => [
                'operators' => ['equals', 'not_equals', 'before', 'after', 'between'],
            ],
            'email' => [
                'operators' => ['equals', 'not_equals', 'contains', 'ends_with'],
            ],
        ];
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_scripts(): void {
        wp_enqueue_script(
            'dragwyb-conditional-logic',
            DRAGWYB_URL . 'assets/js/dragwyb-conditional-logic.js',
            ['jquery'],
            DRAGWYB_VERSION,
            true
        );

        wp_localize_script('dragwyb-conditional-logic', 'dragwybConditional', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dragwyb_conditional_logic'),
        ]);
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts(string $hook): void {
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        wp_enqueue_script(
            'dragwyb-conditional-admin',
            DRAGWYB_URL . 'assets/js/dragwyb-conditional-admin.js',
            ['jquery', 'jquery-ui-sortable'],
            DRAGWYB_VERSION,
            true
        );

        wp_localize_script('dragwyb-conditional-admin', 'dragwybConditionalAdmin', [
            'fieldTypes' => $this->field_types,
            'i18n' => [
                'addRule' => __('Add Rule', 'dragwyb-form-builder'),
                'addGroup' => __('Add Rule Group', 'dragwyb-form-builder'),
                'remove' => __('Remove', 'dragwyb-form-builder'),
                'and' => __('AND', 'dragwyb-form-builder'),
                'or' => __('OR', 'dragwyb-form-builder'),
            ],
        ]);
    }

    /**
     * Add conditional logic settings to field settings
     */
    public function add_conditional_settings(array $settings): array {
        $settings['conditional_logic'] = [
            'label' => __('Conditional Logic', 'dragwyb-form-builder'),
            'type' => 'section',
            'fields' => [
                'enable_conditional' => [
                    'type' => 'checkbox',
                    'label' => __('Enable Conditional Logic', 'dragwyb-form-builder'),
                    'default' => false,
                ],
                'condition_action' => [
                    'type' => 'select',
                    'label' => __('Action', 'dragwyb-form-builder'),
                    'options' => [
                        'show' => __('Show this field', 'dragwyb-form-builder'),
                        'hide' => __('Hide this field', 'dragwyb-form-builder'),
                    ],
                    'default' => 'show',
                    'dependency' => [
                        'enable_conditional' => true,
                    ],
                ],
                'condition_logic' => [
                    'type' => 'rules_builder',
                    'dependency' => [
                        'enable_conditional' => true,
                    ],
                ],
            ],
        ];

        return $settings;
    }

    /**
     * Save conditional rules
     */
    public function save_conditional_rules(): void {
        try {
            check_ajax_referer('dragwyb_conditional_logic');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $form_id = absint($_POST['form_id']);
            $rules = json_decode(stripslashes($_POST['rules']), true);

            if (!$form_id || !is_array($rules)) {
                throw new Exception(__('Invalid data.', 'dragwyb-form-builder'));
            }

            $sanitized_rules = $this->sanitize_rules($rules);
            update_post_meta($form_id, self::RULES_META_KEY, $sanitized_rules);

            wp_send_json_success();

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get conditional rules for a form
     */
    public function get_form_rules(int $form_id): array {
        return get_post_meta($form_id, self::RULES_META_KEY, true) ?: [];
    }

    /**
     * Evaluate conditional rules for a field
     */
    public function evaluate_field_rules(array $rules, array $form_data): bool {
        if (empty($rules)) {
            return true;
        }

        return $this->evaluate_rule_groups($rules, $form_data);
    }

    /**
     * Evaluate rule groups (OR logic between groups)
     */
    private function evaluate_rule_groups(array $groups, array $form_data): bool {
        foreach ($groups as $group) {
            if ($this->evaluate_rule_group($group, $form_data)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Evaluate rules within a group (AND logic)
     */
    private function evaluate_rule_group(array $group, array $form_data): bool {
        foreach ($group as $rule) {
            if (!$this->evaluate_single_rule($rule, $form_data)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Evaluate a single rule
     */
    private function evaluate_single_rule(array $rule, array $form_data): bool {
        $field_value = $form_data[$rule['field']] ?? null;
        $compare_value = $rule['value'];

        switch ($rule['operator']) {
            case 'equals':
                return $field_value == $compare_value;

            case 'not_equals':
                return $field_value != $compare_value;

            case 'contains':
                return is_string($field_value) && 
                       stripos($field_value, $compare_value) !== false;

            case 'not_contains':
                return is_string($field_value) && 
                       stripos($field_value, $compare_value) === false;

            case 'starts_with':
                return is_string($field_value) && 
                       stripos($field_value, $compare_value) === 0;

            case 'ends_with':
                return is_string($field_value) && 
                       substr($field_value, -strlen($compare_value)) === $compare_value;

            case 'greater_than':
                return is_numeric($field_value) && 
                       floatval($field_value) > floatval($compare_value);

            case 'less_than':
                return is_numeric($field_value) && 
                       floatval($field_value) < floatval($compare_value);

            case 'between':
                if (!is_numeric($field_value) || !is_array($compare_value)) {
                    return false;
                }
                return floatval($field_value) >= floatval($compare_value[0]) && 
                       floatval($field_value) <= floatval($compare_value[1]);

            case 'checked':
                return !empty($field_value);

            case 'unchecked':
                return empty($field_value);

            case 'is_empty':
                return empty($field_value);

            case 'is_not_empty':
                return !empty($field_value);

            default:
                return false;
        }
    }

    /**
     * Sanitize conditional rules
     */
    private function sanitize_rules(array $rules): array {
        $sanitized = [];

        foreach ($rules as $group) {
            $sanitized_group = [];
            foreach ($group as $rule) {
                $sanitized_group[] = [
                    'field' => sanitize_text_field($rule['field']),
                    'operator' => sanitize_text_field($rule['operator']),
                    'value' => $this->sanitize_rule_value($rule['value']),
                ];
            }
            $sanitized[] = $sanitized_group;
        }

        return $sanitized;
    }

    /**
     * Sanitize rule value based on type
     */
    private function sanitize_rule_value($value) {
        if (is_array($value)) {
            return array_map('sanitize_text_field', $value);
        }
        return sanitize_text_field($value);
    }
} 