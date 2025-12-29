<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Border;

use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Border extends Control_Base
{
    private string $id = '';
    private array $data = [];
    private string $icon = '';

    protected function init(): void
    {
        $this->type = 'border';
        $this->name = __('Border', 'dragwyb-form-builder');
        $this->icon = 'fas fa-border-all';
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
            'conditions' => 'conditions',
            'selector'   => 'string',
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
            ],
            'width' => [
                'unit' => 'px', // Dimensions usually have one unit active
                'top' => '',
                'right' => '',
                'bottom' => '',
                'left' => '',
                'isLinked' => true,
            ],
            'color' => '#333333',
            'radius' => [
                'unit' => 'px',
                'top' => '',
                'right' => '',
                'bottom' => '',
                'left' => '',
                'isLinked' => true,
            ],
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

        return $sanitized;
    }

    protected function string_sanitize($val)
    {
        return sanitize_text_field($val);
    }

    // --- Output ---

    final public function register_controls(string $id, array $data = []): void
    {
        $this->id = $this->string_sanitize($id);
        $this->data = $data;
    }

    private function get_display_settings(): array
    {
        $defaults = $this->default_setting();
        $user_data = isset($this->data['settings']) && is_array($this->data['settings']) ? $this->data['settings'] : $this->data;
        $valid_user_data = array_intersect_key($user_data, $defaults);
        return array_replace_recursive($defaults, $valid_user_data);
    }

    final public function get_controls(): array
    {
        $settings = $this->get_display_settings();
        $id = $this->string_sanitize($this->id);
        $controls = [];

        // 1. Border Style
        $controls[$id . '_style'] = [
            'type'    => Controls::SELECT,
            'label'   => __('Border Style', 'dragwyb-form-builder'),
            'options' => $settings['style']['options'],
            'default' => $settings['style']['default'],
        ];

        // 2. Border Width
        $controls[$id . '_width'] = [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Width', 'dragwyb-form-builder'),
            'size_units' => ['px', 'em', '%'],
            'default'    => $settings['width'],
            'condition'  => [
                $id . '_style!' => ['none', ''] // Hide width if style is none
            ]
        ];

        // 3. Border Color
        $controls[$id . '_color'] = [
            'type'      => Controls::COLOR,
            'label'     => __('Color', 'dragwyb-form-builder'),
            'default'   => $settings['color'],
            'condition' => [
                $id . '_style!' => ['none', '']
            ]
        ];

        // 4. Border Radius (Always visible usually)
        $controls[$id . '_radius'] = [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Border Radius', 'dragwyb-form-builder'),
            'size_units' => ['px', 'em', '%'],
            'default'    => $settings['radius'],
        ];

        return $controls;
    }
}
