<?php
declare(strict_types=1);

use Dragwyb\Form_Builder\Includes\Modules\Module;

class Dragwyb_Form_Builder_Submission {
    private const SUBMISSION_POST_TYPE = 'dragwyb_submission';

    public function __construct() {
        add_action('init', [$this, 'register_submission_post_type']);
        add_action('wp_ajax_dragwyb_submit_form', [$this, 'handle_form_submission']);
        add_action('wp_ajax_nopriv_dragwyb_submit_form', [$this, 'handle_form_submission']);
    }

    public function register_submission_post_type(): void {
        register_post_type(self::SUBMISSION_POST_TYPE, [
            'labels' => [
                'name' => __('Form Submissions', 'dragwyb-form-builder'),
                'singular_name' => __('Form Submission', 'dragwyb-form-builder'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'edit.php?post_type=dragwyb_form',
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'do_not_allow',
            ],
            'map_meta_cap' => true,
            'supports' => ['title'],
        ]);
    }

    public function handle_form_submission(): void {
        // Verify nonce
        if (!check_ajax_referer('dragwyb_form_submission', 'nonce', false)) {
            wp_send_json_error(['message' => __('Invalid security token.', 'dragwyb-form-builder')]);
        }

        $form_id = intval($_POST['form_id'] ?? 0);
        if (!$form_id) {
            wp_send_json_error(['message' => __('Invalid form ID.', 'dragwyb-form-builder')]);
        }

        // Get form data
        $form_data = get_post_meta($form_id, '_dragwyb_form_data', true);
        if (!$form_data) {
            wp_send_json_error(['message' => __('Form not found.', 'dragwyb-form-builder')]);
        }

        // Validate submission
        $validation_result = $this->validate_submission($_POST['fields'] ?? [], $form_data);
        if (is_wp_error($validation_result)) {
            wp_send_json_error([
                'message' => $validation_result->get_error_message(),
                'errors' => $validation_result->get_error_data(),
            ]);
        }

        // Store submission
        $submission_id = $this->store_submission($form_id, $validation_result);
        if (is_wp_error($submission_id)) {
            wp_send_json_error(['message' => $submission_id->get_error_message()]);
        }

        // Send notifications
        $this->send_notifications($form_id, $submission_id, $validation_result);

        wp_send_json_success([
            'message' => __('Form submitted successfully!', 'dragwyb-form-builder'),
            'submission_id' => $submission_id,
        ]);
    }

    private function validate_submission(array $submitted_fields, array $form_data): array|WP_Error {
        $errors = [];
        $sanitized_data = [];

        foreach ($form_data['fields'] as $field) {
            $field_id = $field['id'];
            $field_type = $field['type'];
            $submitted_value = $submitted_fields[$field_id] ?? null;

            // Get field handler
            $field_handler = Module::instance()->get_field($field_type);
            if (!$field_handler) {
                continue;
            }

            // Validate field
            if (!$field_handler->validate($submitted_value)) {
                $errors[$field_id] = sprintf(
                    __('Invalid value for field "%s"', 'dragwyb-form-builder'),
                    $field['label']
                );
                continue;
            }

            // Sanitize field
            $sanitized_data[$field_id] = $field_handler->sanitize($submitted_value);
        }

        if (!empty($errors)) {
            $error = new WP_Error('validation_failed', __('Form validation failed.', 'dragwyb-form-builder'));
            $error->add_data($errors);
            return $error;
        }

        return $sanitized_data;
    }

    private function store_submission(int $form_id, array $submission_data): int|WP_Error {
        $submission = [
            'post_title' => sprintf(
                __('Submission #%s', 'dragwyb-form-builder'),
                uniqid()
            ),
            'post_type' => self::SUBMISSION_POST_TYPE,
            'post_status' => 'publish',
            'post_author' => get_current_user_id(),
        ];

        $submission_id = wp_insert_post($submission, true);
        if (is_wp_error($submission_id)) {
            return $submission_id;
        }

        // Store submission data
        update_post_meta($submission_id, '_form_id', $form_id);
        update_post_meta($submission_id, '_submission_data', $submission_data);
        update_post_meta($submission_id, '_submission_ip', $_SERVER['REMOTE_ADDR']);
        update_post_meta($submission_id, '_submission_date', current_time('mysql'));

        return $submission_id;
    }

    private function send_notifications(int $form_id, int $submission_id, array $submission_data): void {
        // Get form settings
        $form_settings = get_post_meta($form_id, '_dragwyb_form_settings', true);
        
        // Send admin notification
        if (!empty($form_settings['admin_notification'])) {
            $this->send_admin_notification($form_id, $submission_id, $submission_data);
        }

        // Send user notification
        if (!empty($form_settings['user_notification']) && !empty($submission_data['email'])) {
            $this->send_user_notification($form_id, $submission_data);
        }
    }

    private function send_admin_notification(int $form_id, int $submission_id, array $submission_data): void {
        $form = get_post($form_id);
        $admin_email = get_option('admin_email');
        
        $subject = sprintf(
            __('New form submission: %s', 'dragwyb-form-builder'),
            $form->post_title
        );

        $message = $this->get_notification_message($submission_data, 'admin');

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        wp_mail($admin_email, $subject, $message, $headers);
    }

    private function send_user_notification(int $form_id, array $submission_data): void {
        $form = get_post($form_id);
        $user_email = $submission_data['email'];

        $subject = sprintf(
            __('Thank you for your submission: %s', 'dragwyb-form-builder'),
            $form->post_title
        );

        $message = $this->get_notification_message($submission_data, 'user');

        $headers = ['Content-Type: text/html; charset=UTF-8'];
        
        wp_mail($user_email, $subject, $message, $headers);
    }

    private function get_notification_message(array $submission_data, string $type): string {
        ob_start();
        
        if ($type === 'admin') {
            include DRAGWYB_FORM_BUILDER_PATH . 'templates/emails/admin-notification.php';
        } else {
            include DRAGWYB_FORM_BUILDER_PATH . 'templates/emails/user-notification.php';
        }
        
        return ob_get_clean();
    }
} 