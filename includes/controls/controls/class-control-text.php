<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Text extends Control_Base
{

    protected function register_scripts(): array
    {
        return array('dragwyb-editor-controls');
    }

    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'default' => 'string',
            'label_inline' => 'boolean',
        );
    }


    protected function register_style(): array
    {
        return array('dragwyb-editor-controls');
    }

    public function __construct()
    {
        parent::__construct();
        wp_register_script(
            'dragwyb-editor-controls',
            DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editorControls/editorControls.js',
            ['dragwyb-form-editor'],
            DRAGWYB_FORM_BUILDER_VERSION,
            true
        );

        wp_register_style(
            'dragwyb-editor-controls',
            DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editorControls/editorControls.css',
            [],
            DRAGWYB_FORM_BUILDER_VERSION,
        );
    }

    protected function init(): void
    {
        $this->type = 'text';
        $this->name = __('Text', 'dragwyb-form-builder');
    }

    protected function sanitize_control($value)
    {
        return sanitize_text_field($value);
    }
}
