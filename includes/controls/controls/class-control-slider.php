<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Slider extends Control_Base
{
    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'range' => 'range',
            'default' => 'custom',
            'conditions' => 'conditions',
            'show_label' => 'boolean',
            'units' => 'custom',
        );
    }

    protected function default_setting(): array
    {
        return array(
            'units' => ['px'],
            'show_label' => true
        );
    }

    protected function init(): void
    {
        $this->type = 'slider';
        $this->name = __('Slider', 'dragwyb-form-builder');
    }

    protected function sanitize_control($value)
    {
        $filtered_value = $value;
        if (is_array($filtered_value) && count($filtered_value) > 1) {
            $filtered_array = array();
            $allowed_key = ['unit', 'size'];

            foreach ($filtered_value as $key => $value) {
                if (in_array($key, $allowed_key)) {
                    $filtered_array[$this->string_sanitize($key)] = 'unit' === $key ? $this->string_sanitize($value) : $this->number_sanitize($value);
                }
            };

            return $filtered_value;
        }

        return sanitize_text_field($filtered_value);
    }

    protected function default_setting_sanitize($value)
    {
        $filtered_value = array('unit' => 'px', 'size' => 0);

        if (is_array($value)) {
            foreach ($value as $key => $value) {
                if ($key === 'unit') {
                    $filtered_value[$this->string_sanitize($key)] = $this->string_sanitize($value);
                } else {
                    $filtered_value[$this->string_sanitize($key)] = $this->number_sanitize($value);
                }
            }
        }

        return $filtered_value;
    }

    protected function units_setting_sanitize($value): array
    {
        $filtered_units = ['px'];

        if (is_array($value) && count($value) > 0) {
            foreach ($value as $unit) {
                if (!in_array($unit, $filtered_units)) {
                    $filtered_units[] = $this->string_sanitize($unit);
                }
            }
        }

        return $filtered_units;
    }
}
