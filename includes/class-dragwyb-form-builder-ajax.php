<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes;

if (!defined('ABSPATH')) {
    exit;
}

use Dragwyb\Form_Builder\Includes\Toolbars\Toolbars;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;
use Dragwyb\Form_Builder\Includes\Frontend\Managers\CSS_Manager;

class Dragwyb_Form_Builder_Ajax
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('wp_ajax_dragwyb_save_form', [$this, 'save_form']);
    }

    /**
     * Save form data
     */
    public function save_form(): void
    {

        check_ajax_referer('dragwyb_editor');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied', 'dragwyb-form-builder')]);
        }

        $form_id = absint($_POST['form_id'] ?? 0);
        $form_data = json_decode(wp_unslash($_POST['form_data'] ?? ''), true);

        if (!$form_id || !is_array($form_data)) {
            wp_send_json_error(['message' => __('Invalid form data', 'dragwyb-form-builder')]);
        }

        // Update form meta
        update_post_meta($form_id, '_dragwyb_form_data', $this->sanitize_form_data($form_data, $form_id));

        $css_manager = CSS_Manager::instance();
        $css_manager->clean_cache($form_id);

        wp_send_json_success([
            'message' => __('Form saved successfully', 'dragwyb-form-builder')
        ]);
    }

    /**
     * Sanitize form data
     */
    private function sanitize_form_data(array $data, int $form_id): array
    {

        defined('DRAGWYB_EDITOR_SAVE_AJAX') || define('DRAGWYB_EDITOR_SAVE_AJAX', true);
        $sanitize_data = array();
        $toolbar_obj = new Toolbars();
        $toolbars = $toolbar_obj->get_toolbars();
        $toolbars_cache = array();

        foreach ($data as $key => $value) {
            if ($key === 'id') {
                continue;
            }

            if (count($toolbars) > 0 && isset($toolbars[$key]) && $toolbars[$key] instanceof Toolbar_Base) {
                $toolbar = $toolbars[$key];
                $toolbar->set_form_id($form_id);
                $toolbar->set_toolbar_data($value);
                $toolbar_data = $toolbar->get_toolbar_data();

                if ($toolbar_data) {
                    if ($key === 'fields') {
                        $toolbar_data = $this->sorting_fields($toolbar_data, $data['rootContainers'] ?? array());
                    }
                    $sanitize_data[$key] = $toolbar_data;
                    $toolbars_cache[$key] = $toolbar;
                }
            }
        }

        if (count($toolbars_cache) > 0) {
            foreach ($toolbars_cache as $toolbar) {
                $toolbar->settings_updated();
            }
        }

        return $sanitize_data;
    }

    /**
     * Sort fields
     */
    private function sorting_fields(array $fields, $root_containers): array
    {
        $sorted_fields = array();
        foreach ($root_containers as $root_container) {
            $sorted_fields[$root_container] = $fields[$root_container] ?? array();

            if (isset($fields[$root_container]['is_root_container']) && true === $fields[$root_container]['is_root_container'] && isset($fields[$root_container]['children']) && count($fields[$root_container]['children']) > 0) {
                $this->sorting_child_fields($fields[$root_container]['children'], $fields, $sorted_fields);
            }
        }

        return $sorted_fields;
    }

    /**
     * Sort child fields
     */
    private function sorting_child_fields(array $childrens, array $fields, array &$sorted_fields): void
    {
        foreach ($childrens as $field_id) {
            if (isset($field_id) && isset($fields[$field_id])) {
                $sorted_fields[$field_id] = $fields[$field_id];

                if (isset($fields[$field_id]['children']) && count($fields[$field_id]['children']) > 0) {
                    $this->sorting_child_fields($fields[$field_id]['children'], $fields, $sorted_fields);
                }
            }
        }
    }
}
