<?php
declare(strict_types=1);

class Dragwyb_Entry_Manager {
    private const ENTRIES_PER_PAGE = 20;
    private const ENTRY_POST_TYPE = 'dragwyb_entry';

    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks(): void {
        add_action('init', [$this, 'register_post_type']);
        add_action('admin_menu', [$this, 'add_menu_pages']);
        add_action('admin_init', [$this, 'handle_bulk_actions']);
        add_action('wp_ajax_dragwyb_get_entry_details', [$this, 'get_entry_details']);
        add_action('wp_ajax_dragwyb_update_entry_status', [$this, 'update_entry_status']);
        add_action('wp_ajax_dragwyb_delete_entry', [$this, 'delete_entry']);
        add_action('wp_ajax_dragwyb_export_entries', [$this, 'export_entries']);
        
        // Add columns to entries list
        add_filter('manage_dragwyb_entry_posts_columns', [$this, 'set_entry_columns']);
        add_action('manage_dragwyb_entry_posts_custom_column', [$this, 'render_entry_column'], 10, 2);
        add_filter('manage_edit-dragwyb_entry_sortable_columns', [$this, 'set_sortable_columns']);
    }

    /**
     * Register entry post type
     */
    public function register_post_type(): void {
        register_post_type(self::ENTRY_POST_TYPE, [
            'labels' => [
                'name' => __('Form Entries', 'dragwyb-form-builder'),
                'singular_name' => __('Form Entry', 'dragwyb-form-builder'),
                'menu_name' => __('Entries', 'dragwyb-form-builder'),
                'all_items' => __('All Entries', 'dragwyb-form-builder'),
                'view_item' => __('View Entry', 'dragwyb-form-builder'),
                'search_items' => __('Search Entries', 'dragwyb-form-builder'),
                'not_found' => __('No entries found', 'dragwyb-form-builder'),
                'not_found_in_trash' => __('No entries found in trash', 'dragwyb-form-builder'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'dragwyb-forms',
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => false,
            ],
            'map_meta_cap' => true,
            'supports' => ['title'],
            'menu_icon' => 'dashicons-list-view',
        ]);
    }

    /**
     * Add menu pages
     */
    public function add_menu_pages(): void {
        add_submenu_page(
            'dragwyb-forms',
            __('Form Entries', 'dragwyb-form-builder'),
            __('Entries', 'dragwyb-form-builder'),
            'manage_options',
            'edit.php?post_type=' . self::ENTRY_POST_TYPE
        );
    }

    /**
     * Set entry list columns
     */
    public function set_entry_columns($columns): array {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'title' => __('Entry ID', 'dragwyb-form-builder'),
            'form' => __('Form', 'dragwyb-form-builder'),
            'status' => __('Status', 'dragwyb-form-builder'),
            'submitted_by' => __('Submitted By', 'dragwyb-form-builder'),
            'submitted_on' => __('Submitted On', 'dragwyb-form-builder'),
            'actions' => __('Actions', 'dragwyb-form-builder'),
        ];

        return $columns;
    }

    /**
     * Render entry column content
     */
    public function render_entry_column(string $column, int $post_id): void {
        switch ($column) {
            case 'form':
                $form_id = get_post_meta($post_id, '_form_id', true);
                $form = get_post($form_id);
                echo $form ? esc_html($form->post_title) : __('Unknown Form', 'dragwyb-form-builder');
                break;

            case 'status':
                $status = get_post_meta($post_id, '_entry_status', true) ?: 'unread';
                $this->render_status_badge($status);
                break;

            case 'submitted_by':
                $this->render_submitter_info($post_id);
                break;

            case 'submitted_on':
                echo get_the_date('Y-m-d H:i:s', $post_id);
                break;

            case 'actions':
                $this->render_entry_actions($post_id);
                break;
        }
    }

    /**
     * Set sortable columns
     */
    public function set_sortable_columns($columns): array {
        $columns['form'] = 'form';
        $columns['status'] = 'status';
        $columns['submitted_on'] = 'date';
        return $columns;
    }

    /**
     * Handle bulk actions
     */
    public function handle_bulk_actions(): void {
        if (!isset($_POST['action']) || !isset($_POST['entries'])) {
            return;
        }

        check_admin_referer('bulk-posts');

        $action = sanitize_text_field($_POST['action']);
        $entries = array_map('absint', $_POST['entries']);

        switch ($action) {
            case 'mark_read':
                $this->bulk_update_status($entries, 'read');
                break;

            case 'mark_unread':
                $this->bulk_update_status($entries, 'unread');
                break;

            case 'mark_spam':
                $this->bulk_update_status($entries, 'spam');
                break;

            case 'delete':
                $this->bulk_delete_entries($entries);
                break;

            case 'export':
                $this->export_selected_entries($entries);
                break;
        }
    }

    /**
     * Get entry details via AJAX
     */
    public function get_entry_details(): void {
        try {
            check_ajax_referer('dragwyb_entry_manager');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $entry_id = absint($_POST['entry_id']);
            $entry = get_post($entry_id);

            if (!$entry || $entry->post_type !== self::ENTRY_POST_TYPE) {
                throw new Exception(__('Entry not found.', 'dragwyb-form-builder'));
            }

            $data = [
                'id' => $entry->ID,
                'form_id' => get_post_meta($entry->ID, '_form_id', true),
                'status' => get_post_meta($entry->ID, '_entry_status', true) ?: 'unread',
                'submitted_on' => get_the_date('Y-m-d H:i:s', $entry->ID),
                'fields' => $this->get_entry_fields($entry->ID),
                'files' => $this->get_entry_files($entry->ID),
                'notes' => $this->get_entry_notes($entry->ID),
                'submitter' => $this->get_submitter_info($entry->ID),
            ];

            wp_send_json_success($data);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update entry status via AJAX
     */
    public function update_entry_status(): void {
        try {
            check_ajax_referer('dragwyb_entry_manager');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $entry_id = absint($_POST['entry_id']);
            $status = sanitize_text_field($_POST['status']);

            if (!in_array($status, ['read', 'unread', 'spam'])) {
                throw new Exception(__('Invalid status.', 'dragwyb-form-builder'));
            }

            update_post_meta($entry_id, '_entry_status', $status);

            wp_send_json_success([
                'message' => __('Status updated successfully.', 'dragwyb-form-builder'),
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Delete entry via AJAX
     */
    public function delete_entry(): void {
        try {
            check_ajax_referer('dragwyb_entry_manager');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $entry_id = absint($_POST['entry_id']);
            
            // Delete associated files
            $this->delete_entry_files($entry_id);
            
            // Delete the entry
            wp_delete_post($entry_id, true);

            wp_send_json_success([
                'message' => __('Entry deleted successfully.', 'dragwyb-form-builder'),
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Export entries via AJAX
     */
    public function export_entries(): void {
        try {
            check_ajax_referer('dragwyb_entry_manager');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $format = sanitize_text_field($_POST['format'] ?? 'csv');
            $form_id = absint($_POST['form_id'] ?? 0);
            $status = sanitize_text_field($_POST['status'] ?? '');
            $date_range = [
                'start' => sanitize_text_field($_POST['date_start'] ?? ''),
                'end' => sanitize_text_field($_POST['date_end'] ?? ''),
            ];

            $entries = $this->get_entries_for_export($form_id, $status, $date_range);
            $this->export_entries_as($entries, $format);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Render status badge
     */
    private function render_status_badge(string $status): void {
        $labels = [
            'unread' => __('Unread', 'dragwyb-form-builder'),
            'read' => __('Read', 'dragwyb-form-builder'),
            'spam' => __('Spam', 'dragwyb-form-builder'),
        ];

        $classes = [
            'unread' => 'status-unread',
            'read' => 'status-read',
            'spam' => 'status-spam',
        ];

        printf(
            '<span class="entry-status %s">%s</span>',
            esc_attr($classes[$status] ?? ''),
            esc_html($labels[$status] ?? $status)
        );
    }

    /**
     * Render submitter info
     */
    private function render_submitter_info(int $entry_id): void {
        $submitter = $this->get_submitter_info($entry_id);

        if ($submitter['user_id']) {
            $user = get_user_by('id', $submitter['user_id']);
            echo esc_html($user->display_name);
        } else {
            echo esc_html($submitter['ip_address']);
        }
    }

    /**
     * Render entry actions
     */
    private function render_entry_actions(int $entry_id): void {
        ?>
        <div class="entry-actions">
            <button type="button" 
                    class="button view-entry" 
                    data-entry-id="<?php echo esc_attr($entry_id); ?>">
                <?php esc_html_e('View', 'dragwyb-form-builder'); ?>
            </button>
            
            <button type="button" 
                    class="button delete-entry" 
                    data-entry-id="<?php echo esc_attr($entry_id); ?>">
                <?php esc_html_e('Delete', 'dragwyb-form-builder'); ?>
            </button>
        </div>
        <?php
    }

    /**
     * Get entry fields
     */
    private function get_entry_fields(int $entry_id): array {
        $fields = get_post_meta($entry_id, '_entry_fields', true) ?: [];
        $form_id = get_post_meta($entry_id, '_form_id', true);
        
        // Get field labels from form configuration
        $form_fields = get_post_meta($form_id, '_form_fields', true) ?: [];
        $field_labels = [];
        
        foreach ($form_fields as $field) {
            $field_labels[$field['id']] = $field['label'];
        }

        // Add labels to fields
        foreach ($fields as &$field) {
            $field['label'] = $field_labels[$field['id']] ?? $field['id'];
        }

        return $fields;
    }

    /**
     * Get entry files
     */
    private function get_entry_files(int $entry_id): array {
        $files = get_post_meta($entry_id, '_entry_files', true) ?: [];
        
        foreach ($files as &$file) {
            $file['url'] = wp_get_attachment_url($file['attachment_id']);
        }

        return $files;
    }

    /**
     * Get entry notes
     */
    private function get_entry_notes(int $entry_id): array {
        return get_post_meta($entry_id, '_entry_notes', true) ?: [];
    }

    /**
     * Get submitter info
     */
    private function get_submitter_info(int $entry_id): array {
        return [
            'user_id' => get_post_meta($entry_id, '_user_id', true),
            'ip_address' => get_post_meta($entry_id, '_ip_address', true),
            'user_agent' => get_post_meta($entry_id, '_user_agent', true),
            'referer' => get_post_meta($entry_id, '_referer', true),
        ];
    }

    /**
     * Delete entry files
     */
    private function delete_entry_files(int $entry_id): void {
        $files = get_post_meta($entry_id, '_entry_files', true) ?: [];
        
        foreach ($files as $file) {
            wp_delete_attachment($file['attachment_id'], true);
        }
    }

    /**
     * Bulk update status
     */
    private function bulk_update_status(array $entries, string $status): void {
        foreach ($entries as $entry_id) {
            update_post_meta($entry_id, '_entry_status', $status);
        }
    }

    /**
     * Bulk delete entries
     */
    private function bulk_delete_entries(array $entries): void {
        foreach ($entries as $entry_id) {
            $this->delete_entry_files($entry_id);
            wp_delete_post($entry_id, true);
        }
    }

    /**
     * Get entries for export
     */
    private function get_entries_for_export(int $form_id, string $status, array $date_range): array {
        $args = [
            'post_type' => self::ENTRY_POST_TYPE,
            'posts_per_page' => -1,
            'meta_query' => [],
        ];

        if ($form_id) {
            $args['meta_query'][] = [
                'key' => '_form_id',
                'value' => $form_id,
            ];
        }

        if ($status) {
            $args['meta_query'][] = [
                'key' => '_entry_status',
                'value' => $status,
            ];
        }

        if ($date_range['start'] || $date_range['end']) {
            $args['date_query'] = [];

            if ($date_range['start']) {
                $args['date_query']['after'] = $date_range['start'];
            }

            if ($date_range['end']) {
                $args['date_query']['before'] = $date_range['end'];
            }
        }

        $entries = [];
        $query = new WP_Query($args);

        while ($query->have_posts()) {
            $query->the_post();
            $entries[] = [
                'id' => get_the_ID(),
                'form_id' => get_post_meta(get_the_ID(), '_form_id', true),
                'status' => get_post_meta(get_the_ID(), '_entry_status', true),
                'submitted_on' => get_the_date('Y-m-d H:i:s'),
                'fields' => $this->get_entry_fields(get_the_ID()),
                'submitter' => $this->get_submitter_info(get_the_ID()),
            ];
        }

        wp_reset_postdata();
        return $entries;
    }

    /**
     * Export entries in specified format
     */
    private function export_entries_as(array $entries, string $format): void {
        switch ($format) {
            case 'csv':
                $this->export_as_csv($entries);
                break;

            case 'json':
                $this->export_as_json($entries);
                break;

            case 'xlsx':
                $this->export_as_xlsx($entries);
                break;

            default:
                throw new Exception(__('Invalid export format.', 'dragwyb-form-builder'));
        }
    }

    /**
     * Export as CSV
     */
    private function export_as_csv(array $entries): void {
        $filename = 'form-entries-' . date('Y-m-d-His') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Write headers
        fputcsv($output, [
            'Entry ID',
            'Form',
            'Status',
            'Submitted On',
            'Submitted By',
            'Fields',
        ]);

        // Write data
        foreach ($entries as $entry) {
            fputcsv($output, [
                $entry['id'],
                get_the_title($entry['form_id']),
                $entry['status'],
                $entry['submitted_on'],
                $entry['submitter']['user_id'] ? 
                    get_user_by('id', $entry['submitter']['user_id'])->display_name : 
                    $entry['submitter']['ip_address'],
                json_encode($entry['fields']),
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export as JSON
     */
    private function export_as_json(array $entries): void {
        $filename = 'form-entries-' . date('Y-m-d-His') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo json_encode($entries, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Export as XLSX
     */
    private function export_as_xlsx(array $entries): void {
        require_once DRAGWYB_PATH . 'vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->fromArray([
            'Entry ID',
            'Form',
            'Status',
            'Submitted On',
            'Submitted By',
            'Fields',
        ], null, 'A1');

        // Add data
        $row = 2;
        foreach ($entries as $entry) {
            $sheet->fromArray([
                $entry['id'],
                get_the_title($entry['form_id']),
                $entry['status'],
                $entry['submitted_on'],
                $entry['submitter']['user_id'] ? 
                    get_user_by('id', $entry['submitter']['user_id'])->display_name : 
                    $entry['submitter']['ip_address'],
                json_encode($entry['fields']),
            ], null, 'A' . $row);
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="form-entries-' . date('Y-m-d-His') . '.xlsx"');
        
        $writer->save('php://output');
        exit;
    }
} 