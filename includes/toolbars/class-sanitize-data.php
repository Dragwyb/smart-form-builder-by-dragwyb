<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Toolbars;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Modules\Modules;

if (!class_exists('Sanitize_Data')) {
    class Sanitize_Data
    {
        private static $filtered_data = [];

        private static $data = null;

        private static $toolbar_controls = null;

        private static $control = null;

        public function __construct($data = null, $controls = null)
        {
            self::$filtered_data = [];
            self::$data = $data;
            self::$toolbar_controls = $controls;

            if (self::$data && is_array(self::$data) && self::$toolbar_controls && is_array(self::$toolbar_controls)) {
                $this->set_control();
                $this->data_loop();
            }
        }

        private function set_control(): void
        {
            self::$control =  new Controls();
        }


        private function data_loop(): void
        {
            foreach (self::$data as $id => $value) {
                if (!isset(self::$toolbar_controls[$id]) || !self::$toolbar_controls[$id]['type']) {
                    continue;
                }

                $control_type = self::$toolbar_controls[$id]['type'];

                $control_obj = self::$control->get_control($control_type);

                if (!$control_obj) {
                    continue;
                }

                $control_obj = $control_obj::newInstance();

                $control_obj->set_value($value, $id, self::$toolbar_controls[$id]);
                $filtered_value = $control_obj->get_value();

                if ($filtered_value && !isset(self::$filtered_data[$id])) {
                    self::$filtered_data[$id] = $filtered_value;
                }
            }
        }

        /**
         * Get the filtered data.
         *
         * @return array|false Array of data or false if no data exists.
         */
        public function get_data()
        {
            return (!empty(self::$filtered_data) && is_array(self::$filtered_data))
                ? self::$filtered_data
                : false;
        }
    }
}
