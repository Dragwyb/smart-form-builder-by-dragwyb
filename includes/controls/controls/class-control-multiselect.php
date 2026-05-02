<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Multiselect extends Control_Base
{
    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'default' => 'custom',
            'options' => 'custom',
        );
    }

    protected function init(): void
    {
        $this->type = 'multiselect';
        $this->name = __('Multiselect', 'smart-form-builder-by-dragwyb');
    }

    protected function sanitize_control($value)
    {
        if (!is_array($value)) {
            return array();
        }

        return array_map('sanitize_text_field', $value);
    }

    protected function options_setting_sanitize($value)
    {
        $filterd_options = array();
        if (is_array($value)) {
            foreach ($value as $key => $value) {
                $filterd_options[sanitize_text_field($key)] = sanitize_text_field($value);
            }
        }
        return $filterd_options;
    }
}
