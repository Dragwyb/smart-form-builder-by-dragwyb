<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls;

use Dragwyb\Form_Builder\Includes\Controls\Register\register_controls;
use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;

class Controls
{
    const CHECKBOX='checkbox';
    const COLOR='color';
    const NUMBER='number';
    const RADIO='radio';
    const REPEATER='repeater';
    const SECTION='section';
    const SELECT='select';
    const SLIDER='slider';
    const TABS='tabs';
    const TAB='tab';
    const TEXT='text';
    const TEXTAREA='textarea';
    
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
        // Register control
        $this->register_controls();
    }

    private function register_controls(): void
    {
        // Load field registrations
        $register = Register_Controls::instance();
        $this->controls = $register->get_controls();
    }

    public function get_controls(): array
    {
        return $this->controls;
    }

    public function get_control($type): ?Control_Base
    {
        return $this->controls[$type] ?? null;
    }
}
