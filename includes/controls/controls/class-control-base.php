<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

abstract class Control_Base
{
    protected string $type;
    protected string $name;
    private $value = null;
    private $settings = array();
    protected $field_type = null;
    protected $control_id = null;

    abstract protected function register_scripts();
    abstract protected function register_style();
    abstract protected function init(): void;
    abstract protected function sanitize_control($value);
    abstract protected function register_settings();

    protected function default_setting(): array
    {
        return array();
    }

    public function __construct()
    {
        $this->init();
    }

    public function enqueue_assets()
    {
        $scripts = $this->register_scripts();
        $styles = $this->register_style();

        foreach ($scripts as $script) {
            if (!wp_script_is($script, 'enqueued')) {
                wp_enqueue_script($script);
            }
        }
        foreach ($styles as $style) {
            if (!wp_style_is($style, 'enqueued')) {
                wp_enqueue_style($style);
            }
        }
    }

    public function get_type(): string
    {
        return $this->type;
    }

    public function get_name(): string
    {
        return $this->name;
    }

    public function set_settings(array $data): void
    {
        $control_settings = $this->register_settings();


        if (!isset($control_settings['type'])) {
            $control_settings['type'] = 'string';
        }

        // Default value always set in last index.
        if (isset($data['default'])) {
            $default = $data['default'];

            unset($data['default']);

            $data['default'] = $default;
        }

        foreach ($data as $setting => $value) {
            if (!array_key_exists($setting, $control_settings)) {
                continue;
            }

            $sanitize_setting = $this->filter_setting_data($control_settings[$setting], $value, $setting);

            if ($sanitize_setting) {
                $this->settings[$setting] = $sanitize_setting;
            }
        }

        $default_setting = $this->default_setting();

        foreach ($default_setting as $key => $value) {

            if (!array_key_exists($key, $data)) {
                $sanitize_setting = $this->filter_setting_data($control_settings[$key], $value, $key);

                if ($sanitize_setting) {
                    $this->settings[$key] = $sanitize_setting;
                }
            }
        }
    }

    public function get_settings(): array
    {
        return $this->settings;
    }

    public function set_value($data, string $field_name, string $control_id): void
    {
        $this->field_type = sanitize_text_field($field_name);
        $this->control_id = sanitize_text_field($control_id);

        $this->set_filter_value($data);
    }

    private function set_filter_value($data): void
    {
        $this->value = $this->sanitize_control($data);
    }

    public function get_value()
    {
        return $this->value;
    }

    private function filter_setting_data($type, $value, $key)
    {

        if (!$value || !isset($value) || empty($value) || (is_array($value) && count($value) <= 0)) {
            return false;
        }

        if ($type === 'custom') {
            $sanitize_setting = $key . '_setting_sanitize';

            if (!method_exists($this, $sanitize_setting)) {
                return false;
            }

            return $this->$sanitize_setting($value);
        }

        $sanitize_setting = $type . '_setting_sanitize';

        if (!method_exists($this, $sanitize_setting)) {
            return false;
        }

        return $this->$sanitize_setting($value);
    }

    protected function string_sanitize(string $value)
    {
        return $this->string_setting_sanitize($value);
    }

    private function string_setting_sanitize(string $value)
    {
        return sanitize_text_field($value);
    }

    protected function boolean_sanitize(bool $value)
    {
        return $this->boolean_setting_sanitize($value);
    }

    private function boolean_setting_sanitize(bool $value)
    {
        return (bool) $value;
    }

    protected function number_sanitize(int $value)
    {
        return $this->number_setting_sanitize($value);
    }

    private function number_setting_sanitize(int $value)
    {
        // Keep decimals if float, otherwise cast to int
        return is_float($value) !== false ? floatval($value) : intval($value);
    }

    private function conditions_setting_sanitize(array $conditions)
    {
        $condition = [];

        foreach ($conditions as $key => $value) {
            $condition[sanitize_text_field(esc_html($key))] = sanitize_text_field(esc_html($value));
        }

        return $condition;
    }

    protected function range_sanitize(array $range)
    {
        return $this->range_setting_sanitize($range);
    }

    private function range_setting_sanitize(array $range): array
    {
        $filtered_range = array();

        foreach ($range as $unit => $data) {
            if (is_array($data) & count($data) > 0) {
                $filtered_range[$this->string_setting_sanitize($unit)] = array();

                foreach ($data as $key => $value) {
                    $filtered_range[$this->string_setting_sanitize($unit)][$this->string_setting_sanitize($key)] = $this->number_setting_sanitize($value);
                }
            }
        }

        return $filtered_range;
    }

    public function __destruct()
    {
        $this->type = '';
        $this->name = '';
        $this->value = null;
        $this->settings = null;
    }

    /**
     * Create a new instance of the called class.
     *
     * @return static
     */
    public static function newInstance()
    {
        return new static();
    }
}
