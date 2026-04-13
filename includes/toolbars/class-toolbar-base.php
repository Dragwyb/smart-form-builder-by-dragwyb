<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Toolbars;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

abstract class Toolbar_Base
{
    private string $id;
    private string $name;
    private string $icon;
    private array $data = [];
    private static int $form_id = 0;
    protected $toolbar_settings = null;
    private $root_containers = [];

    public function __construct()
    {
        $this->id   = $this->get_id();
        $this->name = $this->get_name();
        $this->icon = $this->get_icon();
    }


    final public function set_form_id(int $id = 0): void
    {
        $form_id = absint(sanitize_text_field($id));
        self::$form_id = $form_id;
    }

    protected function get_form_id(): int
    {
        return self::$form_id;
    }

    public function enqueue_assets()
    {
        wp_register_script(
            'dragwyb-editor-toolbars',
            esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/dist/toolbars/toolbars.js'),
            ['dragwyb-form-editor'],
            esc_attr(DRAGWYB_FORM_BUILDER_VERSION),
            true
        );

        if (!wp_script_is('dragwyb-editor-toolbars', 'enqueued')) {
            wp_enqueue_script('dragwyb-editor-toolbars');
        }
    }

    /**
     * Each toolbar must define its unique ID
     */
    abstract protected function get_id(): string;

    /**
     * Each toolbar must define its display name
     */
    abstract protected function get_name(): string;

    /**
     * Each toolbar must define its icon class (e.g., FontAwesome or custom)
     */
    abstract protected function get_icon(): string;

    abstract protected function get_setting_instance(): string;

    /**
     * Each toolbar must handle its own data sanitization
     */
    protected function get_settings(): array
    {
        $settings = $this->toolbar_settings;
        $data = array();
        $form_id = absint($this->get_form_id());

        $setting_instance = $this->get_setting_instance();

        if ($settings instanceof $setting_instance) {
            $settings->set_form_id($form_id);
            $conrols = $settings->render_controls();
            // translators: %s is the name of the toolbar
            $data['label'] = sprintf(esc_html__('%s Settings', 'smart-form-builder-by-dragwyb'), sanitize_text_field($this->get_name()));

            if ($conrols && count($conrols) > 0) {
                $data['controls'] = $conrols;
            }
        }

        return $data;
    }

    /**
     * Update toolbar call update settings
     */
    protected function update_toolbar(): void {}

    final public function settings_updated(): void
    {
        $this->update_toolbar();
    }

    /**
     * Quick accessors
     */
    public function get_toolbar_id(): string
    {
        return $this->id;
    }

    public function get_toolbar_name(): string
    {
        return esc_html($this->name);
    }

    public function get_toolbar_icon(): string
    {
        return esc_attr($this->icon);
    }

    public function get_toolbar_settings(): array
    {
        return $this->get_settings();
    }

    public function get_toolbar_data(): array
    {
        return $this->data;
    }

    public function get_root_containers(): array
    {
        return (is_array($this->root_containers) && count($this->root_containers) > 0)
            ? $this->root_containers
            : array();
    }

    /**
     * Update the toolbar data with sanitization
     */
    public function set_toolbar_data(array $data): void
    {
        $this->data = $this->sanitize_data($data);
    }

    public function get_display_settings(): array
    {
        return $this->get_toolbar_data();
    }

    /**
     * Each toolbar must handle its own data sanitization
     */
    protected function sanitize_data(array $data): array
    {
        $settings = $this->get_settings();
        $sanitize_data = new Sanitize_Data($data, $settings['controls']);
        $sanitize_data = $sanitize_data->get_data();

        if ($sanitize_data && is_array($sanitize_data) && count($sanitize_data) > 0) {
            return $sanitize_data;
        }

        return array();
    }

    /**
     * Create a new instance of the called class.
     *
     * @return static
     */
    public static function newInstance()
    {
        return new static();
    }

    public function __destruct()
    {
        $this->id   = '';
        $this->name = '';
        $this->icon = '';
        $this->data = [];
    }
}
