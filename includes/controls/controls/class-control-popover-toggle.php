<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Popover_Toggle extends Control_Base
{
    protected function init(): void
    {
        $this->type = 'popover-toggle';
        $this->name = __('Popover Toogle', 'dragwyb-form-builder');
    }

    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'popover' => 'string',
            'icon' => 'string',
            'label_inline' => 'boolean',
        );
    }

    protected function default_setting(): array
    {
        return [
            'label_inline' => true,
            'icon' => 'fas fa-pen'
        ];
    }

    protected function sanitize_control($value)
    {
        return sanitize_text_field($value);
    }
}
