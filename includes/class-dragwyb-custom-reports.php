<?php
declare(strict_types=1);

class Dragwyb_Custom_Reports {
    private const REPORT_POST_TYPE = 'dragwyb_report';
    private const SCHEDULE_OPTION = 'dragwyb_report_schedules';

    public function __construct() {
        add_action('init', [$this, 'register_report_post_type']);
        add_action('dragwyb_generate_scheduled_reports', [$this, 'generate_scheduled_reports']);
        add_action('admin_post_dragwyb_save_report', [$this, 'save_custom_report']);
        add_action('wp_ajax_dragwyb_preview_report', [$this, 'preview_report']);
    }

    /**
     * Register custom report post type
     */
    public function register_report_post_type(): void {
        register_post_type(self::REPORT_POST_TYPE, [
            'labels' => [
                'name' => __('Custom Reports', 'dragwyb-form-builder'),
                'singular_name' => __('Custom Report', 'dragwyb-form-builder'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'dragwyb-forms',
            'supports' => ['title', 'custom-fields'],
            'capability_type' => 'post',
        ]);
    }

    /**
     * Save custom report configuration
     */
    public function save_custom_report(): void {
        try {
            check_admin_referer('dragwyb_save_report');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $report_data = [
                'title' => sanitize_text_field($_POST['report_title']),
                'forms' => array_map('absint', (array) $_POST['report_forms']),
                'metrics' => array_map('sanitize_text_field', (array) $_POST['report_metrics']),
                'filters' => $this->sanitize_filters($_POST['report_filters'] ?? []),
                'schedule' => [
                    'frequency' => sanitize_text_field($_POST['schedule_frequency']),
                    'recipients' => array_map('sanitize_email', explode(',', $_POST['schedule_recipients'])),
                    'format' => sanitize_text_field($_POST['report_format']),
                ],
            ];

            $report_id = wp_insert_post([
                'post_type' => self::REPORT_POST_TYPE,
                'post_title' => $report_data['title'],
                'post_status' => 'publish',
                'meta_input' => [
                    '_report_config' => $report_data,
                ],
            ]);

            if (is_wp_error($report_id)) {
                throw new Exception($report_id->get_error_message());
            }

            $this->schedule_report($report_id, $report_data['schedule']);

            wp_redirect(add_query_arg([
                'page' => 'dragwyb-reports',
                'message' => 'report_saved',
            ], admin_url('admin.php')));
            exit;

        } catch (Exception $e) {
            wp_die($e->getMessage());
        }
    }

    /**
     * Generate scheduled reports
     */
    public function generate_scheduled_reports(): void {
        $reports = get_posts([
            'post_type' => self::REPORT_POST_TYPE,
            'posts_per_page' => -1,
        ]);

        foreach ($reports as $report) {
            $config = get_post_meta($report->ID, '_report_config', true);
            
            if (empty($config['schedule'])) {
                continue;
            }

            $data = $this->generate_report_data($config);
            $file = $this->generate_report_file($data, $config['schedule']['format']);
            
            $this->send_report_email(
                $config['schedule']['recipients'],
                $config['title'],
                $file
            );
        }
    }

    /**
     * Preview report
     */
    public function preview_report(): void {
        try {
            check_ajax_referer('dragwyb_preview_report');

            $config = [
                'forms' => array_map('absint', (array) $_POST['forms']),
                'metrics' => array_map('sanitize_text_field', (array) $_POST['metrics']),
                'filters' => $this->sanitize_filters($_POST['filters'] ?? []),
            ];

            $data = $this->generate_report_data($config);
            
            wp_send_json_success([
                'html' => $this->generate_report_preview($data),
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate report data
     */
    private function generate_report_data(array $config): array {
        $data = [];

        foreach ($config['forms'] as $form_id) {
            $form_data = [
                'form_name' => get_the_title($form_id),
                'metrics' => [],
            ];

            foreach ($config['metrics'] as $metric) {
                $form_data['metrics'][$metric] = $this->calculate_metric($metric, $form_id, $config['filters']);
            }

            $data[$form_id] = $form_data;
        }

        return $data;
    }

    /**
     * Calculate specific metric
     */
    private function calculate_metric(string $metric, int $form_id, array $filters): array {
        global $wpdb;

        $where_clauses = $this->build_filter_clauses($filters);
        $where_sql = $where_clauses ? 'AND ' . implode(' AND ', $where_clauses) : '';

        switch ($metric) {
            case 'submission_count':
                return $this->get_submission_count($form_id, $where_sql);

            case 'conversion_rate':
                return $this->get_conversion_rate($form_id, $where_sql);

            case 'field_completion':
                return $this->get_field_completion_rates($form_id, $where_sql);

            case 'error_rates':
                return $this->get_error_rates($form_id, $where_sql);

            case 'submission_time':
                return $this->get_submission_times($form_id, $where_sql);

            case 'user_demographics':
                return $this->get_user_demographics($form_id, $where_sql);

            default:
                return [];
        }
    }

    /**
     * Generate report file
     */
    private function generate_report_file(array $data, string $format): string {
        $filename = 'report-' . date('Y-m-d-His') . '.' . $format;
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $filename;

        switch ($format) {
            case 'pdf':
                require_once DRAGWYB_PATH . 'vendor/autoload.php';
                $pdf = new TCPDF();
                $pdf->AddPage();
                $pdf->writeHTML($this->generate_report_html($data));
                $pdf->Output($file_path, 'F');
                break;

            case 'xlsx':
                require_once DRAGWYB_PATH . 'vendor/autoload.php';
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $this->write_report_to_spreadsheet($sheet, $data);
                $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
                $writer->save($file_path);
                break;

            default:
                file_put_contents($file_path, json_encode($data, JSON_PRETTY_PRINT));
        }

        return $file_path;
    }

    /**
     * Send report email
     */
    private function send_report_email(array $recipients, string $title, string $file): void {
        $subject = sprintf(
            __('Form Report: %s - %s', 'dragwyb-form-builder'),
            $title,
            date_i18n(get_option('date_format'))
        );

        $message = sprintf(
            __('Please find attached the automated report for %s.', 'dragwyb-form-builder'),
            $title
        );

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        wp_mail(
            $recipients,
            $subject,
            $message,
            $headers,
            [$file]
        );
    }

    /**
     * Schedule report generation
     */
    private function schedule_report(int $report_id, array $schedule): void {
        if (empty($schedule['frequency'])) {
            return;
        }

        $schedules = get_option(self::SCHEDULE_OPTION, []);
        $schedules[$report_id] = [
            'frequency' => $schedule['frequency'],
            'next_run' => $this->calculate_next_run($schedule['frequency']),
        ];

        update_option(self::SCHEDULE_OPTION, $schedules);
    }

    /**
     * Calculate next run time
     */
    private function calculate_next_run(string $frequency): int {
        $now = current_time('timestamp');

        switch ($frequency) {
            case 'daily':
                return strtotime('tomorrow', $now);
            case 'weekly':
                return strtotime('next monday', $now);
            case 'monthly':
                return strtotime('first day of next month', $now);
            default:
                return $now;
        }
    }

    /**
     * Sanitize report filters
     */
    private function sanitize_filters(array $filters): array {
        $sanitized = [];

        foreach ($filters as $filter) {
            $sanitized[] = [
                'field' => sanitize_text_field($filter['field']),
                'operator' => sanitize_text_field($filter['operator']),
                'value' => sanitize_text_field($filter['value']),
            ];
        }

        return $sanitized;
    }

    /**
     * Build SQL where clauses from filters
     */
    private function build_filter_clauses(array $filters): array {
        global $wpdb;

        $clauses = [];

        foreach ($filters as $filter) {
            switch ($filter['operator']) {
                case 'equals':
                    $clauses[] = $wpdb->prepare(
                        "meta_value = %s",
                        $filter['value']
                    );
                    break;

                case 'contains':
                    $clauses[] = $wpdb->prepare(
                        "meta_value LIKE %s",
                        '%' . $wpdb->esc_like($filter['value']) . '%'
                    );
                    break;

                case 'greater_than':
                    $clauses[] = $wpdb->prepare(
                        "meta_value > %s",
                        $filter['value']
                    );
                    break;

                case 'less_than':
                    $clauses[] = $wpdb->prepare(
                        "meta_value < %s",
                        $filter['value']
                    );
                    break;
            }
        }

        return $clauses;
    }
} 