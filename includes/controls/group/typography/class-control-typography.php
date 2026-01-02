<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Typography;

use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Controls\Fonts\Fonts_Helper;

class Control_Typography extends Control_Base
{
    private string $id = '';
    private array $data = [];
    private string $icon = '';

    protected function init(): void
    {
        $this->type = 'typography';
        $this->name = __('Typography', 'dragwyb-form-builder');
        $this->icon = 'fas fa-pen';
    }

    public function get_icon(): string
    {
        return $this->icon;
    }

    protected function register_settings(): array
    {
        return [
            'name'       => 'string',
            'label'      => 'string',
            'settings'   => 'custom' // Custom array structure
        ];
    }

    /**
     * Define default configuration for all sub-controls
     */
    protected function default_setting(): array
    {
        $units_global = ['px', '%', 'em', 'rem', 'vh', 'vw'];

        $range_size = [
            'px'  => ['min' => 0, 'max' => 200, 'step' => 1],
            '%'   => ['min' => 0, 'max' => 200, 'step' => 1],
            'em'  => ['min' => 0, 'max' => 20,  'step' => 0.1],
            'rem' => ['min' => 0, 'max' => 20,  'step' => 0.1],
            'vh'  => ['min' => 0, 'max' => 100, 'step' => 1],
            'vw'  => ['min' => 0, 'max' => 100, 'step' => 1],
        ];

        $range_spacing = [
            'px'  => ['min' => -50, 'max' => 100, 'step' => 0.1],
            'em'  => ['min' => -5,  'max' => 10,  'step' => 0.01],
            'rem' => ['min' => -5,  'max' => 10,  'step' => 0.01],
            '%'   => ['min' => 0,   'max' => 100, 'step' => 1],
        ];

        return [
            // --- Font Size ---
            'size' => [
                'units'   => $units_global,
                'range'   => $range_size,
                'default' => ['unit' => 'px', 'size' => 16],
            ],
            // --- Font Family Config ---
            'font' => [
                'family'        => [],
                'exclude_fonts' => [],
                'fonts_group'   => [],
            ],
            // --- Weight ---
            'weight' => [
                'options' => [
                    'default' => __('Default', 'dragwyb-form-builder'),
                    'normal'  => __('Normal', 'dragwyb-form-builder'),
                    'bold'    => __('Bold', 'dragwyb-form-builder'),
                    '100'     => '100',
                    '200'     => '200',
                    '300'     => '300',
                    '400'     => '400',
                    '500'     => '500',
                    '600'     => '600',
                    '700'     => '700',
                    '800'     => '800',
                    '900'     => '900',
                ],
                'default' => 'default',
            ],
            // --- Transform ---
            'transform' => [
                'options' => [
                    ''           => __('Default', 'dragwyb-form-builder'),
                    'none'       => __('None', 'dragwyb-form-builder'),
                    'uppercase'  => __('Uppercase', 'dragwyb-form-builder'),
                    'lowercase'  => __('Lowercase', 'dragwyb-form-builder'),
                    'capitalize' => __('Capitalize', 'dragwyb-form-builder'),
                ],
                'default' => '',
            ],
            // --- Style ---
            'style' => [
                'options' => [
                    ''        => __('Default', 'dragwyb-form-builder'),
                    'normal'  => __('Normal', 'dragwyb-form-builder'),
                    'italic'  => __('Italic', 'dragwyb-form-builder'),
                    'oblique' => __('Oblique', 'dragwyb-form-builder'),
                ],
                'default' => '',
            ],
            // --- Decoration ---
            'decoration' => [
                'options' => [
                    ''             => __('Default', 'dragwyb-form-builder'),
                    'none'         => __('None', 'dragwyb-form-builder'),
                    'underline'    => __('Underline', 'dragwyb-form-builder'),
                    'overline'     => __('Overline', 'dragwyb-form-builder'),
                    'line-through' => __('Line Through', 'dragwyb-form-builder'),
                ],
                'default' => '',
            ],
            // --- Sliders ---
            'line_height' => [
                'units'   => $units_global,
                'range'   => $range_size,
                'default' => ['unit' => 'em', 'size' => 1.5],
            ],
            'letter_spacing' => [
                'units'   => $units_global,
                'range'   => $range_spacing,
                'default' => ['unit' => 'px', 'size' => 0],
            ],
            'word_spacing' => [
                'units'   => $units_global,
                'range'   => $range_spacing,
                'default' => ['unit' => 'px', 'size' => 0],
            ],
            // --- Alignment ---
            'alignment' => [
                'options' => [
                    'left' => [
                        'title' => __('Left', 'dragwyb-form-builder'),
                        'icon'  => 'fa fa-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'dragwyb-form-builder'),
                        'icon'  => 'fa fa-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'dragwyb-form-builder'),
                        'icon'  => 'fa fa-align-right',
                    ],
                ],
                'default' => '',
            ],
            'selector' => ''
        ];
    }

    // --- Sanitization Methods ---

    /**
     * Entry point for sanitizing the control settings array.
     */
    protected function settings_setting_sanitize($value)
    {
        return $this->sanitize_control($value);
    }

    protected function sanitize_control($value)
    {
        if (!is_array($value)) return [];

        $sanitized = [];

        // 1. Responsive Sliders (Size, Line Height, Spacing)
        $slider_keys = ['size', 'line_height', 'letter_spacing', 'word_spacing'];
        foreach ($slider_keys as $key) {
            if (isset($value[$key])) {
                $sanitized[$key] = $this->sanitize_responsive_slider($value[$key]);
            }
        }

        // 2. Selects (Weight, Transform, Style, etc.)
        $select_keys = ['weight', 'transform', 'style', 'decoration'];
        foreach ($select_keys as $key) {
            if (isset($value[$key])) {
                $sanitized[$key] = $this->sanitize_select_options($value[$key]);
            }
        }

        // 3. Font Family Group (Nested arrays inside 'font')
        if (isset($value['font']) && is_array($value['font'])) {
            $font_keys = ['exclude_fonts', 'fonts_group', 'family'];
            foreach ($font_keys as $f_key) {
                if (isset($value['font'][$f_key])) {
                    $sanitized['font'][$f_key] = $this->sanitize_array_list($value['font'][$f_key]);
                }
            }
        }

        // 4. Alignment
        if (isset($value['alignment'])) {
            $sanitized['alignment'] = $this->sanitize_choose_options($value['alignment']);
        }

        return $sanitized;
    }

    protected function sanitize_choose_options($setting)
    {
        if (!is_array($setting)) return [];
        $clean = [];

        // Sanitize Options Array
        if (isset($setting['options']) && is_array($setting['options'])) {
            foreach ($setting['options'] as $o => $l) {
                $clean['options'][$this->string_sanitize($o)] = [
                    'title' => $this->string_sanitize($l['title'] ?? $o),
                    'icon'  => $this->string_sanitize($l['icon'] ?? ''),
                ];
            }
        }

        // Sanitize Default
        if (isset($setting['default'])) {
            $clean['default'] = $this->string_sanitize($setting['default']);
        }

        return $clean;
    }

    protected function sanitize_responsive_slider($setting)
    {
        if (!is_array($setting)) return [];
        $clean = [];

        // Sanitize Units
        if (isset($setting['units'])) {
            $clean['units'] = $this->sanitize_array_list($setting['units']);
        }

        // Sanitize Range Config (min/max/step)
        if (isset($setting['range']) && is_array($setting['range'])) {
            foreach ($setting['range'] as $u => $l) {
                $clean['range'][$this->string_sanitize($u)] = [
                    'min'  => $this->number_sanitize($l['min'] ?? 0),
                    'max'  => $this->number_sanitize($l['max'] ?? 100),
                    'step' => $this->number_sanitize($l['step'] ?? 1),
                ];
            }
        }

        // Sanitize Defaults
        if (isset($setting['default'])) {
            $clean['default'] = [
                'unit' => $this->string_sanitize($setting['default']['unit'] ?? 'px'),
                'size' => $this->number_sanitize($setting['default']['size'] ?? 0),
            ];
        }

        return $clean;
    }

    protected function sanitize_select_options($setting)
    {
        if (!is_array($setting)) return [];
        $clean = [];

        // Sanitize Options Array
        if (isset($setting['options']) && is_array($setting['options'])) {
            foreach ($setting['options'] as $k => $v) {
                $clean['options'][$this->string_sanitize((string)$k)] = $this->string_sanitize($v);
            }
        }

        // Sanitize Default Value
        if (isset($setting['default'])) {
            $clean['default'] = $this->string_sanitize($setting['default']);
        }

        return $clean;
    }

    protected function sanitize_array_list($list)
    {
        if (!is_array($list)) return [];
        return array_map([$this, 'string_sanitize'], $list);
    }

    // --- Core Sanitization Helpers ---

    protected function string_sanitize($val)
    {
        return sanitize_text_field($val);
    }

    /**
     * Smartly cast values to int or float based on content.
     * Prevents errors when "15.5" (string) is passed to strict float types.
     * @param mixed $value
     * @return int|float
     */
    protected function number_sanitize($value)
    {
        if (!is_numeric($value)) {
            return 0;
        }
        // Adding zero forces PHP to cast to int if whole, or float if decimal exists
        return $value + 0;
    }

    // --- Control Registration ---

    final public function register_controls(string $id, array $data = []): void
    {
        $this->id = $this->string_sanitize($id);
        $this->data = $data;
    }

    /**
     * Merge defaults with user data, supporting the 'settings' nesting
     */
    private function get_display_settings(): array
    {
        $defaults = $this->default_setting();

        // 1. Determine where the overrides are coming from
        // If 'settings' key exists and is an array, use it. Otherwise use root data.
        $user_data = isset($this->data['settings']) && is_array($this->data['settings'])
            ? $this->data['settings']
            : $this->data;


        // 2. Security: Only allow keys that exist in our defaults
        $valid_user_data = array_intersect_key($user_data, $defaults);

        foreach ($valid_user_data as $key => $value) {
            if (is_array($value) && in_array($key, ['range', 'units'])) {
                $defaults[$key] = $user_data[$key];
            } else if (is_array($value)) {
                $this->merge_array_settings($value, $user_data[$key], $defaults[$key]);
            } else {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }

    private function merge_array_settings($data, $user_data, &$defaults)
    {
        foreach ($data as $key => $value) {
            if (is_array($value) && in_array($key, ['range', 'units', 'options'])) {
                $defaults[$key] = $user_data[$key];
            } else if (is_array($value)) {
                $this->merge_array_settings($value, $user_data[$key], $defaults[$key]);
            } else {
                $defaults[$key] = $value;
            }
        }
    }

    final public function get_controls(): array
    {
        $settings = $this->get_display_settings();
        $id = $this->string_sanitize($this->id);
        $selector = isset($settings['selector']) && !empty($settings['selector']) ? $settings['selector'] : false;
        $controls = [];

        $selecotrs = [
            'family' => array('properties' => 'family', 'placeholder' => '{{VALUE}}'),
            'size' => array('properties' => 'font-size', 'placeholder' => '{{VALUE}}{{UNIT}}'),
            'weight' => array('properties' => 'font-weight', 'placeholder' => '{{VALUE}}'),
            'transform' => array('properties' => 'text-transform', 'placeholder' => '{{VALUE}}'),
            'style' => array('properties' => 'font-style', 'placeholder' => '{{VALUE}}'),
            'decoration' => array('properties' => 'text-decoration', 'placeholder' => '{{VALUE}}'),
            'line_height' => array('properties' => 'line-height', 'placeholder' => '{{VALUE}}{{UNIT}}'),
            'letter_spacing' => array('properties' => 'letter-spacing', 'placeholder' => '{{VALUE}}{{UNIT}}'),
            'word_spacing' => array('properties' => 'word-spacing', 'placeholder' => '{{VALUE}}{{UNIT}}'),
            'align' => array('properties' => 'text-align', 'placeholder' => '{{VALUE}}'),
        ];

        // 1. Font Family Control
        $font_control_args = [
            'type'    => Controls::FONTS,
            'label'   => __('Typography Family', 'dragwyb-form-builder'),
            'default' => 'Default',
        ];

        // Access nested 'font' settings safely
        if (!empty($settings['font']['exclude_fonts'])) $font_control_args['exclude_fonts'] = $settings['font']['exclude_fonts'];
        if (!empty($settings['font']['fonts_group']))   $font_control_args['groups']        = $settings['font']['fonts_group'];
        if (!empty($settings['conditions']))            $font_control_args['conditions']    = $settings['conditions'];

        $controls[$id . '_family'] = $font_control_args;

        // 2. Control Map
        $map = [
            'size'           => ['type' => Controls::SLIDER, 'label' => __('Font Size', 'dragwyb-form-builder')],
            'weight'         => ['type' => Controls::SELECT, 'label' => __('Weight', 'dragwyb-form-builder'), 'label_inline' => true],
            'transform'      => ['type' => Controls::SELECT, 'label' => __('Transform', 'dragwyb-form-builder'), 'label_inline' => true],
            'style'          => ['type' => Controls::SELECT, 'label' => __('Style', 'dragwyb-form-builder'), 'label_inline' => true],
            'decoration'     => ['type' => Controls::SELECT, 'label' => __('Decoration', 'dragwyb-form-builder'), 'label_inline' => true],
            'line_height'    => ['type' => Controls::SLIDER, 'label' => __('Line Height', 'dragwyb-form-builder')],
            'letter_spacing' => ['type' => Controls::SLIDER, 'label' => __('Letter Spacing', 'dragwyb-form-builder')],
            'word_spacing'   => ['type' => Controls::SLIDER, 'label' => __('Word Spacing', 'dragwyb-form-builder')],
            'alignment'      => ['type' => Controls::CHOOSE, 'label' => __('Alignment', 'dragwyb-form-builder'), 'label_inline' => true],
        ];

        // 3. Generate Controls
        foreach ($map as $key => $meta) {
            if (!isset($settings[$key])) continue;

            $config = $settings[$key];

            $control_args = [
                'type'    => $meta['type'],
                'label'   => $meta['label'],
                'default' => $config['default'] ?? '',
            ];

            if (isset($meta['label_inline'])) {
                $control_args['label_inline'] = $meta['label_inline'];
            }

            if ($meta['type'] === Controls::SLIDER) {
                $control_args['range'] = $config['range'] ?? [];
                $control_args['units'] = $config['units'] ?? ['px'];
            } elseif ($meta['type'] === Controls::SELECT) {
                $control_args['options'] = $config['options'] ?? [];
            } elseif ($meta['type'] === Controls::CHOOSE) {
                $control_args['options'] = $config['options'] ?? [];
            }

            $controls[$id . '_' . $key] = $control_args;
        }

        if ($selector) {
            foreach ($selecotrs as $key => $style) {
                if (isset($controls[$id . '_' . $key])) {
                    $controls[$id . '_' . $key]['selectors'] = [
                        $selector => $style['properties'] . ':' . $style['placeholder'],
                    ];
                }
            }
        }

        return $controls;
    }
}
