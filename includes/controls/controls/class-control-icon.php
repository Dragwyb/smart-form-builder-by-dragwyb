<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

use Dragwyb\Form_Builder\Includes\Controls\Icons\Icons_Helper;

class Control_Icon extends Control_Base
{
    protected function register_settings()
    {
        return array(
            'label'   => 'string',
            'default' => 'string',
            'fa_lib' => 'custom',
            'label_inline' => 'boolean',
        );
    }

    protected function init(): void
    {
        $this->type = 'icon';
        $this->name = __('Icon', 'dragwyb-form-builder');
    }


    protected function default_setting(): array
    {
        return array(
            'label_inline' => true,
            'fa_lib' => Icons_Helper::get_icon_groups(),
        );
    }

    protected function fa_lib_settings_sanitize(array $value)
    {
        $icons = array_filter($value, function ($icon) {
            return in_array($icon, Icons_Helper::get_icon_groups());
        });

        return $icons;
    }

    protected function sanitize_control($value)
    {
        $icon = array();
        $icon['icon'] = '';
        $icon['type'] = '';

        if (isset($value['icon']) && isset($value['type'])) {
            $icon['icon'] = sanitize_text_field($value['icon']);
            $icon['type'] = sanitize_text_field($value['type']);
        }

        // Sanitize as a CSS class string (e.g., "fa fa-home")
        return $icon;
    }
}
