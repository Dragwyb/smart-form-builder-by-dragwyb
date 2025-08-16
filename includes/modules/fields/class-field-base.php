<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;
use PSpell\Config;

abstract class Field_Base
{
    protected string $type;
    protected string $name;
    protected string $icon;
    protected array $settings;
    protected array $keywords;
    private ?string $current_section = null;
    private ?string $current_tabs = null;
    private ?string $current_tab = null;
    private ?array $display_settings = array();
    private ?array $settings_arr = array();
    private ?array $current_section_stack = array();
    private ?array $current_tabs_stack = array();
    private ?array $current_control_stack = array();
    private ?object $control_base;
    private $form_id = 0;

    const ContentTab = 'content_tab';
    const StyleTab = 'style_tab';
    const AdvanceTab = 'advance_tab';

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
        $this->control_base = Controls::instance();
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

    public function set_the_id(int $id): void
    {
        $this->form_id = (int) $id;
    }

    public function get_the_id(): int
    {
        return $this->form_id;
    }

    public function get_settings(): array
    {
        return $this->settings_arr;
    }

    public function set_field_settings(array $setting)
    {
        $this->display_settings = $setting;
    }

    protected function get_field_settings(): array
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

        $this->settings_arr[$this->current_section] = $this->controller_settings(array_merge($data, array('type' => 'section', 'conditions' => $conditions)));
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

        $this->current_section_stack[$this->current_tabs] = $this->controller_settings(array_merge($data, array('type' => 'tabs', 'conditions' => $conditions)));
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

        $this->current_tabs_stack[$id] = $this->controller_settings(array_merge(array('type' => 'tab'), $data));
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

        $this->current_control_stack[$id] = $this->controller_settings(array_merge($data, array('conditions' => $conditions)));
    }

    private function controller_settings(array $data): array
    {
        $controls_class = Controls::class;
        $controls_base_class = Control_Base::class;

        if (!isset($data['type']) || !($this->control_base instanceof $controls_class)) {
            return array();
        }

        $type = $data['type'];

        $control_object = $this->control_base->get_control($type);

        if (!$control_object || !($control_object instanceof $controls_base_class)) {
            return array();
        }

        $control_object = $control_object->newInstance();

        $control_object->set_settings($data);

        $value = $control_object->get_settings();

        return $value;
    }

    abstract protected function render_field();
    abstract public function validate($value): bool;
    abstract protected function register_controls(): void;

    public function render_controls()
    {

        $this->render_header_controls();

        $this->register_controls();

        return $this->get_settings();
    }

    public function render()
    {
        $this->render_field();
    }

    public function get_control($id)
    {
        return $this->get_control_by_id($id);
    }

    private function get_control_by_id($id)
    {
        $controls = $this->get_settings();

        if ($controls && isset($controls[$id])) {
            return $controls[$id];
        }

        return false;
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
}
