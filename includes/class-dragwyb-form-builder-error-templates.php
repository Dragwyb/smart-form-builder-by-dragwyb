<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Error_Templates {
    private const TEMPLATE_OPTION = 'dragwyb_error_templates';

    /**
     * Get all error templates
     */
    public function get_templates(): array {
        return get_option(self::TEMPLATE_OPTION, $this->get_default_templates());
    }

    /**
     * Get default error templates
     */
    private function get_default_templates(): array {
        return [
            'required' => [
                'default' => __('This field is required.', 'dragwyb-form-builder'),
                'variables' => [],
            ],
            'email' => [
                'default' => __('Please enter a valid email address.', 'dragwyb-form-builder'),
                'variables' => [],
            ],
            'min_length' => [
                'default' => __('Please enter at least {min} characters.', 'dragwyb-form-builder'),
                'variables' => ['min'],
            ],
            'max_length' => [
                'default' => __('Please enter no more than {max} characters.', 'dragwyb-form-builder'),
                'variables' => ['max'],
            ],
            'pattern' => [
                'default' => __('Please match the requested format.', 'dragwyb-form-builder'),
                'variables' => ['pattern'],
            ],
            'number' => [
                'default' => __('Please enter a valid number.', 'dragwyb-form-builder'),
                'variables' => [],
            ],
            'min_value' => [
                'default' => __('Please enter a value greater than or equal to {min}.', 'dragwyb-form-builder'),
                'variables' => ['min'],
            ],
            'max_value' => [
                'default' => __('Please enter a value less than or equal to {max}.', 'dragwyb-form-builder'),
                'variables' => ['max'],
            ],
            'url' => [
                'default' => __('Please enter a valid URL.', 'dragwyb-form-builder'),
                'variables' => [],
            ],
            'tel' => [
                'default' => __('Please enter a valid phone number.', 'dragwyb-form-builder'),
                'variables' => [],
            ],
            'file_size' => [
                'default' => __('File size must not exceed {size}MB.', 'dragwyb-form-builder'),
                'variables' => ['size'],
            ],
            'file_type' => [
                'default' => __('File type not allowed. Allowed types: {types}', 'dragwyb-form-builder'),
                'variables' => ['types'],
            ],
            'custom' => [
                'default' => __('Invalid value.', 'dragwyb-form-builder'),
                'variables' => ['message'],
            ],
        ];
    }

    /**
     * Get error message with variables replaced
     */
    public function get_message(string $template_key, array $variables = []): string {
        $templates = $this->get_templates();
        
        if (!isset($templates[$template_key])) {
            return $templates['custom']['default'];
        }

        $message = $templates[$template_key]['default'];
        
        foreach ($variables as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $message;
    }

    /**
     * Save custom error template
     */
    public function save_template(string $key, string $message): bool {
        $templates = $this->get_templates();
        
        $templates[$key] = [
            'default' => $message,
            'variables' => $this->extract_variables($message),
        ];

        return update_option(self::TEMPLATE_OPTION, $templates);
    }

    /**
     * Extract variables from message template
     */
    private function extract_variables(string $message): array {
        preg_match_all('/{([^}]+)}/', $message, $matches);
        return $matches[1] ?? [];
    }
} 