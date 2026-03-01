<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Categories\Categories;

abstract class Category_Base
{
    protected string $id;
    protected string $name;
    protected string $icon;

    abstract protected function init(): void;

    public function __construct()
    {
        $this->init();
    }

    public function get_id(): string
    {
        return $this->id;
    }

    public function get_name(): string
    {
        return $this->name;
    }

    public function get_icon(): string
    {
        return $this->icon;
    }
}
