<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Text_Shadow;

use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Text_Shadow extends Control_Base
{
    private string $id = '';
    private array $data = [];
    private string $icon = '';

    protected function init(): void
    {
        $this->type = 'text_shadow';
        $this->name = __('Text Shadow', 'dragwyb-form-builder');
        $this->icon = 'fas fa-pencil-alt';
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
            'settings'   => 'custom'
        ];
    }

    protected function valid_default_settings(): array
    {
        return array_merge(array_keys($this->default_setting()), [
            'color',
            'selector',
            'horizontal',
            'vertical',
            'blur'
        ]);
    }

    protected function settings_setting_sanitize($value)
    {
        return $this->sanitize_control($value);
    }

    protected function sanitize_control($value)
    {
        if (!is_array($value)) return [];
        $sanitized = [];

        if (isset($value['color'])) {
            $sanitized['color'] = sanitize_text_field($value['color']);
        }

        // Added Selector Sanitization
        if (isset($value['selector'])) {
            $sanitized['selector'] = sanitize_text_field($value['selector']);
        }

        foreach (['horizontal', 'vertical', 'blur'] as $key) {
            if (isset($value[$key]) && is_array($value[$key])) {
                $sanitized[$key] = [
                    'size' => floatval($value[$key]['size'] ?? 0),
                    'unit' => sanitize_text_field($value[$key]['unit'] ?? 'px'),
                ];
            }
        }

        return $sanitized;
    }

    protected function string_sanitize($val)
    {
        return sanitize_text_field($val);
    }

    final public function register_controls(string $id, array $data = []): void
    {
        $this->id = $this->string_sanitize($id);
        $this->data = $data;
    }

    private function get_display_settings(): array
    {
        $valid_settings = $this->valid_default_settings();
        $user_data = isset($this->data['settings']) && is_array($this->data['settings'])
            ? $this->data['settings']
            : $this->data;

        // Handle root selector fallback
        if (isset($this->data['selector']) && empty($user_data['selector'])) {
            $user_data['selector'] = $this->data['selector'];
        }        // Only valid keys from defaults
        $valid_user_data = [];

        foreach ($valid_settings as $key) {
            if (isset($user_data[$key])) {
                $valid_user_data[$key] = $user_data[$key];
            }
        }

        return $valid_user_data;
    }

    final public function get_controls(): array
    {
        $settings = $this->get_display_settings();
        $id = $this->string_sanitize($this->id);
        $selector = isset($settings['selector']) && !empty($settings['selector']) ? $settings['selector'] : false;

        $selectors = [
            'color' => array('property' => '--dragwyb-form-text-shadow-color', 'placeholder' => '{{VALUE}}'),
            'horizontal' => array('property' => '--dragwyb-form-text-shadow-h', 'placeholder' => '{{VALUE}}{{UNIT}}'),
            'vertical' => array('property' => '--dragwyb-form-text-shadow-v', 'placeholder' => '{{VALUE}}{{UNIT}}'),
            'blur' => array('property' => '--dragwyb-form-text-shadow-blur', 'placeholder' => '{{VALUE}}{{UNIT}}'),
        ];

        $controls = [];

        $controls[$id . '_color'] = [
            'type'    => Controls::COLOR,
            'label'   => __('Color', 'dragwyb-form-builder'),
        ];

        if (isset($settings['color'])) {
            $controls[$id . '_color']['default'] = $settings['color'];
        }

        $controls[$id . '_horizontal'] = [
            'type'    => Controls::SLIDER,
            'label'   => __('Horizontal', 'dragwyb-form-builder'),
            'range'   => ['px' => ['min' => -100, 'max' => 100, 'step' => 1]],
        ];

        if (isset($settings['horizontal'])) {
            $controls[$id . '_horizontal']['default'] = $settings['horizontal'];
        }

        $controls[$id . '_vertical'] = [
            'type'    => Controls::SLIDER,
            'label'   => __('Vertical', 'dragwyb-form-builder'),
            'range'   => ['px' => ['min' => -100, 'max' => 100, 'step' => 1]],
        ];

        if (isset($settings['vertical'])) {
            $controls[$id . '_vertical']['default'] = $settings['vertical'];
        }

        $controls[$id . '_blur'] = [
            'type'    => Controls::SLIDER,
            'label'   => __('Blur', 'dragwyb-form-builder'),
            'range'   => ['px' => ['min' => 0, 'max' => 100, 'step' => 1]],
        ];

        if (isset($settings['blur'])) {
            $controls[$id . '_blur']['default'] = $settings['blur'];
        }

        // Inject Selector
        if ($selector) {
            foreach ($selectors as $key => $style) {
                if (isset($controls[$id . '_' . $key])) {
                    $controls[$id . '_' . $key]['selectors'] = [
                        $selector => $style['property'] . ':' . $style['placeholder'],
                    ];
                }
            }
        }

        return $controls;
    }
}
