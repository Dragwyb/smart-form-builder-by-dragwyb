<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Frontend;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Modules\Module;
use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;

if (!defined('ABSPATH')) {
    exit;
}

class Frontend_Render
{
    private $form_id;
    private $fields;

    private static $control = null;
    private static $module = null;

    private static $field_module_cache = null;

    private static $Field_Data = null;

    public function __construct($form_id)
    {
        $this->form_id = $form_id;
        $this->fields = get_post_meta($form_id, '_dragwyb_form_fields', true);

        $this->set_control();
        $this->set_module();
    }

    private function set_module(): void
    {
        self::$module =  new Module();
    }

    private function set_control(): void
    {
        self::$control =  new Controls();
    }

    public function render(): string
    {
        if (!$this->fields || !is_array($this->fields) || count($this->fields) < 1) return '';

        return $this->render_fields();
    }

    private function render_fields()
    {

        ob_start();

        echo '<form class="dragwyb-form" id="dragwyb-form-' . esc_attr($this->form_id) . '">';

        foreach ($this->fields as $field) {
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
