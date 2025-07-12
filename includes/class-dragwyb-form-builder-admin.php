<?php
declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes;

class Dragwyb_Form_Builder_Admin {
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('edit_form_after_title', [$this, 'render_form_builder']);
        add_action('admin_footer', [$this, 'render_field_templates']);
        add_filter('post_updated_messages', [$this, 'custom_post_messages']);
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_assets($hook): void {
        $screen = get_current_screen();
        
        // Only load on form edit/add screens
        if (!($screen && $screen->post_type === 'dragwyb_form')) {
            return;
        }

        global $post;

        // Enqueue jQuery UI for drag and drop
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-droppable');

        // Enqueue our custom scripts
        wp_enqueue_script(
            'dragwyb-form-builder',
            DRAGWYB_FORM_BUILDER_URL . 'assets/js/dragwyb-form-builder.js',
            ['jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable'],
            DRAGWYB_FORM_BUILDER_VERSION,
            true
        );

        // Enqueue styles
        wp_enqueue_style(
            'dragwyb-form-builder',
            DRAGWYB_FORM_BUILDER_URL . 'assets/css/dragwyb-form-builder.css',
            [],
            DRAGWYB_FORM_BUILDER_VERSION
        );

        // Localize script
        wp_localize_script('dragwyb-form-builder', 'dragwybFormBuilder', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('dragwyb_form_builder'),
            'formId'=>$post->id,
            'strings' => [
                'confirmDelete' => __('Are you sure you want to delete this field?', 'dragwyb-form-builder'),
                'savingForm' => __('Saving form...', 'dragwyb-form-builder'),
                'formSaved' => __('Form saved successfully!', 'dragwyb-form-builder'),
                'error' => __('An error occurred', 'dragwyb-form-builder'),
            ],
        ]);
    }

    /**
     * Render form builder interface
     */
    public function render_form_builder($post): void {
        if ($post->post_type !== 'dragwyb_form') {
            return;
        }

        // Get saved form data
        $form_data = get_post_meta($post->ID, '_dragwyb_form_data', true);
        $form_data = $form_data ? json_decode($form_data, true) : [];

        // Include the form builder template
        include DRAGWYB_FORM_BUILDER_PATH . 'templates/dragwyb-admin-form-editor.php';
    }

    /**
     * Render field templates for JavaScript use
     */
    public function render_field_templates(): void {
        $screen = get_current_screen();
        if (!($screen && $screen->post_type === 'dragwyb_form')) {
            return;
        }
        ?>
        <script type="text/template" id="tmpl-dragwyb-field-text">
            <div class="dragwyb-field" data-type="text">
                <div class="dragwyb-field-header">
                    <span class="dragwyb-field-title"><?php esc_html_e('Text Field', 'dragwyb-form-builder'); ?></span>
                    <span class="dragwyb-field-actions">
                        <button type="button" class="dragwyb-field-settings" title="<?php esc_attr_e('Settings', 'dragwyb-form-builder'); ?>">
                            <span class="dashicons dashicons-admin-generic"></span>
                        </button>
                        <button type="button" class="dragwyb-field-remove" title="<?php esc_attr_e('Remove', 'dragwyb-form-builder'); ?>">
                            <span class="dashicons dashicons-trash"></span>
                        </button>
                    </span>
                </div>
                <div class="dragwyb-field-settings-panel" style="display: none;">
                    <div class="dragwyb-field-setting">
                        <label><?php esc_html_e('Label', 'dragwyb-form-builder'); ?></label>
                        <input type="text" class="dragwyb-field-label" value="{{ data.label }}">
                    </div>
                    <div class="dragwyb-field-setting">
                        <label><?php esc_html_e('Placeholder', 'dragwyb-form-builder'); ?></label>
                        <input type="text" class="dragwyb-field-placeholder" value="{{ data.placeholder }}">
                    </div>
                    <div class="dragwyb-field-setting">
                        <label>
                            <input type="checkbox" class="dragwyb-field-required" <# if (data.required) { #>checked<# } #>>
                            <?php esc_html_e('Required', 'dragwyb-form-builder'); ?>
                        </label>
                    </div>
                </div>
            </div>
        </script>
        <?php
        // Add more field templates as needed
    }

    /**
     * Customize post updated messages
     */
    public function custom_post_messages($messages): array {
        global $post;

        $messages['dragwyb_form'] = [
            0  => '', // Unused. Messages start at index 1.
            1  => __('Form updated.', 'dragwyb-form-builder'),
            2  => __('Custom field updated.', 'dragwyb-form-builder'),
            3  => __('Custom field deleted.', 'dragwyb-form-builder'),
            4  => __('Form updated.', 'dragwyb-form-builder'),
            5  => isset($_GET['revision']) ? sprintf(
                __('Form restored to revision from %s', 'dragwyb-form-builder'),
                wp_post_revision_title((int) $_GET['revision'], false)
            ) : false,
            6  => __('Form published.', 'dragwyb-form-builder'),
            7  => __('Form saved.', 'dragwyb-form-builder'),
            8  => __('Form submitted.', 'dragwyb-form-builder'),
            9  => sprintf(
                __('Form scheduled for: <strong>%1$s</strong>.', 'dragwyb-form-builder'),
                date_i18n(__('M j, Y @ G:i', 'dragwyb-form-builder'), strtotime($post->post_date))
            ),
            10 => __('Form draft updated.', 'dragwyb-form-builder'),
        ];

        return $messages;
    }
} 