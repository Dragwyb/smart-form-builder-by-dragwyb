<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Style_Settings;

use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;

class Style_Settings extends Toolbar_Base
{
    private static $instance = null;
    protected $toolbar_settings = null;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function get_id(): string
    {
        return 'style';
    }

    protected function get_name(): string
    {
        return __('Style', 'smart-form-builder-by-dragwyb');
    }

    protected function get_icon(): string
    {
        return 'fas fa-paint-brush';
    }

    public function __construct()
    {
        parent::__construct();
        $this->init();
    }

    private function init(): void
    {
        $this->toolbar_settings = new Settings();
    }

    protected function get_setting_instance(): string
    {
        return Settings::class;
    }

    protected function update_toolbar(): void {}
}
