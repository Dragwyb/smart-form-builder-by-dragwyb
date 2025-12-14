<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Url extends Control_Base
{
    protected function register_scripts(): array
    {
        return array('dragwyb-editor-controls');
    }

    protected function register_style(): array
    {
        return array('dragwyb-editor-controls');
    }

    protected function register_settings()
    {
        return array(
            'label'       => 'string',
            'default'     => 'url', // Custom sanitizer below
            'placeholder' => 'string',
            'show_external' => 'boolean', // Option to hide/show the "New Window" checkbox
        );
    }

    protected function init(): void
    {
        $this->type = 'url';
        $this->name = __('URL', 'dragwyb-form-builder');
    }

    /**
     * Sanitize the main value.
     * Expected format: ['url' => '...', 'is_external' => true/false, 'nofollow' => true/false]
     */
    protected function sanitize_control($value)
    {
        if (!is_array($value)) {
            return [];
        }

        return [
            'url'         => isset($value['url']) ? esc_url_raw($value['url']) : '',
            'is_external' => isset($value['is_external']) ? (bool) $value['is_external'] : false,
            'nofollow'    => isset($value['nofollow']) ? (bool) $value['nofollow'] : false,
        ];
    }

    /**
     * Default values for the control settings
     */
    protected function default_setting(): array
    {
        return [
            'url' => '',
            'is_external' => false,
            'nofollow' => false,
        ];
    }

    // Custom sanitizer for the 'default' setting in register_settings
    protected function url_setting_sanitize($value)
    {
        return $this->sanitize_control($value);
    }
}
