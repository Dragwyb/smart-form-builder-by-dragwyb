<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Register;

use Dragwyb\Form_Builder\Includes\Helper\Helper;
use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;

class Register_Fields
{
    private static $instance = null;

    private array $fields = [];

    private array $default_fields = ['button', 'checkbox', 'date', 'text', 'email', 'hidden', 'number', 'radio', 'textarea', 'select', 'row', 'html', 'section', 'url', 'phone', 'name', 'address', 'time', 'range', 'captcha', 'file'];

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

        do_action('Dragwyb/form_builder/fields/register', $this);
    }


    private function register_default_fields(): void
    {
        foreach ($this->default_fields as $field) {

            $dragwyb_name_space = Helper::namespace_into_dir_path(__NAMESPACE__);
            $dir   = dirname($dragwyb_name_space);
            $dir = Helper::dir_path_into_namespace($dir);

            $class = $dir . '\Fields\Field_' . ucfirst(esc_html($field));

            if (class_exists($class)) {
                $this->register_field(new $class());
            }
        }
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
