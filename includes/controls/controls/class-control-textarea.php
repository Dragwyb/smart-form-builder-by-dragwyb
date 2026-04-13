<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Textarea extends Control_Base
{
    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'default' => 'string',
        );
    }

    protected function init(): void
    {
        $this->type = 'textarea';
        $this->name = __('Textarea', 'smart-form-builder-by-dragwyb');
    }

    protected function sanitize_control($value)
    {
        return sanitize_text_field($value);
    }
}
