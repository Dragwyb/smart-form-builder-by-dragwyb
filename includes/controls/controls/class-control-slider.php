<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Slider extends Control_Base
{

    protected function register_scripts()
    {
        return array();
    }


    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'default' => 'custom',
            'conditions' => 'conditions',
            'units' => 'custom',
        );
    }


    protected function register_style()
    {
        return array();
    }

    protected function init(): void
    {
        $this->type = 'slider';
        $this->name = __('Slider', 'dragwyb-form-builder');
    }

    protected function sanitize_control($value)
    {
        return sanitize_text_field($value);
    }

    protected function default_setting_sanitize($value)
    {
        return $value;
    }

    protected function untis_setting_sanitize($value)
    {
        return $value;
    }
}
