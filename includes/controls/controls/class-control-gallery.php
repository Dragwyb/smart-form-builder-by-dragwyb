<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Gallery extends Control_Base
{
    protected function register_settings()
    {
        return array(
            'label'   => 'string',
            'default' => 'gallery', // Custom sanitizer
        );
    }

    protected function init(): void
    {
        $this->type = 'gallery';
        $this->name = __('Gallery', 'smart-form-builder-by-dragwyb');
    }

    /**
     * Override enqueue_assets to load the WP Media Modal core
     */
    protected function register_scripts(): array
    {
        if (!did_action('wp_enqueue_media')) {
            wp_enqueue_media();
        }

        return array();
    }

    /**
     * Sanitize Gallery Data
     * Expected format: Array of objects [ {id: 1, url: '...'}, {id: 2, url: '...'} ]
     */
    protected function sanitize_control($value)
    {
        if (!is_array($value)) {
            return array();
        }

        $gallery = array();

        foreach ($value as $image) {
            if (isset($image['id'])) {
                $gallery[] = array(
                    'id'  => absint($image['id']),
                    'url' => isset($image['url']) ? esc_url_raw($image['url']) : '',
                );
            }
        }

        return $gallery;
    }

    // Default sanitizer for the setting
    protected function gallery_setting_sanitize($value)
    {
        return $this->sanitize_control($value);
    }

    protected function style_placeholders(): array
    {
        return array("URL" => "url");
    }
}
