<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Sanitize_Fields_Settings;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Modules\Modules;

if (!class_exists('Sanitize_Fields_Settings')) {
    class Sanitize_Fields_Settings
    {
        private static $filtered_data = [];

        private static $form_fields = null;

        private static $field_module = null;

        private static $instance = null;

        private static $control = null;
        private static $module = null;

        public static function instance(array $control_data): self
        {
            if (null === self::$instance) {
                self::$instance = new self($control_data);
            }
            return self::$instance;
        }

        public function __construct($data)
        {
            self::$form_fields = $data;

            $this->set_control();
            $this->set_module();
            $this->field_loop();
        }

        private function set_control(): void
        {
            self::$control =  new Controls();
        }

        private function set_module(): void
        {
            self::$module =  new Modules();
        }

        private function field_loop(): void
        {
            foreach (self::$form_fields as $index => $field) {
                if (!isset($field['_id']) || !$field['type']) {
                    continue;
                }

                self::$filtered_data[$index]['_id'] = $field['_id'];
                self::$filtered_data[$index]['type'] = $field['type'];

                if (isset($field['type']) && isset($field['attributes'])){
                    if (isset($field['type']) && is_array($field['attributes']) && count($field['attributes']) > 0) {
                        $type = $field['type'];
                        $attributes = $field['attributes'];

                        if (!isset(self::$field_module[$type])) {
                            $field_module = self::$module->get_field($type);
                            $field_module->render_controls();
                            self::$field_module[$type] = $field_module;
                        }

                        $this->attributes_loop($attributes, $type, $index);
                    }
                }
                    
            }
        }

        private function attributes_loop($attributes, $type, $index): void
        {
            foreach ($attributes as $attribute => $value) {
                if ($field_control = self::$field_module[$type]->get_control($attribute)) {
                    if (isset($field_control['type'])) {
                        $control_type = $field_control['type'];

                        $control_obj = self::$control->get_control($control_type);

                        if (!$control_obj) {
                            continue;
                        }

                        $control_obj = $control_obj::newInstance();

                        $control_obj->set_value($value, $type, $attribute);
                        $filtered_value = $control_obj->get_value();

                        if (isset($filtered_value) && $filtered_value) {
                            self::$filtered_data[$index]['attributes'][$attribute] = $filtered_value;
                        }
                    }
                }
            }
        }

        public function get_data(): array|bool
        {
            return count(self::$filtered_data) > 0 ? self::$filtered_data : false;
        }


        public function __destruct()
        {
            self::$filtered_data = [];
            self::$form_fields = null;
            self::$field_module = null;
            self::$control = null;
            self::$module = null;
        }
    }
}
