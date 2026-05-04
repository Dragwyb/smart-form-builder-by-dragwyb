<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Number extends Control_Base
{
    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'default' => 'number',
            'min' => 'number',
            'max' => 'number',
            'label_inline' => 'boolean',
        );
    }

    protected function init(): void
    {
        $this->type = 'number';
        $this->name = __('Number', 'smart-form-builder-by-dragwyb');
    }

    protected function sanitize_control($value, $settings)
    {
        return !empty($value) ? absint($value) : '';
    }
}
