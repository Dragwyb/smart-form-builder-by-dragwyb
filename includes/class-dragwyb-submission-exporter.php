<?php
declare(strict_types=1);

class Dragwyb_Submission_Exporter {
    private const BATCH_SIZE = 100;
    private const ALLOWED_FORMATS = ['csv', 'json', 'xlsx'];

    /**
     * Initialize exporter
     */
    public function __construct() {
        add_action('admin_post_dragwyb_export_submissions', [$this, 'handle_export_request']);
        add_action('admin_init', [$this, 'register_export_settings']);
    }

    /**
     * Handle export request
     */
    public function handle_export_request(): void {
        try {
            // Verify permissions
            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            // Verify nonce
            check_admin_referer('dragwyb_export_submissions');

            // Get export parameters
            $format = $_POST['export_format'] ?? 'csv';
            $form_id = absint($_POST['form_id'] ?? 0);
            $date_range = [
                'start' => sanitize_text_field($_POST['date_start'] ?? ''),
                'end' => sanitize_text_field($_POST['date_end'] ?? ''),
            ];

            if (!in_array($format, self::ALLOWED_FORMATS)) {
                throw new Exception(__('Invalid export format.', 'dragwyb-form-builder'));
            }

            // Generate and send file
            $this->export_submissions($format, $form_id, $date_range);

        } catch (Exception $e) {
            wp_die($e->getMessage());
        }
    }

    /**
     * Export submissions
     */
    private function export_submissions(string $format, int $form_id, array $date_range): void {
        $submissions = $this->get_submissions($form_id, $date_range);
        
        switch ($format) {
            case 'csv':
                $this->export_csv($submissions);
                break;
            case 'json':
                $this->export_json($submissions);
                break;
            case 'xlsx':
                $this->export_xlsx($submissions);
                break;
        }
    }

    /**
     * Get submissions query
     */
    private function get_submissions(int $form_id, array $date_range): WP_Query {
        $args = [
            'post_type' => 'dragwyb_submission',
            'posts_per_page' => -1,
            'meta_query' => [],
        ];

        if ($form_id) {
            $args['meta_query'][] = [
                'key' => '_form_id',
                'value' => $form_id,
            ];
        }

        if (!empty($date_range['start'])) {
            $args['date_query']['after'] = $date_range['start'];
        }

        if (!empty($date_range['end'])) {
            $args['date_query']['before'] = $date_range['end'];
        }

        return new WP_Query($args);
    }

    /**
     * Export as CSV
     */
    private function export_csv(WP_Query $submissions): void {
        $filename = 'form-submissions-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Write headers
        fputcsv($output, [
            'ID',
            'Form ID',
            'Date',
            'IP Address',
            'User Agent',
            'Fields',
        ]);

        // Write data
        while ($submissions->have_posts()) {
            $submissions->the_post();
            $submission_data = get_post_meta(get_the_ID(), '_submission_data', true);
            
            fputcsv($output, [
                get_the_ID(),
                get_post_meta(get_the_ID(), '_form_id', true),
                get_the_date('Y-m-d H:i:s'),
                get_post_meta(get_the_ID(), '_user_ip', true),
                get_post_meta(get_the_ID(), '_user_agent', true),
                json_encode($submission_data),
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Export as JSON
     */
    private function export_json(WP_Query $submissions): void {
        $filename = 'form-submissions-' . date('Y-m-d') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $data = [];
        
        while ($submissions->have_posts()) {
            $submissions->the_post();
            $data[] = [
                'id' => get_the_ID(),
                'form_id' => get_post_meta(get_the_ID(), '_form_id', true),
                'date' => get_the_date('Y-m-d H:i:s'),
                'ip_address' => get_post_meta(get_the_ID(), '_user_ip', true),
                'user_agent' => get_post_meta(get_the_ID(), '_user_agent', true),
                'fields' => get_post_meta(get_the_ID(), '_submission_data', true),
            ];
        }

        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Export as XLSX
     */
    private function export_xlsx(WP_Query $submissions): void {
        require_once DRAGWYB_PATH . 'vendor/autoload.php';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->fromArray([
            'ID',
            'Form ID',
            'Date',
            'IP Address',
            'User Agent',
            'Fields',
        ], null, 'A1');

        // Add data
        $row = 2;
        while ($submissions->have_posts()) {
            $submissions->the_post();
            $submission_data = get_post_meta(get_the_ID(), '_submission_data', true);

            $sheet->fromArray([
                get_the_ID(),
                get_post_meta(get_the_ID(), '_form_id', true),
                get_the_date('Y-m-d H:i:s'),
                get_post_meta(get_the_ID(), '_user_ip', true),
                get_post_meta(get_the_ID(), '_user_agent', true),
                json_encode($submission_data),
            ], null, 'A' . $row);

            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="form-submissions-' . date('Y-m-d') . '.xlsx"');
        
        $writer->save('php://output');
        exit;
    }
} 