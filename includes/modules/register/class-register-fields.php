<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Register;

use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;

class Register_Fields
{
    private static $instance = null;

    private array $fields = [];

    private array $default_fields = ['date', 'text', 'email', 'file', 'radio', 'textarea', 'select'];

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->register_default_fields();

        do_action('Dragwyb/register_fields', $this);
    }


    private function register_default_fields(): void
    {
        foreach ($this->default_fields as $field) {

            $dir = dirname(__NAMESPACE__);
            $class = $dir . '\Fields\Field_' . ucfirst(esc_html($field));

            if (class_exists($class)) {
                $this->register_field(new $class());
            }
        }
        // Register more fields here
    }

    public function register_field(Field_Base $field): void
    {
        $this->fields[$field->get_type()] = $field;
    }

    public function get_fields(): array
    {
        return $this->fields;
    }
}
