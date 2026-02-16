<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Border;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Controls\Group\Group_Control_Base;

class Control_Border extends Group_Control_Base
{
    protected function init(): void
    {
        $this->type = 'border';
        $this->name = __('Border', 'dragwyb-form-builder');
        $this->icon = 'fas fa-border-all';
    }

    protected function register_settings(): array
    {
        return [
            'name'       => 'string',
            'label'      => 'string',
            'settings'   => 'custom'
        ];
    }

    protected function default_setting(): array
    {
        return [
            'style' => [
                'options' => [
                    ''       => __('Default', 'dragwyb-form-builder'),
                    'none'   => __('None', 'dragwyb-form-builder'),
                    'solid'  => __('Solid', 'dragwyb-form-builder'),
                    'double' => __('Double', 'dragwyb-form-builder'),
                    'dotted' => __('Dotted', 'dragwyb-form-builder'),
                    'dashed' => __('Dashed', 'dragwyb-form-builder'),
                    'groove' => __('Groove', 'dragwyb-form-builder'),
                ],
                'default' => 'solid',
            ]
        ];
    }

    protected function valid_default_settings(): array
    {
        return [
            'style',
            'width',
            'radius',
            'color',
            'selector'
        ];
    }

    // --- Sanitization ---

    protected function settings_setting_sanitize($value)
    {
        return $this->sanitize_control($value);
    }

    protected function sanitize_control($value)
    {
        if (!is_array($value)) return [];
        $sanitized = [];

        // Style
        if (isset($value['style'])) {
            $sanitized['style'] = sanitize_text_field($value['style']);
        }

        // Color
        if (isset($value['color'])) {
            $sanitized['color'] = sanitize_hex_color($value['color']);
        }

        // Width & Radius (Dimensions)
        foreach (['width', 'radius'] as $key) {
            if (isset($value[$key]) && is_array($value[$key])) {
                $sanitized[$key] = [
                    'unit'     => sanitize_text_field($value[$key]['unit'] ?? 'px'),
                    'top'      => sanitize_text_field($value[$key]['top'] ?? ''),
                    'right'    => sanitize_text_field($value[$key]['right'] ?? ''),
                    'bottom'   => sanitize_text_field($value[$key]['bottom'] ?? ''),
                    'left'     => sanitize_text_field($value[$key]['left'] ?? ''),
                    'isLinked' => isset($value[$key]['isLinked']) ? (bool)$value[$key]['isLinked'] : true,
                ];
            }
        }

        // Selector
        if (isset($value['selector'])) {
            $sanitized['selector'] = sanitize_text_field($value['selector']);
        }

        return $sanitized;
    }

    protected function string_sanitize($val)
    {
        return sanitize_text_field($val);
    }

    private function get_display_settings(): array
    {
        $defaults = $this->default_setting();
        $valid_settings = $this->valid_default_settings();
        // Extract settings safely
        $user_data = isset($this->data['settings']) && is_array($this->data['settings'])
            ? $this->data['settings']
            : $this->data;

        // Ensure we handle the root selector if passed directly in data (common pattern)
        if (isset($this->data['selector']) && empty($user_data['selector'])) {
            $user_data['selector'] = $this->data['selector'];
        }

        $user_data = array_replace_recursive($defaults, $user_data);

        // Only valid keys from defaults
        $valid_user_data = [];

        foreach ($valid_settings as $key) {
            if (isset($user_data[$key])) {
                $valid_user_data[$key] = $user_data[$key];
            }
        }

        return $valid_user_data;
    }

    protected function register_group_controls(): void
    {
        $settings = $this->get_display_settings();
        $id = $this->string_sanitize($this->id);
        $selector = isset($settings['selector']) && !empty($settings['selector']) ? $settings['selector'] : false;

        // Definition of selectors map
        // Note: Dimensions (width/radius) use specific placeholders {{TOP}}, {{RIGHT}}, etc.
        $selectors = [
            'style'  => ['property' => '--dragwyb-form-border-style',  'placeholder' => '{{VALUE}}'],
            'color'  => ['property' => '--dragwyb-form-border-color',  'placeholder' => '{{VALUE}}'],
            'width'  => ['property' => '--dragwyb-form-border-width',  'placeholder' => '{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'],
            'radius' => ['property' => '--dragwyb-form-border-radius', 'placeholder' => '{{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}'],
        ];


        // 2. Control Map
        $map = [
            'style'         => ['type' => Controls::SELECT, 'label' => __('Style', 'dragwyb-form-builder'), 'options' => ['' => __('None', 'dragwyb-form-builder'), 'solid' => __('Solid', 'dragwyb-form-builder'), 'double' => __('Double', 'dragwyb-form-builder'), 'dotted' => __('Dotted', 'dragwyb-form-builder'), 'dashed' => __('Dashed', 'dragwyb-form-builder'), 'groove' => __('Groove', 'dragwyb-form-builder'), 'ridge' => __('Ridge', 'dragwyb-form-builder'), 'inset' => __('Inset', 'dragwyb-form-builder'), 'outset' => __('Outset', 'dragwyb-form-builder')], 'label_inline' => true],
            'color'         => ['type' => Controls::COLOR, 'label' => __('Color', 'dragwyb-form-builder'), 'conditions' => [
                $id . '_style!' => ['none', '']
            ]],
            'width'         => ['type' => Controls::DIMENSIONS, 'label' => __('Width', 'dragwyb-form-builder'), 'units' => ['px', 'em', '%'], 'conditions' => [
                $id . '_style!' => ['none', '']
            ], 'responsive' => true],
            'radius'        => ['type' => Controls::DIMENSIONS, 'label' => __('Radius', 'dragwyb-form-builder'), 'units' => ['px', 'em', '%'], 'responsive' => true],
        ];

        if (isset($settings['style']['options'])) $map['style']['options'] = $settings['style']['options'];

        // 3. Generate Controls
        foreach ($map as $key => $meta) {
            $config = $settings[$key];

            $control_args = array_filter($meta, function ($key, $value) {
                return $key !== 'responsive';
            }, ARRAY_FILTER_USE_BOTH);

            if (isset($config['default'])) {
                $control_args['default'] = $config['default'];
            }

            if ($settings['conditions'] && !empty($settings['conditions'])) {
                $control_args['conditions'] = isset($control_args['conditions']) ? array_merge($control_args['conditions'], $settings['conditions']) : $settings['conditions'];
            }

            if ($meta['type'] === Controls::SELECT) {
                if (isset($config['options'])) {
                    $control_args['options'] = $config['options'];
                }
            } elseif ($meta['type'] === Controls::DIMENSIONS) {
                if (isset($config['units'])) {
                    $control_args['units'] = $config['units'];
                }
            }

            if (isset($selectors[$key])) {
                $control_args['selectors'] = [
                    $selector => $selectors[$key]['property'] . ':' . $selectors[$key]['placeholder'],
                ];
            }

            if (isset($meta['responsive']) && $meta['responsive']) {
                $this->add_responsive_control($id . '_' . $key, $control_args);
            } else {
                $this->add_control($id . '_' . $key, $control_args);
            }
        }
    }
}
