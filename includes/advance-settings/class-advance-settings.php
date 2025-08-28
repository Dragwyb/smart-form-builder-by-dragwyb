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
        return 'fas fa-cog';
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
    }
}
