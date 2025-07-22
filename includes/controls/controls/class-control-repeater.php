<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Repeater extends Control_Base
{

    protected function register_scripts()
    {
        return array();
    }


    protected function register_style()
    {
        return array();
    }

    protected function init(): void
    {
        $this->type = 'repeater';
        $this->name = __('Repeater', 'dragwyb-form-builder');
    }

    protected function sanitize_control($value)
    {
        return sanitize_text_field($value);
    }
}
