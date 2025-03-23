<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Styling {
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);
        add_action('dragwyb_form_settings', [$this, 'render_styling_settings']);
        add_filter('dragwyb_form_settings_save', [$this, 'save_styling_settings']);
    }

    /**
     * Enqueue admin assets for style preview
     */
    public function enqueue_admin_assets(): void {
        $screen = get_current_screen();
        if ($screen && $screen->base === 'post' && $screen->post_type === 'dragwyb_form') {
            // Enqueue WordPress color picker
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');

            // Enqueue our style preview script
            wp_enqueue_script(
                'dragwyb-style-preview',
                DRAGWYB_FORM_BUILDER_URL . 'assets/js/dragwyb-style-preview.js',
                ['jquery', 'wp-color-picker'],
                DRAGWYB_FORM_BUILDER_VERSION,
                true
            );

            // Enqueue admin styles
            wp_enqueue_style(
                'dragwyb-admin-styles',
                DRAGWYB_FORM_BUILDER_URL . 'assets/css/dragwyb-admin.css',
                [],
                DRAGWYB_FORM_BUILDER_VERSION
            );
        }
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets(): void {
        if (is_singular('dragwyb_form') || has_shortcode(get_the_content(), 'dragwyb_form')) {
            wp_enqueue_style(
                'dragwyb-form-styles',
                DRAGWYB_FORM_BUILDER_URL . 'assets/css/dragwyb-form.css',
                [],
                DRAGWYB_FORM_BUILDER_VERSION
            );
        }
    }

    public function get_style_settings(): array {
        return [
            'layout' => [
                'label' => __('Layout Settings', 'dragwyb-form-builder'),
                'settings' => [
                    'form_width' => [
                        'type' => 'text',
                        'label' => __('Form Width', 'dragwyb-form-builder'),
                        'default' => '100%',
                    ],
                    'field_margin' => [
                        'type' => 'text',
                        'label' => __('Field Margin', 'dragwyb-form-builder'),
                        'default' => '0 0 20px 0',
                    ],
                    'label_position' => [
                        'type' => 'select',
                        'label' => __('Label Position', 'dragwyb-form-builder'),
                        'options' => [
                            'top' => __('Top', 'dragwyb-form-builder'),
                            'left' => __('Left', 'dragwyb-form-builder'),
                            'right' => __('Right', 'dragwyb-form-builder'),
                        ],
                        'default' => 'top',
                    ],
                ],
            ],
            'typography' => [
                'label' => __('Typography', 'dragwyb-form-builder'),
                'settings' => [
                    'label_font_size' => [
                        'type' => 'text',
                        'label' => __('Label Font Size', 'dragwyb-form-builder'),
                        'default' => '14px',
                    ],
                    'input_font_size' => [
                        'type' => 'text',
                        'label' => __('Input Font Size', 'dragwyb-form-builder'),
                        'default' => '14px',
                    ],
                    'label_font_weight' => [
                        'type' => 'select',
                        'label' => __('Label Font Weight', 'dragwyb-form-builder'),
                        'options' => [
                            'normal' => __('Normal', 'dragwyb-form-builder'),
                            'bold' => __('Bold', 'dragwyb-form-builder'),
                        ],
                        'default' => 'normal',
                    ],
                ],
            ],
            'colors' => [
                'label' => __('Colors', 'dragwyb-form-builder'),
                'settings' => [
                    'label_color' => [
                        'type' => 'color',
                        'label' => __('Label Color', 'dragwyb-form-builder'),
                        'default' => '#333333',
                    ],
                    'input_color' => [
                        'type' => 'color',
                        'label' => __('Input Text Color', 'dragwyb-form-builder'),
                        'default' => '#333333',
                    ],
                    'input_background' => [
                        'type' => 'color',
                        'label' => __('Input Background', 'dragwyb-form-builder'),
                        'default' => '#ffffff',
                    ],
                    'input_border_color' => [
                        'type' => 'color',
                        'label' => __('Input Border Color', 'dragwyb-form-builder'),
                        'default' => '#dddddd',
                    ],
                    'button_color' => [
                        'type' => 'color',
                        'label' => __('Button Text Color', 'dragwyb-form-builder'),
                        'default' => '#ffffff',
                    ],
                    'button_background' => [
                        'type' => 'color',
                        'label' => __('Button Background', 'dragwyb-form-builder'),
                        'default' => '#2271b1',
                    ],
                ],
            ],
            'borders' => [
                'label' => __('Borders', 'dragwyb-form-builder'),
                'settings' => [
                    'input_border_width' => [
                        'type' => 'text',
                        'label' => __('Input Border Width', 'dragwyb-form-builder'),
                        'default' => '1px',
                    ],
                    'input_border_radius' => [
                        'type' => 'text',
                        'label' => __('Input Border Radius', 'dragwyb-form-builder'),
                        'default' => '4px',
                    ],
                    'button_border_radius' => [
                        'type' => 'text',
                        'label' => __('Button Border Radius', 'dragwyb-form-builder'),
                        'default' => '4px',
                    ],
                ],
            ],
        ];
    }

    private function get_responsive_settings(): array {
        return [
            'tablet' => [
                'form_width' => [
                    'label' => __('Form Width', 'dragwyb-form-builder'),
                    'default' => '100%',
                ],
                'form_padding' => [
                    'label' => __('Form Padding', 'dragwyb-form-builder'),
                    'default' => '15px',
                ],
                'label_font_size' => [
                    'label' => __('Label Font Size', 'dragwyb-form-builder'),
                    'default' => '14px',
                ],
                'input_font_size' => [
                    'label' => __('Input Font Size', 'dragwyb-form-builder'),
                    'default' => '14px',
                ],
                'input_padding' => [
                    'label' => __('Input Padding', 'dragwyb-form-builder'),
                    'default' => '8px',
                ],
            ],
            'mobile' => [
                'form_width' => [
                    'label' => __('Form Width', 'dragwyb-form-builder'),
                    'default' => '100%',
                ],
                'form_padding' => [
                    'label' => __('Form Padding', 'dragwyb-form-builder'),
                    'default' => '10px',
                ],
                'label_font_size' => [
                    'label' => __('Label Font Size', 'dragwyb-form-builder'),
                    'default' => '13px',
                ],
                'input_font_size' => [
                    'label' => __('Input Font Size', 'dragwyb-form-builder'),
                    'default' => '13px',
                ],
                'input_padding' => [
                    'label' => __('Input Padding', 'dragwyb-form-builder'),
                    'default' => '6px',
                ],
            ],
        ];
    }

    public function generate_css(array $settings): string {
        $css = parent::generate_css($settings);
        
        // Add custom CSS if present
        if (!empty($settings['custom_css'])) {
            $css .= "\n/* Custom CSS */\n";
            $css .= str_replace(
                '.dragwyb-form',
                '.dragwyb-form-' . $settings['form_id'],
                $settings['custom_css']
            );
        }
        
        return $css;
    }

    public function render_styling_settings($form_id): void {
        $settings = $this->get_style_settings();
        $responsive_settings = $this->get_responsive_settings();
        $saved_settings = get_post_meta($form_id, '_form_style_settings', true) ?: [];
        
        include DRAGWYB_FORM_BUILDER_PATH . 'templates/admin/style-settings.php';
    }

    public function save_styling_settings($form_id): void {
        if (isset($_POST['form_style_settings'])) {
            $settings = $this->sanitize_style_settings($_POST['form_style_settings']);
            update_post_meta($form_id, '_form_style_settings', $settings);
            
            // Generate and save CSS
            $css = $this->generate_css(array_merge($settings, ['form_id' => $form_id]));
            update_post_meta($form_id, '_form_custom_css', $css);
        }
    }

    private function sanitize_style_settings(array $settings): array {
        $sanitized = [];
        foreach ($settings as $key => $value) {
            switch ($key) {
                case 'form_width':
                case 'field_margin':
                case 'label_font_size':
                case 'input_font_size':
                case 'input_border_width':
                case 'input_border_radius':
                case 'button_border_radius':
                    $sanitized[$key] = sanitize_text_field($value);
                    break;
                    
                case 'label_color':
                case 'input_color':
                case 'input_background':
                case 'input_border_color':
                case 'button_color':
                case 'button_background':
                    $sanitized[$key] = sanitize_hex_color($value);
                    break;
                    
                case 'label_position':
                case 'label_font_weight':
                    $sanitized[$key] = sanitize_key($value);
                    break;
            }
        }
        return $sanitized;
    }
} 