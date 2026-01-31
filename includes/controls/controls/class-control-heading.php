<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

if (!defined('ABSPATH')) {
    exit;
}

class Control_Heading extends Control_Base
{
    /**
     * Initialize the control type and name
     */
    protected function init(): void
    {
        $this->type = 'heading';
        $this->name = __('Heading', 'dragwyb-form-builder');
    }

    /**
     * Enqueue necessary scripts (if any specific ones are needed)
     */
    protected function register_scripts(): array
    {
        return ['dragwyb-editor-controls'];
    }

    /**
     * Headings are purely visual, they don't have a value to save.
     * We return an empty schema.
     */
    protected function register_settings(): array
    {
        return [
            'label'     => 'string',
            'separator' => 'string', // 'before', 'after', or 'none'
        ];
    }

    /**
     * Sanitize: Since Headings don't save values, we just return null or empty.
     */
    protected function sanitize_control($value)
    {
        return '';
    }
}
