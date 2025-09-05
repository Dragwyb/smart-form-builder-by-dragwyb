<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls;

use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;

abstract class Register_Controls_Base
{
    private ?string $current_section = null;
    private ?string $current_tabs = null;
    private ?string $current_tab = null;
    private ?array $settings_arr = array();
    private ?array $current_section_stack = array();
    private ?array $current_tabs_stack = array();
    private ?array $current_control_stack = array();
    private ?array $current_popover = array();
    private ?object $control_base;
    private static ?int $form_id = 0;

    public function __construct()
    {
        $this->control_base = Controls::instance();
    }

    final public function set_form_id(int $id = 0): void
    {
        $form_id = absint(sanitize_text_field($id));
        self::$form_id = $form_id;
    }

    protected function get_form_id(): int
    {
        return self::$form_id;
    }

    protected function tab_condition(&$conditions, $data)
    {
        return $conditions;
    }

    protected function header_controls(): array
    {
        return array();
    }

    final public function get_settings(): array
    {
        return $this->settings_arr;
    }

    public static function validate_id($id, $type)
    {
        if (!is_string($id) || !preg_match('/^[A-Za-z0-9_]+$/', $id)) {
            throw new \Exception(sprintf(__('%s ID must only contain letters, numbers, and underscores.', 'dragwyb-form-builder'), $type));

            return false;
        }

        return sanitize_text_field($id);
    }

    final protected function start_section(string $id = '', array $data = array()): void
    {
        if (!$id = self::validate_id($id, 'Section')) return;

        if ($this->current_section !== null) {
            throw new \Exception(__('A section is already started.', 'dragwyb-form-builder'));
        }

        if (isset($this->settings_arr[$id])) {
            throw new \Exception(__('Do not use duplicate section ID use unique Id.', 'dragwyb-form-builder'));
        }

        $this->current_section = $id; // Assuming type is the section identifier

        $conditions = isset($data['conditions']) ? $data['conditions'] : array();

        $conditions = $this->tab_condition($conditions, $data);


        $this->settings_arr[$this->current_section] = $this->controller_settings(array_merge($data, array('type' => 'section', 'conditions' => $conditions)));
    }

    final protected function end_section(): void
    {
        if ($this->current_section === null) {
            throw new \Exception(__('No section is currently open.', 'dragwyb-form-builder'));
        }

        $this->settings_arr = array_merge($this->settings_arr, $this->current_section_stack, $this->current_control_stack);


        $this->current_section = null;

        $this->current_control_stack = array();
        $this->current_section_stack = array();
    }

    final protected function start_tabs(string $id = '', array $data = array()): void
    {
        if (!$id = self::validate_id($id, 'Tabs')) return;

        if ($this->current_section === null) {
            throw new \Exception(__('No section is currently open.', 'dragwyb-form-builder'));
        }

        if ($this->current_tabs !== null) {
            throw new \Exception(__('Tabs are already started.', 'dragwyb-form-builder'));
        }

        if ($this->current_section_stack && isset($this->current_section_stack[$id])) {
            throw new \Exception(__('Do not use duplicate tabs ID use unique Id.', 'dragwyb-form-builder'));
        }

        $this->current_tabs = $id; // Assuming type is the tabs identifier  

        $conditions = isset($data['conditions']) ? $data['conditions'] : array();
        $conditions['section'] = $this->current_section;

        if (isset($this->settings_arr[$this->current_section]['conditions'])) {
            $conditions = array_merge($conditions, $this->settings_arr[$this->current_section]['conditions']);
        }

        $this->current_section_stack[$this->current_tabs] = $this->controller_settings(array_merge($data, array('type' => 'tabs', 'conditions' => $conditions)));
    }

    final protected function end_tabs(): void
    {
        if ($this->current_tabs === null) {
            throw new \Exception(__('No tabs are currently open.', 'dragwyb-form-builder'));
        }

        $this->current_section_stack[$this->current_tabs]['tabs'] = $this->current_tabs_stack;

        $this->current_section_stack = array_merge($this->current_section_stack, $this->current_control_stack);

        $this->current_tabs = null;
        $this->current_tabs_stack = array();
        unset($this->current_control_stack[$this->current_tabs]);
    }

    final protected function start_tab(string $id = '', array $data = array()): void
    {
        if (!$id = self::validate_id($id, 'Tab')) return;

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

    final protected function end_tab(): void
    {
        if ($this->current_tab === null) {
            throw new \Exception(__('No tab are currently open.', 'dragwyb-form-builder'));
        }

        $this->current_tab = null;
    }

    final protected function start_popover(): void
    {
        if ($this->current_section === null) {
            throw new \Exception(__('No section is currently open.', 'dragwyb-form-builder'));
        }

        if (isset($this->current_popover['initialize'])) {
            throw new \Exception(__('Popover are already started.', 'dragwyb-form-builder'));
        }

        $this->current_popover['initialize'] = false;
    }

    final protected function end_popover(): void
    {
        if (!isset($this->current_popover['initialize'])) {
            throw new \Exception(__('No popover are currently open.', 'dragwyb-form-builder'));
        }

        $last_control = $this->get_last_control();

        if(isset($this->current_control_stack[$last_control['key']])){
            $this->current_control_stack[$last_control['key']]['popover'] = array('end'=>true);
        }

        $this->current_popover = array();
    }

    final protected function add_control(string $id = '', array $data = array()): void
    {
        if (!$id = self::validate_id($id, 'Control')) return;

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
        } else {
            $conditions = array_merge($conditions, array('section' => $this->current_section));
        }

        $control_data=$this->controller_settings(array_merge($data, array('conditions' => $conditions)));

        if (isset($this->current_popover['initialize']) && !isset($control_data['popover'])) {
            $control_data['popover'] = array();

            if (false === $this->current_popover['initialize']) {
                $control_data['popover']['start'] = true;
                $this->current_popover['initialize'] = true;
            }
        }

        $this->current_control_stack[$id] = $control_data;
    }

    private function get_last_control(): array
    {
        $keys = array_keys($this->current_control_stack);
        $last_key = end($keys);

        return array("key" => $last_key, "value" => $this->current_control_stack[$last_key]);
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

    abstract protected function register_controls(): void;

    final public function render_controls()
    {
        $this->settings_arr = $this->header_controls();

        $this->register_controls();

        return $this->get_settings();
    }

    final public function get_control($id)
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
}
