<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Text_Shadow;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Controls\Group\Group_Control_Base;

class Control_Text_Shadow extends Group_Control_Base
{

    protected function init(): void
    {
        $this->type = 'text_shadow';
        $this->name = __('Text Shadow', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-pencil-alt';
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
            'blur',
            'conditions',
            'prefix'
        ]);
    }

    protected function settings_setting_sanitize($value)
    {
        return $this->sanitize_control($value);
    }

    protected function sanitize_control($value, $settings)
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
            if (!array_key_exists($key, $user_data)) {
                continue;
            }

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
        $prefix = isset($settings['prefix']) && !empty($settings['prefix']) ? $settings['prefix'] : 'form';

        $selectors = [
            'color' => array('--dragwyb-' . $prefix . '-text-shadow-color' => '{{VALUE}}'),
            'horizontal' => array('--dragwyb-' . $prefix . '-text-shadow-h' => '{{VALUE}}{{UNIT}}'),
            'vertical' => array('--dragwyb-' . $prefix . '-text-shadow-v' => '{{VALUE}}{{UNIT}}'),
            'blur' => array('--dragwyb-' . $prefix . '-text-shadow-blur' => '{{VALUE}}{{UNIT}}'),
        ];


        // 2. Control Map
        $map = [
            'color'         => ['type' => Controls::COLOR, 'label' => __('Color', 'smart-form-builder-by-dragwyb')],
            'horizontal'    => ['type' => Controls::SLIDER, 'label' => __('Horizontal', 'smart-form-builder-by-dragwyb'), 'range' => ['px' => ['min' => -100, 'max' => 100, 'step' => 1]], 'units' => ['px'], 'responsive' => true],
            'vertical'      => ['type' => Controls::SLIDER, 'label' => __('Vertical', 'smart-form-builder-by-dragwyb'), 'range' => ['px' => ['min' => -100, 'max' => 100, 'step' => 1]], 'units' => ['px'], 'responsive' => true],
            'blur'          => ['type' => Controls::SLIDER, 'label' => __('Blur', 'smart-form-builder-by-dragwyb'), 'range' => ['px' => ['min' => 0, 'max' => 100, 'step' => 1]], 'units' => ['px'], 'responsive' => true],
        ];

        // 3. Generate Controls
        foreach ($map as $key => $meta) {
            $config = isset($settings[$key]) ? $settings[$key] : array();

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
