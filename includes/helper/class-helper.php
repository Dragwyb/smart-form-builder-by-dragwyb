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
}
