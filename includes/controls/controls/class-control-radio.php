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
            'conditions' => 'conditions',
            'options' => 'custom'
        );
    }

    protected function init(): void
    {
        $this->type = 'radio';
        $this->name = __('Radio', 'dragwyb-form-builder');
    }

    protected function sanitize_control($value)
    {
        return sanitize_text_field($value);
    }

    protected function options_setting_sanitize($value)
    {
        return $value;
    }
}
