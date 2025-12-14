<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Select extends Control_Base
{
    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'default' => 'string',
            'conditions' => 'conditions',
            'options' => 'custom',
            'label_inline' => 'boolean',
        );
    }

    protected function init(): void
    {
        $this->type = 'select';
        $this->name = __('Select', 'dragwyb-form-builder');
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
