<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Frontend;

use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;

if (!defined('ABSPATH')) {
    exit;
}

class Form_Preview
{
    private static $instance = null;

    private static $form_id = 0;

    private static $is_iframe_mode = false;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        add_action('template_redirect', [$this, 'init']);
        add_action('wp_head', [$this, 'render_dynamic_style_container']);
        add_action('Dragwyb/Editor/Preview/Init', [$this, 'init_iframe']);
    }

    public function init()
    {
        if (is_user_logged_in() && isset($_GET['preview_id']) && isset($_GET['p']) && isset($_GET['post_type']) && $_GET['post_type'] === Dragwyb_Post::POST_TYPE && wp_verify_nonce($_GET['preview_id'], self::private_key_name(absint($_GET['p'])))) {


            if (function_exists('status_header')) status_header(200);

            if (isset($_GET['dragwyb_iframe_mode']) && $_GET['dragwyb_iframe_mode'] === 'true') {
                do_action('Dragwyb/Editor/Preview/Init');
                $frontend_render = Frontend_Render::instance();
                self::$form_id = absint($_GET['p']);
                self::$is_iframe_mode = true;
                $frontend_render->init(self::$form_id);
                $frontend_render->enqueue_static_assets();
                $this->enqueue_editor_preview_styles();
                $this->enqueue_editor_preview_scripts();
                do_action('wp_head');
                exit;
            }

            $post_id = absint($_GET['p']);

            !defined("DRAGWYB_FORM_PREVIEW") && define('DRAGWYB_FORM_PREVIEW', true);

            add_filter('pre_get_document_title', [$this, 'set_document_title'], 999);

            if (function_exists('get_header')) get_header();

            // 3. ECHO THE SHORTCODE (Crucial Step)
            echo '<div id="dragwyb-preview-wrapper">';
            echo do_shortcode('[dragwyb-form id="' . $post_id . '"]');
            echo '</div>';

            if (function_exists('get_footer')) get_footer();

            remove_filter('pre_get_document_title', [$this, 'set_document_title'], 999);

            // 4. STOP EXECUTION
            exit;
        }
    }

    public function init_iframe()
    {
        // font-awesome@5.15.4
        wp_enqueue_style(
            'dragwyb-font-awesome',
            esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/font-awesome/v5/all.min.css'),
            [],
            '5.15.4'
        );
    }

    public function render_dynamic_style_container()
    {
        if (!self::$form_id || !self::$is_iframe_mode) {
            return;
        }
        echo '<style id="dragwyb-form-' . self::$form_id . '"></style>';
    }

    public function enqueue_editor_preview_styles()
    {
        wp_enqueue_style(
            'dragwyb-form-editor-global',
            esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/css/editor-global.css'),
            [],
            esc_attr(DRAGWYB_FORM_BUILDER_VERSION)
        );

        wp_enqueue_style('dragwyb-editor-preview', esc_url(DRAGWYB_FORM_BUILDER_URL . '/assets/css/editor-preview.css'), ['dragwyb-form-editor-global'], esc_attr(DRAGWYB_FORM_BUILDER_VERSION));
    }

    public function enqueue_editor_preview_scripts()
    {
        do_action('Dragwyb/Editor/Preview/Enqueue_Scripts');
    }

    public function set_document_title(string $title): string
    {
        return get_the_title(absint($_GET['p']));
    }

    private static function private_key_name(int $form_id): string
    {
        $post_type = Dragwyb_Post::POST_TYPE;
        return $post_type . '-' . $form_id;
    }

    final public static function generate_key(int $form_id): string
    {
        return wp_create_nonce(self::private_key_name($form_id));
    }
}
