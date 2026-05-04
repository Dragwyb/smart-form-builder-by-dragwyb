<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group\Background;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Controls\Group\Group_Control_Base;

class Control_Background extends Group_Control_Base
{
    protected function init(): void
    {
        $this->type = 'background';
        $this->name = __('Background', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-image';
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
        return [
            'background',
            'color',
            'color_stop',
            'color_b',
            'color_b_stop',
            'gradient_type',
            'gradient_angle',
            'gradient_position',
            'image',
            'position',
            'xpos',
            'ypos',
            'attachment',
            'repeat',
            'size',
            'bg_width',
            'selector',
            'conditions'
        ];
    }

    protected function settings_setting_sanitize($value)
    {
        return $this->sanitize_control($value);
    }

    protected function sanitize_control($value, $settings)
    {
        if (!is_array($value)) return [];
        $sanitized = [];

        if (isset($value['selector'])) {
            $sanitized['selector'] = sanitize_text_field($value['selector']);
        }

        foreach (['background', 'color', 'color_b', 'gradient_type', 'gradient_position', 'position', 'attachment', 'repeat', 'size'] as $key) {
            if (isset($value[$key])) {
                $sanitized[$key] = sanitize_text_field($value[$key]);
            }
        }

        foreach (['color_stop', 'color_b_stop', 'gradient_angle', 'xpos', 'ypos', 'bg_width'] as $key) {
            if (isset($value[$key]) && is_array($value[$key])) {
                $sanitized[$key] = [
                    'size' => floatval($value[$key]['size'] ?? 0),
                    'unit' => sanitize_text_field($value[$key]['unit'] ?? 'px'),
                ];
            }
        }

        if (isset($value['image']) && is_array($value['image'])) {
            $sanitized['image'] = [
                'id' => isset($value['image']['id']) ? absint($value['image']['id']) : '',
                'url' => isset($value['image']['url']) ? esc_url_raw($value['image']['url']) : ''
            ];
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

        if (isset($this->data['selector']) && empty($user_data['selector'])) {
            $user_data['selector'] = $this->data['selector'];
        }

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
            'color'             => array('--dragwyb-' . $prefix . '-bg-color' => '{{VALUE}}'),
            'color_stop'        => array('--dragwyb-' . $prefix . '-bg-color-stop' => '{{VALUE}}{{UNIT}}'),
            'color_b'           => array('--dragwyb-' . $prefix . '-bg-color-b' => '{{VALUE}}'),
            'color_b_stop'      => array('--dragwyb-' . $prefix . '-bg-color-b-stop' => '{{VALUE}}{{UNIT}}'),
            'gradient_angle'    => array('--dragwyb-' . $prefix . '-bg-gradient-angle' => '{{VALUE}}{{UNIT}}'),
            'gradient_position' => array('--dragwyb-' . $prefix . '-bg-gradient-position' => '{{VALUE}}'),
            'image'             => array('--dragwyb-' . $prefix . '-bg-image' => 'url("{{URL}}")'),
            'position'          => array('--dragwyb-' . $prefix . '-bg-position' => '{{VALUE}}'),
            'xpos'              => array('--dragwyb-' . $prefix . '-bg-xpos' => '{{VALUE}}{{UNIT}}'),
            'ypos'              => array('--dragwyb-' . $prefix . '-bg-ypos' => '{{VALUE}}{{UNIT}}'),
            'attachment'        => array('--dragwyb-' . $prefix . '-bg-attachment' => '{{VALUE}}'),
            'repeat'            => array('--dragwyb-' . $prefix . '-bg-repeat' => '{{VALUE}}'),
            'size'              => array('--dragwyb-' . $prefix . '-bg-size' => '{{VALUE}}'),
            'bg_width'          => array('--dragwyb-' . $prefix . '-bg-width' => '{{VALUE}}{{UNIT}}'),
        ];

        $fields = [];

        $fields['background'] = [
            'label' => __('Background Type', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::CHOOSE,
            'options' => [
                'classic' => ['title' => __('Classic', 'smart-form-builder-by-dragwyb'), 'icon' => 'fa fa-paint-brush'],
                'image' => ['title' => __('Image', 'smart-form-builder-by-dragwyb'), 'icon' => 'fa fa-image'],
                'gradient' => ['title' => __('Gradient', 'smart-form-builder-by-dragwyb'), 'icon' => 'fa fa-barcode'],
            ],
            'default' => 'classic'
        ];

        $fields['gradient_notice'] = [
            'type' => Controls::RAW_HTML,
            'raw' => '<p class="dragwyb-alert dragwyb-alert-warning">' . __('Set locations and angle for each breakpoint to ensure the gradient adapts to different screen sizes.', 'smart-form-builder-by-dragwyb') . '</p>',
            'conditions' => [
                $id . '_background' => ['gradient'],
            ],
        ];

        $fields['color'] = [
            'label' => __('Color', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::COLOR,
            'conditions' => [
                $id . '_background' => ['classic', 'gradient'],
            ],
        ];

        $fields['color_stop'] = [
            'label' => __('Location', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::SLIDER,
            'units' => ['%', 'px'],
            'default' => [
                'unit' => '%',
                'size' => 0,
            ],
            'responsive' => true,
            'conditions' => [
                $id . '_background' => ['gradient'],
            ],
        ];

        $fields['color_b'] = [
            'label' => __('Second Color', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::COLOR,
            'conditions' => [
                $id . '_background' => ['gradient'],
            ],
        ];

        $fields['color_b_stop'] = [
            'label' => __('Location', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::SLIDER,
            'units' => ['%', 'px'],
            'default' => [
                'unit' => '%',
                'size' => 100,
            ],
            'responsive' => true,
            'conditions' => [
                $id . '_background' => ['gradient'],
            ],
        ];

        $fields['gradient_type'] = [
            'label' => __('Type', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::SELECT,
            'label_inline' => true,
            'options' => [
                'linear' => __('Linear', 'smart-form-builder-by-dragwyb'),
                'radial' => __('Radial', 'smart-form-builder-by-dragwyb'),
            ],
            'default' => 'linear',
            'conditions' => [
                $id . '_background' => ['gradient'],
            ],
        ];

        $fields['gradient_angle'] = [
            'label' => __('Angle', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::SLIDER,
            'units' => ['deg', 'rad', 'turn'],
            'default' => [
                'unit' => 'deg',
                'size' => 180,
            ],
            'range' => [
                'deg' => [
                    'min' => 0,
                    'max' => 360,
                ],
                'rad' => [
                    'min' => 0,
                    'max' => 2 * M_PI,
                ],
                'turn' => [
                    'min' => 0,
                    'max' => 1,
                ],
            ],
            'responsive' => true,
            'conditions' => [
                $id . '_background' => ['gradient'],
                $id . '_gradient_type' => 'linear',
            ],
        ];

        $fields['gradient_position'] = [
            'label' => __('Position', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::SELECT,
            'label_inline' => true,
            'options' => [
                'center center' => __('Center Center', 'smart-form-builder-by-dragwyb'),
                'center left' => __('Center Left', 'smart-form-builder-by-dragwyb'),
                'center right' => __('Center Right', 'smart-form-builder-by-dragwyb'),
                'top center' => __('Top Center', 'smart-form-builder-by-dragwyb'),
                'top left' => __('Top Left', 'smart-form-builder-by-dragwyb'),
                'top right' => __('Top Right', 'smart-form-builder-by-dragwyb'),
                'bottom center' => __('Bottom Center', 'smart-form-builder-by-dragwyb'),
                'bottom left' => __('Bottom Left', 'smart-form-builder-by-dragwyb'),
                'bottom right' => __('Bottom Right', 'smart-form-builder-by-dragwyb'),
            ],
            'default' => 'center center',
            'responsive' => true,
            'conditions' => [
                $id . '_background' => ['gradient'],
                $id . '_gradient_type' => 'radial',
            ],
        ];

        $fields['image'] = [
            'label' => __('Image', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::IMAGE,
            'responsive' => true,
            'conditions' => [
                $id . '_background' => ['image'],
            ],
        ];

        $fields['position'] = [
            'label' => __('Position', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::SELECT,
            'label_inline' => true,
            'default' => 'center center',
            'responsive' => true,
            'options' => [
                'center center' => __('Center Center', 'smart-form-builder-by-dragwyb'),
                'center left' => __('Center Left', 'smart-form-builder-by-dragwyb'),
                'center right' => __('Center Right', 'smart-form-builder-by-dragwyb'),
                'top center' => __('Top Center', 'smart-form-builder-by-dragwyb'),
                'top left' => __('Top Left', 'smart-form-builder-by-dragwyb'),
                'top right' => __('Top Right', 'smart-form-builder-by-dragwyb'),
                'bottom center' => __('Bottom Center', 'smart-form-builder-by-dragwyb'),
                'bottom left' => __('Bottom Left', 'smart-form-builder-by-dragwyb'),
                'bottom right' => __('Bottom Right', 'smart-form-builder-by-dragwyb'),
                'initial' => __('Custom', 'smart-form-builder-by-dragwyb'),
            ],
            'conditions' => [
                $id . '_background' => ['image'],
            ],
        ];

        $fields['xpos'] = [
            'label' => __('X Position', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::SLIDER,
            'responsive' => true,
            'units' => ['px', '%', 'em', 'vw'],
            'default' => [
                'size' => 0,
            ],
            'range' => [
                'px' => [
                    'min' => -800,
                    'max' => 800,
                ],
                'em' => [
                    'min' => -100,
                    'max' => 100,
                ],
                '%' => [
                    'min' => -100,
                    'max' => 100,
                ],
                'vw' => [
                    'min' => -100,
                    'max' => 100,
                ],
            ],
            'conditions' => [
                $id . '_background' => ['image'],
                $id . '_position' => ['initial'],
            ],
        ];

        $fields['ypos'] = [
            'label' => __('Y Position', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::SLIDER,
            'responsive' => true,
            'units' => ['px', '%', 'em', 'vh'],
            'default' => [
                'size' => 0,
            ],
            'range' => [
                'px' => [
                    'min' => -800,
                    'max' => 800,
                ],
                'em' => [
                    'min' => -100,
                    'max' => 100,
                ],
                '%' => [
                    'min' => -100,
                    'max' => 100,
                ],
                'vh' => [
                    'min' => -100,
                    'max' => 100,
                ],
            ],
            'conditions' => [
                $id . '_background' => ['image'],
                $id . '_position' => ['initial'],
            ],
        ];

        $fields['attachment'] = [
            'label' => __('Attachment', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::SELECT,
            'label_inline' => true,
            'default' => '',
            'options' => [
                '' => __('Default', 'smart-form-builder-by-dragwyb'),
                'scroll' => __('Scroll', 'smart-form-builder-by-dragwyb'),
                'fixed' => __('Fixed', 'smart-form-builder-by-dragwyb'),
            ],
            'conditions' => [
                $id . '_background' => ['image'],
            ],
        ];

        $fields['attachment_alert'] = [
            'type' => Controls::RAW_HTML,
            'raw' => '<p class="dragwyb-control-field-description">' . __('Note: Attachment Fixed works only on desktop.', 'smart-form-builder-by-dragwyb') . '</p>',
            'conditions' => [
                $id . '_background' => ['image'],
                $id . '_attachment' => 'fixed',
            ],
        ];

        $fields['repeat'] = [
            'label' => __('Repeat', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::SELECT,
            'label_inline' => true,
            'default' => 'no-repeat',
            'responsive' => true,
            'options' => [
                'no-repeat' => __('No-repeat', 'smart-form-builder-by-dragwyb'),
                'repeat' => __('Repeat', 'smart-form-builder-by-dragwyb'),
                'repeat-x' => __('Repeat-x', 'smart-form-builder-by-dragwyb'),
                'repeat-y' => __('Repeat-y', 'smart-form-builder-by-dragwyb'),
            ],
            'conditions' => [
                $id . '_background' => ['image'],
            ],
        ];

        $fields['size'] = [
            'label' => __('Display Size', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::SELECT,
            'label_inline' => true,
            'responsive' => true,
            'default' => 'cover',
            'options' => [
                'auto' => __('Auto', 'smart-form-builder-by-dragwyb'),
                'cover' => __('Cover', 'smart-form-builder-by-dragwyb'),
                'contain' => __('Contain', 'smart-form-builder-by-dragwyb'),
                'initial' => __('Custom', 'smart-form-builder-by-dragwyb'),
            ],
            'conditions' => [
                $id . '_background' => ['image'],
            ],
        ];

        $fields['bg_width'] = [
            'label' => __('Width', 'smart-form-builder-by-dragwyb'),
            'type' => Controls::SLIDER,
            'responsive' => true,
            'units' => ['px', '%', 'em', 'vw'],
            'range' => [
                'px' => [
                    'max' => 1000,
                ],
            ],
            'default' => [
                'size' => 100,
                'unit' => '%',
            ],
            'conditions' => [
                $id . '_background' => ['image'],
                $id . '_size' => ['initial'],
            ],
        ];

        // Process fields into controls
        foreach ($fields as $key => $meta) {
            $config = isset($settings[$key]) ? $settings[$key] : [];

            $control_args = array_filter($meta, function ($k, $value) {
                return $k !== 'responsive';
            }, ARRAY_FILTER_USE_BOTH);

            if (isset($config['default'])) {
                $control_args['default'] = $config['default'];
            }

            if (!empty($settings['conditions'])) {
                if (isset($control_args['conditions']) && is_array($control_args['conditions'])) {
                    $control_args['conditions'] = array_merge($control_args['conditions'], $settings['conditions']);
                } else {
                    $control_args['conditions'] = $settings['conditions'];
                }
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

                if (!empty($selector_style) && $selector) {
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
