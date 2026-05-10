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
        add_action('Dragwyb/Editor/Preview/Init', [$this, 'init_iframe']);
    }

    public function init()
    {
        if (!is_user_logged_in()) {
            return;
        }

        $form_preview_id = isset($_GET['preview_id']) ? sanitize_text_field(wp_unslash($_GET['preview_id'])) : '';
        $form_id = isset($_GET['p']) ? absint($_GET['p']) : 0;
        $post_type = isset($_GET['post_type']) ? sanitize_text_field(wp_unslash($_GET['post_type'])) : '';

        if ($form_preview_id && $form_id && $post_type === Dragwyb_Post::POST_TYPE && $this->current_user_can_preview($form_id) && wp_verify_nonce($form_preview_id, self::private_key_name($form_id))) {


            if (function_exists('status_header')) status_header(200);

            $iframe_mode = isset($_GET['dragwyb_iframe_mode']) ? sanitize_key(wp_unslash($_GET['dragwyb_iframe_mode'])) : '';

            if ('true' === $iframe_mode) {
                do_action('Dragwyb/Editor/Preview/Init');
                $frontend_render = Frontend_Render::instance();
                self::$form_id = $form_id;
                self::$is_iframe_mode = true;
                $frontend_render->init(self::$form_id);
                $frontend_render->enqueue_static_assets();
                $this->enqueue_editor_preview_styles();
                $this->enqueue_editor_preview_scripts();
                // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- it's a core WordPress hook for rendering the head section
                do_action('wp_head');
                exit;
            }

            $post_id = $form_id;

            !defined("DRAGWYB_FORM_PREVIEW") && define('DRAGWYB_FORM_PREVIEW', true);

            add_filter('pre_get_document_title', [$this, 'set_document_title'], 999);

            if (function_exists('get_header')) get_header();

            // 3. ECHO THE SHORTCODE (Crucial Step)
            echo '<div id="dragwyb-preview-wrapper" style="width: 100%;">';
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
        $form_preview_id = isset($_GET['preview_id']) ? sanitize_text_field(wp_unslash($_GET['preview_id'])) : '';
        $form_id = isset($_GET['p']) ? absint($_GET['p']) : 0;

        if ($form_preview_id && $form_id && $this->current_user_can_preview($form_id) && wp_verify_nonce($form_preview_id, self::private_key_name($form_id))) {
            return get_the_title($form_id);
        }

        return $title;
    }

    private function current_user_can_preview(int $form_id): bool
    {
        return current_user_can('manage_options') || current_user_can('edit_post', $form_id);
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
