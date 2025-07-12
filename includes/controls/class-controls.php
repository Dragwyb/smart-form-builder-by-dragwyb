<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls;

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls;
use Dragwyb\Form_Builder\Includes\Controls\Control_Base;

class Controls
{
    private static $instance = null;
    private $controls = [];

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
        $this->load_controls();
    }

    private function load_controls(): void
    {
        // Register field types
        $this->register_default_controls();
    }

    private function register_default_controls(): void
    {
        // Load field registrations
        $register = Register_Controls::instance();
        $this->controls = $register->get_controls();
    }

    public function get_controls(): array
    {
        return $this->controls;
    }

    public function get_field($type): ?Control_Base
    {
        return $this->controls[$type] ?? null;
    }
}
