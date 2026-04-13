<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules;

use Dragwyb\Form_Builder\Includes\Modules\Register\Register_Fields;
use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;
use Dragwyb\Form_Builder\Includes\Modules\Sanitize_Module_Settings\Sanitize_Module_Settings;
use Dragwyb\Form_Builder\Includes\Categories\Categories;
use Dragwyb\Form_Builder\Includes\Categories\Categories\Category_Base;

class Modules extends Toolbar_Base
{
    private static $instance = null;
    private $fields = [];
    private $root_containers = [];

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
        return __('Field', 'smart-form-builder-by-dragwyb');
    }

    protected function get_icon(): string
    {
        return 'fas fa-plus';
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


    protected function sanitize_data(array $data): array
    {
        $sanitize_module_data = new Sanitize_Module_Settings($data);
        $sanitize_data = $sanitize_module_data->get_data();
        $this->root_containers = $sanitize_module_data->get_root_containers();

        if ($sanitize_data && is_array($sanitize_data) && count($sanitize_data) > 0) {
            return $sanitize_data;
        }

        return array();
    }

    protected function get_settings(): array
    {
        $fields_data = $this->get_fields();
        $form_id = absint($this->get_form_id());
        $data = array();
        // translators: %s is the name of the module
        $data['label'] = sprintf(esc_html__('%s Settings', 'smart-form-builder-by-dragwyb'), sanitize_text_field($this->get_name()));
        $fields = [];

        $categories_object = Categories::instance();

        $register_categories = $categories_object->get_categories();

        $field_categories = array();

        foreach ($register_categories as $key => $category) {
            if (!isset($category) || !$category instanceof Category_Base) {
                continue;
            }

            $field_categories[$key] = array('name' => $category->get_name(), 'icon' => $category->get_icon(), 'fields' => array());
        }

        foreach ($fields_data as $key => $field) {
            $field_category = $field->get_category();

            if (!isset($field_categories[$field_category])) {
                continue;
            }


            $field->set_form_id($form_id);
            $field->enqueue_assets();

            $name = $field->get_name();
            $conrols = $field->render_controls();
            $icon = $field->get_icon();
            $keywords = $field->get_keywords();
            $is_root_container = $field->is_root_container();
            $allow_child = $field->get_allow_child();

            array_push($field_categories[$field_category]['fields'], $key);

            $fields[$key]['label'] = esc_html($name);
            $fields[$key]['icon'] = esc_attr($icon);
            $fields[$key]['controls'] = $conrols;

            if ($is_root_container === true) {
                $fields[$key]['is_root_container'] = true;
            }

            if ($allow_child === true) {
                $fields[$key]['allow_child'] = true;
            }

            if ($keywords && count($keywords) > 0) {
                $fields[$key]['keywords'] = $keywords;
            }
        }

        $data['fields'] = $fields;
        $data['categories'] = $field_categories;

        return $data;
    }

    protected function get_setting_instance(): string
    {
        return Settings::class;
    }

    public function get_root_containers(): array
    {
        return (is_array($this->root_containers) && count($this->root_containers) > 0)
            ? $this->root_containers
            : array();
    }
}
