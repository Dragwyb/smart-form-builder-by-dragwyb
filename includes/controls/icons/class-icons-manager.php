<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Icons;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Icons_Manager
{
    /**
     * Render Icon.
     *
     * Used to render Icon for the frontend.
     *
     * @param array  $icon       Icon array containing 'icon' and 'type'.
     * @param array  $attributes HTML attributes to add to the rendered icon.
     * @param string $tag        HTML tag to use for the icon. Default 'i'.
     *
     * @return void
     */
    public static function render_icon(array $icon, array $attributes = [], string $tag = 'i'): void
    {
        if (empty($icon['icon'])) {
            return;
        }

        $icon_type = isset($icon['type']) ? sanitize_key($icon['type']) : 'solid';
        $icon_name = sanitize_html_class($icon['icon']);

        // Prevent rendering invalid tags
        $tag = tag_escape($tag);
        if (empty($tag)) {
            $tag = 'i';
        }

        if (!isset($attributes['class'])) {
            $attributes['class'] = [];
        } elseif (!is_array($attributes['class'])) {
            $attributes['class'] = explode(' ', $attributes['class']);
        }

        $prefix = self::get_icon_prefix($icon_type);

        // Add prefix classes
        $attributes['class'][] = trim($prefix);

        // FontAwesome prefixes the icon name with 'fa-' usually
        if (strpos($icon_name, 'fa-') !== 0 && $prefix !== 'dragwyb-icon') {
            $icon_class = 'fa-' . $icon_name;
        } else {
            $icon_class = $icon_name;
        }

        $attributes['class'][] = $icon_class;

        // Ensure unique and clean classes
        $attributes['class'] = array_unique(array_filter($attributes['class']));

        if (!isset($attributes['aria-hidden'])) {
            $attributes['aria-hidden'] = 'true';
        }

        self::render_html_tag($tag, $attributes);
    }

    /**
     * Get Icon CLass.
     * 
     * @param array $icon Icon array containing 'icon' and 'type'.
     * 
     * @return string
     */
    public static function get_icon_class(array $icon)
    {
        if (empty($icon['icon'])) {
            return;
        }

        $icon_type = isset($icon['type']) ? sanitize_key($icon['type']) : 'solid';
        $icon_name = sanitize_html_class($icon['icon']);

        $prefix = self::get_icon_prefix($icon_type);

        // Add prefix classes
        $attributes['class'][] = trim($prefix);

        // FontAwesome prefixes the icon name with 'fa-' usually
        if (strpos($icon_name, 'fa-') !== 0 && $prefix !== 'dragwyb-icon') {
            $icon_class = 'fa-' . $icon_name;
        } else {
            $icon_class = $icon_name;
        }

        $attributes['class'][] = $icon_class;

        // Ensure unique and clean classes
        $attributes['class'] = array_unique(array_filter($attributes['class']));

        return esc_attr(implode(' ', $attributes['class']));
    }

    /**
     * Get Icon HTML.
     *
     * @param array  $icon       Icon array containing 'icon' and 'type'.
     * @param array  $attributes HTML attributes to add to the rendered icon.
     * @param string $tag        HTML tag to use for the icon. Default 'i'.
     *
     * @return string
     */
    public static function get_icon_html(array $icon, array $attributes = [], string $tag = 'i'): string
    {
        ob_start();
        self::render_icon($icon, $attributes, $tag);
        return ob_get_clean();
    }

    /**
     * Get icon prefix based on icon type.
     *
     * @param string $type
     * @return string
     */
    private static function get_icon_prefix(string $type): string
    {
        $prefixes = [
            'solid'   => 'fas',
            'regular' => 'far',
            'brands'  => 'fab',
            'custom'  => 'dragwyb-icon',
        ];

        return $prefixes[$type] ?? 'fas';
    }

    /**
     * Render HTML Tag.
     *
     * @param string $tag
     * @param array  $attributes
     * @return void
     */
    private static function render_html_tag(string $tag, array $attributes = []): void
    {
        $attributes_string = self::render_attributes($attributes);

        echo sprintf('<%1$s %2$s></%1$s>', esc_attr($tag), trim($attributes_string));
    }

    /**
     * Render Attributes.
     *
     * @param array $attributes
     * @return string
     */
    private static function render_attributes(array $attributes): string
    {
        $rendered_attributes = [];

        foreach ($attributes as $attribute_key => $attribute_values) {
            $attribute_key = sanitize_key($attribute_key);

            if (is_array($attribute_values)) {
                $attribute_values = implode(' ', $attribute_values);
            }

            $rendered_attributes[] = sprintf('%1$s="%2$s"', $attribute_key, esc_attr((string) $attribute_values));
        }

        return implode(' ', $rendered_attributes);
    }
}
