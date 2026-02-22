<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Group;

use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;

abstract class Group_Control_Base extends Control_Base
{
    protected string $icon = '';
    protected string $id = '';
    protected array $data = [];
    private array $settings_array = [];

    abstract protected function register_group_controls(): void;

    final public function get_icon(): string
    {
        return $this->icon;
    }

    final public function register_controls(string $id, array $data = []): void
    {
        $this->settings_array = [];
        $this->id = $this->string_sanitize($id);
        $this->data = $data;
        $this->register_group_controls();
    }

    protected function add_control(string $id, array $data): void
    {
        if (isset($this->settings_array[$id])) {
            return;
        }

        $this->settings_array[$id] = $data;
    }

    protected function add_responsive_control(string $id, array $data): void
    {
        $responsive_types = ['desktop', 'tablet', 'mobile'];

        foreach ($responsive_types as $index => $responsive_type) {

            if ('desktop' !== $responsive_type && isset($data[$responsive_type . '_default'])) {
                $data['default'] = $data[$responsive_type . '_default'];
            } else if ('desktop' !== $responsive_type) {
                unset($data['default']);
            }

            $data['responsive_type'] = $responsive_type;
            $data['responsive_control'] = true;

            $control_id = $id;

            if ('desktop' !== $responsive_type) {
                $control_id .= '_' . $responsive_type;
            }

            $this->add_control($control_id, $data);
        }
    }

    public function get_settings(): array
    {
        return $this->settings_array;
    }
}
