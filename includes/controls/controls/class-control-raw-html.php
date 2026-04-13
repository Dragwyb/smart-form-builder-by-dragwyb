<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

if (!defined('ABSPATH')) exit;

class Control_Raw_Html extends Control_Base
{
    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'raw' => 'custom',
        );
    }

    protected function init(): void
    {
        $this->type = 'raw_html';
        $this->name = __('Raw HTML', 'smart-form-builder-by-dragwyb');
    }

    protected function raw_setting_sanitize($value)
    {
        return wp_kses_post($value);
    }

    protected function sanitize_control($value)
    {
        return false;
    }
}
