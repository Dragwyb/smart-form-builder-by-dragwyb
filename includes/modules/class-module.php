<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules;

use Dragwyb\Form_Builder\Includes\Modules\Register\Register_Fields;
use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;

class Module
{
    private static $instance = null;
    private $fields = [];

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->init();
    }

    private function init(): void
    {
        // Load and register all field types
        $this->load_fields();
    }

    private function load_fields(): void
    {
        // Register field types
        $this->register_default_fields();
    }

    private function register_default_fields(): void
    {
        // Load field registrations
        $register = Register_Fields::instance();
        $this->fields = $register->get_fields();
    }

    public function get_fields(): array
    {
        return $this->fields;
    }

    public function get_field($type): ?Field_Base
    {
        return $this->fields[$type] ?? null;
    }
}
