<?php

namespace Dragwyb\Form_Builder\Includes\Helper;

if (!defined('ABSPATH')) {
    exit;
}

class Helper
{
    public static function directory_separator()
    {
        if (defined('DIRECTORY_SEPARATOR')) {
            return DIRECTORY_SEPARATOR;
        }

        $dir = (strpos(DRAGWYB_FORM_BUILDER_PATH, '\\') !== false) ? '\\' : '/';
        return $dir;
    }

    public static function namespace_into_dir_path($namespace)
    {
        $seperator = self::directory_separator();
        if (!strpos($namespace, $seperator)) {
            $namespace = str_replace('\\', $seperator, $namespace);
        }
        return $namespace;
    }

    public static function dir_path_into_namespace($dir)
    {
        $seperator = self::directory_separator();

        if (strpos($dir, $seperator)) {
            $dir = str_replace($seperator, '\\', $dir);
        }

        return $dir;
    }
}
