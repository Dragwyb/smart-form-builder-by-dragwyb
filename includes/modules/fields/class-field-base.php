<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;

abstract class Field_Base extends Register_Controls_Base
{
    protected string $type;
    protected string $name;
    protected string $icon;
    protected array $settings;
    protected array $form_settings;
    protected array $keywords = array();
    private ?array $display_settings = array();
    private $form_id = 0;

    const ContentTab = 'content_tab';
    const StyleTab = 'style_tab';
    const AdvanceTab = 'advance_tab';

    abstract protected function register_scripts();
    abstract protected function register_style();

    public function __construct()
    {
        $this->init();
        parent::__construct();
    }

    abstract protected function init(): void;

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

    public function get_icon(): string
    {
        return $this->icon;
    }

    /**
     * Get the keywords for this field.
     *
     * @return array|false Array of keywords or false if not set.
     */
    public function get_keywords(): array|false
    {
        return $this->keywords && is_array($this->keywords) && count($this->keywords) > 0 ? $this->keywords : false;
    }

    public function set_the_id(int $id): void
    {
        $this->form_id = (int) $id;
    }

    public function get_the_id(): int
    {
        return $this->form_id;
    }

    protected function tab_condition(&$conditions, $data): array
    {
        if ((isset($data['tab']) && !empty($data['tab']))) {
            $conditions['header_controls'] = $data['tab'];
        } else if (!isset($data['tab']) || empty($data['tab'])) {
            $conditions['header_controls'] = self::ContentTab;
        }

        return $conditions;
    }

    public function set_field_settings(array $setting)
    {
        $this->display_settings = $setting;
    }

    protected function get_field_settings(): array
    {
        return $this->display_settings;
    }

    public function set_form_settings(array $setting): void
    {
        $this->form_settings = $setting;
    }

    protected function get_form_settings(): array|null
    {
        return $this->form_settings;
    }


    abstract protected function render_field();
    abstract public function validate($value): bool;

    public function render()
    {
        $this->render_field();
    }

    protected function field_key_exist(array $array, string $key, $default = false)
    {
        return $this->field_array_key_exist($array, $key, $default);
    }

    private function field_array_key_exist(array $array, string $key, $default = false)
    {
        if (isset($array[$key]) && (is_array($array[$key]) || is_string($array[$key]))) {
            if (is_array($array[$key]) && count($array[$key]) > 0) {
                return $array[$key];
            }

            if (is_string($array[$key]) && !empty($array[$key])) {
                return $array[$key];
            }
        }

        return $default;
    }

    protected function header_controls(): array
    {
        $header_tab = array();
        $tabs = array(
            self::ContentTab => array(
                'label' => 'Content'
            ),
            self::StyleTab => array(
                'label' => 'Style'
            ),
            self::AdvanceTab => array(
                'label' => 'Advance'
            ),
        );

        $tabs = apply_filters('Dragwy/Editor/render_controls/header_tabs', $tabs);

        $header_tab['header_controls'] = array(
            'type' => 'tabs',
            'tabs' => $tabs
        );

        return $header_tab;
    }
}
