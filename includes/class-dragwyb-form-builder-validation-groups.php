<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Validation_Groups {
    private const GROUP_OPTION = 'dragwyb_validation_groups';
    private $groups = [];
    private $active_groups = [];

    public function __construct() {
        $this->load_groups();
    }

    /**
     * Load validation groups
     */
    private function load_groups(): void {
        $this->groups = get_option(self::GROUP_OPTION, $this->get_default_groups());
    }

    /**
     * Get default validation groups
     */
    private function get_default_groups(): array {
        return [
            'basic' => [
                'name' => __('Basic Validation', 'dragwyb-form-builder'),
                'rules' => ['required', 'email', 'number', 'url'],
            ],
            'advanced' => [
                'name' => __('Advanced Validation', 'dragwyb-form-builder'),
                'rules' => ['pattern', 'custom_regex', 'word_count'],
            ],
            'security' => [
                'name' => __('Security Validation', 'dragwyb-form-builder'),
                'rules' => ['password_strength', 'unique_value', 'recaptcha'],
            ],
            'file' => [
                'name' => __('File Validation', 'dragwyb-form-builder'),
                'rules' => ['file_size', 'file_type', 'mime_type'],
            ],
            'custom' => [
                'name' => __('Custom Validation', 'dragwyb-form-builder'),
                'rules' => [],
            ],
        ];
    }

    /**
     * Set active validation groups
     */
    public function set_active_groups(array $groups): void {
        $this->active_groups = array_intersect_key($this->groups, array_flip($groups));
    }

    /**
     * Get active validation rules
     */
    public function get_active_rules(): array {
        $rules = [];
        foreach ($this->active_groups as $group) {
            $rules = array_merge($rules, $group['rules']);
        }
        return array_unique($rules);
    }

    /**
     * Add custom validation group
     */
    public function add_group(string $key, string $name, array $rules): bool {
        $this->groups[$key] = [
            'name' => $name,
            'rules' => $rules,
        ];
        return $this->save_groups();
    }

    /**
     * Save groups to database
     */
    private function save_groups(): bool {
        return update_option(self::GROUP_OPTION, $this->groups);
    }
} 