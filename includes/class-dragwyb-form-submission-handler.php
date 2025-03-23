<?php
declare(strict_types=1);

class Dragwyb_Form_Submission_Handler {
    private const SUBMISSION_POST_TYPE = 'dragwyb_submission';
    private const NONCE_ACTION = 'dragwyb_form_submit';
    private $db;
    private $mailer;
    private $logger;

    public function __construct() {
        global $wpdb;
        $this->db = $wpdb;
        
        // Initialize dependencies
        add_action('init', [$this, 'register_submission_post_type']);
        add_action('wp_ajax_dragwyb_submit_form', [$this, 'handle_ajax_submission']);
        add_action('wp_ajax_nopriv_dragwyb_submit_form', [$this, 'handle_ajax_submission']);
    }

    /**
     * Register submission post type for storing form submissions
     */
    public function register_submission_post_type(): void {
        register_post_type(self::SUBMISSION_POST_TYPE, [
            'labels' => [
                'name' => __('Form Submissions', 'dragwyb-form-builder'),
                'singular_name' => __('Form Submission', 'dragwyb-form-builder'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'dragwyb-forms',
            'capability_type' => 'post',
            'supports' => ['title', 'custom-fields'],
            'menu_icon' => 'dashicons-feedback',
        ]);
    }

    /**
     * Handle form submission via AJAX
     */
    public function handle_ajax_submission(): void {
        try {
            // Verify nonce
            if (!check_ajax_referer(self::NONCE_ACTION, 'nonce', false)) {
                throw new Exception(__('Security check failed.', 'dragwyb-form-builder'));
            }

            // Get and validate form data
            $form_id = absint($_POST['form_id'] ?? 0);
            $form_data = $this->sanitize_form_data($_POST['form_data'] ?? []);

            if (!$form_id || empty($form_data)) {
                throw new Exception(__('Invalid form data.', 'dragwyb-form-builder'));
            }

            // Process submission
            $submission_id = $this->process_submission($form_id, $form_data);

            wp_send_json_success([
                'message' => __('Form submitted successfully.', 'dragwyb-form-builder'),
                'submission_id' => $submission_id,
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Process form submission
     */
    private function process_submission(int $form_id, array $form_data): int {
        // Start transaction
        $this->db->query('START TRANSACTION');

        try {
            // Store submission
            $submission_id = $this->store_submission($form_id, $form_data);

            // Handle file uploads if any
            $this->handle_file_uploads($submission_id, $form_data);

            // Process form actions (email, webhook, etc.)
            $this->process_form_actions($form_id, $submission_id, $form_data);

            // Commit transaction
            $this->db->query('COMMIT');

            return $submission_id;

        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->query('ROLLBACK');
            throw $e;
        }
    }

    /**
     * Store form submission in database
     */
    private function store_submission(int $form_id, array $form_data): int {
        $submission = [
            'post_type' => self::SUBMISSION_POST_TYPE,
            'post_title' => sprintf(
                __('Form Submission #%s', 'dragwyb-form-builder'),
                uniqid()
            ),
            'post_status' => 'publish',
            'meta_input' => [
                '_form_id' => $form_id,
                '_submission_data' => $form_data,
                '_submission_date' => current_time('mysql'),
                '_user_ip' => $this->get_user_ip(),
                '_user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ],
        ];

        $submission_id = wp_insert_post($submission);

        if (is_wp_error($submission_id)) {
            throw new Exception($submission_id->get_error_message());
        }

        return $submission_id;
    }

    /**
     * Handle file uploads
     */
    private function handle_file_uploads(int $submission_id, array $form_data): void {
        foreach ($form_data as $field_id => $value) {
            if (!is_array($value) || empty($value['tmp_name'])) {
                continue;
            }

            $upload = wp_handle_upload($value, ['test_form' => false]);

            if (!empty($upload['error'])) {
                throw new Exception($upload['error']);
            }

            // Store file information
            add_post_meta($submission_id, "_file_{$field_id}", [
                'url' => $upload['url'],
                'file' => $upload['file'],
                'type' => $upload['type'],
            ]);
        }
    }

    /**
     * Process form actions
     */
    private function process_form_actions(int $form_id, int $submission_id, array $form_data): void {
        $actions = get_post_meta($form_id, '_form_actions', true) ?: [];

        foreach ($actions as $action) {
            switch ($action['type']) {
                case 'email':
                    $this->send_email_notification($action, $form_data, $submission_id);
                    break;

                case 'webhook':
                    $this->send_webhook_notification($action, $form_data, $submission_id);
                    break;

                case 'redirect':
                    // Store redirect URL for client-side handling
                    update_post_meta($submission_id, '_redirect_url', $action['url']);
                    break;
            }
        }
    }

    /**
     * Send email notification
     */
    private function send_email_notification(array $action, array $form_data, int $submission_id): void {
        $to = $this->parse_template($action['to'], $form_data);
        $subject = $this->parse_template($action['subject'], $form_data);
        $message = $this->parse_template($action['message'], $form_data);

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        if (!empty($action['from_email'])) {
            $headers[] = 'From: ' . $action['from_name'] . ' <' . $action['from_email'] . '>';
        }

        $result = wp_mail($to, $subject, $message, $headers);

        // Log email result
        add_post_meta($submission_id, '_email_sent', [
            'to' => $to,
            'subject' => $subject,
            'success' => $result,
            'timestamp' => current_time('mysql'),
        ]);
    }

    /**
     * Send webhook notification
     */
    private function send_webhook_notification(array $action, array $form_data, int $submission_id): void {
        $response = wp_remote_post($action['url'], [
            'body' => [
                'form_data' => $form_data,
                'submission_id' => $submission_id,
            ],
            'headers' => [
                'Content-Type' => 'application/json',
                'X-Dragwyb-Signature' => $this->generate_webhook_signature($form_data),
            ],
        ]);

        // Log webhook result
        add_post_meta($submission_id, '_webhook_sent', [
            'url' => $action['url'],
            'success' => !is_wp_error($response),
            'response' => is_wp_error($response) ? $response->get_error_message() : wp_remote_retrieve_response_code($response),
            'timestamp' => current_time('mysql'),
        ]);
    }

    /**
     * Parse template with form data
     */
    private function parse_template(string $template, array $form_data): string {
        return preg_replace_callback('/\{\{(.+?)\}\}/', function($matches) use ($form_data) {
            $field = trim($matches[1]);
            return $form_data[$field] ?? '';
        }, $template);
    }

    /**
     * Generate webhook signature
     */
    private function generate_webhook_signature(array $data): string {
        $secret = get_option('dragwyb_webhook_secret');
        return hash_hmac('sha256', json_encode($data), $secret);
    }

    /**
     * Get user IP address
     */
    private function get_user_ip(): string {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        }
        return sanitize_text_field($ip);
    }

    /**
     * Sanitize form data
     */
    private function sanitize_form_data(array $data): array {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitize_form_data($value);
            } else {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }
        return $sanitized;
    }
} 