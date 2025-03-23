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

    protected array $controls;

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

    protected function start_section(): void
    {
        if ($this->current_section !== null) {
            throw new \Exception(__('A section is already started.', 'dragwyb-form-builder'));
        }
        $this->current_section = $this->type; // Assuming type is the section identifier
    }

    protected function end_section(): void
    {
        if ($this->current_section === null) {
            throw new \Exception(__('No section is currently open.', 'dragwyb-form-builder'));
        }
        $this->current_section = null;
    }

    protected function start_tabs(): void
    {
        if ($this->current_section === null) {
            throw new \Exception(__('No section is currently open.', 'dragwyb-form-builder'));
        }

        if ($this->current_tabs !== null) {
            throw new \Exception(__('Tabs are already started.', 'dragwyb-form-builder'));
        }
        $this->current_tabs = $this->type; // Assuming type is the tabs identifier
    }

    protected function end_tabs(): void
    {
        if ($this->current_tabs === null) {
            throw new \Exception(__('No tabs are currently open.', 'dragwyb-form-builder'));
        }
        $this->current_tabs = null;
    }

    protected function start_tab(): void
    {
        if ($this->current_section === null) {
            throw new \Exception(__('No section is currently open.', 'dragwyb-form-builder'));
        }

        if ($this->current_tabs === null) {
            throw new \Exception(__('Tabs must be started before a tab can be opened.', 'dragwyb-form-builder'));
        }

        if ($this->current_tab !== null) {
            throw new \Exception(__('Tab are already started.', 'dragwyb-form-builder'));
        }
        $this->current_tabs = $this->type; // Assuming type is the tabs identifier
    }

    protected function end_tab(): void
    {
        if ($this->current_tab === null) {
            throw new \Exception(__('No tab are currently open.', 'dragwyb-form-builder'));
        }
        $this->current_tabs = null;
    }

    protected function add_control(): void
    {
        if ($this->current_section === null) {
            throw new \Exception(__('No section is currently open to add controls.', 'dragwyb-form-builder'));
        }
    }

    // abstract public function render_admin(): string;
    abstract public function render_frontend(array $field_data): string;
    abstract public function validate($value): bool;
    abstract public function sanitize($value);

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
