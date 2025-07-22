<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Register;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;

class Register_Controls
{
    private static $instance = null;

    private array $controls = [];

    private array $default_controls = [Controls::CHECKBOX,Controls::COLOR,Controls::NUMBER,Controls::RADIO,Controls::REPEATER,Controls::SECTION,Controls::SELECT,Controls::SLIDER,Controls::TABS,Controls::TEXT,Controls::TEXTAREA];

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->register_default_controls();

        do_action('Dragwyb/register_controls', $this);
    }


    private function register_default_controls(): void
    {
        foreach ($this->default_controls as $control) {

            $dir = dirname(__NAMESPACE__);
            $class = $dir . '\Controls\Control_' . ucfirst(esc_html($control));

            if (class_exists($class)) {
                $this->register_control(new $class());
            }
        }
        // Register more controls here
    }

    public function register_control(Control_Base $field): void
    {
        $this->controls[$field->get_type()] = $field;
    }

    public function get_controls(): array
    {
        return $this->controls;
    }
}
