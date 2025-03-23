<?php
declare(strict_types=1);

class Dragwyb_Notifications {
    private const TEMPLATE_POST_TYPE = 'dragwyb_email_template';
    private const DEFAULT_TEMPLATES = [
        'admin_notification',
        'user_confirmation',
        'payment_confirmation',
        'form_abandonment'
    ];

    public function __construct() {
        add_action('init', [$this, 'register_template_post_type']);
        add_action('dragwyb_form_submitted', [$this, 'send_notifications'], 10, 2);
        add_action('dragwyb_form_abandoned', [$this, 'send_abandonment_notification'], 10, 2);
        add_action('wp_ajax_dragwyb_save_email_template', [$this, 'save_template']);
        add_action('wp_ajax_dragwyb_test_email_template', [$this, 'test_template']);
        add_filter('dragwyb_email_content', [$this, 'process_smart_tags'], 10, 3);
    }

    /**
     * Register email template post type
     */
    public function register_template_post_type(): void {
        register_post_type(self::TEMPLATE_POST_TYPE, [
            'labels' => [
                'name' => __('Email Templates', 'dragwyb-form-builder'),
                'singular_name' => __('Email Template', 'dragwyb-form-builder')
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'dragwyb_forms',
            'supports' => ['title', 'editor'],
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => 'manage_options',
                'edit_posts' => 'manage_options',
                'delete_posts' => 'manage_options'
            ]
        ]);

        $this->create_default_templates();
    }

    /**
     * Create default email templates
     */
    private function create_default_templates(): void {
        foreach (self::DEFAULT_TEMPLATES as $template_slug) {
            $template = get_page_by_path($template_slug, OBJECT, self::TEMPLATE_POST_TYPE);
            
            if (!$template) {
                $template_data = $this->get_default_template_data($template_slug);
                
                wp_insert_post([
                    'post_title' => $template_data['title'],
                    'post_content' => $template_data['content'],
                    'post_type' => self::TEMPLATE_POST_TYPE,
                    'post_status' => 'publish',
                    'post_name' => $template_slug
                ]);
            }
        }
    }

    /**
     * Get default template data
     */
    private function get_default_template_data(string $template_slug): array {
        $templates = [
            'admin_notification' => [
                'title' => __('Admin Notification', 'dragwyb-form-builder'),
                'content' => $this->get_admin_notification_template()
            ],
            'user_confirmation' => [
                'title' => __('User Confirmation', 'dragwyb-form-builder'),
                'content' => $this->get_user_confirmation_template()
            ],
            'payment_confirmation' => [
                'title' => __('Payment Confirmation', 'dragwyb-form-builder'),
                'content' => $this->get_payment_confirmation_template()
            ],
            'form_abandonment' => [
                'title' => __('Form Abandonment', 'dragwyb-form-builder'),
                'content' => $this->get_form_abandonment_template()
            ]
        ];

        return $templates[$template_slug] ?? [
            'title' => __('Custom Template', 'dragwyb-form-builder'),
            'content' => ''
        ];
    }

    /**
     * Send form notifications
     */
    public function send_notifications(int $form_id, array $submission_data): void {
        $notifications = get_post_meta($form_id, '_form_notifications', true) ?: [];

        foreach ($notifications as $notification) {
            if (!$this->should_send_notification($notification, $submission_data)) {
                continue;
            }

            $this->send_email_notification($notification, $submission_data);
        }
    }

    /**
     * Send abandonment notification
     */
    public function send_abandonment_notification(int $form_id, array $partial_data): void {
        $settings = get_post_meta($form_id, '_form_settings', true) ?: [];
        
        if (empty($settings['enable_abandonment_emails']) || empty($partial_data['email'])) {
            return;
        }

        $template = $this->get_template_by_slug('form_abandonment');
        if (!$template) {
            return;
        }

        $notification = [
            'to' => $partial_data['email'],
            'subject' => __('Complete Your Form Submission', 'dragwyb-form-builder'),
            'template_id' => $template->ID,
            'from_name' => get_bloginfo('name'),
            'from_email' => get_bloginfo('admin_email')
        ];

        $this->send_email_notification($notification, $partial_data);
    }

    /**
     * Check if notification should be sent
     */
    private function should_send_notification(array $notification, array $submission_data): bool {
        if (empty($notification['active'])) {
            return false;
        }

        if (!empty($notification['conditions'])) {
            foreach ($notification['conditions'] as $condition) {
                if (!$this->evaluate_condition($condition, $submission_data)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Send email notification
     */
    private function send_email_notification(array $notification, array $submission_data): bool {
        try {
            $to = $this->process_smart_tags($notification['to'], $submission_data);
            $subject = $this->process_smart_tags($notification['subject'], $submission_data);
            
            $template = get_post($notification['template_id']);
            if (!$template) {
                throw new Exception('Email template not found.');
            }

            $content = $this->process_smart_tags($template->post_content, $submission_data);
            $headers = $this->get_email_headers($notification);

            $attachments = $this->get_attachments($notification, $submission_data);
            
            return wp_mail($to, $subject, $content, $headers, $attachments);

        } catch (Exception $e) {
            error_log('Dragwyb Notification Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Process smart tags in content
     */
    public function process_smart_tags(string $content, array $data, array $extra = []): string {
        $tags = [
            'all_fields' => $this->get_all_fields_table($data),
            'form_name' => get_the_title($data['form_id'] ?? 0),
            'submission_date' => current_time('mysql'),
            'submission_time' => current_time('timestamp'),
            'user_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'site_name' => get_bloginfo('name'),
            'admin_email' => get_bloginfo('admin_email'),
            'site_url' => get_site_url()
        ];

        // Add form field values
        foreach ($data as $key => $value) {
            $tags["field_$key"] = is_array($value) ? implode(', ', $value) : $value;
        }

        // Add extra tags
        $tags = array_merge($tags, $extra);

        // Replace tags
        foreach ($tags as $tag => $value) {
            $content = str_replace("{{$tag}}", $value, $content);
        }

        return $content;
    }

    /**
     * Get email headers
     */
    private function get_email_headers(array $notification): array {
        $headers = [];

        if (!empty($notification['from_name']) && !empty($notification['from_email'])) {
            $headers[] = sprintf(
                'From: %s <%s>',
                $notification['from_name'],
                $notification['from_email']
            );
        }

        if (!empty($notification['reply_to'])) {
            $headers[] = sprintf('Reply-To: %s', $notification['reply_to']);
        }

        if (!empty($notification['cc'])) {
            $headers[] = sprintf('Cc: %s', $notification['cc']);
        }

        if (!empty($notification['bcc'])) {
            $headers[] = sprintf('Bcc: %s', $notification['bcc']);
        }

        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        return $headers;
    }

    /**
     * Get email attachments
     */
    private function get_attachments(array $notification, array $submission_data): array {
        $attachments = [];

        // Add form file uploads
        if (!empty($notification['include_uploads'])) {
            foreach ($submission_data as $field) {
                if (is_array($field) && !empty($field['type']) && $field['type'] === 'file') {
                    $attachments[] = $field['path'];
                }
            }
        }

        // Add custom attachments
        if (!empty($notification['attachments'])) {
            foreach ($notification['attachments'] as $attachment) {
                if (file_exists($attachment)) {
                    $attachments[] = $attachment;
                }
            }
        }

        return $attachments;
    }

    /**
     * Save email template
     */
    public function save_template(): void {
        try {
            check_ajax_referer('dragwyb_save_template');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $template_id = absint($_POST['template_id'] ?? 0);
            $content = wp_kses_post($_POST['content'] ?? '');
            $settings = json_decode(stripslashes($_POST['settings'] ?? '{}'), true);

            if (!$content) {
                throw new Exception(__('Template content is required.', 'dragwyb-form-builder'));
            }

            if ($template_id) {
                wp_update_post([
                    'ID' => $template_id,
                    'post_content' => $content
                ]);
            } else {
                $template_id = wp_insert_post([
                    'post_title' => $settings['name'] ?? __('Custom Template', 'dragwyb-form-builder'),
                    'post_content' => $content,
                    'post_type' => self::TEMPLATE_POST_TYPE,
                    'post_status' => 'publish'
                ]);
            }

            update_post_meta($template_id, '_template_settings', $settings);

            wp_send_json_success([
                'message' => __('Template saved successfully.', 'dragwyb-form-builder'),
                'template_id' => $template_id
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Test email template
     */
    public function test_template(): void {
        try {
            check_ajax_referer('dragwyb_test_template');

            if (!current_user_can('manage_options')) {
                throw new Exception(__('Permission denied.', 'dragwyb-form-builder'));
            }

            $template_id = absint($_POST['template_id'] ?? 0);
            $test_email = sanitize_email($_POST['test_email'] ?? '');

            if (!$template_id || !$test_email) {
                throw new Exception(__('Invalid template ID or email address.', 'dragwyb-form-builder'));
            }

            $template = get_post($template_id);
            if (!$template) {
                throw new Exception(__('Template not found.', 'dragwyb-form-builder'));
            }

            $notification = [
                'to' => $test_email,
                'subject' => sprintf(
                    __('Test Email: %s', 'dragwyb-form-builder'),
                    $template->post_title
                ),
                'template_id' => $template_id,
                'from_name' => get_bloginfo('name'),
                'from_email' => get_bloginfo('admin_email')
            ];

            $test_data = $this->get_test_data();

            if ($this->send_email_notification($notification, $test_data)) {
                wp_send_json_success([
                    'message' => __('Test email sent successfully.', 'dragwyb-form-builder')
                ]);
            } else {
                throw new Exception(__('Failed to send test email.', 'dragwyb-form-builder'));
            }

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get test data for email preview
     */
    private function get_test_data(): array {
        return [
            'form_id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123-456-7890',
            'message' => 'This is a test message.',
            'submission_id' => '12345',
            'payment_amount' => '99.99',
            'payment_status' => 'completed'
        ];
    }

    /**
     * Get template by slug
     */
    private function get_template_by_slug(string $slug) {
        return get_page_by_path($slug, OBJECT, self::TEMPLATE_POST_TYPE);
    }

    /**
     * Get all fields table HTML
     */
    private function get_all_fields_table(array $data): string {
        $html = '<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">';
        $html .= '<tr style="background-color: #f8f9fa;">';
        $html .= '<th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Field</th>';
        $html .= '<th style="padding: 10px; border: 1px solid #dee2e6; text-align: left;">Value</th>';
        $html .= '</tr>';

        foreach ($data as $key => $value) {
            if (in_array($key, ['form_id', 'submission_id'])) {
                continue;
            }

            $html .= '<tr>';
            $html .= sprintf(
                '<td style="padding: 10px; border: 1px solid #dee2e6;">%s</td>',
                esc_html(ucwords(str_replace('_', ' ', $key)))
            );
            $html .= sprintf(
                '<td style="padding: 10px; border: 1px solid #dee2e6;">%s</td>',
                is_array($value) ? implode(', ', $value) : esc_html($value)
            );
            $html .= '</tr>';
        }

        $html .= '</table>';
        return $html;
    }

    /**
     * Get admin notification template
     */
    private function get_admin_notification_template(): string {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>New Form Submission</h2>
                </div>
                <div class="content">
                    <p>A new submission has been received from {form_name}.</p>
                    <p>Submission Details:</p>
                    {all_fields}
                    <p>Submitted on: {submission_date}</p>
                    <p>IP Address: {user_ip}</p>
                </div>
                <div class="footer">
                    <p>This email was sent from {site_name}</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Get user confirmation template
     */
    private function get_user_confirmation_template(): string {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Thank You for Your Submission</h2>
                </div>
                <div class="content">
                    <p>Dear {field_name},</p>
                    <p>Thank you for submitting the {form_name} form. We have received your information and will process it shortly.</p>
                    <p>Here's a copy of your submission:</p>
                    {all_fields}
                    <p>If you have any questions, please don't hesitate to contact us.</p>
                </div>
                <div class="footer">
                    <p>This email was sent from {site_name}</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Get payment confirmation template
     */
    private function get_payment_confirmation_template(): string {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Payment Confirmation</h2>
                </div>
                <div class="content">
                    <p>Dear {field_name},</p>
                    <p>Your payment has been successfully processed.</p>
                    <p>Payment Details:</p>
                    <ul>
                        <li>Amount: {field_payment_amount}</li>
                        <li>Status: {field_payment_status}</li>
                        <li>Transaction ID: {field_transaction_id}</li>
                    </ul>
                    <p>Thank you for your business!</p>
                </div>
                <div class="footer">
                    <p>This email was sent from {site_name}</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Get form abandonment template
     */
    private function get_form_abandonment_template(): string {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #f8f9fa; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                .button { display: inline-block; padding: 10px 20px; background: #007bff; color: #fff; text-decoration: none; border-radius: 5px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h2>Complete Your Form Submission</h2>
                </div>
                <div class="content">
                    <p>Hello,</p>
                    <p>We noticed you started filling out our form but haven't completed it yet. Would you like to continue where you left off?</p>
                    <p style="text-align: center;">
                        <a href="{field_resume_url}" class="button">Complete Form</a>
                    </p>
                    <p>If you're having any issues or need assistance, please don't hesitate to contact us.</p>
                </div>
                <div class="footer">
                    <p>This email was sent from {site_name}</p>
                    <p>If you don't want to receive these emails, you can <a href="{field_unsubscribe_url}">unsubscribe</a>.</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
} 