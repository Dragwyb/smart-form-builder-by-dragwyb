<?php

/**
 * Plugin Name: Smart Form Builder by Dragwyb
 * Description: Drag and drop form builder for WordPress
 * Version: 1.0.1
 * Author: Dragwyb
 * Author URI:  https://dragwyb.com/
 * Text Domain: smart-form-builder-by-dragwyb
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

use Dragwyb\Form_Builder\Dragwyb_Form_Builder_Autoload;
use Dragwyb\Form_Builder\Includes\Dragwyb_Init;

final class Dragwyb_Form_Builder
{
    /**
     * Plugin version
     */
    const VERSION = '1.0.1';

    /**
     * Plugin instance
     */
    private static $instance = null;

    private $styling;

    /**
     * Get plugin instance
     */
    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct()
    {
        $this->define_constants();

        // Load autoloader
        require_once DRAGWYB_FORM_BUILDER_PATH . 'class-dragwyb-autoload.php';
        Dragwyb_Form_Builder_Autoload::instance();

        $this->init_hooks();
    }

    /**
     * Define plugin constants
     */
    private function define_constants(): void
    {
        define('DRAGWYB_PREFIX', 'dragwyb');
        define('DRAGWYB_FORM_BUILDER_VERSION', self::VERSION);
        define('DRAGWYB_FORM_BUILDER_PATH', plugin_dir_path(__FILE__));
        define('DRAGWYB_FORM_BUILDER_URL', plugin_dir_url(__FILE__));
    }

    /**
     * Initialize WordPress hooks
     */
    private function init_hooks(): void
    {
        $this->init_plugin();
    }

    /**
     * Initialize plugin components
     */
    public function init_plugin(): void
    {

        $dragwyb = Dragwyb_Init::instance();
        $dragwyb->init();
    }
}

// Initialize plugin
function dragwyb_form_builder()
{
    return Dragwyb_Form_Builder::instance();
}

// Start the plugin
dragwyb_form_builder();
