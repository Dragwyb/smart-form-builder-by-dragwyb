<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Form_Overview;

class Form_Bulk_Actions_Handler
{

    protected $post_type;

    private static ?self $instance = null;

    public static function instance($post_type): self
    {
        if (null === self::$instance) {
            self::$instance = new self($post_type);
        }

        return self::$instance;
    }

    public function __construct($post_type)
    {
        $this->post_type = sanitize_text_field($post_type);
        $this->handle_actions();
    }

    public function handle_actions()
    {
        if (!current_user_can('delete_posts')) {
            return;
        }

        $action = $this->get_current_action();

        if (!$action) {
            return;
        }

        check_admin_referer('bulk-' . sanitize_text_field($this->post_type));

        $post_ids = isset($_REQUEST['post']) ? array_map('absint', (array) $_REQUEST['post']) : [];

        if (empty($post_ids)) {
            return;
        }
        foreach ($post_ids as $post_id) {
            if (get_post_type($post_id) !== $this->post_type) {
                continue;
            }
           
            switch ($action) {
                case 'trash':
                    $this->trash_post($post_id);
                    break;
                case 'delete':
                    $this->delete_post($post_id);
                    break;
                case 'untrash':
                    $this->untrash_post($post_id);
                    break;
            }
        }

        $this->redirect_after_action();
    }

    protected function get_current_action()
    {
        return $_REQUEST['action'] !== '-1' ? sanitize_key($_REQUEST['action']) : sanitize_key($_REQUEST['action2'] ?? '');
    }

    protected function trash_post($post_id)
    {
        if (current_user_can('delete_post', $post_id)) {
            wp_trash_post($post_id);
        }
    }

    protected function delete_post($post_id)
    {
        if (current_user_can('delete_post', $post_id)) {
            wp_delete_post($post_id, true);
        }
    }

    protected function untrash_post($post_id)
    {
        if (current_user_can('delete_post', $post_id)) {
            wp_untrash_post($post_id);
        }
    }

    protected function redirect_after_action()
    {
        $current_status = $_GET['post_status'] ?? 'all';
        $base_url = remove_query_arg(['_wpnonce', '_wp_http_referer', 'action', 'action2', 'post', 'bulk_action']);

        if ($current_status === 'trash') {
            $trash_count = wp_count_posts($this->post_type)->trash ?? 0;
            if ($trash_count === 0) {
                wp_redirect(add_query_arg('post_status', 'all', $base_url));
                exit;
            }
        }

        if($current_status === 'all'){
            $all_post = wp_count_posts($this->post_type)->trash ?? 0;
            $publish_posts_count = ($all_post->publish ?? 0) + ($all_post->draft ?? 0);
            $trash_count = all_post->trash ?? 0;

            if($publish_posts_count === 0 && )
        }

        wp_redirect($base_url);
        exit;
    }
}
