<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Form_Overview;

use WP_List_Table;
use WP_Post;
use WP_Screen;
use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;

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

    /**
     * Primary class constructor.
     *
     * @since 1.8.6
     */
    public function __construct()
    {
        parent::__construct(
            [
                'singular' => 'form',
                'plural'   => 'forms',
                'ajax'     => false,
            ]
        );

        $this->per_page = (int) apply_filters(DRAGWYB_PREFIX . '_overview_per_page', 20);
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
            'name' => 'Name',
            'author' => 'Author',
            'shortcode' => 'Shortcode',
            'id' => 'ID',
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
        return '<input type="checkbox" name="form_id[]" value="' . absint($form->ID) . '" />';
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

            case 'php':
                $value = '<code>if ( function_exists( \'my_form_function\' ) ) { my_form_function( ' . $form->ID . ' ); }</code>';
                break;

            default:
                $value = '';
        }

        return apply_filters('dragwyb_form_overview_column_value', $value, $form, $column_name);
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
        $preview_url = get_permalink($form->ID);
        $value = sprintf(
            '<a href="%s" target="_blank">%s</a>',
            esc_url($preview_url),
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

        $actions['edit'] = '<a href="?page=dragwyb-form-builder&form_id='.(int) esc_attr($form->ID).'">Edit</a>';
        $actions['delete'] = sprintf(
            '<a href="%s" class="submitdelete" onclick="return confirm(\'Are you sure you want to delete %s form?\');">%s</a>',
            esc_url( wp_nonce_url( "post.php?action=trash&post={$form->ID}", 'trash-post_' . $form->ID ) ),
            $form->post_title.'('.$form->ID.')',
            __( 'Delete Permanently' )
        );
        
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
        return [
            'delete' => 'Delete',
            // Add more bulk actions if needed
        ];
    }


    /**
     * Fetch and set up the final data for the table.
     *
     * @since 1.8.6
     */
    public function prepare_items()
    {
        // Set up columns
        $columns = $this->get_columns();
        $hidden = get_hidden_columns($this->screen);
        $sortable = [
            'id'      => ['ID', false],
            'name'    => ['title', false],
            'author'  => ['author', false],
            'created' => ['date', false],
        ];

        // Set column headers
        $this->_column_headers = [$columns, $hidden, $sortable];

        // Query arguments
        $page = $this->get_pagenum();
        $order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'ASC' : 'DESC';
        $orderby = isset($_GET['orderby']) ? sanitize_key($_GET['orderby']) : 'ID';
        $per_page = $this->get_items_per_page('dragwyb_forms_per_page', $this->per_page);

        // Modify the query args based on the order and pagination
        $args = [
            'orderby'        => $orderby,
            'order'          => $order,
            'nopaging'       => false,
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'post_status'    => array('publish','draft'),
            'post_type'      => Dragwyb_Post::POST_TYPE
        ];

        // Get the posts (forms)
        $this->items = get_posts($args);

        // Update the form counts (total forms, etc.)
        $this->update_count($args);

        // Pagination
        $this->set_pagination_args([
            'total_items' => $this->count['all'] ?? 0,
            'per_page'    => $per_page,
            'total_pages' => ceil($this->count['all'] / $per_page),
        ]);
    }

    /**
     * Calculate and update form counts.
     *
     * @since 1.8.6
     *
     * @param array $args Get forms arguments.
     */
    private function update_count($args)
    {
        $this->count = [];
        $this->count['all'] = wp_count_posts(Dragwyb_Post::POST_TYPE)->publish;
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
        esc_html_e('No forms found.', 'dragwyb-form-builder');
    }
}
