<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Dragwyb_Pages;

if (!defined('ABSPATH')) {
    exit;
}

use Dragwyb\Form_Builder\Admin\Form_Overview\Form_Bulk_Actions_Handler;

class Dragwyb_Post
{
    /**
     * Post type name
     */
    const POST_TYPE = DRAGWYB_PREFIX . '-forms';

    public static function post_type()
    {
        return self::POST_TYPE;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('init', [$this, 'register_post_type']);
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', [$this, 'set_custom_columns']);
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', [$this, 'render_custom_columns'], 10, 2);

        add_action('init', [$this, 'dragwyb_add_caps_to_admin']);

        add_action('admin_init', [$this, 'dragwyb_bulk_actions_handler']);

        add_action('admin_notices', [$this, 'bulk_action_notices']);
    }

    /**
     * Register the custom post type
     */
    public function register_post_type(): void
    {
        $args = [
            'label'               => 'Dragwyb Form',
            'public'              => false,
            'exclude_from_search' => true,
            'show_ui'             => false,
            'show_in_admin_bar'   => false,
            'rewrite'             => false,
            'query_var'           => false,
            'can_export'          => false,
            'supports'            => ['title', 'author', 'revisions'],
            'capability_type'     => array(sanitize_text_field(DRAGWYB_PREFIX) . '_forms', sanitize_text_field(DRAGWYB_PREFIX) . '_form'), // Not using 'capability_type' anywhere. It just has to be custom for security reasons.
            'capabilities'        => $this->capabilties(),
            'map_meta_cap'        => true
        ];

        register_post_type(self::POST_TYPE, $args);
    }

    public function capabilties()
    {
        $caps = [
            'delete_post'            => 'delete_' . sanitize_text_field(DRAGWYB_PREFIX) . '_form',
            'delete_posts'           => 'delete_' . sanitize_text_field(DRAGWYB_PREFIX) . '_forms',
            'delete_published_posts' => 'delete_published_' . sanitize_text_field(DRAGWYB_PREFIX) . '_forms',
        ];

        $caps = apply_filters('Dragwyb/Forms/Capablitlies', $caps);

        return $caps;
    }

    function dragwyb_add_caps_to_admin()
    {
        $role = get_role('administrator');
        if (!$role) return;

        $caps = $this->capabilties();


        if (is_array($caps) && count($caps) > 0) {

            $caps = array_unique(array_values($caps));

            foreach ($caps as $cap) {
                $role->add_cap($cap);
            }
        }
    }

    /**
     * Set custom columns for the forms list
     */
    public function set_custom_columns($columns): array
    {
        $new_columns = [
            'cb'        => $columns['cb'],
            'title'     => __('Form Name', 'smart-form-builder-by-dragwyb'),
            'type'      => __('Form Type', 'smart-form-builder-by-dragwyb'),
            'shortcode' => __('Shortcode', 'smart-form-builder-by-dragwyb'),
            'entries'   => __('Entries', 'smart-form-builder-by-dragwyb'),
            'date'      => $columns['date'],
        ];
        return $new_columns;
    }

    /**
     * Render custom column content
     */
    public function render_custom_columns($column, $post_id): void
    {
        switch ($column) {
            case 'type':
                $form_type = get_post_meta($post_id, '_Dragwyb_Page_type', true);
                echo esc_html(ucfirst($form_type ?: 'Standard'));
                break;

            case 'shortcode':
                echo '<input type="text" readonly class="regular-text code" value="[Dragwyb_Page id=&quot;' . esc_attr($post_id) . '&quot;]" onclick="this.select()">';
                break;

            case 'entries':
                $entries_count = $this->get_form_entries_count($post_id);
                echo esc_html($entries_count);
                break;
        }
    }

    /**
     * Get form entries count
     */
    private function get_form_entries_count($form_id): int
    {
        // This will be implemented when we add form submissions functionality
        return 0;
    }

    /**
     * Redirect to custom editor
     */
    public function redirect_to_custom_editor(): void
    {
        global $post_type;

        if ($post_type !== self::POST_TYPE) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No nonce is required for admin dashboard pages check
        $form_id = isset($_GET['post']) ? absint($_GET['post']) : 0;

        if ($form_id) {
            wp_safe_redirect(add_query_arg([
                'page' => DRAGWYB_PREFIX . '-form-overview',
                'form_id' => $form_id
            ], admin_url('admin.php')));
            exit;
        }
    }

    /**
     * Disable Gutenberg for forms
     */
    public function disable_gutenberg(bool $use_block_editor, string $post_type): bool
    {
        if ($post_type === self::POST_TYPE) {
            return false;
        }
        return $use_block_editor;
    }

    public function dragwyb_bulk_actions_handler()
    {
        if (isset($_GET['_wpnonce'])) {
            $current_post_type = sanitize_text_field(self::POST_TYPE);
            $nonce = isset($_REQUEST['_wpnonce']) ? sanitize_text_field(wp_unslash($_REQUEST['_wpnonce'])) : '';
            if (!empty($nonce) && wp_verify_nonce($nonce, "bulk-" . $current_post_type)) {

                if (function_exists('wp_get_referer')) {
                    $referal_url = wp_get_referer();

                    if (str_contains($referal_url, 'page=dragwyb-form-overview')) {
                        Form_Bulk_Actions_Handler::instance($current_post_type);
                    }
                }
            }
        }
    }

    public function bulk_action_notices()
    {

        if (!function_exists('get_current_screen') || !property_exists(get_current_screen(), 'id') || !str_contains(get_current_screen()->id, 'dragwyb-form-overview')) {
            return;
        }

        if (!empty($_GET['trashed']) && isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), "bulk-trash-" . sanitize_text_field(self::POST_TYPE))) {
            $count = absint($_GET['trashed']);
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                // translators: %s is the number of forms moved to the trash
                sprintf(esc_html('%s form moved to the Trash.', 'smart-form-builder-by-dragwyb'), absint($count))
            );
        }

        if (!empty($_GET['deleted']) && isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), "bulk-delete-" . sanitize_text_field(self::POST_TYPE))) {
            $count = absint($_GET['deleted']);
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                // translators: %s is the number of forms permanently deleted
                sprintf(esc_html('%s form permanently deleted.', 'smart-form-builder-by-dragwyb'), absint($count))
            );
        }

        if (!empty($_GET['untrashed']) && isset($_GET['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['_wpnonce'])), "bulk-untrash-" . sanitize_text_field(self::POST_TYPE))) {
            $count = absint($_GET['untrashed']);

            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                // translators: %s is the number of forms restored from trash
                sprintf(esc_html('%s form restored from Trash.', 'smart-form-builder-by-dragwyb'), absint($count))
            );
        }
    }
}
