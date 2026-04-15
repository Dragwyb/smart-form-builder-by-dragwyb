<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Form_Overview;

use WP_List_Table;
use WP_Post;
use WP_Screen;
use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;
use Dragwyb\Form_Builder\Includes\Frontend\Form_Preview;

/**
 * Generate the table on the plugin overview page.
 *
 * @since 1.8.6
 */
class List_Table extends WP_List_Table
{

    public $per_page;
    private $count;
    private $view;
    private string $upload_dir;

    /**
     * Primary class constructor.
     *
     * @since 1.8.6
     */
    public function __construct()
    {
        parent::__construct(
            [
                'singular' => 'dragwyb-form',
                'plural'   => 'dragwyb-forms',
                'ajax'     => false,
            ]
        );

        $this->per_page = (int) apply_filters(DRAGWYB_PREFIX . '_overview_per_page', 20);

        $upload_info = wp_upload_dir();
        // Create a specific folder for your plugin's CSS
        $this->upload_dir = $upload_info['basedir'] . '/dragwyb-forms/css/';
    }

    /**
     * Get the instance of a class and store it in itself.
     *
     * @since 1.8.6
     */
    public static function get_instance()
    {
        static $instance;

        if (! $instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * Retrieve the table columns.
     *
     * @since 1.8.6
     *
     * @return array
     */
    public function get_columns()
    {
        $columns = array(
            'cb'       => '<input type="checkbox" />', // Required for bulk actions
            'name' => 'Name',
            'author' => 'Author',
            'shortcode' => 'Shortcode',
            'id' => 'ID',
            'clean_cache' => 'Clean Cache',
            'date' => 'Date'
        );

        // Modify columns via a filter
        $columns = apply_filters('dragwyb_form_overview_columns', $columns);

        return $columns;
    }

    /**
     * Render the checkbox column.
     *
     * @since 1.8.6
     *
     * @param WP_Post $form Form.
     *
     * @return string
     */
    public function column_cb($form)
    {
        return sprintf(
            '<input type="checkbox" name="post[]" value="%d" />',
            $form->ID
        );
    }

    /**
     * Render the columns.
     *
     * @since 1.8.6
     *
     * @param WP_Post $form        CPT object as a form representation.
     * @param string  $column_name Column Name.
     *
     * @return string
     */
    public function column_default($form, $column_name)
    {
        switch ($column_name) {
            case 'id':
                $value = $form->ID;
                break;

            case 'shortcode':
                $value = '[' . DRAGWYB_PREFIX . '-form id="' . $form->ID . '"]';
                break;

            case 'created':
                $value = get_the_date('Y-m-d', $form);
                break;

            case 'entries':
                $entry_count = get_post_meta($form->ID, '_form_entries_count', true) ?: 0;
                $value = '<span class="form-entries-count">' . esc_html($entry_count) . '</span>';
                break;

            case 'date':
                $value = get_the_modified_date('Y-m-d', $form);
                break;

            case 'author':
                $author = get_user_by('ID', $form->post_author);
                $value = $author ? esc_html($author->display_name) : '';
                break;

            case 'clean_cache':
                if ($this->css_cache_exist($form->ID)) {
                    $value = '<button type="button" id="clean-cache-' . (int)$form->ID . '" data-key="' . wp_create_nonce(sanitize_text_field($form->post_type) . (int)$form->ID . '-clean-cache') . '" data-clean-key="' . wp_create_nonce('delete_cache_nonce') . '" class="button">Clean Cache</button>';
                } else {
                    $value = '<button type="button" id="clean-cache-' . (int)$form->ID . '" disabled class="button">Clean Cache</button>';
                }
                break;

            default:
                $value = '';
        }

        return apply_filters('dragwyb_form_overview_column_value', $value, $form, $column_name);
    }

    private function css_cache_exist($form_id)
    {
        $file_name = 'form-' . $form_id . '.css';
        $file_path = $this->upload_dir . $file_name;
        return file_exists($file_path);
    }

    /**
     * Render the form name column with action links.
     *
     * @since 1.8.6
     *
     * @param WP_Post $form Form.
     *
     * @return string
     */
    public function column_name($form)
    {
        $title = $this->get_column_name_title($form);
        $states = _post_states($form, false);
        $actions = $this->get_column_name_row_actions($form);

        return $title . $states . $actions;
    }

    /**
     * Get the form name HTML for the form name column.
     *
     * @since 1.8.6
     *
     * @param WP_Post $form Form object.
     *
     * @return string
     */
    protected function get_column_name_title($form)
    {
        $title = ! empty($form->post_title) ? $form->post_title : $form->post_name;

        if ($this->view === 'trash') {
            return esc_html($title);
        }

        // Generate preview and edit links for users with appropriate permissions
        $edit_url = get_edit_post_link($form->ID);
        $value = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url($this->get_preview_url($form->ID)),
            esc_html($title)
        );

        if (current_user_can('edit_post', $form->ID)) {
            $value = sprintf(
                '<a href="%s" target="_blank">%s</a>',
                esc_url($edit_url),
                esc_html($title)
            );
        }

        return $value;
    }

    private function get_preview_url(int $id)
    {
        return home_url('/?post_type=' . sanitize_text_field(Dragwyb_Post::POST_TYPE) . '&p=' . $id . '&preview_id=' . Form_Preview::generate_key($id));
    }

    /**
     * Get the row actions HTML for the form name column.
     *
     * @since 1.8.6
     *
     * @param WP_Post $form Form object.
     *
     * @return string
     */
    protected function get_column_name_row_actions($form)
    {
        $actions = [];

        if ('trash' === $form->post_status) {
            $actions['untrash'] = sprintf(
                '<a href="%s" class="submitdelete" onclick="return confirm(\'Are you sure you want to delete %s form?\');">%s</a>',
                esc_url(wp_nonce_url("post.php?action=untrash&post={$form->ID}", 'untrash-post_' . $form->ID)),
                $form->post_title . '(' . $form->ID . ')',
                __('Restore', 'smart-form-builder-by-dragwyb')
            );
            $actions['delete'] = sprintf(
                '<a href="%s" class="submitdelete" onclick="return confirm(\'Are you sure you want to delete %s form?\');">%s</a>',
                esc_url(wp_nonce_url("post.php?action=delete&post={$form->ID}", 'delete-post_' . $form->ID)),
                $form->post_title . '(' . $form->ID . ')',
                __('Delete', 'smart-form-builder-by-dragwyb')
            );
        } else {
            $actions['edit'] = '<a href="?page=dragwyb-form-builder&form_id=' . (int) esc_attr($form->ID) . '">Edit</a>';
            $actions['view'] = '<a href="' . esc_url($this->get_preview_url($form->ID)) . '" target="_blank">View</a>';
            $actions['trash'] = sprintf(
                '<a href="%s" class="submitdelete" onclick="return confirm(\'Are you sure you want to delete %s form?\');">%s</a>',
                esc_url(wp_nonce_url("post.php?action=trash&post={$form->ID}", 'trash-post_' . $form->ID)),
                $form->post_title . '(' . $form->ID . ')',
                __('Trash', 'smart-form-builder-by-dragwyb')
            );
        }

        // Add more actions if necessary, such as delete, etc.

        return $this->row_actions($actions);
    }

    /**
     * Define bulk actions available for our table listing.
     *
     * @since 1.8.6
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- for post status check
        if (isset($_REQUEST['post_status']) && $_REQUEST['post_status'] === 'trash') {
            return [
                'delete' => 'Delete Permanently',
                'untrash' => 'Restore',
            ];
        }

        return [
            'trash' => 'Move to Trash',
        ];
    }


    public function get_views()
    {
        $statuses = [
            'all'      => ['label' => 'All'],
            'publish'  => ['label' => 'Published'],
            'draft'    => ['label' => 'Draft'],
            'trash'    => ['label' => 'Trash'],
        ];

        $views = [];
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- for post status check
        $current = isset($_REQUEST['post_status']) ? sanitize_text_field(wp_unslash($_REQUEST['post_status'])) : 'all';

        // Get post counts
        $post_counts = wp_count_posts(Dragwyb_Post::POST_TYPE);

        // Build each view link
        foreach ($statuses as $key => $val) {
            // Get the count for the status
            if ($key === 'all') {
                $count = ($post_counts->publish ?? 0) + ($post_counts->draft ?? 0);
                $url = remove_query_arg(array('post_status', 'paged', 's'));
            } else {
                $count = $post_counts->{$key} ?? 0;
                $url = add_query_arg('post_status', $key);
                $url = remove_query_arg(array('paged', 's'), $url);
            }

            if ($count > 0) {
                $views[$key] = sprintf(
                    '<a href="%s"%s>%s <span class="count">(%d)</span></a>',
                    esc_url($url),
                    $current === $key ? ' class="current"' : '',
                    esc_html($val['label']),
                    absint($count)
                );
            }
        }

        return $views;
    }

    /**
     * Fetch and set up the final data for the table.
     *
     * @since 1.8.6
     */
    public function prepare_items()
    {
        // 1. Setup columns
        $columns  = $this->get_columns();
        $hidden   = get_hidden_columns($this->screen);
        $sortable = [
            'id'      => ['ID', false],
            'name'    => ['title', false],
            'author'  => ['author', false],
            'created' => ['date', false],
        ];
        $this->_column_headers = [$columns, $hidden, $sortable];

        // 2. Setup pagination, sorting, and status filters
        $current_page = $this->get_pagenum();
        $per_page     = $this->get_items_per_page('dragwyb_forms_per_page', $this->per_page);

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- for order check
        $orderby      = sanitize_key(wp_unslash($_GET['orderby'] ?? 'ID'));

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- for order check
        $order        = strtoupper(sanitize_text_field(wp_unslash($_GET['order'] ?? 'DESC')));
        $order        = in_array($order, ['ASC', 'DESC'], true) ? $order : 'DESC';

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- for post status check
        $status = isset($_GET['post_status']) ? sanitize_key($_GET['post_status']) : 'all';

        switch ($status) {
            case 'publish':
            case 'draft':
            case 'trash':
                $post_status = $status;
                break;

            default:
                $post_status = array('publish', 'draft'); // ✅ Compatible syntax for all versions
                break;
        }

        // 4. Query the forms
        $args = [
            'post_type'      => Dragwyb_Post::POST_TYPE,
            'post_status'    => $post_status,
            'orderby'        => $orderby,
            'order'          => $order,
            'paged'          => $current_page,
            'posts_per_page' => $per_page,
            'no_found_rows'  => false, // needed for pagination
        ];

        $this->items = get_posts($args);

        $post_counts = wp_count_posts(Dragwyb_Post::POST_TYPE);

        $total_items = 0;

        switch ($status) {
            case 'all':
                $total_items = ($post_counts->publish ?? 0) + ($post_counts->draft ?? 0);
                break;
            default:
                $total_items = $post_counts->{$status} ?? 0;
                break;
        }

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil($total_items / $per_page),
        ]);
    }

    /**
     * Display the pagination.
     *
     * @since 1.8.6
     *
     * @param string $which The location of the table pagination: 'top' or 'bottom'.
     */
    protected function pagination($which)
    {
        if ($this->has_items()) {
            parent::pagination($which);
        } else {
            echo '<div class="tablenav-pages one-page"><span class="displaying-num">0 items</span></div>';
        }
    }

    /**
     * Message to be displayed when there are no forms.
     *
     * @since 1.8.6
     */
    public function no_items()
    {
        esc_html_e('No forms found.', 'smart-form-builder-by-dragwyb');
    }
}
