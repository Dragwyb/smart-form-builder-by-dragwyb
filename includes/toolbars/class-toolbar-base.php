<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Toolbars;

abstract class Toolbar_Base
{
    private string $id;
    private string $name;
    private string $icon;
    private array $data = [];

    public function __construct()
    {
        $this->id   = $this->get_id();
        $this->name = $this->get_name();
        $this->icon = $this->get_icon();
    }

    /**
     * Each toolbar must define its unique ID
     */
    abstract protected function get_id(): string;

    /**
     * Each toolbar must define its display name
     */
    abstract protected function get_name(): string;

    /**
     * Each toolbar must define its icon class (e.g., FontAwesome or custom)
     */
    abstract protected function get_icon(): string;

    /**
     * Each toolbar must handle its own data sanitization
     */
    abstract protected function sanitize_data(array $data): array;

    /**
     * Each toolbar must handle its own data sanitization
     */
    abstract protected function get_settings(): array;

    /**
     * Quick accessors
     */
    public function get_toolbar_id(): string
    {
        return $this->id;
    }

    public function get_toolbar_name(): string
    {
        return esc_html($this->name);
    }

    public function get_toolbar_icon(): string
    {
        return esc_attr($this->icon);
    }
    
    public function get_toolbar_settings(): array{
        return $this->get_settings();
    }

    public function get_toolbar_data(): array
    {
        return $this->data;
    }

    /**
     * Update the toolbar data with sanitization
     */
    public function set_toolbar_data (array $data): void
    {
        $this->data = $this->sanitize_data($data);
    }

    /**
     * Static constructor (like Control_Base::newInstance)
     */
    public static function newInstance(): static
    {
        return new static();
    }

    public function __destruct()
    {
        $this->id   = '';
        $this->name = '';
        $this->icon = '';
        $this->data = [];
    }
}
