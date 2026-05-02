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

        $js_assets_info = array(
            'version' => DRAGWYB_FORM_BUILDER_VERSION,
            'dependencies' => array('dragwyb-form-editor')
        );

        if (file_exists(DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/editorControls/editorControls.asset.php')) {
            $dragwyb_js_assets_info = require_once(DRAGWYB_FORM_BUILDER_PATH . 'assets/dist/editorControls/editorControls.asset.php');

            if (isset($dragwyb_js_assets_info['dependencies'])) {
                $js_assets_info['dependencies'] = array_merge($js_assets_info['dependencies'], $dragwyb_js_assets_info['dependencies']);
            }

            if (isset($dragwyb_js_assets_info['version'])) {
                $js_assets_info['version'] = $dragwyb_js_assets_info['version'];
            }
        }

        wp_register_script(
            'dragwyb-editor-controls',
            esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editorControls/editorControls.js'),
            $js_assets_info['dependencies'],
            esc_attr($js_assets_info['version']),
            true
        );

        wp_register_style(
            'dragwyb-editor-controls',
            esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editorControls/editorControls.css'),
            [],
            esc_attr($js_assets_info['version']),
        );
    }

    protected function init(): void
    {
        $this->type = 'text';
        $this->name = __('Text', 'smart-form-builder-by-dragwyb');
    }

    protected function sanitize_control($value)
    {
        return sanitize_text_field($value);
    }
}
