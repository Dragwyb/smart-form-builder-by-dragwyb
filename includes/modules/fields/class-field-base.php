<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Categories\Categories;

abstract class Field_Base extends Register_Controls_Base
{
    protected string $type;
    protected string $name;
    protected string $icon;
    protected string $category = Categories::STANDARD_FIELDS;
    protected array $settings;
    protected array $form_settings;
    protected array $keywords = array();
    private ?array $display_settings = array();
    private $field_id = 0;

    const ContentTab = 'content_tab';
    const StyleTab = 'style_tab';
    const AdvanceTab = 'advance_tab';

    abstract protected function register_scripts();
    abstract protected function register_style();
    abstract protected function register_field_controls();

    final protected function register_controls(): void
    {
        $this->layout_id_controls();
        $this->register_field_controls();
    }

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

    public function get_category(): string
    {
        return $this->category;
    }

    /**
     * Get the keywords for this field.
     *
     * @return array|false Array of keywords or false if not set.
     */
    public function get_keywords()
    {
        return (!empty($this->keywords) && is_array($this->keywords))
            ? $this->keywords
            : false;
    }


    public function set_the_id($id = ''): void
    {
        $field_id = sanitize_text_field($id);
        $this->field_id = $field_id;
    }

    public function get_the_id(): string
    {
        return $this->field_id;
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

    /**
     * Get the form settings.
     *
     * @return array|null Array of settings or null if not set.
     */
    protected function get_form_settings()
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

    protected function layout_id_controls(): void
    {
        $this->start_section('section_advance_layout', [
            'label' => __('Layout & ID', 'dragwyb-form-builder'),
            'tab'   => self::AdvanceTab,
        ]);

        // The Field ID is a unique identifier used for saving data and logic
        $this->add_control('field_id', [
            'type'        => Controls::TEXT,
            'label'       => __('Field ID', 'dragwyb-form-builder'),
            'description' => __('Unique ID for logic and emails (e.g., text_field_1).', 'dragwyb-form-builder'),
            'dynamic'     => ['active' => false],
        ]);

        $this->add_responsive_control('field_width', [
            'type'        => Controls::SLIDER,
            'label'       => __('Width', 'dragwyb-form-builder'),
            'default' => [
                'size' => 100
            ],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-field-width: {{VALUE}};',
            ]
        ]);

        $this->add_control('css_classes', [
            'type'        => Controls::TEXT,
            'label'       => __('Custom CSS Classes', 'dragwyb-form-builder'),
            'description' => __('Add custom classes to the wrapper.', 'dragwyb-form-builder'),
        ]);

        $this->end_section();

        $this->start_section('section_advance_logic', [
            'label' => __('Conditional Logic', 'dragwyb-form-builder'),
            'tab'   => self::AdvanceTab,
        ]);

        $this->add_control('enable_logic', [
            'type'         => Controls::SWITCHER,
            'label'        => __('Enable Logic', 'dragwyb-form-builder'),
            'default'      => '',
            'return_value' => '',
            'disabled'     => true,
        ]);

        $this->add_control('logic_msg', [
            'type' => Controls::RAW_HTML,
            'raw'  => '<div style="color: hsl(var(--dragwyb-sidebar-foreground)/var(--dragwyb-text-opacity, 1)); font-size: 12px; padding: 10px 0;">' . sprintf(__('%1$sComing Soon%2$s: Advanced Conditional Logic is in development. This feature will allow you to dynamically show or hide fields based on user input.', 'dragwyb-form-builder'), '<strong>', '</strong>') . '</div>',
            'condition' => [
                'enable_logic' => 'yes',
            ],
        ]);

        $this->end_section();
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
