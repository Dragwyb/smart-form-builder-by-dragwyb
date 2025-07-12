<?php
declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes;

use Dragwyb\Form_Builder\Includes\Modules\Module;

class Dragwyb_Form_Builder_Ajax {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_dragwyb_save_form', [$this, 'save_form']);
        add_action('wp_ajax_dragwyb_preview_form', [$this, 'preview_form']);
        add_action('wp_ajax_dragwyb_get_field_settings', [$this, 'get_field_settings']);
        add_action('wp_ajax_dragwyb_get_preset', [$this, 'get_preset']);
        add_action('wp_ajax_dragwyb_save_preset', [$this, 'save_preset']);
        add_action('wp_ajax_dragwyb_save_error_template', [$this, 'save_error_template']);
    }

    /**
     * Save form data
     */
    public function save_form(): void {
        // Verify nonce
        if (!check_ajax_referer('dragwyb_editor', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }

        $form_id = intval($_POST['form_id'] ?? 0);
        if (!$form_id) {
            wp_send_json_error(['message' => 'Invalid post ID']);
        }

        // Validate and sanitize form data
        $form_data = json_decode(stripslashes($_POST['form_data'] ?? ''), true);
        if (!$form_data) {
            wp_send_json_error(['message' => 'Invalid form data']);
        }

        // Sanitize form fields
        $form_data = $this->sanitize_form_data($form_data);

        // Save form data
        update_post_meta($form_id, '_dragwyb_form_data', wp_json_encode($form_data));

        wp_send_json_success(['message' => 'Form saved successfully']);
    }

    /**
     * Generate form preview
     */
    public function preview_form(): void {
        // Verify nonce
        if (!check_ajax_referer('dragwyb_form_builder', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }

        // Get and validate form data
        $form_data = json_decode(stripslashes($_POST['form_data'] ?? ''), true);
        if (!$form_data) {
            wp_send_json_error(['message' => 'Invalid form data']);
        }

        // Generate preview URL
        $preview_url = add_query_arg([
            'dragwyb_preview' => '1',
            'form_data' => base64_encode(wp_json_encode($form_data))
        ], home_url('/'));

        wp_send_json_success(['preview_url' => $preview_url]);
    }

    /**
     * Sanitize form data
     */
    private function sanitize_form_data(array $data): array {
        if (!isset($data['fields']) || !is_array($data['fields'])) {
            return ['fields' => []];
        }

        foreach ($data['fields'] as &$field) {
            $field = array_merge([
                'type' => '',
                'label' => '',
                'required' => false,
                'placeholder' => '',
                'options' => ''
            ], $field);

            $field['type'] = sanitize_key($field['type']);
            $field['label'] = sanitize_text_field($field['label']);
            $field['required'] = (bool) $field['required'];
            $field['placeholder'] = sanitize_text_field($field['placeholder']);
            $field['options'] = sanitize_textarea_field($field['options']);
        }

        return $data;
    }

    public function get_field_settings(): void {
        // Verify nonce
        if (!check_ajax_referer('dragwyb_form_builder', 'nonce', false)) {
            wp_send_json_error(['message' => 'Invalid nonce']);
        }

        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied']);
        }

        $field_type = sanitize_key($_POST['field_type'] ?? '');
        if (!$field_type) {
            wp_send_json_error(['message' => 'Invalid field type']);
        }

        $field = Module::instance()->get_field($field_type);
        if (!$field) {
            wp_send_json_error(['message' => 'Field type not found']);
        }

        wp_send_json_success([
            'settings' => $field->get_settings()
        ]);
    }

    public function get_preset(): void {
        check_ajax_referer('dragwyb_form_builder', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied', 'dragwyb-form-builder')]);
        }

        $preset_slug = sanitize_key($_POST['preset']);
        $presets = new Dragwyb_Form_Builder_Style_Presets();
        $all_presets = $presets->get_presets();

        if (!isset($all_presets[$preset_slug])) {
            wp_send_json_error(['message' => __('Preset not found', 'dragwyb-form-builder')]);
        }

        wp_send_json_success([
            'settings' => $all_presets[$preset_slug]['settings']
        ]);
    }

    public function save_preset(): void {
        check_ajax_referer('dragwyb_form_builder', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied', 'dragwyb-form-builder')]);
        }

        $name = sanitize_text_field($_POST['name']);
        $settings = json_decode(stripslashes($_POST['settings']), true);

        if (!$name || !is_array($settings)) {
            wp_send_json_error(['message' => __('Invalid data', 'dragwyb-form-builder')]);
        }

        $presets = new Dragwyb_Form_Builder_Style_Presets();
        $slug = sanitize_title($name);

        if ($presets->save_preset($name, $settings)) {
            wp_send_json_success([
                'message' => __('Preset saved successfully', 'dragwyb-form-builder'),
                'slug' => $slug,
                'name' => $name
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to save preset', 'dragwyb-form-builder')]);
        }
    }

    public function save_error_template(): void {
        // Verify nonce
        check_ajax_referer('dragwyb_admin', 'nonce');

        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied', 'dragwyb-form-builder')]);
        }

        $key = sanitize_key($_POST['key']);
        $message = sanitize_textarea_field($_POST['message']);

        if (!$key || !$message) {
            wp_send_json_error(['message' => __('Invalid data', 'dragwyb-form-builder')]);
        }

        $templates = new Dragwyb_Form_Builder_Error_Templates();
        if ($templates->save_template($key, $message)) {
            wp_send_json_success(['message' => __('Template saved successfully', 'dragwyb-form-builder')]);
        } else {
            wp_send_json_error(['message' => __('Failed to save template', 'dragwyb-form-builder')]);
        }
    }
} 