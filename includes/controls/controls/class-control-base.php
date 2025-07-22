<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Controls;

abstract class Control_Base
{
    protected string $type;
    protected string $name;
    private $value=null;

    abstract protected function register_scripts();
    abstract protected function register_style();
    abstract protected function init(): void;
    abstract protected function sanitize_control($value);

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

    public function set_value($data): void
    {
        $this->set_filter_value($data);
    }

    private function set_filter_value($data): void
    {
        $this->value=$this->sanitize_control($data);
    }

    public function get_value()
    {
        return $this->value;
    }
}
