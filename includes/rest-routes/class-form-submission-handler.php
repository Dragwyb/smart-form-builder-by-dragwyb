<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Rest_Routes;

use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbars;
use Dragwyb\Form_Builder\Includes\Modules\Fields\Field_Base;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class Form_Submission_Handler
 *
 * Handles validation and submission errors for the form builder.
 *
 * @package Dragwyb\Form_Builder\Includes\Rest_Routes
 */
class Form_Submission_Handler
{

    /**
     * Stores the form id.
     *
     * @var int
     */
    private int $form_id;

    /**
     * Stores the raw form data.
     *
     * @var array
     */
    private array $raw_data;

    /**
     * Stores the sanitized data.
     *
     * @var array
     */
    private array $sanitized_data = [];

    /**
     * Stores the form configuration.
     *
     * @var array
     */
    private array $form_config = [];

    /**
     * Stores the list of errors.
     *
     * @var array<string, string>
     */
    private array $errors = [];

    /**
     * Stores the form return data.
     *
     * @var array
     */
    private array $form_return_data = [];

    /**
     * Constructor.
     *
     * @param int   $form_id   The form ID.
     * @param array $form_data The raw form data to process.
     */
    public function __construct(int $form_id, array $form_data)
    {
        if (!defined('DRAGWYB_FORM_SUBMISSION_REQUEST')) {
            return;
        }

        $form_data = wp_unslash($form_data);

        $this->form_id  = absint($form_id);
        $this->generate_form_config($form_data);

        $this->validate_honeypot($form_data);

        if (!$this->has_errors()) {
            $this->validate_fields($form_data);
        }

        $this->process_submission();
    }

    /**
     * Process the complete form submission workflow.
     *
     * @return void
     */
    private function process_submission(): void
    {
        if ($this->has_errors()) {
            return;
        }

        $frontend = Frontend_Render::instance();
        $frontend->init($this->form_id);
        $toolbar_obj = Toolbars::instance();
        $after_submission_toolbar_config = $toolbar_obj->get_toolbar('after-submission');


        if (!isset($after_submission_toolbar_config) || !$after_submission_toolbar_config instanceof Toolbar_Base) {
            return;
        }

        do_action('Dragwyb/Form/Submission/Before_Processing', $this->sanitized_data, $this->form_config, $this);

        $after_submission_toolbar_config->process_submission($this->form_id, $this->sanitized_data, $this->form_config, $this);

        do_action('Dragwyb/Form/Submission/After_Processing', $this->sanitized_data, $this->form_config, $this);
    }

    private function generate_form_config(array $form_data): void
    {
        $frontend = Frontend_Render::instance();
        $frontend->init($this->form_id);
        $toolbar_obj = Toolbars::instance();
        $toolbar_types = $toolbar_obj->get_toolbar_types();

        $this->form_config = array();

        foreach ($toolbar_types as $toolbar_type) {
            if ($toolbar_type === 'fields') {
                $this->set_fields_config($frontend->get_fields_values(), $form_data, $frontend);
            } else {
                $this->form_config[$toolbar_type] = $frontend->get_toolbars_values($toolbar_type);
            }
        }
    }

    private function set_fields_config(array $fields, array $form_data, Frontend_Render $frontend): void
    {

        $field_map = array();

        foreach ($form_data as $field_value) {
            $field_orignal_key = sanitize_text_field($field_value['name']);

            $field_key = substr($field_orignal_key, 6);

            if (isset($fields[$field_key])) {
                if (! isset($fields[$field_key]['type'])) {
                    continue;
                }

                $field_type = $fields[$field_key]['type'];

                $this->set_fields_sanitized_values($field_type, $field_orignal_key, $fields[$field_key], $field_value['value'], $frontend);

                unset($fields[$field_key]);
                $field_map[$field_key] = array();
            } else {
                $unique_keys = array_diff_key($fields, $field_map);

                foreach ($unique_keys as $key => $field_data) {
                    if (isset($field_data['attributes']['field_id']) && $field_data['attributes']['field_id'] === $field_orignal_key) {
                        if (! isset($field_data['type'])) {
                            break;
                        }

                        $field_type = $field_data['type'];

                        $this->set_fields_sanitized_values($field_type, $field_orignal_key, $field_data, $field_value['value'], $frontend);

                        $field_map[$key] = array();
                        unset($fields[$key]);
                        break;
                    }
                }
            }
        }

        foreach ($fields as $key => $field) {
            if (!isset($field['type'])) {
                continue;
            }

            $custom_id = 'field_' . $key;

            $field_id = isset($field['attributes']['field_id']) && $field['attributes']['field_id'] !== $custom_id ? $field['attributes']['field_id'] : $custom_id;

            $this->form_config['fields'][$field_id] = $field;
        }
    }

    /**
     * Sanitize field values.
     *
     * @param string $field_type The field type.
     * @param string $field_orignal_key The field original key.
     * @param array $field_data The field data.
     * @param mixed $field_value The field value.
     * @param Frontend_Render $frontend The frontend instance.
     * @return void
     */
    private function set_fields_sanitized_values(string $field_type, string $field_orignal_key, array $field_data, $field_value, Frontend_Render $frontend): void
    {
        $field_orignal_key = sanitize_text_field($field_orignal_key);

        $this->form_config['fields'][$field_orignal_key] = $field_data;
        $field_module = $frontend->get_module($field_type);

        if (!$field_module instanceof Field_Base) {
            return;
        }

        $this->form_config['fields'][$field_orignal_key]['raw_value'] = $field_value;
        $field_sanitized_value = apply_filters('Dragwyb/Field/Value/Sanitize/' . $field_type, '', $field_value);
        $this->form_config['fields'][$field_orignal_key]['value'] = $field_sanitized_value;
    }

    /**
     * Validate fields
     */
    private function validate_fields(array $form_data): void
    {
        $form_config_data = $this->form_config;
        do_action('Dragwyb/Form/Before_Validation', $form_data, $form_config_data, $this);

        foreach ($form_data as $field_value) {
            $field_orignal_key = sanitize_text_field($field_value['name']);

            if (!isset($this->form_config['fields'][$field_orignal_key])) {
                continue;
            }

            $field_data = $this->form_config['fields'][$field_orignal_key];

            $field_type = $field_data['type'];
            $field_value = $field_value['value'];

            do_action('Dragwyb/Field/Value/Validate/' . $field_type, $field_value, $field_orignal_key, $form_config_data, $this);

            $sanitized_value = apply_filters('Dragwyb/Field/Value/Sanitize/' . $field_type, '', $field_value);
            $this->sanitized_data[$field_orignal_key] = $sanitized_value;
        }

        do_action('Dragwyb/Form/After_Validation', $form_data, $this->form_config, $this);
    }

    /**
     * Validate the honeypot field.
     *
     * @param array $form_data The raw form data.
     * @return void
     */
    private function validate_honeypot(array $form_data): void
    {
        $advance_settings = isset($this->form_config['advance']) ? $this->form_config['advance'] : [];

        if (isset($advance_settings['honeypot']) && $advance_settings['honeypot'] === 'yes') {
            foreach ($form_data as $field) {
                if (isset($field['name']) && $field['name'] === 'dragwyb_h_email') {
                    if (!empty($field['value'])) {
                        $this->add_error('honeypot', __('Spam detected. Form submission rejected.', 'smart-form-builder-by-dragwyb'));
                    }
                    break;
                }
            }
        }
    }

    public function get_sanitized_data(): array
    {
        return $this->sanitized_data;
    }

    /**
     * Set the form return data.
     * @param string $id The form return data ID.
     * @param $value The form return data value.
     * @return void
     */
    public function set_form_return_data(string $id, $value): void
    {
        $this->form_return_data[$id] = $value;
    }

    /**
     * Get the form return data.
     * @return array The form return data.
     */
    public function get_form_return_data(): array
    {
        return isset($this->form_return_data) ? $this->form_return_data : [];
    }

    /**
     * Add an error for a specific field or identifier.
     *
     * @param string $id      The field ID or error identifier.
     * @param string $message The error message.
     *
     * @return void
     */
    public function add_error(string $id, string $message): void
    {
        $this->errors[$id] = $this->sanitize_error($message);
    }

    /**
     * Remove an error by its ID.
     *
     * @param string $id The field ID or error identifier.
     *
     * @return void
     */
    public function remove_error(string $id): void
    {
        if (isset($this->errors[$id])) {
            unset($this->errors[$id]);
        }
    }

    /**
     * Sanitize an error message.
     *
     * @param string $message The error message to sanitize.
     *
     * @return string The sanitized error message.
     */
    public function sanitize_error(string $message): string
    {
        return sanitize_text_field(wp_unslash($message));
    }

    /**
     * Check if there are any errors.
     *
     * @return bool True if there are errors, false otherwise.
     */
    public function has_errors(): bool
    {
        return ! empty($this->errors);
    }

    /**
     * Get all registered errors.
     *
     * @return array<string, string> The array of errors.
     */
    public function get_errors(): array
    {
        return $this->errors;
    }

    /**
     * Get a specific error by its ID.
     *
     * @param string $id The field ID or error identifier.
     *
     * @return string|null The error message, or null if not found.
     */
    public function get_error(string $id): ?string
    {
        return $this->errors[$id] ?? null;
    }

    /**
     * Clear all errors.
     *
     * @return void
     */
    public function clear_errors(): void
    {
        $this->errors = [];
    }
}
