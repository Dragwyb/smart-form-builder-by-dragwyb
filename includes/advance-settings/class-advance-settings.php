<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Advance_Settings;

use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;
// use Dragwyb\Form_Builder\Includes\Modules\Sanitize_Module_Settings\Sanitize_Module_Settings;

class Advance_Settings extends Toolbar_Base
{
    private static $instance = null;
    private $advance_settings = null;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function get_id(): string
    {
        return 'advance';
    }

    protected function get_name(): string
    {
        return __('Advance', 'dragwyb-form-builder');
    }

    protected function get_icon(): string
    {
        return 'fas fa-sliders-h';
    }

    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    private function init(): void
    {
        $this->advance_settings = new Settings();
    }

    protected function get_settings(): array
    {
        $settings = $this->advance_settings;
        $data = array();
        $form_id = absint($this->get_form_id());

        if ($settings instanceof Settings) {
            $settings->set_form_id($form_id);
            $conrols = $settings->render_controls();
            $data['label'] = sprintf(esc_html__('%s Settings'), sanitize_text_field($this->get_name()));

            if ($conrols && count($conrols) > 0) {
                $data['controls'] = $conrols;
            }
        }

        return $data;
    }

    protected function update_toolbar(): void
    {
        $settings = $this->get_display_settings();

        if ($settings) {
            $form_id = $this->get_form_id();

            $post_update = array();

            if (isset($settings['form_name'])) {
                $post_update['post_title'] = sanitize_text_field($settings['form_name']);
            }
            if (isset($settings['form_status'])) {
                $post_update['post_status'] = sanitize_text_field($settings['form_status']);
            }

            if (!empty($post_update)) {
                $post_update['ID'] = $form_id;

                $post_update = wp_update_post($post_update);
            }
        }
    }
}
