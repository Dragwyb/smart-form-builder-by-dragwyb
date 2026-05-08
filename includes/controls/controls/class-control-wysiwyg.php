<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

if (!defined('ABSPATH')) exit;

class Control_Wysiwyg extends Control_Base
{
    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'default' => 'custom',
        );
    }

    protected function init(): void
    {
        $this->type = 'wysiwyg';
        $this->name = __('WYSIWYG Editor', 'smart-form-builder-by-dragwyb');
    }

    protected function default_setting_sanitize($value)
    {
        return wp_kses_post($value);
    }

    protected function sanitize_control($value, $settings)
    {
        if (!is_string($value)) {
            return '';
        }

        return wp_kses_post($value);
    }
}
