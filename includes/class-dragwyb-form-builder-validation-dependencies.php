<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Validation_Dependencies {
    private $dependencies = [];
    private $rule_requirements = [];

    public function __construct() {
        $this->init_dependencies();
    }

    /**
     * Initialize default dependencies
     */
    private function init_dependencies(): void {
        $this->dependencies = [
            'password_strength' => ['required'],
            'word_count' => ['required'],
            'file_size' => ['file_type'],
            'mime_type' => ['file_type'],
            'unique_value' => ['required'],
            'date_range' => ['date_format'],
            'custom_regex' => ['required'],
        ];

        $this->rule_requirements = [
            'recaptcha' => [
                'options' => ['site_key', 'secret_key'],
                'functions' => ['curl_init'],
            ],
            'password_strength' => [
                'php_version' => '7.4',
            ],
            'file_type' => [
                'php_extensions' => ['fileinfo'],
            ],
        ];
    }

    /**
     * Check if all dependencies for a rule are met
     */
    public function are_dependencies_met(string $rule, array $active_rules): bool {
        if (!isset($this->dependencies[$rule])) {
            return true;
        }

        foreach ($this->dependencies[$rule] as $dependency) {
            if (!in_array($dependency, $active_rules)) {
                return false;
            }
        }

        return $this->check_requirements($rule);
    }

    /**
     * Check if system requirements are met for a rule
     */
    private function check_requirements(string $rule): bool {
        if (!isset($this->rule_requirements[$rule])) {
            return true;
        }

        $requirements = $this->rule_requirements[$rule];

        // Check PHP version
        if (isset($requirements['php_version'])) {
            if (version_compare(PHP_VERSION, $requirements['php_version'], '<')) {
                return false;
            }
        }

        // Check PHP extensions
        if (isset($requirements['php_extensions'])) {
            foreach ($requirements['php_extensions'] as $extension) {
                if (!extension_loaded($extension)) {
                    return false;
                }
            }
        }

        // Check required functions
        if (isset($requirements['functions'])) {
            foreach ($requirements['functions'] as $function) {
                if (!function_exists($function)) {
                    return false;
                }
            }
        }

        // Check required options
        if (isset($requirements['options'])) {
            foreach ($requirements['options'] as $option) {
                if (!get_option("dragwyb_{$option}")) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get all dependencies for a rule
     */
    public function get_dependencies(string $rule): array {
        return $this->dependencies[$rule] ?? [];
    }

    /**
     * Add custom dependency
     */
    public function add_dependency(string $rule, string $dependency): void {
        if (!isset($this->dependencies[$rule])) {
            $this->dependencies[$rule] = [];
        }
        $this->dependencies[$rule][] = $dependency;
    }
} 