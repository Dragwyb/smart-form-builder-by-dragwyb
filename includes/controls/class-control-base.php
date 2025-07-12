<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls;

if (!defined("ABSPATH")) {
    die("You can't access this page");
}


if (!class_exists('Control_Base')) {

    abstract class Control_Base
    {
        // Abstract methods for the child classes to implement
        abstract public function control_name();
        abstract public function control_scripts();
        abstract public function control_styles();
        abstract public function default_value();
        abstract public function render();

        // Shared property to store control's value
        protected $value;

        public function __construct($value = '')
        {
            $this->value = $value;
        }

        // A method to get the value of the control
        public function get_value()
        {
            return $this->value;
        }

        // A method to set the value of the control
        public function set_value($value)
        {
            $this->value = $value;
        }
    }
}
