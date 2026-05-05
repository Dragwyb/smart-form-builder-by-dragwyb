<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Radio extends Control_Base
{
    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'default' => 'string',
            'options' => 'custom',
            'label_inline' => 'boolean',
        );
    }

    protected function init(): void
    {
        $this->type = 'radio';
        $this->name = __('Radio', 'smart-form-builder-by-dragwyb');
    }

    protected function sanitize_control($value, $settings)
    {
        return sanitize_text_field($value);
    }

    protected function options_setting_sanitize($value)
    {
        return $value;
    }
}
