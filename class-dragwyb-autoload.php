<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder;

if (!defined('ABSPATH')) {
    exit;
}


if (!class_exists('Dragwyb_Form_Builder_Autoload')) {
    class Dragwyb_Form_Builder_Autoload
    {

        private  static $instance;

        public static function instance()
        {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            static $autoloade_status = false;

            if (! $autoloade_status) {
                $autoloade_status = spl_autoload_register([__CLASS__, 'autoload']);
            }
        }

        public function autoload($class_name)
        {
            if (0 !== strpos($class_name, __NAMESPACE__)) {
                return;
            }

            $has_class_alias = isset($this->classes_aliases[$class_name]);

            // Backward Compatibility: Save old class name for set an alias after the new class is loaded
            if ($has_class_alias) {
                $class_alias_name = $this->classes_aliases[$class_name];
                $class_to_load = $class_alias_name;
            } else {
                $class_to_load = $class_name;
            }

            if (! class_exists($class_to_load)) {
                $filename = strtolower(
                    preg_replace(
                        [
                            '/^' . preg_quote(__NAMESPACE__ . '\\', '/') . '/',
                            '/([a-z])([A-Z])/',
                            '/_/',
                            '/\\\/'
                        ],
                        [
                            '',
                            '$1-$2',
                            '-',
                            DIRECTORY_SEPARATOR
                        ],
                        $class_to_load
                    )
                );

                $slash = preg_match('/\//', $filename) ? '/' : '\\';;

                $parts = explode($slash, $filename);

                if (count($parts) > 1) {
                    $last_index = count($parts) - 1;
                    $parts[$last_index] = 'class-' . $parts[$last_index];
                }

                $filename = implode($slash, $parts);


                $filename = trailingslashit(DRAGWYB_FORM_BUILDER_PATH) . $filename . '.php';

                if (is_readable($filename)) {
                    require_once $filename;
                }
            }

            if ($has_class_alias) {
                class_alias($class_alias_name, $class_name);
            }
        }
    }
}
