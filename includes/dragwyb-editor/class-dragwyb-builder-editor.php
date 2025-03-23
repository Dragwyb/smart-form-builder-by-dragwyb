<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Dragwyb_Editor;

use Dragwyb\Form_Builder\Includes\Dragwyb_Pages\Dragwyb_Pages;
use Dragwyb\Form_Builder\Includes\Modules\Module;

if (!defined("ABSPATH")) {
    die("You can't access this page");
}


if (!class_exists('Dragwyb_Builder_Editor')) {
    class Dragwyb_Builder_Editor
    {
        private const Current_Page = DRAGWYB_PREFIX . '-form-builder';
        private static ?self $instance = null;

        public static function instance(): self
        {
            if (null === self::$instance) {
                self::$instance = new self();
            }

            return self::$instance;
        }
        public function __construct()
        {
            add_filter('Dragwyb_allowed_pages', [$this, 'allowed_page']);
            add_filter('Dragwyb_Admin_Pages', [$this, 'allowed_page']);
            add_action('Dragwyb_Current_Screen', [$this, 'init'], 1);
        }

        public function init($screen)
        {
            if (gettype($screen) === 'object' && $screen(self::Current_Page)) {

                add_action('admin_enqueue_scripts', [$this, 'enqueue_editor_assets']);
                add_action('Dragwyb_Menu_Page', [$this, 'render_entries'], 1);
                add_action('admin_head', [$this, 'remove_default_wp_content']);
            }
        }

        public function allowed_page($pages)
        {
            return array_merge([self::Current_Page], $pages);
        }

        public function render_entries($screen)
        {
            if (gettype($screen) === 'object' && $screen(self::Current_Page)) {
                $this->builder_output();
            }
        }

        public function builder_output()
        {
            echo '<div id="' . esc_attr(self::Current_Page) . '-editor-wrapper" ><div id="' . esc_attr(self::Current_Page) . '-editor-container" ></div></div>';
        }

        public function remove_default_wp_content()
        {
            // Remove the sidebar and top nav bar
            remove_action('admin_bar_menu', 'wp_admin_bar_my_account_menu', 25);
            remove_action('admin_menu', 'remove_menu_pages');
            remove_action('wp_footer', 'wp_admin_bar_render', 100);

            // Remove notices
            remove_all_actions('admin_notices'); // Remove all admin notices
            remove_all_actions('all_admin_notices'); // Remove all admin notices globally
            remove_all_actions('user_admin_notices'); // Remove user-specific notices
        }

        public function enqueue_editor_assets()
        {
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
                'editorContainer' => esc_html(self::Current_Page) . '-editor-container',
                'formData' => $this->get_form_data($form_id),
                'fieldTypes' => $this->get_field_types(),
                'formTypes' => $this->get_form_types(),
                'settings' => $this->get_form_advance_settings(),
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
                'fields' => get_post_meta($form_id, '_form_fields', true) ?: [],
                'settings' => get_post_meta($form_id, '_form_settings', true) ?: [],
                'styles' => get_post_meta($form_id, '_form_styles', true) ?: [],
                'notifications' => get_post_meta($form_id, '_form_notifications', true) ?: [],
                'confirmations' => get_post_meta($form_id, '_form_confirmations', true) ?: [],
            ];
        }

        /**
         * Get field types configuration
         */
        private function get_field_types(): array
        {
            $module = new Module();

            $fields_data = $module->get_fields();

            $fields = [];

            foreach ($fields_data as $key => $field) {
                $setting = $field->get_settings();

                $name = $field->get_name();

                $fields[$key] = ['label' => esc_html($name)];
                $fields[$key]['settings'] = $setting;
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
        private function get_form_advance_settings(): array
        {
            $form_settings = [
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
            ];

            $form_setting = apply_filters('Dragwy_advance_settings', $form_settings);

            return $form_setting;
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
                'cancel' => __('Cancel', 'dragwyb-form-builder'),
                'settings' => __('Settings', 'dragwyb-form-builder'),
                'general' => __('General', 'dragwyb-form-builder'),
                'advanced' => __('Advanced', 'dragwyb-form-builder'),
                'display' => __('Display', 'dragwyb-form-builder'),
                'validation' => __('Validation', 'dragwyb-form-builder'),
                'notifications' => __('Notifications', 'dragwyb-form-builder'),
                'confirmations' => __('Confirmations', 'dragwyb-form-builder'),
                'confirmation_email' => __('Confirmation Email', 'dragwyb-form-builder'),
                'confirmation_message' => __('Confirmation Message', 'dragwyb-form-builder'),
                'confirmation_subject' => __('Confirmation Subject', 'dragwyb-form-builder'),
                'confirmation_message' => __('Confirmation Message', 'dragwyb-form-builder'),
            ];
        }
    }
}
