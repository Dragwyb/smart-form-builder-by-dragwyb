<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Modules\Module;
use Dragwyb\Form_Builder\Includes\Modules\Sanitize_Fields_Settings\Sanitize_Fields_Settings;

class Dragwyb_Form_Builder_Ajax
{
    /**
     * Constructor
     */
    public function __construct()
    {
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
    public function save_form(): void
    {
        try {
            check_ajax_referer('dragwyb_editor');

            if (!current_user_can('edit_posts')) {
                throw new \Exception(__('Permission denied', 'dragwyb-form-builder'));
            }

            $form_id = absint($_POST['form_id'] ?? 0);
            $form_data = json_decode(stripslashes($_POST['form_data'] ?? ''), true);

            if (!$form_id || !is_array($form_data)) {
                throw new \Exception(__('Invalid form data', 'dragwyb-form-builder'));
            }

            // Update form
            wp_update_post([
                'ID' => $form_id,
                'post_title' => sanitize_text_field($form_data['title']),
                'post_status' => 'publish'
            ]);

            // Update form meta
            update_post_meta($form_id, '_dragwyb_form_type', $form_data['type']);
            update_post_meta($form_id, '_dragwyb_form_fields', $this->sanitize_form_data($form_data));
            update_post_meta($form_id, '_form_settings', $form_data['settings']);
            update_post_meta($form_id, '_form_styles', $form_data['styles']);
            update_post_meta($form_id, '_form_notifications', $form_data['notifications']);
            update_post_meta($form_id, '_form_confirmations', $form_data['confirmations']);

            wp_send_json_success([
                'message' => __('Form saved successfully', 'dragwyb-form-builder')
            ]);
        } catch (\Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate form preview
     */
    public function preview_form(): void
    {
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
    private function sanitize_form_data(array $data): array
    {

        if (!isset($data['fields']) || !is_array($data['fields'])) {
            return [];
        }

        $sanitize_form_data = Sanitize_Fields_Settings::instance($data['fields']);
        $fields = $sanitize_form_data->get_data();

        if ($fields && is_array($fields) && count($fields) > 0) {
            return $fields;
        }

        return [];
    }

    public function get_field_settings(): void
    {
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

    public function get_preset(): void
    {
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

    public function save_preset(): void
    {
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

    public function save_error_template(): void
    {
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
