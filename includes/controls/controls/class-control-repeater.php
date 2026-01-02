<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Control_Repeater extends Control_Base
{
    private $controls = null;
    private $repeater_data = null;
    private $value = null;

    protected function register_settings()
    {
        return array(
            'name' => 'string',
            'label' => 'string',
            'default' => 'custom',
            'items' => 'custom',
            'item_label' => 'string',
            'add_item' => 'string',
            '_id' => 'string'
        );
    }

    protected function default_setting(): array
    {
        return array(
            'add_item' => __('Add Item', 'dragwyb-form-builder')
        );
    }

    protected function init(): void
    {
        $this->type = 'repeater';
        $this->name = __('Repeater', 'dragwyb-form-builder');
    }

    public function set_value($data, string $control_id, $repeater_data = null): void
    {
        if (isset($repeater_data['type']) && $repeater_data['type'] == 'repeater' && isset($repeater_data['items'])) {
            $this->repeater_data = $repeater_data;
        } else {
            $this->repeater_data = array();
        }

        $this->control_id = sanitize_text_field($control_id);

        $this->set_filter_value($data);
    }

    private function set_filter_value($data): void
    {
        $this->value = $this->sanitize_control($data);
    }

    protected function sanitize_control($items)
    {
        if (!is_array($items) || count($items) <= 0 || !isset($this->control_id)) {
            return '';
        }

        $data = array();

        foreach ($items as $index => $item) {
            if (!isset($item['_id'])) {
                continue;
            }

            $data[$index] = array('_id' => $item['_id']);

            if (!isset($this->repeater_data) || !is_array($this->repeater_data) || count($this->repeater_data) <= 0) {
                continue;
            }

            $register_fields = $this->repeater_data['items'];

            if (!isset($register_fields) || !is_array($register_fields) || count($register_fields) < 0) {
                continue;
            }

            if (!isset($item['attributes']) || !is_array($item['attributes']) || count($item['attributes']) <= 0) {
                continue;
            }

            $attributes = $item['attributes'];

            $data[$index]['attributes'] = array();


            foreach ($attributes as $field => $value) {
                if (!isset($register_fields[$field]['type'])) {
                    continue;
                }

                if (!isset($this->controls)) {
                    $this->controls = new Controls;;
                }

                $type = $register_fields[$field]['type'];

                $control_obj = $this->controls->get_control($type);

                if (!$control_obj) {
                    continue;
                }

                $control_obj = $control_obj::newInstance();

                $control_obj->set_value($value, $field);
                $filtered_value = $control_obj->get_value();

                if (isset($filtered_value) && $filtered_value) {
                    $data[$index]['attributes'][$field] = $value;
                }
            }
        }

        return $data;
    }

    public function get_value()
    {
        return $this->value;
    }

    protected function items_setting_sanitize($fields)
    {

        if (!is_array($fields) || count($fields) < 0) {
            return array();
        }

        $data = array();

        foreach ($fields as $field) {
            $data[$field['name']] = $field;
        }

        return $data;
    }

    protected function default_setting_sanitize($fields)
    {
        if (!is_array($fields) || count($fields) <= 0) {
            return '';
        }

        $fields_settings = $this->get_settings();

        if (!isset($fields_settings['items']) || !is_array($fields_settings['items']) || count($fields_settings['items']) <= 0) {
            return '';
        }

        $repeater_fields = $fields_settings['items'];

        $data = array();
        foreach ($fields as $index => $field) {
            $data[$index] = array();

            foreach ($field as $key => $value) {
                if (!isset($repeater_fields[$key]) || !isset($repeater_fields[$key]['type'])) {
                    continue;
                }

                if (!isset($this->controls)) {
                    $this->controls = new Controls;;
                }

                $type = $repeater_fields[$key]['type'];

                $control_obj = $this->controls->get_control($type);

                if (!$control_obj) {
                    continue;
                }

                $control_obj = $control_obj::newInstance();

                $control_obj->set_value($value, $key);
                $filtered_value = $control_obj->get_value();

                if (isset($filtered_value) && $filtered_value) {
                    $data[$index][$key] = $value;
                }
            }
        }

        return $data;
    }
}
