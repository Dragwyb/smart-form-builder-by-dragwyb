<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Style_Presets {
    private const PRESET_OPTION_KEY = 'dragwyb_style_presets';

    public function __construct() {
        add_action('admin_init', [$this, 'register_default_presets']);
    }

    /**
     * Get all available presets
     */
    public function get_presets(): array {
        $presets = get_option(self::PRESET_OPTION_KEY, []);
        return array_merge($this->get_default_presets(), $presets);
    }

    /**
     * Get default presets
     */
    private function get_default_presets(): array {
        return [
            'modern' => [
                'name' => __('Modern', 'dragwyb-form-builder'),
                'settings' => [
                    'form_width' => '100%',
                    'form_padding' => '30px',
                    'form_background' => '#ffffff',
                    'label_color' => '#333333',
                    'label_font_size' => '16px',
                    'label_font_weight' => '500',
                    'input_color' => '#333333',
                    'input_background' => '#f8f8f8',
                    'input_border_color' => '#e0e0e0',
                    'input_border_width' => '1px',
                    'input_border_radius' => '8px',
                    'button_color' => '#ffffff',
                    'button_background' => '#2271b1',
                    'button_border_radius' => '8px',
                ],
            ],
            'minimal' => [
                'name' => __('Minimal', 'dragwyb-form-builder'),
                'settings' => [
                    'form_width' => '100%',
                    'form_padding' => '20px',
                    'form_background' => 'transparent',
                    'label_color' => '#666666',
                    'label_font_size' => '14px',
                    'label_font_weight' => 'normal',
                    'input_color' => '#333333',
                    'input_background' => '#ffffff',
                    'input_border_color' => '#dddddd',
                    'input_border_width' => '1px',
                    'input_border_radius' => '4px',
                    'button_color' => '#ffffff',
                    'button_background' => '#333333',
                    'button_border_radius' => '4px',
                ],
            ],
            'classic' => [
                'name' => __('Classic', 'dragwyb-form-builder'),
                'settings' => [
                    'form_width' => '100%',
                    'form_padding' => '25px',
                    'form_background' => '#f5f5f5',
                    'label_color' => '#444444',
                    'label_font_size' => '15px',
                    'label_font_weight' => 'bold',
                    'input_color' => '#333333',
                    'input_background' => '#ffffff',
                    'input_border_color' => '#cccccc',
                    'input_border_width' => '1px',
                    'input_border_radius' => '0',
                    'button_color' => '#ffffff',
                    'button_background' => '#4CAF50',
                    'button_border_radius' => '0',
                ],
            ],
            'material' => [
                'name' => __('Material Design', 'dragwyb-form-builder'),
                'settings' => [
                    'form_width' => '100%',
                    'form_padding' => '24px',
                    'form_background' => '#ffffff',
                    'label_color' => '#666666',
                    'label_font_size' => '12px',
                    'label_font_weight' => '500',
                    'label_transform' => 'uppercase',
                    'input_color' => '#333333',
                    'input_background' => '#ffffff',
                    'input_border_color' => '#e0e0e0',
                    'input_border_width' => '0 0 2px 0',
                    'input_border_radius' => '0',
                    'input_padding' => '8px 0',
                    'input_box_shadow' => 'none',
                    'button_color' => '#ffffff',
                    'button_background' => '#6200ee',
                    'button_border_radius' => '4px',
                    'button_text_transform' => 'uppercase',
                    'button_font_weight' => '500',
                    'custom_css' => '
                        .dragwyb-form .dragwyb-field-wrapper {
                            position: relative;
                            margin-bottom: 28px;
                        }
                        .dragwyb-form .dragwyb-field-wrapper label {
                            position: absolute;
                            top: -18px;
                            left: 0;
                            transition: all 0.2s ease-in-out;
                        }
                        .dragwyb-form .dragwyb-field-wrapper input:focus,
                        .dragwyb-form .dragwyb-field-wrapper textarea:focus {
                            border-bottom-color: #6200ee;
                        }
                    '
                ],
            ],
            'neumorphic' => [
                'name' => __('Neumorphic', 'dragwyb-form-builder'),
                'settings' => [
                    'form_width' => '100%',
                    'form_padding' => '30px',
                    'form_background' => '#e0e5ec',
                    'label_color' => '#444444',
                    'label_font_size' => '14px',
                    'label_font_weight' => '600',
                    'input_color' => '#333333',
                    'input_background' => '#e0e5ec',
                    'input_border_color' => 'transparent',
                    'input_border_width' => '0',
                    'input_border_radius' => '10px',
                    'input_padding' => '15px',
                    'input_box_shadow' => '5px 5px 10px #b8bec7, -5px -5px 10px #ffffff',
                    'button_color' => '#444444',
                    'button_background' => '#e0e5ec',
                    'button_border_radius' => '10px',
                    'custom_css' => '
                        .dragwyb-form .dragwyb-field-wrapper input,
                        .dragwyb-form .dragwyb-field-wrapper textarea {
                            box-shadow: inset 2px 2px 5px #b8bec7, inset -3px -3px 7px #ffffff;
                        }
                        .dragwyb-form button[type="submit"] {
                            box-shadow: 5px 5px 10px #b8bec7, -5px -5px 10px #ffffff;
                            transition: all 0.2s ease-in-out;
                        }
                        .dragwyb-form button[type="submit"]:hover {
                            box-shadow: 3px 3px 6px #b8bec7, -3px -3px 6px #ffffff;
                        }
                    '
                ],
            ],
            'gradient' => [
                'name' => __('Gradient', 'dragwyb-form-builder'),
                'settings' => [
                    'form_width' => '100%',
                    'form_padding' => '30px',
                    'form_background' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                    'label_color' => '#ffffff',
                    'label_font_size' => '15px',
                    'label_font_weight' => '500',
                    'input_color' => '#333333',
                    'input_background' => 'rgba(255, 255, 255, 0.9)',
                    'input_border_color' => 'transparent',
                    'input_border_width' => '0',
                    'input_border_radius' => '8px',
                    'input_padding' => '12px',
                    'button_color' => '#ffffff',
                    'button_background' => 'rgba(255, 255, 255, 0.2)',
                    'button_border_radius' => '8px',
                    'custom_css' => '
                        .dragwyb-form {
                            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
                        }
                        .dragwyb-form button[type="submit"] {
                            backdrop-filter: blur(5px);
                            border: 1px solid rgba(255, 255, 255, 0.3);
                        }
                    '
                ],
            ],
            'dark-mode' => [
                'name' => __('Dark Mode', 'dragwyb-form-builder'),
                'settings' => [
                    'form_width' => '100%',
                    'form_padding' => '30px',
                    'form_background' => '#1a1a1a',
                    'label_color' => '#ffffff',
                    'label_font_size' => '14px',
                    'label_font_weight' => '500',
                    'input_color' => '#ffffff',
                    'input_background' => '#2d2d2d',
                    'input_border_color' => '#3d3d3d',
                    'input_border_width' => '1px',
                    'input_border_radius' => '6px',
                    'input_padding' => '12px',
                    'button_color' => '#ffffff',
                    'button_background' => '#0066cc',
                    'button_border_radius' => '6px',
                    'custom_css' => '
                        .dragwyb-form .dragwyb-field-wrapper input:focus,
                        .dragwyb-form .dragwyb-field-wrapper textarea:focus {
                            background: #333333;
                            border-color: #0066cc;
                        }
                        .dragwyb-form button[type="submit"]:hover {
                            background: #0052a3;
                        }
                    '
                ],
            ],
        ];
    }

    /**
     * Register default presets
     */
    public function register_default_presets(): void {
        if (!get_option(self::PRESET_OPTION_KEY)) {
            update_option(self::PRESET_OPTION_KEY, $this->get_default_presets());
        }
    }

    /**
     * Save custom preset
     */
    public function save_preset(string $name, array $settings): bool {
        $presets = get_option(self::PRESET_OPTION_KEY, []);
        $slug = sanitize_title($name);
        
        $presets[$slug] = [
            'name' => sanitize_text_field($name),
            'settings' => $this->sanitize_preset_settings($settings),
        ];

        return update_option(self::PRESET_OPTION_KEY, $presets);
    }

    /**
     * Delete custom preset
     */
    public function delete_preset(string $slug): bool {
        $presets = get_option(self::PRESET_OPTION_KEY, []);
        
        if (isset($presets[$slug])) {
            unset($presets[$slug]);
            return update_option(self::PRESET_OPTION_KEY, $presets);
        }

        return false;
    }

    /**
     * Sanitize preset settings
     */
    private function sanitize_preset_settings(array $settings): array {
        // Reuse the sanitization logic from the styling class
        $styling = new Dragwyb_Form_Builder_Styling();
        return $styling->sanitize_style_settings($settings);
    }
} 