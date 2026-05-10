<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Frontend\Managers;

if (!defined('ABSPATH')) {
    exit;
}

use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;
use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;

class CSS_Manager
{
    private string $upload_dir;
    private string $upload_url;
    private Frontend_Render $frontend;
    private static $instance = null;
    private static $google_fonts_cache = [];
    private static $unique_id = [];

    public static function instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $dragwyb_upload_info = wp_upload_dir();

        // Create a specific folder for your plugin's CSS
        $this->upload_dir = $dragwyb_upload_info['basedir'] . '/dragwyb-forms/css/';
        $this->upload_url = $this->get_upload_dir_url($dragwyb_upload_info['baseurl']) . '/dragwyb-forms/css/';

        add_action('wp_ajax_dragwyb_clean_form_cache', [$this, 'clean_cache_request']);
    }

    /**
     * Filter and return url based on ssl protocol form start
     */
    private function get_upload_dir_url(string $url): string
    {
        return is_ssl() ? preg_replace('/^http:/', 'https:', $url) : $url;
    }

    /**
     * Main entry point: Enqueue the style, generating it if missing.
     */
    public function enqueue_form_styles(int $form_id, Frontend_Render $frontend): void
    {

        if (defined('DRAGWYB_FORM_PREVIEW') && true === DRAGWYB_FORM_PREVIEW) {
            return;
        }

        $unique_id = get_post_meta($form_id, '_dragwyb_form_assets_id', true);

        $file_name = 'form-' . $form_id . '-' . $unique_id . '.css';
        $file_path = $this->upload_dir . $file_name;
        $file_url  = $this->upload_url . $file_name;
        $this->frontend = $frontend;

        // 1. Check if file exists
        if (!file_exists($file_path) || !isset($unique_id) || $unique_id === '') {
            // 2. If missing, generate it
            $style_content = $this->generate_css_content();
            $google_fonts = $style_content['google_fonts'];
            $css_content = $style_content['css'];

            // Generate uniqueid based on current time & date.
            $unique_id = time();

            // Convert uniqueid to string.
            $unique_id = (string) $unique_id;

            $file_name = 'form-' . $form_id . '-' . $unique_id . '.css';
            $file_path = $this->upload_dir . $file_name;
            $file_url  = $this->upload_url . $file_name;

            // Update uniqueid in post meta.
            update_post_meta($form_id, '_dragwyb_form_assets_id', $unique_id);

            if ($css_content && !empty($css_content)) {
                $this->write_file($file_path, $css_content);
            }

            if ($google_fonts && !empty($google_fonts) && is_array($google_fonts)) {
                update_post_meta($form_id, 'dragwyb_form_google_fonts', array_map('sanitize_text_field', $google_fonts));
            }
        }

        // 3. Enqueue the file (Standard WordPress caching)
        if (file_exists($file_path)) {
            wp_enqueue_style(
                'dragwyb-form-' . $form_id,
                esc_url($file_url),
                [],
                esc_attr(DRAGWYB_FORM_BUILDER_VERSION) // Version based on file modification time
            );
        }
    }

    /**
     * Logic to pull CSS data from your Frontend Render class
     */
    private function generate_css_content(): array
    {
        return $this->frontend->get_generated_css();
    }

    private function write_file(string $path, string $content): void
    {
        // Use WP_Filesystem for security
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        // Ensure directory exists
        if (!is_dir($this->upload_dir)) {
            wp_mkdir_p($this->upload_dir);
        }

        $wp_filesystem->put_contents($path, $content, FS_CHMOD_FILE);
    }

    final public function clean_cache(int $form_id)
    {
        return $this->delete_cache_file($form_id);
    }

    public function clean_cache_request(): void
    {
        if (!isset($_POST['delete_cache_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['delete_cache_nonce'])), 'delete_cache_nonce')) {
            wp_send_json_error('Invalid nonce');
        }

        if (!isset($_POST['form_id'])) {
            wp_send_json_error('Invalid form id 1');
        }

        // 1. Check Permission
        if (! current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized use it for get form id and below use nonce verification based on form ID.
        $form_id = absint(sanitize_text_field(wp_unslash($_POST['form_id'])));

        if ($form_id <= 0) {
            wp_send_json_error('Invalid form id 2');
        }

        $post_type = Dragwyb_Post::POST_TYPE;

        $nonce_key = $post_type . $form_id . '-clean-cache';

        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), $nonce_key)) {
            wp_send_json_error('Invalid nonce');
        }

        $form_id = $this->clean_cache($form_id);

        if ($form_id <= 0) {
            wp_send_json_error('Invalid form id 3');
        }

        wp_send_json_success(array(
            'message' => 'Cache cleaned successfully',
            'form_id' => $form_id
        ));
    }

    private function delete_cache_file(int $id)
    {
        $unique_id = get_post_meta($id, '_dragwyb_form_assets_id', true);
        $file_name = 'form-' . $id . '-' . sanitize_text_field($unique_id) . '.css';
        $file_path = $this->upload_dir . $file_name;
        if (file_exists($file_path)) {
            wp_delete_file($file_path);
        }

        delete_post_meta($id, 'dragwyb_form_google_fonts');

        return $id;
    }
}
