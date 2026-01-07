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
    }

    public function init()
    {
        if (is_user_logged_in() && isset($_GET['preview_id']) && isset($_GET['p']) && isset($_GET['post_type']) && $_GET['post_type'] === Dragwyb_Post::POST_TYPE && wp_verify_nonce($_GET['preview_id'], self::private_key_name(absint($_GET['p'])))) {

            $post_id = absint($_GET['p']);

            if (function_exists('status_header')) status_header(200);

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
