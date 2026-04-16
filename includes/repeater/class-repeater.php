<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Repeater;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;

class Repeater
{
    private ?string $current_section = null;
    private ?string $current_tabs = null;
    private ?string $current_tab = null;
    private ?array $settings_arr = array();
    private ?array $current_section_stack = array();
    private ?array $current_tabs_stack = array();
    private ?array $current_control_stack = array();
    private ?object $control_base;

    public function __construct()
    {
        $this->control_base = Controls::instance();
    }

    public static function validate_id($id, $type)
    {
        if (!is_string($id) || !preg_match('/^[A-Za-z0-9_]+$/', $id)) {
            // translators: %s is the type of the control
            throw new \Exception(sprintf(esc_html__('%s ID must only contain letters, numbers, and underscores.', 'smart-form-builder-by-dragwyb'), esc_html($type)));

            return false;
        }

        return sanitize_text_field($id);
    }

    final public function start_tabs(string $id = '', array $data = array()): void
    {
        if (!$id = self::validate_id($id, 'Tabs')) return;

        if ($this->current_tabs !== null) {
            throw new \Exception(esc_html__('Tabs are already started.', 'smart-form-builder-by-dragwyb'));
        }

        $this->current_tabs = $id; // Assuming type is the tabs identifier  

        $conditions = isset($data['conditions']) ? $data['conditions'] : array();

        if (isset($this->settings_arr[$this->current_section]['conditions'])) {
            $conditions = array_merge($conditions, $this->settings_arr[$this->current_section]['conditions']);
        }

        if (!isset($data['name'])) {
            $data['name'] = $id;
        }

        $this->current_section_stack[$this->current_tabs] = $this->controller_settings(array_merge($data, array('type' => 'tabs', 'conditions' => $conditions)));
    }

    final public function end_tabs(): void
    {
        if ($this->current_tabs === null) {
            throw new \Exception(esc_html__('No tabs are currently open.', 'smart-form-builder-by-dragwyb'));
        }

        $this->current_section_stack[$this->current_tabs]['tabs'] = $this->current_tabs_stack;

        $this->settings_arr = array_merge($this->settings_arr, $this->current_section_stack, $this->current_control_stack);

        $this->current_tabs = null;
        $this->current_tabs_stack = array();
        $this->current_control_stack = array();
        $this->current_section_stack = array();
    }

    final public function start_tab(string $id = '', array $data = array()): void
    {
        if (!$id = self::validate_id($id, 'Tab')) return;

        if ($this->current_tabs === null) {
            throw new \Exception(esc_html__('Tabs must be started before a tab can be opened.', 'smart-form-builder-by-dragwyb'));
        }

        if ($this->current_tabs_stack && isset($this->current_tabs_stack[$id])) {
            throw new \Exception(esc_html__('Do not use duplicate tab ID use unique Id.', 'smart-form-builder-by-dragwyb'));
        }

        if ($this->current_tab !== null) {
            throw new \Exception(esc_html__('Tab are already started.', 'smart-form-builder-by-dragwyb'));
        }
        $this->current_tab = $id; // Assuming type is the tabs identifier

        $this->current_tabs_stack[$id] = $this->controller_settings(array_merge(array('type' => 'tab'), $data));
    }

    final public function end_tab(): void
    {
        if ($this->current_tab === null) {
            throw new \Exception(esc_html__('No tab are currently open.', 'smart-form-builder-by-dragwyb'));
        }

        $this->current_tab = null;
    }

    final public function add_control(string $id = '', array $data = array()): void
    {
        if (!$id = self::validate_id($id, 'Control')) return;

        if (isset($this->settings_arr[$id]) || isset($this->current_control_stack[$id])) {
            throw new \Exception(esc_html__('Do not use duplicate control ID use unique Id.', 'smart-form-builder-by-dragwyb'));
        }

        if (!isset($data['name'])) {
            $data['name'] = $id;
        }

        $conditions = isset($data['conditions']) ? $data['conditions'] : array();

        if ($this->current_tabs && isset($this->current_section_stack[$this->current_tabs])) {
            if (isset($this->current_section_stack[$this->current_tabs]['conditions'])) {
                $conditions = array_merge($this->current_section_stack[$this->current_tabs]['conditions'], $conditions);
                $conditions[$this->current_tabs] = $this->current_tab;
            }

            if (!isset($conditions[$this->current_tabs])) {
                $conditions[$this->current_tabs] = $this->current_tab;
            }
            $this->current_control_stack[$id] = $this->controller_settings(array_merge($data, array('conditions' => $conditions)));
        } else {
            $this->settings_arr = array_merge($this->settings_arr, array($this->controller_settings(array_merge($data, array('conditions' => $conditions)))));
        }
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

    final public function get_settings(): array
    {
        return $this->settings_arr;
    }
}
