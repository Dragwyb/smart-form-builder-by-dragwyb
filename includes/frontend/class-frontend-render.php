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

    private static $field_data = null;
    private static $form_data = null;
    private static $toolbar_data = array();

    private static $css_cache = array();

    private static $instance = null;


    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        add_action('Dragwyb/before_enqueue/editor_scripts', [$this, 'enqueue_static_assets']);
    }

    public function init(int $form_id)
    {
        $this->clean_old_data();
        self::$form_id = absint($form_id);
        self::$toolbar_data = array();
        self::$fields = array();
        self::$form_data = get_post_meta(self::$form_id, '_dragwyb_form_data', true);

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

            self::$field_data['_id'] = $field['_id'];
            self::$field_data['type'] = $field['type'];

            if (isset($field['type'])) {
                if (isset($field['type'])) {
                    $type = $field['type'];


                    if (!isset(self::$field_module_cache[$type])) {
                        $field_module_cache = self::$module->get_field($type);
                        $field_module_cache->render_controls();
                        self::$field_module_cache[$type] = $field_module_cache;
                    }

                    if (!self::$field_module_cache[$type] instanceof Field_Base) return;

                    self::$field_module_cache[$type]->set_the_id((int) self::$field_data['_id']);
                    self::$field_module_cache[$type]->set_form_settings(self::$toolbar_data);

                    if (isset($field['attributes']) && !empty($field['attributes'])) {
                        $attributes = $field['attributes'];
                        $this->attributes_loop($attributes, $type);
                        self::$field_module_cache[$type]->set_field_settings(self::$field_data['attributes']);
                    } else {
                        self::$field_module_cache[$type]->set_field_settings(array());
                    }

                    self::$field_module_cache[$type]->render();
                }
            }

            self::$field_data = null;
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

                    $control_obj->set_value($value, $attribute, $field_control);
                    $filtered_value = $control_obj->get_value();

                    if (isset($filtered_value) && $filtered_value) {
                        self::$field_data['attributes'][$attribute] = $filtered_value;
                    }
                }
            }
        }
    }

    public function get_generated_css(): string
    {
        $toolbar_obj = new Toolbars();
        $toolbars = $toolbar_obj->get_toolbars();

        foreach (self::$toolbar_data as $toolbar_key => $settings) {
            if (isset($toolbars[$toolbar_key]) && method_exists($toolbars[$toolbar_key], 'get_toolbar_settings')) {
                $toolbar_settings = $toolbars[$toolbar_key]->get_toolbar_settings();

                if (isset($toolbar_settings['controls']) && is_array($toolbar_settings['controls']) && count($toolbar_settings['controls']) > 0) {
                    $css = $this->extract_css_from_settings($settings, $toolbar_settings['controls']);
                    if ($css && !empty($css)) {
                        $css_output .= $css;
                    }
                }
            }
        }

        // 2. Fields CSS
        if (!empty(self::$fields)) {
            foreach (self::$fields as $field) {
                if (empty($field['type']) || empty($field['_id'])) continue;

                $type = $field['type'];

                // Load Module if not loaded
                if (!isset(self::$field_module_cache[$type])) {
                    $field_module = self::$module->get_field($type);
                    if ($field_module) {
                        self::$field_module_cache[$type] = $field_module;
                    }
                }

                // Extract CSS from Field Attributes
                if (isset(self::$field_module_cache[$type]) && !empty($field['attributes']) && method_exists(self::$field_module_cache[$type], 'get_settings')) {
                    $field_settings = self::$field_module_cache[$type]->get_settings();

                    if ($field_settings && is_array($field_settings) && count($field_settings) > 0) {
                        $this->extract_css_from_settings($field['attributes'], $field_settings);
                    }
                }
            }
        }

        if (self::$css_cache && count(self::$css_cache) > 0) {
            $css_string = json_encode(self::$css_cache);


            $css_string = substr($css_string, 1, -1);

            $css_string = preg_replace('/"([^"]+)":"({[^}]+})"(?:,|$)/', '$1$2', $css_string);
            $css_string = str_replace('":"', ':', $css_string);
            $css_string = ltrim($css_string, '"');

            return $css_string;
        }

        return '';
    }

    public static function enqueue_static_assets()
    {
        self::frontend_assets();
    }

    private static function frontend_assets()
    {
        do_action('Dragwyb/Frontend/Before_Render/Enqueue_Static_Assets');

        wp_enqueue_style('dragwyb-form-builder', esc_url(DRAGWYB_FORM_BUILDER_URL . '/assets/css/dragwyb-form-frontend.css'), [], sanitize_text_field(DRAGWYB_FORM_BUILDER_VERSION));

        do_action('Dragwyb/Frontend/After_Render/Enqueue_Static_Assets');
    }

    /**
     * Generates CSS string from settings.
     * Only processes controls that define 'selectors'.
     */
    private function extract_css_from_settings(array $settings, array $controls): void
    {
        $form_wrapper_id = '#dragwyb-form-wrapper-' . self::$form_id;

        foreach ($settings as $control_id => $setting_value) {
            // 1. Validate: Ensure control definition and 'selectors' exist
            if (
                !isset($controls[$control_id]) ||
                !isset($controls[$control_id]['type'])
            ) {
                continue;
            }

            $control_config = $controls[$control_id];

            // 2. Instantiate Control
            $control_manager = self::$control->get_control($control_config['type']);

            if (!$control_manager || !$control_manager instanceof Control_Base) {
                continue;
            }

            if ($control_config['type'] === 'repeater' && isset($control_config['items'])) {
                $control_instance = $control_manager::newInstance();
                $control_instance->set_value($setting_value, $control_id, $control_config);
                $repeater_values = $control_instance->get_value();

                $repeater_controls_instance_cache = array();
                foreach ($repeater_values as $index => $item) {
                    if (!isset($item['attributes']) || count($item['attributes']) < 1) continue;

                    $repeater_id = $item['_id'];

                    foreach ($item['attributes'] as $item_control_id => $item_control_data) {
                        if (!isset($control_config['items'][$item_control_id])) continue;
                        if (!isset($control_config['items'][$item_control_id]['selectors'])) continue;
                        if (count($control_config['items'][$item_control_id]['selectors']) < 1) continue;

                        if (!isset($repeater_controls_instance_cache[$item_control_id])) {
                            $repeater_control_manager = self::$control->get_control($control_config['items'][$item_control_id]['type']);
                            $repeater_controls_instance_cache[$item_control_id] = $repeater_control_manager::newInstance();
                        }

                        $repeater_controls_instance_cache[$item_control_id]->set_value($item_control_data, $item_control_id);
                        $repeater_item_value = $repeater_controls_instance_cache[$item_control_id]->get_value();

                        $placeholders = $this->get_control_placeholders($control_instance);

                        $placeholders = $this->replace_selector_placeholders($form_wrapper_id, $control_config['items'][$item_control_id]['selectors'], $placeholders, $repeater_item_value, $repeater_id);
                    }
                }
                continue;
            } else if (
                !isset($controls[$control_id]['selectors']) ||
                !is_array($controls[$control_id]['selectors']) &&
                count($controls[$control_id]['selectors']) < 1
            ) {
                continue;
            }

            $control_instance = $control_manager::newInstance();
            $control_instance->set_value($setting_value, $control_id, $control_config);
            $control_value = $control_instance->get_value();

            // 3. Get Data Placeholders (e.g., ['VALUE' => '#fff'] or ['TOP' => 10, 'UNIT' => 'px'])
            // Uses helper method to support both complex and simple controls
            $placeholders = $this->get_control_placeholders($control_instance);

            $this->replace_selector_placeholders($form_wrapper_id, $control_config['selectors'], $placeholders, $control_value);
        }
    }

    private function replace_selector_placeholders($wrapper_id, $selectors, $placeholders, $value, $current_item = false): void
    {
        // 4. Process 'selectors' Loop
        // Format: ['{{WRAPPER}} .title' => 'color: {{VALUE}};']
        foreach ($selectors as $css_selector => $css_property) {

            $final_selector = trim(sanitize_text_field($css_selector));

            // A. Parse Selector (Replace {{WRAPPER}})
            $final_selector = str_replace('{{WRAPPER}}', $wrapper_id, $final_selector);

            if ($current_item && is_string($current_item)) {
                $final_selector = str_replace('{{CURRENT_ITEM}}', '.' . $current_item, $final_selector);
            }

            // B. Parse Property (Replace {{VALUE}}, {{UNIT}}, etc.)
            $final_property = $css_property;

            foreach ($placeholders as $ph_key => $ph_value) {
                $css_value = '';
                if ($ph_value === true && is_string($value)) {
                    $css_value = $value;
                } else {
                    $css_value = isset($value[$ph_value]) ? $value[$ph_value] : '';
                }

                $final_property = trim(str_replace('{{' . $ph_key . '}}', (string)$css_value, $final_property));
            }

            // C. Append to CSS string if property is valid
            if (!empty($final_property)) {
                $final_property = str_ends_with($final_property, ';') ? $final_property : $final_property . ';';
                if (!isset(self::$css_cache[$final_selector])) {
                    self::$css_cache[$final_selector] = "{" . $final_property . "}";
                } else {
                    self::$css_cache[$final_selector] = rtrim(self::$css_cache[$final_selector], '}') . $final_property . "}";
                }
            }
        }
    }

    /**
     * Helper: Normalize placeholder data retrieval
     */
    private function get_control_placeholders($control_instance): array
    {
        // If control has specific logic (e.g., Dimensions returns TOP, RIGHT, UNIT)
        if (method_exists($control_instance, 'get_style_placeholders')) {
            return $control_instance->get_style_placeholders();
        }

        // Fallback for simple controls (Color, Text) that just use {{VALUE}}
        return ['VALUE' => 'value'];
    }

    private function clean_old_data(): void
    {
        self::$form_id = null;
        self::$css_cache = array();
        self::$fields = array();
        self::$module = null;
        self::$toolbar_data = null;
        self::$field_module_cache = null;
        self::$field_data = null;
        self::$form_data = null;
    }
}
