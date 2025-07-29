<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Dragwyb_Pages;

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

        // Add form type meta box
        add_action('add_meta_boxes', [$this, 'add_form_type_meta_box']);
        add_action('save_post', [$this, 'save_form_type_meta']);

        // Add editor integration
        add_action('load-post.php', [$this, 'redirect_to_custom_editor']);
        add_action('load-post-new.php', [$this, 'redirect_to_custom_editor']);

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
            'map_meta_cap'        => true, // Don't 
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
            'title'     => __('Form Name', 'dragwyb-form-builder'),
            'type'      => __('Form Type', 'dragwyb-form-builder'),
            'shortcode' => __('Shortcode', 'dragwyb-form-builder'),
            'entries'   => __('Entries', 'dragwyb-form-builder'),
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
     * Add meta box for form type selection
     */
    public function add_form_type_meta_box(): void
    {
        add_meta_box(
            'Dragwyb_Page_type',
            __('Form Type', 'dragwyb-form-builder'),
            [$this, 'render_form_type_meta_box'],
            self::POST_TYPE,
            'side',
            'high'
        );
    }

    /**
     * Render form type meta box
     */
    public function render_form_type_meta_box($post): void
    {
        // Add nonce for security
        wp_nonce_field('Dragwyb_Page_type_meta_box', 'Dragwyb_Page_type_nonce');

        $form_type = get_post_meta($post->ID, '_Dragwyb_Page_type', true);
?>
        <select name="Dragwyb_Page_type" id="Dragwyb_Page_type">
            <option value="standard" <?php selected($form_type, 'standard'); ?>>
                <?php esc_html_e('Standard Form', 'dragwyb-form-builder'); ?>
            </option>
            <option value="quiz" <?php selected($form_type, 'quiz'); ?>>
                <?php esc_html_e('Quiz', 'dragwyb-form-builder'); ?>
            </option>
            <option value="poll" <?php selected($form_type, 'poll'); ?>>
                <?php esc_html_e('Poll', 'dragwyb-form-builder'); ?>
            </option>
            <option value="survey" <?php selected($form_type, 'survey'); ?>>
                <?php esc_html_e('Survey', 'dragwyb-form-builder'); ?>
            </option>
        </select>
<?php
    }

    /**
     * Save form type meta
     */
    public function save_form_type_meta($post_id): void
    {
        // Check if nonce is set and valid
        if (
            !isset($_POST['Dragwyb_Page_type_nonce']) ||
            !wp_verify_nonce($_POST['Dragwyb_Page_type_nonce'], 'Dragwyb_Page_type_meta_box')
        ) {
            return;
        }

        // Check if this is an autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save form type
        if (isset($_POST['Dragwyb_Page_type'])) {
            $form_type = sanitize_text_field($_POST['Dragwyb_Page_type']);
            update_post_meta($post_id, '_Dragwyb_Page_type', $form_type);
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
            $nonce = sanitize_text_field(wp_unslash($_REQUEST['_wpnonce']));
            if (wp_verify_nonce($nonce, "bulk-" . $current_post_type)) {

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


        if (empty($_GET['trashed']) && empty($_GET['deleted']) && empty($_GET['untrashed'])) {
            return;
        }

        if (!empty($_GET['trashed'])) {
            $count = absint($_GET['trashed']);
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                sprintf(_n('%s form moved to the Trash.', '%s forms moved to the Trash.', $count, 'your-textdomain'), $count)
            );
        }

        if (!empty($_GET['deleted'])) {
            $count = absint($_GET['deleted']);
            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                sprintf(_n('%s form permanently deleted.', '%s forms permanently deleted.', $count, 'your-textdomain'), $count)
            );
        }

        if (!empty($_GET['untrashed'])) {
            $count = absint($_GET['untrashed']);

            printf(
                '<div class="notice notice-success is-dismissible"><p>%s</p></div>',
                sprintf(_n('%s form restored from Trash.', '%s forms restored from Trash.', $count, 'your-textdomain'), $count)
            );
        }
    }
}
