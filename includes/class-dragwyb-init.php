<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes;

use Dragwyb\Form_Builder\Includes\Dragwyb_Form_Builder_Ajax;
use Dragwyb\Form_Builder\Includes\Dragwyb_Form_Builder_Editor;
use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;
use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Pages;
use Dragwyb\Form_Builder\Admin\Dragwyb_Editor\Dragwyb_Builder_Editor;
use Dragwyb\Form_Builder\Includes\Frontend\Shortcode\Shortcode_Handler;
use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;
use Dragwyb\Form_Builder\Includes\Frontend\Form_Preview;
use Dragwyb\Form_Builder\Includes\Frontend\Managers\CSS_Manager;

class Dragwyb_Init
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init(): void
    {
        // Initialize admin
        if (is_admin()) {
            // new Dragwyb_Form_Builder_Admin();
            new Dragwyb_Builder_Editor();
            // new Dragwyb_Form_Builder_Editor();
            new Dragwyb_Pages();
            // Initialize post type
            new Dragwyb_Post();
            // Initialize AJAX handler
            new Dragwyb_Form_Builder_Ajax();
            // Initialize Frontend Render
            Frontend_Render::instance();
            // Initialize Frontend Preview
        }

        Form_Preview::instance();

        Shortcode_Handler::instance();

        add_action('admin_init', [$this, 'initial_files']);
    }

    public function initial_files()
    {
        CSS_Manager::instance();
    }

    public static function core_script()
    {

        $thisObj = self::instance();

        // Enqueue React and dependencies
        wp_register_script(
            'dragwyb-form-core',
            DRAGWYB_FORM_BUILDER_URL . 'assets/dist/core/core.js',
            ['wp-element', 'wp-components', 'wp-i18n', 'jquery'],
            DRAGWYB_FORM_BUILDER_VERSION,
            true
        );

        $thisObj->localize_script();
    }

    private function localize_script()
    {
        global $post;

        $form_id = $post->ID ?? 0;

        $data = array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'i18n' => $this->get_translations(),
            // 'nonce' => wp_create_nonce('dragwyb_editor'),
            'formId' => $form_id
        );

        $data = apply_filters('Dragwyb_Localize_Core_Script', $data);
        // Localize data
        wp_localize_script('dragwyb-form-core', 'DragwybBuilder', $data);
    }

    private function get_translations()
    {
        $localize_strings = [
            'exit' => __('Exit', 'dragwyb-form-builder'),
            'submit' => __('Submit', 'dragwyb-form-builder'),
        ];

        return apply_filters('Dragwyb_i18n', $localize_strings);
    }
}
