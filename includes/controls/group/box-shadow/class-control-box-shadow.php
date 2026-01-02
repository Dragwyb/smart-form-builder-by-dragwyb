<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Box_Shadow;

use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Box_Shadow extends Control_Base
{
    private string $id = '';
    private array $data = [];
    private string $icon = '';

    protected function init(): void
    {
        $this->type = 'box-shadow';
        $this->name = __('Box Shadow', 'dragwyb-form-builder');
        $this->icon = 'fas fa-clone';
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

    protected function default_setting(): array
    {
        return [
            'color'      => 'rgba(0,0,0,0.5)',
            'horizontal' => ['size' => 0, 'unit' => 'px'],
            'vertical'   => ['size' => 0, 'unit' => 'px'],
            'blur'       => ['size' => 10, 'unit' => 'px'],
            'spread'     => ['size' => 0, 'unit' => 'px'],
            'position'   => 'outline', // outline | inset
        ];
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
            $sanitized['color'] = sanitize_text_field($value['color']); // Allow RGBA strings
        }

        if (isset($value['position'])) {
            $sanitized['position'] = sanitize_text_field($value['position']);
        }

        foreach (['horizontal', 'vertical', 'blur', 'spread'] as $key) {
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

        $controls[$id . '_color'] = [
            'type'    => Controls::COLOR,
            'label'   => __('Color', 'dragwyb-form-builder'),
            'default' => $settings['color'],
        ];

        $controls[$id . '_horizontal'] = [
            'type'    => Controls::SLIDER,
            'label'   => __('Horizontal', 'dragwyb-form-builder'),
            'range'   => ['px' => ['min' => -100, 'max' => 100, 'step' => 1]],
            'default' => $settings['horizontal'],
        ];

        $controls[$id . '_vertical'] = [
            'type'    => Controls::SLIDER,
            'label'   => __('Vertical', 'dragwyb-form-builder'),
            'range'   => ['px' => ['min' => -100, 'max' => 100, 'step' => 1]],
            'default' => $settings['vertical'],
        ];

        $controls[$id . '_blur'] = [
            'type'    => Controls::SLIDER,
            'label'   => __('Blur', 'dragwyb-form-builder'),
            'range'   => ['px' => ['min' => 0, 'max' => 100, 'step' => 1]],
            'default' => $settings['blur'],
        ];

        $controls[$id . '_spread'] = [
            'type'    => Controls::SLIDER,
            'label'   => __('Spread', 'dragwyb-form-builder'),
            'range'   => ['px' => ['min' => -100, 'max' => 100, 'step' => 1]],
            'default' => $settings['spread'],
        ];

        $controls[$id . '_position'] = [
            'type'    => Controls::SELECT,
            'label'   => __('Position', 'dragwyb-form-builder'),
            'options' => [
                'outline' => __('Outline', 'dragwyb-form-builder'),
                'inset'   => __('Inset', 'dragwyb-form-builder'),
            ],
            'default' => $settings['position'],
            'label_inline' => true,
        ];

        return $controls;
    }
}
