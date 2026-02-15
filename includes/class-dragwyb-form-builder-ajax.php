<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Modules\Modules;
use Dragwyb\Form_Builder\Includes\Modules\Sanitize_Module_Settings\Sanitize_Module_Settings;
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
        $form_data = json_decode(stripslashes($_POST['form_data'] ?? ''), true);

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
}
