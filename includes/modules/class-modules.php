<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules;

use Dragwyb\Form_Builder\Includes\Modules\Register\Register_Fields;
use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;
use Dragwyb\Form_Builder\Includes\Modules\Sanitize_Module_Settings\Sanitize_Module_Settings;

class Modules extends Toolbar_Base
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

    protected function get_id(): string
    {
        return 'fields';
    }

    protected function get_name(): string
    {
        return __('Field', 'dragwyb-form-builder');
    }

    protected function get_icon(): string
    {
        return 'fas fa-plus';
    }

    protected function sanitize_data(array $data): array
    {
        $sanitize_module_data = Sanitize_Module_Settings::instance($data);
        $sanitize_data = $sanitize_module_data->get_data();

        if ($sanitize_data && is_array($sanitize_data) && count($sanitize_data) > 0) {
            return $sanitize_data;
        }
        return array();
    }

    public function __construct()
    {
        parent::__construct();
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
        $this->register_fields();
    }

    private function register_fields(): void
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

    protected function get_settings(): array
    {
        $fields_data = $this->get_fields();

        $fields = [];

        foreach ($fields_data as $key => $field) {
            $field->enqueue_assets();

            $name = $field->get_name();

            $conrols = $field->render_controls();

            $icon = $field->get_icon();

            $fields[$key]['label'] = esc_html($name);
            $fields[$key]['icon'] = esc_attr($icon);
            $fields[$key]['controls'] = $conrols;
        }

        return $fields;
    }
}
