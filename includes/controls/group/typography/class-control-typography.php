<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Typography;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Controls\Group\Group_Control_Base;

class Control_Typography extends Group_Control_Base
{
    protected function init(): void
    {
        $this->type = 'typography';
        $this->name = __('Typography', 'dragwyb-form-builder');
        $this->icon = 'fas fa-pen';
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

    protected function valid_default_settings(): array
    {
        return [
            'font',
            'size',
            'weight',
            'transform',
            'style',
            'decoration',
            'line_height',
            'letter_spacing',
            'word_spacing',
            'alignment',
            'selector',
            'conditions',
            'prefix'
        ];
    }

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



    /**
     * Merge defaults with user data, supporting the 'settings' nesting
     */
    private function get_display_settings(): array
    {
        $defaults = $this->default_setting();
        $valid_settings = $this->valid_default_settings();

        // 1. Determine where the overrides are coming from
        // If 'settings' key exists and is an array, use it. Otherwise use root data.
        $user_data = isset($this->data['settings']) && is_array($this->data['settings'])
            ? $this->data['settings']
            : $this->data;


        // 2. Security: Only allow keys that exist in our defaults
        foreach ($valid_settings as $key) {
            if (!array_key_exists($key, $user_data)) {
                continue;
            }

            if (is_array($user_data[$key]) && in_array($key, ['range', 'units'])) {
                $defaults[$key] = $user_data[$key];
            } else if (is_array($user_data[$key])) {
                $this->merge_array_settings($user_data[$key], $user_data[$key], $defaults[$key]);
            } else {
                $defaults[$key] = $user_data[$key];
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

    protected function register_group_controls(): void
    {
        $settings = $this->get_display_settings();

        $id = $this->string_sanitize($this->id);
        $selector = isset($settings['selector']) && !empty($settings['selector']) ? $settings['selector'] : false;
        $prefix = isset($settings['prefix']) && !empty($settings['prefix']) ? $settings['prefix'] : 'form';

        $selectors = [
            'family' => array('--dragwyb-' . $prefix . '-typography-family' => '{{VALUE}}'),
            'size' => array('--dragwyb-' . $prefix . '-typography-size' => '{{VALUE}}{{UNIT}}'),
            'weight' => array('--dragwyb-' . $prefix . '-typography-wt' => '{{VALUE}}'),
            'transform' => array('--dragwyb-' . $prefix . '-typography-ts' => '{{VALUE}}'),
            'style' => array('--dragwyb-' . $prefix . '-typography-st' => '{{VALUE}}'),
            'decoration' => array('--dragwyb-' . $prefix . '-typography-dt' => '{{VALUE}}'),
            'line_height' => array('--dragwyb-' . $prefix . '-typography-lh' => '{{VALUE}}{{UNIT}}'),
            'letter_spacing' => array('--dragwyb-' . $prefix . '-typography-ls' => '{{VALUE}}{{UNIT}}'),
            'word_spacing' => array('--dragwyb-' . $prefix . '-typography-ws' => '{{VALUE}}{{UNIT}}'),
            'alignment' => array('--dragwyb-' . $prefix . '-typography-align' => '{{VALUE}}'),
        ];

        // 2. Control Map
        $map = [
            'family'         => ['type' => Controls::FONTS, 'label' => __('Family', 'dragwyb-form-builder'), 'default' => 'Default'],
            'size'           => ['type' => Controls::SLIDER, 'label' => __('Font Size', 'dragwyb-form-builder'), 'range' => ['px' => ['min' => 0, 'max' => 100, 'step' => 1]], 'units' => ['px'], 'responsive' => true],
            'weight'         => ['type' => Controls::SELECT, 'label' => __('Weight', 'dragwyb-form-builder'), 'label_inline' => true],
            'transform'      => ['type' => Controls::SELECT, 'label' => __('Transform', 'dragwyb-form-builder'), 'label_inline' => true],
            'style'          => ['type' => Controls::SELECT, 'label' => __('Style', 'dragwyb-form-builder'), 'label_inline' => true],
            'decoration'     => ['type' => Controls::SELECT, 'label' => __('Decoration', 'dragwyb-form-builder'), 'label_inline' => true],
            'line_height'    => ['type' => Controls::SLIDER, 'label' => __('Line Height', 'dragwyb-form-builder'), 'range' => ['px' => ['min' => 0, 'max' => 100, 'step' => 1]], 'responsive' => true],
            'letter_spacing' => ['type' => Controls::SLIDER, 'label' => __('Letter Spacing', 'dragwyb-form-builder'), 'range' => ['px' => ['min' => 0, 'max' => 100, 'step' => 1]], 'units' => ['px'], 'responsive' => true],
            'word_spacing'   => ['type' => Controls::SLIDER, 'label' => __('Word Spacing', 'dragwyb-form-builder'), 'range' => ['px' => ['min' => 0, 'max' => 100, 'step' => 1]], 'units' => ['px'], 'responsive' => true],
            'alignment'      => ['type' => Controls::CHOOSE, 'label' => __('Alignment', 'dragwyb-form-builder'), 'label_inline' => true],
        ];

        // Access nested 'font' settings safely
        if (!empty($settings['font']['exclude_fonts'])) $map['family']['exclude_fonts'] = $settings['font']['exclude_fonts'];
        if (!empty($settings['font']['fonts_group']))   $map['family']['groups']        = $settings['font']['fonts_group'];

        // 3. Generate Controls
        foreach ($map as $key => $meta) {
            $config = $key === 'family' ? $settings['font'] : (isset($settings[$key]) ? $settings[$key] : array());

            $control_args = array_filter($meta, function ($key, $value) {
                return $key !== 'responsive';
            }, ARRAY_FILTER_USE_BOTH);

            if (isset($config['default'])) {
                $control_args['default'] = $config['default'];
            }

            if ($settings['conditions'] && !empty($settings['conditions'])) {
                $control_args['conditions'] = $settings['conditions'];
            }

            if ($meta['type'] === Controls::SLIDER) {
                if (isset($config['range'])) {
                    $control_args['range'] = $config['range'];
                }
                if (isset($config['units'])) {
                    $control_args['units'] = $config['units'];
                }
            } elseif ($meta['type'] === Controls::SELECT) {
                if (isset($config['options'])) {
                    $control_args['options'] = $config['options'];
                }
            } elseif ($meta['type'] === Controls::CHOOSE) {
                if (isset($config['options'])) {
                    $control_args['options'] = $config['options'];
                }
            }

            if (isset($selectors[$key]) && is_array($selectors[$key])) {
                $selector_style = '';
                foreach ($selectors[$key] as $selector_key => $selector_value) {
                    $selector_style .= $selector_key . ':' . $selector_value . ';';
                }

                if (!empty($selector_style)) {
                    $control_args['selectors'][$selector] = $selector_style;
                }
            }

            if (isset($meta['responsive']) && $meta['responsive']) {
                $this->add_responsive_control($id . '_' . $key, $control_args);
            } else {
                $this->add_control($id . '_' . $key, $control_args);
            }
        }
    }
}
