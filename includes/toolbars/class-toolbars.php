<?php
declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Toolbars;

use Dragwyb\Form_Builder\Includes\Toolbars\Register_Toolbar;

class Toolbars {

    // Toolbar identifiers (constants for easy reference)
    const ADD_FIELD = 'add_field';
    const GENERAL   = 'general';
    const ADVANCE   = 'advance';

    private static ?Toolbars $instance = null;

    /**
     * All registered toolbars
     * @var Toolbar_Base[]
     */
    private array $toolbars = [];

    public static function instance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->init();
    }

    public function defaultToolbar(){
        $default='fields';

        $default=apply_filters('Dragwyb/Toolbars/Default_Tab', $default);

        return $default;
    }

    private function init(): void {
        $this->load_toolbars();
    }

    private function load_toolbars(): void {
        $this->register_toolbars();
    }

    private function register_toolbars(): void {
        $register = Register_Toolbar::instance();
        $this->toolbars = $register->get_toolbars();
    }

    /**
     * Get all toolbars
     */
    public function get_toolbars(): array {
        return $this->toolbars;
    }

    /**
     * Get a single toolbar by ID
     */
    public function get_toolbar(string $id): ?Toolbar_Base {
        return $this->toolbars[$id] ?? null;
    }
}
