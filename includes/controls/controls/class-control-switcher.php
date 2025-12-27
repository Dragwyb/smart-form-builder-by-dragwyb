<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Switcher extends Control_Base
{
    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'default' => 'string',
            'on_label' => 'string',
            'off_label' => 'string',
            'return_value' => 'string',
            'show_label' => 'boolean',
            'conditions' => 'conditions',
            'label_inline' => 'boolean',
            'default' => 'string',
        );
    }

    protected function default_setting(): array
    {
        return array(
            'on_label' => __('Yes', 'dragwyb-form-builder'),
            'off_label' => __('No', 'dragwyb-form-builder'),
            'return_value' => 'yes',
            'default' => 'no',
            'show_label' => true
        );
    }

    protected function init(): void
    {
        $this->type = 'switcher';
        $this->name = __('Switcher', 'dragwyb-form-builder');
    }

    protected function sanitize_control($value)
    {
        return sanitize_text_field($value);
    }
}
