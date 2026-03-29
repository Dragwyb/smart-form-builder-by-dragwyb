<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Categories\Categories;
use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;

abstract class Field_Base extends Register_Controls_Base
{
    protected string $type;
    protected string $name;
    protected string $icon;
    protected bool $allow_child = false;
    protected bool $is_root_container = false;
    protected string $category = Categories::STANDARD_FIELDS;
    protected array $settings;
    protected Frontend_Render $frontend_handler;
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

    public function is_root_container(): bool
    {
        return $this->is_root_container;
    }

    public function get_allow_child(): bool
    {
        return $this->allow_child;
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

    public function set_frontend_handler(Frontend_Render $frontend_render): void
    {
        $this->frontend_handler = $frontend_render;
    }

    /**
     * Get the form settings.
     *
     * @return array|null Array of settings or null if not set.
     */
    protected function get_toolbars_values(String $type = '')
    {
        if (isset($type) && !empty($type)) {
            return $this->frontend_handler->get_toolbars_values($type);
        }

        return array();
    }

    protected function get_field_data(string $field_id): array
    {
        if (isset($field_id) && !empty($field_id)) {
            return $this->frontend_handler->get_field_data($field_id);
        }

        return array();
    }

    protected function get_module(string $type)
    {
        if (isset($type) && !empty($type)) {
            return $this->frontend_handler->get_module($type);
        }

        return array();
    }

    protected function get_control_handler(string $type)
    {
        if (isset($type) && !empty($type)) {
            return $this->frontend_handler->get_control($type);
        }

        return array();
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
        if (isset($array[$key])) {
            return $array[$key];
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

        // column span
        $this->add_responsive_control('column_span', [
            'type'        => Controls::SLIDER,
            'label'       => __('Column Span', 'dragwyb-form-builder'),
            'range' => [
                'px' => [
                    'min' => 1,
                    'max' => 30,
                ],
            ],
            'default' => [
                'size' => 1,
            ],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-column-span: {{VALUE}};',
            ]
        ]);

        $this->add_responsive_control('field_width', [
            'type'        => Controls::SLIDER,
            'label'       => __('Width', 'dragwyb-form-builder'),
            'range' => [
                'px' => [
                    'min' => 1,
                    'max' => 1000,
                ],
                '%' => [
                    'min' => 1,
                    'max' => 100,
                ],
                'em' => [
                    'min' => 1,
                    'max' => 100,
                ],
                'rem' => [
                    'min' => 1,
                    'max' => 100,
                ],
            ],
            'units' => ['px', '%', 'em', 'rem'],
            'default' => [
                'size' => 100,
                'unit' => '%',
            ],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-field-width: {{VALUE}}{{UNIT}};',
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
            // translators: %1$s is the opening bold tag, %2$s is the closing bold tag
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

        $tabs = apply_filters('Dragwyb/Editor/render_controls/header_tabs', $tabs);

        $header_tab['header_controls'] = array(
            'type' => 'tabs',
            'tabs' => $tabs
        );

        return $header_tab;
    }
}
