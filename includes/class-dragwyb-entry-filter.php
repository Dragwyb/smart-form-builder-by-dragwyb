<?php
declare(strict_types=1);

class Dragwyb_Entry_Filter {
    /**
     * Initialize filters
     */
    public function __construct() {
        add_action('restrict_manage_posts', [$this, 'add_filter_fields']);
        add_filter('parse_query', [$this, 'filter_entries_query']);
        add_filter('posts_where', [$this, 'filter_entries_where'], 10, 2);
        add_filter('posts_join', [$this, 'filter_entries_join'], 10, 2);
    }

    /**
     * Add filter fields to entries list
     */
    public function add_filter_fields(): void {
        global $typenow;

        if ($typenow !== 'dragwyb_entry') {
            return;
        }

        // Form filter
        $forms = get_posts([
            'post_type' => 'dragwyb_form',
            'posts_per_page' => -1,
        ]);

        echo '<select name="form_id">';
        echo '<option value="">' . esc_html__('All Forms', 'dragwyb-form-builder') . '</option>';
        
        foreach ($forms as $form) {
            $selected = isset($_GET['form_id']) && $_GET['form_id'] == $form->ID ? 'selected' : '';
            echo sprintf(
                '<option value="%d" %s>%s</option>',
                $form->ID,
                $selected,
                esc_html($form->post_title)
            );
        }
        echo '</select>';

        // Status filter
        $statuses = [
            'all' => __('All Status', 'dragwyb-form-builder'),
            'unread' => __('Unread', 'dragwyb-form-builder'),
            'read' => __('Read', 'dragwyb-form-builder'),
            'spam' => __('Spam', 'dragwyb-form-builder'),
        ];

        echo '<select name="entry_status">';
        foreach ($statuses as $value => $label) {
            $selected = isset($_GET['entry_status']) && $_GET['entry_status'] === $value ? 'selected' : '';
            echo sprintf(
                '<option value="%s" %s>%s</option>',
                esc_attr($value),
                $selected,
                esc_html($label)
            );
        }
        echo '</select>';

        // Date range filter
        $start_date = $_GET['start_date'] ?? '';
        $end_date = $_GET['end_date'] ?? '';

        echo '<input type="date" name="start_date" value="' . esc_attr($start_date) . '" placeholder="' . esc_attr__('Start Date', 'dragwyb-form-builder') . '">';
        echo '<input type="date" name="end_date" value="' . esc_attr($end_date) . '" placeholder="' . esc_attr__('End Date', 'dragwyb-form-builder') . '">';

        // Field search
        echo '<input type="text" name="field_search" value="' . esc_attr($_GET['field_search'] ?? '') . '" placeholder="' . esc_attr__('Search in fields...', 'dragwyb-form-builder') . '">';
    }

    /**
     * Filter entries query
     */
    public function filter_entries_query($query): void {
        global $pagenow, $typenow;

        if (!is_admin() || $pagenow !== 'edit.php' || $typenow !== 'dragwyb_entry') {
            return;
        }

        $meta_query = [];

        // Form filter
        if (!empty($_GET['form_id'])) {
            $meta_query[] = [
                'key' => '_form_id',
                'value' => absint($_GET['form_id']),
            ];
        }

        // Status filter
        if (!empty($_GET['entry_status']) && $_GET['entry_status'] !== 'all') {
            $meta_query[] = [
                'key' => '_entry_status',
                'value' => sanitize_text_field($_GET['entry_status']),
            ];
        }

        // Date range filter
        if (!empty($_GET['start_date']) || !empty($_GET['end_date'])) {
            $date_query = [];

            if (!empty($_GET['start_date'])) {
                $date_query['after'] = sanitize_text_field($_GET['start_date']);
            }

            if (!empty($_GET['end_date'])) {
                $date_query['before'] = sanitize_text_field($_GET['end_date']);
            }

            $query->set('date_query', $date_query);
        }

        if (!empty($meta_query)) {
            $query->set('meta_query', $meta_query);
        }
    }

    /**
     * Filter entries where clause for field search
     */
    public function filter_entries_where(string $where, WP_Query $query): string {
        global $wpdb;

        if (!is_admin() || !$query->is_main_query() || empty($_GET['field_search'])) {
            return $where;
        }

        $search_term = '%' . $wpdb->esc_like($_GET['field_search']) . '%';

        $where .= $wpdb->prepare(
            " AND (
                pm.meta_key = '_entry_fields'
                AND pm.meta_value LIKE %s
            )",
            $search_term
        );

        return $where;
    }

    /**
     * Add join clause for field search
     */
    public function filter_entries_join(string $join, WP_Query $query): string {
        global $wpdb;

        if (!is_admin() || !$query->is_main_query() || empty($_GET['field_search'])) {
            return $join;
        }

        $join .= " LEFT JOIN {$wpdb->postmeta} pm ON {$wpdb->posts}.ID = pm.post_id ";

        return $join;
    }
} 