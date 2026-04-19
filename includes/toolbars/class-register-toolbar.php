<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Toolbars;

use Dragwyb\Form_Builder\Includes\Helper\Helper;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;
use Dragwyb\Form_Builder\Includes\Modules\Modules;

class Register_Toolbar
{

    private static ?Register_Toolbar $instance = null;

    private array $toolbars = [];

    private array $default_toolbars = [
        'modules',
        'style_settings',
        'after_submission',
        'advance_settings',
    ];

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->register_default_toolbars();

        // Hook to allow developers to add custom toolbars
        do_action('Dragwyb/register_toolbars', $this);
    }

    /**
     * Register core toolbars (Add Field, General, Advance)
     */
    private function register_default_toolbars(): void
    {
        foreach ($this->default_toolbars as $toolbar) {
            $dragwyb_name_space = Helper::namespace_into_dir_path(__NAMESPACE__);
            $dir   = dirname($dragwyb_name_space);
            $dir = Helper::dir_path_into_namespace($dir);

            $class = $dir . '\\' . ucfirst($toolbar) . '\\' . ucfirst($toolbar);

            if (class_exists($class)) {
                $this->register_toolbar(new $class());
            }
        }
    }

    /**
     * Register a single toolbar
     */
    public function register_toolbar(Toolbar_Base $toolbar): void
    {
        $this->toolbars[$toolbar->get_toolbar_id()] = $toolbar;
    }

    /**
     * Get all registered toolbars
     */
    public function get_toolbars(): array
    {
        return $this->toolbars;
    }

    /**
     * Get a specific toolbar
     */
    public function get_toolbar(string $id): ?Toolbar_Base
    {
        return $this->toolbars[$id] ?? null;
    }
}
