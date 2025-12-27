<?php

namespace Dragwyb\Form_Builder\Includes\Controls\Icons;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class Icons_Helper
{
    private static $solid_icons;

    private static $regular_icons;

    private static $brands_icons;

    private static $icon_groups;

    public static function get_icons_solid()
    {
        if (null === self::$solid_icons) {
            $icons = self::get_icons_list('solid');

            self::$solid_icons = apply_filters('Dragwyb/icons_list/solid/icons', $icons);
        }

        return self::$solid_icons;
    }

    public static function get_icons_regular()
    {
        if (null === self::$regular_icons) {
            $icons = self::get_icons_list('regular');

            self::$regular_icons = apply_filters('Dragwyb/icons_list/regular/icons', $icons);
        }

        return self::$regular_icons;
    }

    public static function get_icons_brands()
    {
        if (null === self::$brands_icons) {
            $icons = self::get_icons_list('brands');

            self::$brands_icons = apply_filters('Dragwyb/icons_list/brands/icons', $icons);
        }

        return self::$brands_icons;
    }

    private static function get_icons_list($path)
    {
        $path = sanitize_text_field($path);

        $file_path = DRAGWYB_FORM_BUILDER_PATH . 'includes/controls/icons/list/' . $path . '.php';

        if (file_exists($file_path)) {
            $icons = require_once $file_path;

            if (is_array($icons)) {
                return $icons;
            }
        }

        return [];
    }

    public static function get_icons_list_group()
    {
        $icons_type = self::get_icon_groups();
        $icons = [];

        foreach ($icons_type as $type) {
            $icons_method = 'get_icons_' . $type;

            if (method_exists(self::class, $icons_method)) {
                $icons_list = self::$icons_method();
                $icons[$type] = $icons_list;
            }
        }

        return $icons;
    }

    public static function get_icon_groups()
    {
        if (null === self::$icon_groups) {
            self::$icon_groups = apply_filters('Dragwyb/icons_list/icon_groups', array('solid', 'regular', 'brands'));
        }

        return self::$icon_groups;
    }
}
