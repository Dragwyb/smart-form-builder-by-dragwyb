<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Tabs extends Control_Base
{
    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'active_tab' => 'string',
            'tabs' => 'custom'
        );
    }

    protected function init(): void
    {
        $this->type = 'tabs';
        $this->name = __('Tabs', 'smart-form-builder-by-dragwyb');
    }

    protected function sanitize_control($value, $settings)
    {
        return sanitize_text_field($value);
    }

    protected function tabs_setting_sanitize($value)
    {
        return $value;
    }
}
