<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes;

use Dragwyb\Form_Builder\Includes\Modules\Modules;

class Dragwyb_Form_Builder_Editor
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_editor_assets']);
        add_action('wp_ajax_dragwyb_save_form', [$this, 'handle_form_save']);
        add_action('admin_head', [$this, 'hide_default_editor']);
        add_action('edit_form_after_title', [$this, 'render_editor_page']);
    }

    /**
     * Hide default editor and metaboxes
     */
    public function hide_default_editor(): void
    {
        $screen = get_current_screen();

        if (!($screen && $screen->post_type === 'dragwyb_form')) {
            return;
        }

        // Remove default editor
        remove_post_type_support('dragwyb_form', 'editor');

        // Custom CSS to hide unwanted elements
?>
        <style type="text/css">
            .dragwyb-editor-wrap {
                margin: 20px 0;
            }

            #postdivrich,
            #normal-sortables {
                display: none;
            }

            #submitdiv {
                display: none;
            }
        </style>
    <?php
    }

    /**
     * Render editor page
     */
    public function render_editor_page($post): void
    {
        if ($post->post_type !== 'dragwyb_form') {
            return;
        }

    ?>
        <div class="dragwyb-form-builder-editor-wrapper">
            <div id="dragwyb-form-editor"></div>
        </div>
<?php
    }

    /**
     * Enqueue editor assets
     */
    public function enqueue_editor_assets(string $hook): void
    {
        $screen = get_current_screen();

        // Only load on form edit/add screens
        if (!($screen && $screen->post_type === 'dragwyb_form')) {
            return;
        }

        // Enqueue React and dependencies
        wp_enqueue_script(
            'dragwyb-form-editor',
            DRAGWYB_FORM_BUILDER_URL . 'admin/js/dist/editor.js',
            ['wp-element', 'wp-components', 'wp-i18n'],
            DRAGWYB_FORM_BUILDER_VERSION,
            true
        );

        wp_enqueue_style(
            'dragwyb-form-editor',
            DRAGWYB_FORM_BUILDER_URL . 'admin/css/editor.css',
            ['wp-components'],
            DRAGWYB_FORM_BUILDER_VERSION
        );

        global $post;
        $form_id = $post->ID ?? 0;

        // Localize data
        wp_localize_script('dragwyb-form-editor', 'DragwybEditor', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dragwyb_editor'),
            'formId' => $form_id,
            'formData' => $this->get_form_data($form_id),
            'fieldTypes' => $this->get_field_types(),
            'formTypes' => $this->get_form_types(),
            'settings' => $this->get_form_settings(),
            'adminUrl' => admin_url('edit.php?post_type=dragwyb_form'),
            'i18n' => $this->get_translations()
        ]);
    }

    /**
     * Get form data
     */
    private function get_form_data(int $form_id): array
    {
        $form = get_post($form_id);

        return [
            'id' => $form_id,
            'title' => $form ? $form->post_title : '',
            'type' => get_post_meta($form_id, '_dragwyb_form_type', true) ?: 'standard',
            'fields' => get_post_meta($form_id, '_dragwyb_form_fields', true) ?: [],
            'settings' => get_post_meta($form_id, '_form_settings', true) ?: [],
            'styles' => get_post_meta($form_id, '_form_styles', true) ?: [],
            'notifications' => get_post_meta($form_id, '_form_notifications', true) ?: [],
            'confirmations' => get_post_meta($form_id, '_form_confirmations', true) ?: []
        ];
    }

    /**
     * Get field types configuration
     */
    private function get_field_types(): array
    {
        $module = new Modules();

        $fields_data = $module->get_fields();

        $fields = [];

        foreach ($fields_data as $key => $field) {
            $setting = $field->get_settings();

            $name = $field->get_name();

            $fields[$key] = ['label' => esc_html($name)];
        }

        return $fields;
    }

    /**
     * Get form types
     */
    private function get_form_types(): array
    {
        $default_types = [
            'standard' => __('Standard Form', 'dragwyb-form-builder'),
            'quiz' => __('Quiz', 'dragwyb-form-builder'),
            'poll' => __('Poll', 'dragwyb-form-builder'),
            'survey' => __('Survey', 'dragwyb-form-builder')
        ];

        return apply_filters('dragwyb_form_types', $default_types);
    }

    /**
     * Get form settings configuration
     */
    private function get_form_settings(): array
    {
        return [
            'general' => [
                'label' => __('General', 'dragwyb-form-builder'),
                'icon' => 'admin-generic',
                'settings' => [
                    'form_class' => [
                        'type' => 'text',
                        'label' => __('Form CSS Class', 'dragwyb-form-builder')
                    ],
                    'submit_text' => [
                        'type' => 'text',
                        'label' => __('Submit Button Text', 'dragwyb-form-builder')
                    ]
                ]
            ],
            // Add more settings sections...
        ];
    }

    /**
     * Get translations
     */
    private function get_translations(): array
    {
        return [
            'addField' => __('Add Field', 'dragwyb-form-builder'),
            'fieldSettings' => __('Field Settings', 'dragwyb-form-builder'),
            'formSettings' => __('Form Settings', 'dragwyb-form-builder'),
            'save' => __('Save Form', 'dragwyb-form-builder'),
            'preview' => __('Preview Form', 'dragwyb-form-builder'),
            'formTitle' => __('Form Title', 'dragwyb-form-builder'),
            'cancel' => __('Cancel', 'dragwyb-form-builder')
        ];
    }

    /**
     * Handle form save
     */
    public function handle_form_save(): void
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
                'post_title' => sanitize_text_field($form_data['title'])
            ]);

            // Update form meta
            update_post_meta($form_id, '_dragwyb_form_type', $form_data['type']);
            update_post_meta($form_id, '_dragwyb_form_fields', $form_data['fields']);
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
}
