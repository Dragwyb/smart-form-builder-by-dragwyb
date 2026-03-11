<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Frontend;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Modules\Modules;
use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbars;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;
use Dragwyb\Form_Builder\Includes\Controls\Fonts\Fonts_Helper;

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
    private static $toolbars = null;
    private static $toolbar_data = array();
    private static $toolbar_settings = array();

    private static $css_cache = array();

    private static $google_fonts_cache = null;

    private static $google_fonts = null;

    private static $instance = null;


    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
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

    public function get_fields_values(): array
    {
        return self::$fields;
    }

    public function get_toolbars_values(string $type): array
    {
        if (!isset(self::$toolbar_data[$type])) {
            return array();
        }

        return self::$toolbar_data[$type];
    }

    public function get_toolbar_data(string $type): array
    {
        if (!isset(self::$toolbars[$type])) {
            return array();
        }

        if (!isset(self::$toolbar_settings[$type])) {
            self::$toolbar_settings[$type] = self::$toolbars[$type]->get_toolbar_settings();
        }

        return self::$toolbar_settings[$type];
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

                    self::$field_module_cache[$type]->set_the_id(sanitize_text_field(self::$field_data['_id']));
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

                    if (isset($filtered_value)) {
                        self::$field_data['attributes'][$attribute] = $filtered_value;
                    }
                }
            }
        }
    }

    public function get_generated_css(): array
    {
        $toolbar_obj = new Toolbars();
        self::$toolbars = $toolbar_obj->get_toolbars();

        foreach (self::$toolbar_data as $toolbar_key => $settings) {
            if (isset(self::$toolbars[$toolbar_key]) && method_exists(self::$toolbars[$toolbar_key], 'get_toolbar_settings')) {
                self::$toolbar_settings[$toolbar_key] = self::$toolbars[$toolbar_key]->get_toolbar_settings();

                if (isset(self::$toolbar_settings[$toolbar_key]['controls']) && is_array(self::$toolbar_settings[$toolbar_key]['controls']) && count(self::$toolbar_settings[$toolbar_key]['controls']) > 0) {
                    $this->extract_css_from_settings($settings, self::$toolbar_settings[$toolbar_key]['controls'], $toolbar_key);
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
                        $this->extract_css_from_settings($field['attributes'], $field_settings, 'fields', $field['_id']);
                    }
                }
            }
        }

        if (defined('DRAGWYB_EDITOR') && true === DRAGWYB_EDITOR) {
            return array('css' => self::$css_cache, 'google_fonts' => self::$google_fonts);
        }

        if (self::$css_cache && count(self::$css_cache) > 0) {
            $tablet_css = '';
            $mobile_css = '';

            if (isset(self::$css_cache['tablet'])) {
                $tablet_css = self::$css_cache['tablet'];
                unset(self::$css_cache['tablet']);
            }

            if (isset(self::$css_cache['mobile'])) {
                $mobile_css = self::$css_cache['mobile'];
                unset(self::$css_cache['mobile']);
            }

            $css_string = $this->convert_css_into_strings(self::$css_cache);

            if ($tablet_css && $tablet_css !== '') {
                $css_string .= '@media (max-width: 768px) {' . $this->convert_css_into_strings($tablet_css) . '}';
            }

            if ($mobile_css && $mobile_css !== '') {
                $css_string .= '@media (max-width: 480px) {' . $this->convert_css_into_strings($mobile_css) . '}';
            }

            return array('css' => $css_string, 'google_fonts' => self::$google_fonts);
        }

        return array('css' => '', 'google_fonts' => array());
    }

    private function convert_css_into_strings($css_cache): string
    {
        $css_string = '';

        foreach ($css_cache as $selector => $styles) {
            $css_string .= $selector . '{';
            $css_string .= $styles;
            $css_string .= '}';
        }

        return sanitize_text_field($css_string);
    }

    public static function enqueue_static_assets()
    {
        self::frontend_assets();
    }

    private static function frontend_assets()
    {
        do_action('Dragwyb/Frontend/Before_Render/Enqueue_Static_Assets');

        wp_enqueue_style('dragwyb-form-builder', esc_url(DRAGWYB_FORM_BUILDER_URL . '/assets/css/form-frontend.css'), [], esc_attr(DRAGWYB_FORM_BUILDER_VERSION));


        if (defined('DRAGWYB_FORM_PREVIEW') && true === DRAGWYB_FORM_PREVIEW && function_exists('wp_add_inline_style')) {
            $style_content = self::instance()->get_generated_css();
            wp_add_inline_style('dragwyb-form-builder', $style_content['css']);

            if ($style_content['google_fonts'] && !empty($style_content['google_fonts'])) {
                self::instance()->load_google_fonts(array_map('sanitize_text_field', $style_content['google_fonts']));
            }
        }

        do_action('Dragwyb/Frontend/After_Render/Enqueue_Static_Assets');
    }

    private function load_google_fonts(array $google_fonts): void
    {

        if ($google_fonts && !empty($google_fonts) && is_array($google_fonts)) {
            $font_url = "https://fonts.googleapis.com/css2?";

            $fontFamilies = [];

            foreach ($google_fonts as $fontName) {
                if (in_array($fontName, self::$google_fonts_cache)) {
                    continue;
                }

                self::$google_fonts_cache[] = $fontName;

                $fontName = str_replace(' ', '+', $fontName);

                $fontFamilies[] = $fontName;
            }


            if (count($fontFamilies) < 1) {
                return;
            }

            $font_url .= "family=" . implode('&family=', $fontFamilies);

            wp_enqueue_style('dragwyb-form-google-fonts', 'https://fonts.googleapis.com/css2?' . $font_url, [], DRAGWYB_FORM_BUILDER_VERSION);
        }
    }

    /**
     * Generates CSS string from settings.
     * Only processes controls that define 'selectors'.
     */
    private function extract_css_from_settings(array $settings, array $controls, $type, $field_id = null): void
    {
        $form_wrapper_id = '#dragwyb-form-wrapper-' . self::$form_id;

        if ($field_id && is_string($field_id) && !empty($field_id)) {
            $form_wrapper_id .= ' #dragwyb-field-wrapper-' . $field_id;
        }

        foreach ($controls as $control_id => $control_settings) {
            // 1. Validate: Ensure control definition and 'selectors' exist
            if (
                !isset($control_settings['type'])
            ) {
                continue;
            }

            // 2. Instantiate Control
            $control_manager = self::$control->get_control($control_settings['type']);

            if (!$control_manager || !$control_manager instanceof Control_Base) {
                continue;
            }

            $setting_value = null;

            if (isset($settings[$control_id])) {
                $setting_value = $settings[$control_id];
            } else if (isset($control_settings['default'])) {
                $setting_value = $control_settings['default'];
            }

            if (!isset($setting_value)) {
                continue;
            }

            if ($control_settings['type'] === 'repeater' && isset($control_settings['items'])) {
                $control_instance = $control_manager::newInstance();

                $control_instance->set_value($setting_value, $control_id, $control_settings);
                $repeater_values = $control_instance->get_value();

                $repeater_controls_instance_cache = array();
                foreach ($repeater_values as $index => $item) {
                    if (!isset($item['attributes']) || count($item['attributes']) < 1) continue;

                    $repeater_id = $item['_id'];

                    foreach ($item['attributes'] as $item_control_id => $item_control_data) {
                        if (!isset($control_settings['items'][$item_control_id])) continue;
                        if (!isset($control_settings['items'][$item_control_id]['selectors'])) continue;
                        if (count($control_settings['items'][$item_control_id]['selectors']) < 1) continue;

                        if (!isset($repeater_controls_instance_cache[$item_control_id])) {
                            $repeater_control_manager = self::$control->get_control($control_settings['items'][$item_control_id]['type']);
                            $repeater_controls_instance_cache[$item_control_id] = $repeater_control_manager::newInstance();
                        }

                        if (!$this->control_render_conditions($control_settings['items'][$item_control_id], $control_settings['items'], $item['attributes'])) {
                            continue;
                        }

                        $repeater_controls_instance_cache[$item_control_id]->set_value($item_control_data, $item_control_id);
                        $repeater_item_value = $repeater_controls_instance_cache[$item_control_id]->get_value();

                        // google fonts cache
                        $this->fonts_family_cache($repeater_controls_instance_cache[$item_control_id], $repeater_item_value);

                        $placeholders = $this->get_control_placeholders($control_instance);

                        $placeholders = $this->replace_selector_placeholders($form_wrapper_id, $control_settings['items'][$item_control_id]['selectors'], $placeholders, $repeater_item_value, $type, $control_id, $control_settings['items'][$item_control_id], $field_id, $repeater_id);
                    }
                }
                continue;
            } else if (
                !isset($control_settings['selectors']) ||
                !is_array($control_settings['selectors']) &&
                count($control_settings['selectors']) < 1
            ) {
                continue;
            }

            $control_instance = $control_manager::newInstance();
            $control_instance->set_value($setting_value, $control_id, $control_settings);
            $control_value = $control_instance->get_value();

            if (!$this->control_render_conditions($control_settings, $controls, $settings)) {
                continue;
            }

            // google fonts cache
            $this->fonts_family_cache($control_instance, $control_value);

            // Uses helper method to support both complex and simple controls
            $placeholders = $this->get_control_placeholders($control_instance);

            $this->replace_selector_placeholders($form_wrapper_id, $control_settings['selectors'], $placeholders, $control_value, $type, $control_id, $control_settings, $field_id);
        }
    }

    private function replace_selector_placeholders($wrapper_id, $selectors, $placeholders, $value, $type, $control_id, $control_config, $field_id = null, $current_item = null): void
    {
        $css_array = &self::$css_cache;

        $responsive = false;
        if (isset($control_config['responsive_control']) && isset($control_config['responsive_type']) && true === $control_config['responsive_control'] && !empty($control_config['responsive_type']) && is_string($control_config['responsive_type']) && 'desktop' !== $control_config['responsive_type']) {
            $responsive = sanitize_text_field($control_config['responsive_type']);

            if (!isset($css_array[$responsive])) {
                $css_array[$responsive] = array();
            }

            $css_array = &$css_array[$responsive];
        }

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

            $final_property = $this->clean_css_params($final_property);

            if (trim($final_property) === '') {
                continue;
            }

            // C. Append to CSS string if property is valid
            if (!empty($final_property)) {
                $this->set_css_cache($css_array, $final_selector, $final_property, $type, $control_id, $field_id, $current_item);
            }
        }
    }

    private function clean_css_params($css)
    {
        $css = preg_replace('/[\w-]*:\s*(;|$)/', '', $css);
        return trim(preg_replace('/\s+/', ' ', $css));
    }

    private function set_css_cache(&$css_array, $selector, $property, $type, $control_id, $field_id = null, $current_item = null)
    {
        $property = str_ends_with($property, ';') ? $property : $property . ';';

        if (defined('DRAGWYB_EDITOR') && true === DRAGWYB_EDITOR) {
            $unique_key = sanitize_text_field($type);

            if ($field_id && is_string($field_id)) {
                $unique_key .= '_' . sanitize_text_field($field_id);
            }

            $unique_key .= '_' . sanitize_text_field($control_id);

            if ($current_item && is_string($current_item)) {
                $unique_key .= '_' . sanitize_text_field($current_item);
            }

            if (!isset($css_array[$unique_key])) {
                $css_array[$unique_key] = array();
            }

            $css_array[$unique_key][$selector] = $property;
        } else {
            if (!isset($css_array[$selector])) {
                $css_array[$selector] = $property;
            } else {
                $css_array[$selector] = $css_array[$selector] . $property;
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

    private function fonts_family_cache($control_instance, $value): void
    {
        $type = $control_instance->get_type();

        if ($type !== 'fonts') {
            return;
        }

        if (!isset(self::$google_fonts_cache) || empty(self::$google_fonts_cache)) {
            self::$google_fonts_cache = Fonts_Helper::get_fonts_by_groups(['google']);
        }

        if (isset(self::$google_fonts_cache[sanitize_text_field($value)]) && 'google' === self::$google_fonts_cache[sanitize_text_field($value)] && (!isset(self::$google_fonts) || !in_array(sanitize_text_field($value), self::$google_fonts))) {
            self::$google_fonts[] = sanitize_text_field($value);
        }
    }

    /**
     * Helper: Check if a control should be rendered based on conditions
     */
    private function control_render_conditions($current_control, $controls, $toolbar_values): bool
    {
        $conditions = $current_control['conditions'] ?? null;

        // If no conditions or not an array, allow rendering
        if (empty($conditions) || !is_array($conditions)) {
            return true;
        }

        foreach ($conditions as $key => $expected) {
            // Check if key ends with "!"
            $is_not = (substr($key, -1) === '!');

            // Remove the "!" to get the real control ID
            $clean_key = $is_not ? substr($key, 0, -1) : $key;

            // We look up the dependency control config using the clean key
            if (isset($controls[$clean_key])) {
                $dependency_control = $controls[$clean_key];
                $type = $dependency_control['type'] ?? '';

                // If the dependency is just a layout element, ignore this condition
                if (in_array($type, ['section', 'tabs'], true)) {
                    continue;
                }
            }

            if (!isset($toolbar_values[$clean_key]) && $clean_key === 'section') {
                continue;
            }

            // We use array_key_exists to ensure we catch null values correctly
            if (!array_key_exists($clean_key, $toolbar_values) && $clean_key !== 'section') {
                if (!array_key_exists($clean_key, $controls) || !array_key_exists('default', $controls[$clean_key])) {
                    return false;
                }
            }

            $actual = '';
            if (isset($toolbar_values[$clean_key])) {
                $actual = $toolbar_values[$clean_key];
            } else {
                $actual = sanitize_text_field($controls[$clean_key]['default']);
            }

            // 4. Comparison Logic
            if ($is_not) {
                // Logic: Fail if they ARE equal

                if (is_array($expected)) {
                    if (in_array($actual, $expected)) {
                        return false;
                    }
                } else if ($actual === $expected) {
                    return false;
                }
            } else {
                if (is_array($expected)) {
                    if (!in_array($actual, $expected)) {
                        return false;
                    }
                } else if ($actual !== $expected) {
                    return false;
                }
            }
        }

        return true;
    }

    private function clean_old_data(): void
    {
        self::$form_id = null;
        self::$fields = array();
        self::$module = null;
        self::$field_module_cache = null;
        self::$field_data = null;
        self::$form_data = null;
        self::$toolbar_data = array();
        self::$toolbar_settings = array();
        self::$css_cache = array();
        self::$google_fonts_cache = null;
        self::$google_fonts = null;
    }
}
