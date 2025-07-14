<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Form_Overview;

if (!defined("ABSPATH")) {
    die("You can't access this page");
}

if (!class_exists('Overview_Columns')) {
    class Overview_Columns
    {
        private static ?self $instance = null;

        public static function instance(): self
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }
    }
}
