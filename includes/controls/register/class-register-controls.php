<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Controls\Register;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;

class Register_Controls
{
    private static $instance = null;

    private array $controls = [];

    private array $group_controls = [];

    private array $default_controls = [Controls::CHOOSE, Controls::COLOR, Controls::DIMENSIONS, Controls::FONTS, Controls::GALLERY, Controls::IMAGE, Controls::HEADING, Controls::ICON, Controls::NUMBER, Controls::RADIO, Controls::RAW_HTML, Controls::REPEATER, Controls::SECTION, Controls::SELECT, Controls::SLIDER, Controls::SWITCHER, Controls::TABS, Controls::TAB, Controls::TEXT, Controls::TEXTAREA, Controls::POPOVER_TOGGLE, Controls::URL];

    private array $default_group_controls = [Controls::GROUP_TYPOGRAPHY, Controls::GROUP_BORDER, Controls::GROUP_BOX_SHADOW, Controls::GROUP_TEXT_SHADOW, Controls::GROUP_CSS_FILTER, Controls::GROUP_BACKGROUND];

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
        $this->register_default_group_controls();

        do_action('Dragwyb/register_controls', $this);
    }


    private function register_default_controls(): void
    {
        foreach ($this->default_controls as $control) {

            $dir = dirname(__NAMESPACE__);
            $control = $this->captialize_class_name($control);
            $class = $dir . '\Controls\Control_' . ucfirst(esc_html($control));

            if (class_exists($class)) {
                $this->register_control(new $class());
            }
        }
    }

    private function register_default_group_controls(): void
    {
        foreach ($this->default_group_controls as $control) {

            $dir = dirname(__NAMESPACE__);
            $control = $this->captialize_class_name($control);
            $class = $dir . '\Group\\' . $control . '\\Control_' . ucfirst(esc_html($control));

            if (class_exists($class)) {
                $this->register_group_control(new $class());
            }
        }
    }

    private function captialize_class_name($string)
    {
        // Replace hyphens with underscores
        $string = str_replace('-', '_', $string);

        // Capitalize first letter and letters after underscores
        return preg_replace_callback('/(^|_)([a-z])/', function ($matches) {
            return $matches[1] . strtoupper($matches[2]);
        }, $string);
    }

    public function register_control(Control_Base $field): void
    {
        $this->controls[$field->get_type()] = $field;
    }

    public function register_group_control(Control_Base $field): void
    {
        $this->group_controls[$field->get_type()] = $field;
    }

    public function get_controls(): array
    {
        return $this->controls;
    }

    public function get_group_controls(): array
    {
        return $this->group_controls;
    }
}
