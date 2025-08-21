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

    protected function sanitize_data(array $data): array
    {
        // $sanitize_module_data = Sanitize_Module_Settings::instance($data);

        // if ($sanitize_data && is_array($sanitize_data) && count($sanitize_data) > 0) {
        //     return $sanitize_data;
        // }
        return array();
    }

    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    private function init(): void
    {
        $this->advance_settings=new Settings();
    }

    protected function get_settings(): array
    {
       $settings=$this->advance_settings;

       if($settings instanceof Settings){
            $conrols = $settings->render_controls();

            if($conrols && count($conrols) > 0){
                return $conrols;
            }
        }

        return array();
    }
}
