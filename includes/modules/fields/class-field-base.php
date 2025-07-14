<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

abstract class Field_Base
{
    protected string $type;
    protected string $name;
    protected string $icon;
    protected array $settings;
    private ?string $current_section = null;
    private ?string $current_tabs = null;
    private ?string $current_tab = null;
    private ?array $display_settings = array();
    private ?array $settings_arr = array();
    private ?array $current_section_stack = array();
    private ?array $current_tabs_stack = array();
    private ?array $current_control_stack = array();

    const ContentTab = 'content_tab';
    const StyleTab = 'style_tab';
    const AdvanceTab = 'advance_tab';


    protected array $controls;

    abstract protected function register_scripts();
    abstract protected function register_style();

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

    public function __construct()
    {
        $this->init();
    }

    abstract protected function init(): void;

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

    public function get_settings(): array
    {
        return $this->settings;
    }

    protected function set_display_setting(array $setting)
    {
        $this->display_settings = $setting;
    }

    protected function get_display_setting(array $setting): array
    {
        return $this->display_settings;
    }

    protected function start_section(string $id = '', array $data = array()): void
    {
        if ($this->current_section !== null) {
            throw new \Exception(__('A section is already started.', 'dragwyb-form-builder'));
        }

        if (isset($this->settings_arr[$id])) {
            throw new \Exception(__('Do not use duplicate section ID use unique Id.', 'dragwyb-form-builder'));
        }

        $this->current_section = $id; // Assuming type is the section identifier

        $conditions = isset($data['conditions']) ? $data['conditions'] : array();

        if ((isset($data['tab']) && !empty($data['tab']))) {
            $conditions['header_controls'] = $data['tab'];
        } else if (!isset($data['tab']) || empty($data['tab'])) {
            $conditions['header_controls'] = self::ContentTab;
        }

        $this->settings_arr[$this->current_section] = array_merge($data, array('type' => 'section', 'conditions' => $conditions));
    }

    protected function end_section(): void
    {
        if ($this->current_section === null) {
            throw new \Exception(__('No section is currently open.', 'dragwyb-form-builder'));
        }

        $this->settings_arr = array_merge($this->settings_arr, $this->current_section_stack, $this->current_control_stack);


        $this->current_section = null;

        $this->current_control_stack = array();
        $this->current_section_stack = array();
    }

    protected function start_tabs(string $id = '', array $data = array()): void
    {
        if ($this->current_section === null) {
            throw new \Exception(__('No section is currently open.', 'dragwyb-form-builder'));
        }

        if ($this->current_tabs !== null) {
            throw new \Exception(__('Tabs are already started.', 'dragwyb-form-builder'));
        }

        if ($this->current_section_stack && isset($this->current_section_stack[$id])) {
            throw new \Exception(__('Do not use duplicate tabs ID use unique Id.', 'dragwyb-form-builder'));
        }

        $this->current_section_stack = array_merge($this->current_section_stack, $this->current_control_stack);

        $this->current_control_stack = array();

        $this->current_tabs = $id; // Assuming type is the tabs identifier  

        $conditions = isset($data['conditions']) ? $data['conditions'] : array();
        $conditions['section'] = $this->current_section;

        if (isset($this->settings_arr[$this->current_section]['conditions'])) {
            $conditions = array_merge($conditions, $this->settings_arr[$this->current_section]['conditions']);
        }

        $this->current_section_stack[$this->current_tabs] = array_merge($data, array('type' => 'tabs', 'conditions' => $conditions));
    }

    protected function end_tabs(): void
    {
        if ($this->current_tabs === null) {
            throw new \Exception(__('No tabs are currently open.', 'dragwyb-form-builder'));
        }

        $this->current_section_stack[$this->current_tabs]['tabs'] = $this->current_tabs_stack;

        $this->current_section_stack = array_merge($this->current_section_stack, $this->current_control_stack);

        $this->current_tabs = null;
        $this->current_tabs_stack = array();
        $this->current_control_stack = array();
    }

    protected function start_tab(string $id = '', array $data = array()): void
    {
        if ($this->current_section === null) {
            throw new \Exception(__('No section is currently open.', 'dragwyb-form-builder'));
        }

        if ($this->current_tabs === null) {
            throw new \Exception(__('Tabs must be started before a tab can be opened.', 'dragwyb-form-builder'));
        }

        if ($this->current_tabs_stack && isset($this->current_tabs_stack[$id])) {
            throw new \Exception(__('Do not use duplicate tab ID use unique Id.', 'dragwyb-form-builder'));
        }

        if ($this->current_tab !== null) {
            throw new \Exception(__('Tab are already started.', 'dragwyb-form-builder'));
        }
        $this->current_tab = $id; // Assuming type is the tabs identifier

        $this->current_tabs_stack[$id] = array_merge(array('type' => 'tab'), $data);
    }

    protected function end_tab(): void
    {
        if ($this->current_tab === null) {
            throw new \Exception(__('No tab are currently open.', 'dragwyb-form-builder'));
        }

        $this->current_tab = null;
    }

    protected function add_control(string $id = '', array $data = array()): void
    {
        if ($this->current_section === null) {
            throw new \Exception(__('No section is currently open to add controls.', 'dragwyb-form-builder'));
        }

        if (isset($this->settings_arr[$id]) || isset($this->current_control_stack[$id])) {
            throw new \Exception(__('Do not use duplicate control ID use unique Id.', 'dragwyb-form-builder'));
        }

        $conditions = isset($data['conditions']) ? $data['conditions'] : array();

        if (isset($this->current_section_stack[$this->current_tabs]['conditions'])) {
            $conditions = array_merge($this->current_section_stack[$this->current_tabs]['conditions'], $conditions);
            $conditions[$this->current_tabs] = $this->current_tab;
        } else if (isset($this->settings_arr[$this->current_section]['conditions'])) {
            $conditions = array_merge($conditions, $this->settings_arr[$this->current_section]['conditions']);
            $conditions['section'] = $this->current_section;
        }

        $this->current_control_stack[$id] = array_merge($data, array('conditions' => $conditions));
    }

    // abstract public function render_admin(): string;
    abstract public function render_frontend(array $field_data): string;
    abstract public function validate($value): bool;
    abstract public function sanitize($value);
    abstract protected function register_controls(): void;

    public function render_controls()
    {

        $this->render_header_controls();

        $this->register_controls();

        return $this->settings_arr;
    }

    private function render_header_controls()
    {
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

        $this->settings_arr = $header_tab;
    }

    protected function get_default_settings(): array
    {
        return [
            'label' => [
                'type' => 'text',
                'label' => __('Field Label', 'dragwyb-form-builder'),
                'default' => '',
            ],
            'placeholder' => [
                'type' => 'text',
                'label' => __('Placeholder', 'dragwyb-form-builder'),
                'default' => '',
            ],
            'required' => [
                'type' => 'checkbox',
                'label' => __('Required', 'dragwyb-form-builder'),
                'default' => false,
            ],
            'css_class' => [
                'type' => 'text',
                'label' => __('CSS Class', 'dragwyb-form-builder'),
                'default' => '',
            ],
        ];
    }

    protected function get_conditional_logic_settings(): array
    {
        return [
            'enable_conditional' => [
                'type' => 'checkbox',
                'label' => __('Enable Conditional Logic', 'dragwyb-form-builder'),
                'default' => false,
            ],
            'conditional_rules' => [
                'type' => 'repeater',
                'label' => __('Rules', 'dragwyb-form-builder'),
                'default' => [],
                'fields' => [
                    'field' => [
                        'type' => 'select',
                        'label' => __('Field', 'dragwyb-form-builder'),
                        'dynamic_options' => true, // Will be populated with form fields
                    ],
                    'operator' => [
                        'type' => 'select',
                        'label' => __('Operator', 'dragwyb-form-builder'),
                        'options' => [
                            'equals' => __('Equals', 'dragwyb-form-builder'),
                            'not_equals' => __('Not Equals', 'dragwyb-form-builder'),
                            'contains' => __('Contains', 'dragwyb-form-builder'),
                            'not_contains' => __('Not Contains', 'dragwyb-form-builder'),
                            'greater_than' => __('Greater Than', 'dragwyb-form-builder'),
                            'less_than' => __('Less Than', 'dragwyb-form-builder'),
                        ],
                    ],
                    'value' => [
                        'type' => 'text',
                        'label' => __('Value', 'dragwyb-form-builder'),
                    ],
                ],
            ],
            'conditional_action' => [
                'type' => 'select',
                'label' => __('Action', 'dragwyb-form-builder'),
                'default' => 'show',
                'options' => [
                    'show' => __('Show', 'dragwyb-form-builder'),
                    'hide' => __('Hide', 'dragwyb-form-builder'),
                ],
            ],
            'conditional_logic' => [
                'type' => 'select',
                'label' => __('Logic', 'dragwyb-form-builder'),
                'default' => 'all',
                'options' => [
                    'all' => __('All rules must match', 'dragwyb-form-builder'),
                    'any' => __('Any rule must match', 'dragwyb-form-builder'),
                ],
            ],
        ];
    }
}
