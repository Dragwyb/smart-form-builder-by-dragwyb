<?php
declare(strict_types=1);

class Dragwyb_Form_Styler {
    private const STYLE_OPTION = 'dragwyb_form_styles';
    private const THEME_OPTION = 'dragwyb_form_themes';
    private $default_styles;
    private $current_theme;

    public function __construct() {
        $this->init_hooks();
        $this->init_default_styles();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks(): void {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
        add_action('wp_ajax_dragwyb_save_form_style', [$this, 'save_form_style']);
        add_action('wp_ajax_dragwyb_save_theme', [$this, 'save_theme']);
        add_filter('dragwyb_form_output', [$this, 'apply_styles'], 10, 2);
    }

    /**
     * Initialize default styles
     */
    private function init_default_styles(): void {
        $this->default_styles = [
            'form' => [
                'background' => '#ffffff',
                'padding' => '20px',
                'border_radius' => '4px',
                'border_color' => '#dee2e6',
                'border_width' => '1px',
                'border_style' => 'solid',
                'box_shadow' => '0 2px 4px rgba(0,0,0,0.1)',
                'max_width' => '600px',
                'margin' => '0 auto',
            ],
            'fields' => [
                'spacing' => '15px',
                'label_color' => '#495057',
                'label_font_size' => '14px',
                'label_font_weight' => '600',
                'label_margin' => '0 0 5px 0',
                'input_background' => '#ffffff',
                'input_color' => '#495057',
                'input_font_size' => '14px',
                'input_padding' => '8px 12px',
                'input_border_radius' => '4px',
                'input_border_color' => '#ced4da',
                'input_border_width' => '1px',
                'input_border_style' => 'solid',
                'input_box_shadow' => 'none',
                'input_focus_border_color' => '#80bdff',
                'input_focus_box_shadow' => '0 0 0 0.2rem rgba(0,123,255,0.25)',
                'placeholder_color' => '#6c757d',
            ],
            'buttons' => [
                'background' => '#007bff',
                'color' => '#ffffff',
                'font_size' => '14px',
                'font_weight' => '600',
                'padding' => '10px 20px',
                'border_radius' => '4px',
                'border_color' => '#007bff',
                'border_width' => '1px',
                'border_style' => 'solid',
                'hover_background' => '#0056b3',
                'hover_color' => '#ffffff',
                'hover_border_color' => '#0056b3',
                'active_background' => '#004085',
                'active_border_color' => '#004085',
            ],
            'validation' => [
                'error_color' => '#dc3545',
                'error_background' => '#f8d7da',
                'error_border_color' => '#f5c6cb',
                'error_padding' => '8px 12px',
                'error_border_radius' => '4px',
                'error_font_size' => '12px',
                'success_color' => '#28a745',
                'success_background' => '#d4edda',
                'success_border_color' => '#c3e6cb',
                'success_padding' => '8px 12px',
                'success_border_radius' => '4px',
                'success_font_size' => '12px',
            ],
            'responsive' => [
                'breakpoint_mobile' => '576px',
                'breakpoint_tablet' => '768px',
                'mobile_font_size' => '14px',
                'mobile_padding' => '15px',
                'mobile_button_padding' => '8px 16px',
            ],
            'animations' => [
                'transition_duration' => '0.3s',
                'transition_timing' => 'ease-in-out',
                'hover_transform' => 'translateY(-1px)',
                'active_transform' => 'translateY(1px)',
            ],
        ];
    }

    /**
     * Enqueue frontend styles
     */
    public function enqueue_styles(): void {
        wp_enqueue_style(
            'dragwyb-form-styles',
            DRAGWYB_URL . 'assets/css/dragwyb-form-styles.css',
            [],
            DRAGWYB_VERSION
        );

        // Add custom styles
        $custom_css = $this->generate_custom_css();
        wp_add_inline_style('dragwyb-form-styles', $custom_css);
    }

    /**
     * Enqueue admin styles
     */
    public function enqueue_admin_styles(string $hook): void {
        if (!in_array($hook, ['post.php', 'post-new.php'])) {
            return;
        }

        wp_enqueue_style(
            'dragwyb-style-editor',
            DRAGWYB_URL . 'assets/css/dragwyb-style-editor.css',
            [],
            DRAGWYB_VERSION
        );

        wp_enqueue_script(
            'dragwyb-style-editor',
            DRAGWYB_URL . 'assets/js/dragwyb-style-editor.js',
            ['jquery', 'wp-color-picker'],
            DRAGWYB_VERSION,
            true
        );

        wp_localize_script('dragwyb-style-editor', 'dragwybStyler', [
            'defaultStyles' => $this->default_styles,
            'themes' => $this->get_themes(),
            'nonce' => wp_create_nonce('dragwyb_style_editor'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
        ]);
    }

    /**
     * Save form style
     */
    public function save_form_style(): void {
        try {
            check_ajax_referer('dragwyb_style_editor');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $form_id = absint($_POST['form_id']);
            $styles = json_decode(stripslashes($_POST['styles']), true);

            if (!$form_id || !is_array($styles)) {
                throw new Exception(__('Invalid data.', 'dragwyb-form-builder'));
            }

            $sanitized_styles = $this->sanitize_styles($styles);
            update_post_meta($form_id, '_form_styles', $sanitized_styles);

            wp_send_json_success();

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Save theme
     */
    public function save_theme(): void {
        try {
            check_ajax_referer('dragwyb_style_editor');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $theme_data = json_decode(stripslashes($_POST['theme']), true);

            if (!is_array($theme_data) || empty($theme_data['name'])) {
                throw new Exception(__('Invalid theme data.', 'dragwyb-form-builder'));
            }

            $themes = get_option(self::THEME_OPTION, []);
            $themes[$theme_data['name']] = [
                'styles' => $this->sanitize_styles($theme_data['styles']),
                'created' => current_time('mysql'),
                'modified' => current_time('mysql'),
            ];

            update_option(self::THEME_OPTION, $themes);

            wp_send_json_success();

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Apply styles to form output
     */
    public function apply_styles(string $output, int $form_id): string {
        $styles = $this->get_form_styles($form_id);
        $custom_css = $this->generate_form_css($form_id, $styles);

        return sprintf(
            '<style>%s</style>%s',
            $custom_css,
            $output
        );
    }

    /**
     * Get form styles
     */
    public function get_form_styles(int $form_id): array {
        $styles = get_post_meta($form_id, '_form_styles', true);
        return wp_parse_args($styles, $this->default_styles);
    }

    /**
     * Get available themes
     */
    public function get_themes(): array {
        return get_option(self::THEME_OPTION, []);
    }

    /**
     * Generate custom CSS
     */
    private function generate_custom_css(): string {
        $css = '';
        $themes = $this->get_themes();

        foreach ($themes as $theme_name => $theme_data) {
            $css .= $this->generate_theme_css($theme_name, $theme_data['styles']);
        }

        return $css;
    }

    /**
     * Generate form-specific CSS
     */
    private function generate_form_css(int $form_id, array $styles): string {
        $selector = ".dragwyb-form[data-form-id=\"{$form_id}\"]";
        
        return "
            {$selector} {
                background: {$styles['form']['background']};
                padding: {$styles['form']['padding']};
                border-radius: {$styles['form']['border_radius']};
                border: {$styles['form']['border_width']} {$styles['form']['border_style']} {$styles['form']['border_color']};
                box-shadow: {$styles['form']['box_shadow']};
                max-width: {$styles['form']['max_width']};
                margin: {$styles['form']['margin']};
            }

            {$selector} .dragwyb-field {
                margin-bottom: {$styles['fields']['spacing']};
            }

            {$selector} .dragwyb-field label {
                color: {$styles['fields']['label_color']};
                font-size: {$styles['fields']['label_font_size']};
                font-weight: {$styles['fields']['label_font_weight']};
                margin: {$styles['fields']['label_margin']};
                display: block;
            }

            {$selector} .dragwyb-field input,
            {$selector} .dragwyb-field select,
            {$selector} .dragwyb-field textarea {
                background: {$styles['fields']['input_background']};
                color: {$styles['fields']['input_color']};
                font-size: {$styles['fields']['input_font_size']};
                padding: {$styles['fields']['input_padding']};
                border-radius: {$styles['fields']['input_border_radius']};
                border: {$styles['fields']['input_border_width']} {$styles['fields']['input_border_style']} {$styles['fields']['input_border_color']};
                box-shadow: {$styles['fields']['input_box_shadow']};
                width: 100%;
                transition: all {$styles['animations']['transition_duration']} {$styles['animations']['transition_timing']};
            }

            {$selector} .dragwyb-field input:focus,
            {$selector} .dragwyb-field select:focus,
            {$selector} .dragwyb-field textarea:focus {
                border-color: {$styles['fields']['input_focus_border_color']};
                box-shadow: {$styles['fields']['input_focus_box_shadow']};
                outline: none;
            }

            {$selector} .dragwyb-field input::placeholder,
            {$selector} .dragwyb-field select::placeholder,
            {$selector} .dragwyb-field textarea::placeholder {
                color: {$styles['fields']['placeholder_color']};
            }

            {$selector} .dragwyb-submit-button {
                background: {$styles['buttons']['background']};
                color: {$styles['buttons']['color']};
                font-size: {$styles['buttons']['font_size']};
                font-weight: {$styles['buttons']['font_weight']};
                padding: {$styles['buttons']['padding']};
                border-radius: {$styles['buttons']['border_radius']};
                border: {$styles['buttons']['border_width']} {$styles['buttons']['border_style']} {$styles['buttons']['border_color']};
                cursor: pointer;
                transition: all {$styles['animations']['transition_duration']} {$styles['animations']['transition_timing']};
            }

            {$selector} .dragwyb-submit-button:hover {
                background: {$styles['buttons']['hover_background']};
                color: {$styles['buttons']['hover_color']};
                border-color: {$styles['buttons']['hover_border_color']};
                transform: {$styles['animations']['hover_transform']};
            }

            {$selector} .dragwyb-submit-button:active {
                background: {$styles['buttons']['active_background']};
                border-color: {$styles['buttons']['active_border_color']};
                transform: {$styles['animations']['active_transform']};
            }

            {$selector} .dragwyb-error-message {
                color: {$styles['validation']['error_color']};
                background: {$styles['validation']['error_background']};
                border: 1px solid {$styles['validation']['error_border_color']};
                padding: {$styles['validation']['error_padding']};
                border-radius: {$styles['validation']['error_border_radius']};
                font-size: {$styles['validation']['error_font_size']};
                margin-top: 5px;
            }

            {$selector} .dragwyb-success-message {
                color: {$styles['validation']['success_color']};
                background: {$styles['validation']['success_background']};
                border: 1px solid {$styles['validation']['success_border_color']};
                padding: {$styles['validation']['success_padding']};
                border-radius: {$styles['validation']['success_border_radius']};
                font-size: {$styles['validation']['success_font_size']};
                margin-top: 5px;
            }

            @media (max-width: {$styles['responsive']['breakpoint_mobile']}) {
                {$selector} {
                    padding: {$styles['responsive']['mobile_padding']};
                }

                {$selector} .dragwyb-field input,
                {$selector} .dragwyb-field select,
                {$selector} .dragwyb-field textarea {
                    font-size: {$styles['responsive']['mobile_font_size']};
                }

                {$selector} .dragwyb-submit-button {
                    padding: {$styles['responsive']['mobile_button_padding']};
                }
            }
        ";
    }

    /**
     * Generate theme CSS
     */
    private function generate_theme_css(string $theme_name, array $styles): string {
        $selector = ".dragwyb-form[data-theme=\"{$theme_name}\"]";
        return $this->generate_form_css(0, $styles);
    }

    /**
     * Sanitize styles
     */
    private function sanitize_styles(array $styles): array {
        $sanitized = [];

        foreach ($styles as $section => $properties) {
            if (!is_array($properties)) {
                continue;
            }

            $sanitized[$section] = [];
            foreach ($properties as $property => $value) {
                $sanitized[$section][$property] = $this->sanitize_css_value($value);
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize CSS value
     */
    private function sanitize_css_value($value): string {
        if (is_array($value)) {
            return implode(' ', array_map([$this, 'sanitize_css_value'], $value));
        }

        // Remove potentially harmful content
        $value = wp_strip_all_tags($value);
        $value = str_replace(['javascript:', 'data:'], '', $value);

        // Allow specific CSS functions
        $allowed_functions = ['rgb', 'rgba', 'hsl', 'hsla', 'calc', 'var'];
        $pattern = '/[a-z-]+\s*\(/i';
        if (preg_match($pattern, $value, $matches)) {
            $function = str_replace('(', '', trim($matches[0]));
            if (!in_array($function, $allowed_functions)) {
                return '';
            }
        }

        return $value;
    }
} 