<?php
declare(strict_types=1);

class Dragwyb_Stripe_Gateway {
    private $stripe;
    private $secret_key;
    private $publishable_key;

    public function __construct() {
        $this->init_stripe();
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_dragwyb_create_payment_intent', [$this, 'create_payment_intent']);
        add_action('wp_ajax_nopriv_dragwyb_create_payment_intent', [$this, 'create_payment_intent']);
    }

    /**
     * Initialize Stripe
     */
    private function init_stripe(): void {
        $settings = get_option('dragwyb_stripe_settings');
        $this->secret_key = $settings['secret_key'] ?? '';
        $this->publishable_key = $settings['publishable_key'] ?? '';

        if ($this->secret_key) {
            require_once DRAGWYB_PATH . 'vendor/autoload.php';
            \Stripe\Stripe::setApiKey($this->secret_key);
            $this->stripe = new \Stripe\StripeClient($this->secret_key);
        }
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts(): void {
        wp_enqueue_script(
            'stripe-js',
            'https://js.stripe.com/v3/',
            [],
            null,
            true
        );

        wp_enqueue_script(
            'dragwyb-stripe',
            DRAGWYB_URL . 'assets/js/dragwyb-stripe.js',
            ['jquery', 'stripe-js'],
            DRAGWYB_VERSION,
            true
        );

        wp_localize_script('dragwyb-stripe', 'dragwybStripe', [
            'publishableKey' => $this->publishable_key,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dragwyb_stripe')
        ]);
    }

    /**
     * Process payment
     */
    public function process_payment(array $payment_data): array {
        try {
            if (empty($payment_data['payment_method_id'])) {
                throw new Exception(__('Payment method not provided.', 'dragwyb-form-builder'));
            }

            // Create or get customer
            $customer = $this->get_or_create_customer($payment_data['customer']);

            // Handle subscription
            if (!empty($payment_data['subscription'])) {
                return $this->process_subscription($customer, $payment_data);
            }

            // Handle one-time payment
            return $this->process_one_time_payment($customer, $payment_data);

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => $e->getCode()
            ];
        }
    }

    /**
     * Create payment intent
     */
    public function create_payment_intent(): void {
        try {
            check_ajax_referer('dragwyb_stripe');

            $amount = floatval($_POST['amount'] ?? 0);
            $currency = sanitize_text_field($_POST['currency'] ?? 'USD');

            if (!$amount) {
                throw new Exception(__('Invalid payment amount.', 'dragwyb-form-builder'));
            }

            $intent = $this->stripe->paymentIntents->create([
                'amount' => $amount * 100, // Convert to cents
                'currency' => $currency,
                'payment_method_types' => ['card']
            ]);

            wp_send_json_success([
                'client_secret' => $intent->client_secret
            ]);

        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get or create customer
     */
    private function get_or_create_customer(array $customer_data): \Stripe\Customer {
        // Try to find existing customer by email
        $customers = $this->stripe->customers->search([
            'query' => "email:'{$customer_data['email']}'"
        ]);

        if (!empty($customers->data)) {
            return $customers->data[0];
        }

        // Create new customer
        return $this->stripe->customers->create([
            'email' => $customer_data['email'],
            'name' => $customer_data['name'],
            'phone' => $customer_data['phone'],
            'metadata' => [
                'source' => 'dragwyb_form_builder'
            ]
        ]);
    }

    /**
     * Process one-time payment
     */
    private function process_one_time_payment(\Stripe\Customer $customer, array $payment_data): array {
        $payment_intent = $this->stripe->paymentIntents->create([
            'amount' => $payment_data['amount'] * 100, // Convert to cents
            'currency' => $payment_data['currency'],
            'customer' => $customer->id,
            'payment_method' => $payment_data['payment_method_id'],
            'off_session' => true,
            'confirm' => true,
            'description' => $payment_data['description'],
            'metadata' => $payment_data['metadata']
        ]);

        if ($payment_intent->status === 'succeeded') {
            return [
                'success' => true,
                'transaction_id' => $payment_intent->id,
                'payment_method' => 'stripe'
            ];
        }

        throw new Exception(__('Payment failed.', 'dragwyb-form-builder'));
    }

    /**
     * Process subscription
     */
    private function process_subscription(\Stripe\Customer $customer, array $payment_data): array {
        // Attach payment method to customer
        $this->stripe->paymentMethods->attach(
            $payment_data['payment_method_id'],
            ['customer' => $customer->id]
        );

        // Update customer's default payment method
        $this->stripe->customers->update($customer->id, [
            'invoice_settings' => [
                'default_payment_method' => $payment_data['payment_method_id']
            ]
        ]);

        // Create subscription
        $subscription = $this->stripe->subscriptions->create([
            'customer' => $customer->id,
            'items' => [
                ['price' => $payment_data['subscription']['plan_id']]
            ],
            'trial_period_days' => $payment_data['subscription']['trial_period_days'],
            'metadata' => $payment_data['metadata']
        ]);

        if ($subscription->status === 'active' || $subscription->status === 'trialing') {
            return [
                'success' => true,
                'transaction_id' => $subscription->id,
                'payment_method' => 'stripe'
            ];
        }

        throw new Exception(__('Subscription creation failed.', 'dragwyb-form-builder'));
    }
} 