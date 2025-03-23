<?php
declare(strict_types=1);

class Dragwyb_Payments {
    private const PAYMENT_POST_TYPE = 'dragwyb_payment';
    private const SUPPORTED_CURRENCIES = ['USD', 'EUR', 'GBP', 'CAD', 'AUD'];
    private $gateways = [];

    public function __construct() {
        add_action('init', [$this, 'register_payment_post_type']);
        add_action('init', [$this, 'register_payment_gateways']);
        add_action('dragwyb_before_form_submit', [$this, 'process_payment'], 10, 2);
        add_action('dragwyb_after_payment_success', [$this, 'handle_successful_payment'], 10, 3);
        add_action('dragwyb_after_payment_failure', [$this, 'handle_failed_payment'], 10, 3);
        add_action('wp_ajax_dragwyb_get_payment_status', [$this, 'get_payment_status']);
        add_action('wp_ajax_nopriv_dragwyb_get_payment_status', [$this, 'get_payment_status']);
    }

    /**
     * Register payment post type
     */
    public function register_payment_post_type(): void {
        register_post_type(self::PAYMENT_POST_TYPE, [
            'labels' => [
                'name' => __('Payments', 'dragwyb-form-builder'),
                'singular_name' => __('Payment', 'dragwyb-form-builder')
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => 'dragwyb_forms',
            'supports' => ['title'],
            'capability_type' => 'post',
            'capabilities' => [
                'create_posts' => false
            ],
            'map_meta_cap' => true
        ]);

        // Register payment status taxonomy
        register_taxonomy(
            'dragwyb_payment_status',
            self::PAYMENT_POST_TYPE,
            [
                'labels' => [
                    'name' => __('Payment Status', 'dragwyb-form-builder'),
                    'singular_name' => __('Payment Status', 'dragwyb-form-builder')
                ],
                'hierarchical' => false,
                'public' => false,
                'show_ui' => true
            ]
        );

        // Add default payment statuses
        $statuses = ['pending', 'processing', 'completed', 'failed', 'refunded'];
        foreach ($statuses as $status) {
            if (!term_exists($status, 'dragwyb_payment_status')) {
                wp_insert_term($status, 'dragwyb_payment_status');
            }
        }
    }

    /**
     * Register payment gateways
     */
    public function register_payment_gateways(): void {
        $this->gateways = [
            'stripe' => new Dragwyb_Stripe_Gateway(),
            'paypal' => new Dragwyb_PayPal_Gateway(),
            'square' => new Dragwyb_Square_Gateway()
        ];

        do_action('dragwyb_register_payment_gateways', $this->gateways);
    }

    /**
     * Process payment
     */
    public function process_payment(array $form_data, int $form_id): void {
        $payment_settings = get_post_meta($form_id, '_payment_settings', true) ?: [];
        
        if (empty($payment_settings['enabled'])) {
            return;
        }

        try {
            $payment_data = $this->prepare_payment_data($form_data, $payment_settings);
            $gateway = $this->get_gateway($payment_settings['gateway']);

            if (!$gateway) {
                throw new Exception(__('Payment gateway not configured.', 'dragwyb-form-builder'));
            }

            $payment_id = $this->create_payment_record($payment_data);
            $result = $gateway->process_payment($payment_data);

            if ($result['success']) {
                do_action('dragwyb_after_payment_success', $payment_id, $result, $form_data);
            } else {
                do_action('dragwyb_after_payment_failure', $payment_id, $result, $form_data);
                throw new Exception($result['message']);
            }

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Prepare payment data
     */
    private function prepare_payment_data(array $form_data, array $payment_settings): array {
        $amount = $this->calculate_payment_amount($form_data, $payment_settings);
        
        return [
            'amount' => $amount,
            'currency' => $payment_settings['currency'] ?? 'USD',
            'description' => $payment_settings['description'] ?? '',
            'customer' => [
                'name' => $form_data['name'] ?? '',
                'email' => $form_data['email'] ?? '',
                'phone' => $form_data['phone'] ?? ''
            ],
            'metadata' => [
                'form_id' => $form_data['form_id'],
                'submission_id' => $form_data['submission_id']
            ],
            'billing' => $this->get_billing_details($form_data),
            'subscription' => $this->get_subscription_details($payment_settings)
        ];
    }

    /**
     * Calculate payment amount
     */
    private function calculate_payment_amount(array $form_data, array $payment_settings): float {
        $amount = 0;

        switch ($payment_settings['amount_type']) {
            case 'fixed':
                $amount = floatval($payment_settings['fixed_amount']);
                break;

            case 'field':
                $field = $payment_settings['amount_field'];
                $amount = floatval($form_data[$field] ?? 0);
                break;

            case 'calculation':
                $amount = $this->calculate_dynamic_amount($form_data, $payment_settings['calculation']);
                break;
        }

        // Apply discounts
        if (!empty($payment_settings['discounts'])) {
            $amount = $this->apply_discounts($amount, $payment_settings['discounts'], $form_data);
        }

        // Apply tax
        if (!empty($payment_settings['tax_rate'])) {
            $amount = $this->apply_tax($amount, floatval($payment_settings['tax_rate']));
        }

        return round($amount, 2);
    }

    /**
     * Calculate dynamic amount
     */
    private function calculate_dynamic_amount(array $form_data, string $calculation): float {
        // Replace field placeholders with actual values
        preg_match_all('/\{([^}]+)\}/', $calculation, $matches);
        
        foreach ($matches[1] as $field) {
            $value = floatval($form_data[$field] ?? 0);
            $calculation = str_replace("{{$field}}", (string)$value, $calculation);
        }

        // Evaluate the calculation
        try {
            $calculation = preg_replace('/[^0-9+\-.*\/()%]/', '', $calculation);
            return floatval(eval("return $calculation;"));
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Apply discounts
     */
    private function apply_discounts(float $amount, array $discounts, array $form_data): float {
        foreach ($discounts as $discount) {
            if (!$this->is_discount_valid($discount, $form_data)) {
                continue;
            }

            if ($discount['type'] === 'percentage') {
                $amount -= ($amount * ($discount['value'] / 100));
            } else {
                $amount -= $discount['value'];
            }
        }

        return max(0, $amount);
    }

    /**
     * Check if discount is valid
     */
    private function is_discount_valid(array $discount, array $form_data): bool {
        // Check if discount code matches
        if (!empty($discount['code'])) {
            $submitted_code = $form_data['discount_code'] ?? '';
            if ($submitted_code !== $discount['code']) {
                return false;
            }
        }

        // Check if discount is within date range
        if (!empty($discount['start_date']) && strtotime($discount['start_date']) > time()) {
            return false;
        }

        if (!empty($discount['end_date']) && strtotime($discount['end_date']) < time()) {
            return false;
        }

        // Check usage limit
        if (!empty($discount['usage_limit'])) {
            $usage_count = $this->get_discount_usage_count($discount['code']);
            if ($usage_count >= $discount['usage_limit']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Apply tax
     */
    private function apply_tax(float $amount, float $tax_rate): float {
        return $amount * (1 + ($tax_rate / 100));
    }

    /**
     * Get billing details
     */
    private function get_billing_details(array $form_data): array {
        return [
            'address' => [
                'line1' => $form_data['billing_address'] ?? '',
                'line2' => $form_data['billing_address_2'] ?? '',
                'city' => $form_data['billing_city'] ?? '',
                'state' => $form_data['billing_state'] ?? '',
                'postal_code' => $form_data['billing_zip'] ?? '',
                'country' => $form_data['billing_country'] ?? ''
            ]
        ];
    }

    /**
     * Get subscription details
     */
    private function get_subscription_details(array $payment_settings): ?array {
        if (empty($payment_settings['subscription'])) {
            return null;
        }

        return [
            'plan_id' => $payment_settings['subscription']['plan_id'],
            'interval' => $payment_settings['subscription']['interval'],
            'interval_count' => $payment_settings['subscription']['interval_count'],
            'trial_period_days' => $payment_settings['subscription']['trial_period_days'] ?? 0
        ];
    }

    /**
     * Create payment record
     */
    private function create_payment_record(array $payment_data): int {
        $payment_id = wp_insert_post([
            'post_title' => sprintf(
                __('Payment for %s', 'dragwyb-form-builder'),
                $payment_data['metadata']['submission_id']
            ),
            'post_type' => self::PAYMENT_POST_TYPE,
            'post_status' => 'publish'
        ]);

        if (is_wp_error($payment_id)) {
            throw new Exception($payment_id->get_error_message());
        }

        update_post_meta($payment_id, '_payment_data', $payment_data);
        wp_set_object_terms($payment_id, 'pending', 'dragwyb_payment_status');

        return $payment_id;
    }

    /**
     * Handle successful payment
     */
    public function handle_successful_payment(int $payment_id, array $result, array $form_data): void {
        // Update payment status
        wp_set_object_terms($payment_id, 'completed', 'dragwyb_payment_status');

        // Store transaction details
        update_post_meta($payment_id, '_transaction_id', $result['transaction_id']);
        update_post_meta($payment_id, '_payment_method', $result['payment_method']);

        // Send notifications
        $this->send_payment_notifications($payment_id, $form_data, 'success');

        // Trigger success actions
        do_action('dragwyb_payment_completed', $payment_id, $result, $form_data);
    }

    /**
     * Handle failed payment
     */
    public function handle_failed_payment(int $payment_id, array $result, array $form_data): void {
        // Update payment status
        wp_set_object_terms($payment_id, 'failed', 'dragwyb_payment_status');

        // Store error details
        update_post_meta($payment_id, '_error_message', $result['message']);
        update_post_meta($payment_id, '_error_code', $result['error_code'] ?? '');

        // Send notifications
        $this->send_payment_notifications($payment_id, $form_data, 'failure');

        // Trigger failure actions
        do_action('dragwyb_payment_failed', $payment_id, $result, $form_data);
    }

    /**
     * Send payment notifications
     */
    private function send_payment_notifications(int $payment_id, array $form_data, string $type): void {
        $notifications = new Dragwyb_Notifications();
        
        $payment_data = get_post_meta($payment_id, '_payment_data', true);
        $transaction_id = get_post_meta($payment_id, '_transaction_id', true);

        $notification_data = array_merge($form_data, [
            'payment_id' => $payment_id,
            'payment_amount' => $payment_data['amount'],
            'payment_currency' => $payment_data['currency'],
            'transaction_id' => $transaction_id,
            'payment_status' => $type === 'success' ? 'completed' : 'failed'
        ]);

        if ($type === 'success') {
            $notifications->send_notifications($form_data['form_id'], $notification_data);
        } else {
            // Send failure notification to admin
            $admin_email = get_option('admin_email');
            $notifications->send_notifications($form_data['form_id'], $notification_data, [$admin_email]);
        }
    }

    /**
     * Get payment status
     */
    public function get_payment_status(): void {
        try {
            check_ajax_referer('dragwyb_payment_status');

            $payment_id = absint($_POST['payment_id'] ?? 0);
            if (!$payment_id) {
                throw new Exception(__('Invalid payment ID.', 'dragwyb-form-builder'));
            }

            $status = wp_get_object_terms($payment_id, 'dragwyb_payment_status');
            if (is_wp_error($status)) {
                throw new Exception($status->get_error_message());
            }

            wp_send_json_success([
                'status' => $status[0]->slug ?? 'unknown',
                'transaction_id' => get_post_meta($payment_id, '_transaction_id', true)
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get gateway instance
     */
    private function get_gateway(string $gateway_id) {
        return $this->gateways[$gateway_id] ?? null;
    }

    /**
     * Get discount usage count
     */
    private function get_discount_usage_count(string $code): int {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM $wpdb->postmeta
            WHERE meta_key = '_payment_data'
            AND meta_value LIKE %s
        ", '%' . $wpdb->esc_like($code) . '%'));

        return (int)$count;
    }
} 