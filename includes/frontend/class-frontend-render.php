<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Frontend;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Modules\Modules;
use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbars;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Render
{
    private static $form_id;
    private static $fields = array();

    private static $control = null;
    private static $module = null;

    private static $field_module_cache = null;

    private static $Field_Data = null;
    private static $form_data = null;
    private static $toolbar_data = array();

    public function __construct($form_id)
    {
        self::$form_id = $form_id;
        self::$toolbar_data = array();
        self::$fields = array();
        self::$form_data = get_post_meta($form_id, '_dragwyb_form_data', true);

        if (empty(self::$form_data) || !is_array(self::$form_data) || !isset(self::$form_data['fields']) || count(self::$form_data) < 1) {
            self::$fields = array();
            return;
        }

        $this->set_control();
        $this->set_module();
        $this->set_toolbar_data();
    }

    private function set_module(): void
    {
        self::$module =  new Modules();
    }

    private function set_control(): void
    {
        self::$control =  new Controls();
    }

    private function set_toolbar_data(): void
    {
        $toolbar_obj = new Toolbars();
        $toolbars = $toolbar_obj->get_toolbars();

        foreach (self::$form_data as $key => $value) {
            if ($key === 'id') {
                continue;
            }

            if (count($toolbars) > 0 && isset($toolbars[$key]) && $toolbars[$key] instanceof Toolbar_Base) {
                $toolbar = $toolbars[$key];
                $toolbar->set_form_id(self::$form_id);
                $toolbar->set_toolbar_data($value);
                $toolbar_data = $toolbar->get_toolbar_data();

                if ($toolbar_data) {
                    if ($key === 'fields') {
                        self::$fields = $toolbar_data;
                    } else {
                        self::$toolbar_data[$key] = $toolbar_data;
                    }
                }
            }
        }
    }

    public function render(): string
    {
        return $this->render_fields();
    }

    private function render_fields()
    {

        ob_start();

        if (count(self::$fields) < 1) {
            echo '<p>' . esc_html__('No fields found in this form.', 'dragwyb-form-builder') . '</p>';
            return ob_get_clean();
        }

        echo '<form class="dragwyb-form" id="dragwyb-form-' . esc_attr(self::$form_id) . '">';

        foreach (self::$fields as $field) {
            if (!isset($field['_id']) || !$field['type'] || empty($field['_id']) || empty($field['type'])) {
                continue;
            }

            self::$Field_Data['_id'] = $field['_id'];
            self::$Field_Data['type'] = $field['type'];

            if (isset($field['type'])) {
                if (isset($field['type'])) {
                    $type = $field['type'];


                    if (!isset(self::$field_module_cache[$type])) {
                        $field_module_cache = self::$module->get_field($type);
                        $field_module_cache->render_controls();
                        self::$field_module_cache[$type] = $field_module_cache;
                    }

                    if (!self::$field_module_cache[$type] instanceof Field_Base) return;

                    self::$field_module_cache[$type]->set_the_id((int) self::$Field_Data['_id']);

                    if (isset($field['attributes'])) {
                        $attributes = $field['attributes'];
                        $this->attributes_loop($attributes, $type);
                        self::$field_module_cache[$type]->set_field_settings(self::$Field_Data['attributes']);
                    } else {
                        self::$field_module_cache[$type]->set_field_settings(array());
                    }

                    self::$field_module_cache[$type]->render();
                }
            }

            self::$Field_Data = null;
        }

        return ob_get_clean();
    }

    private function attributes_loop($attributes, $type): void
    {

        if (!self::$field_module_cache[$type] instanceof Field_Base) return;

        foreach ($attributes as $attribute => $value) {
            if ($field_control = self::$field_module_cache[$type]->get_control($attribute)) {
                if (isset($field_control['type'])) {
                    $control_type = $field_control['type'];

                    $control_obj = self::$control->get_control($control_type);

                    if (!$control_obj || !$control_obj instanceof Control_Base) {
                        continue;
                    }

                    $control_obj = $control_obj::newInstance();

                    $control_obj->set_value($value, $type, $attribute);
                    $filtered_value = $control_obj->get_value();

                    if (isset($filtered_value) && $filtered_value) {
                        self::$Field_Data['attributes'][$attribute] = $filtered_value;
                    }
                }
            }
        }
    }
}
