<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Dragwyb_Editor;

use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;
use Dragwyb\Form_Builder\Includes\Modules\Modules;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Dragwyb_Init;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbars;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;
use Dragwyb\Form_Builder\Includes\Controls\Icons\Icons_Helper;
use Dragwyb\Form_Builder\Includes\Frontend\Form_Preview;
use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;

if (!defined("ABSPATH")) {
    die("You can't access this page");
}


if (!class_exists('Dragwyb_Builder_Editor')) {
    class Dragwyb_Builder_Editor
    {
        private static $form_id = null;
        private const Current_Page = DRAGWYB_PREFIX . '-form-builder';
        private static ?self $instance = null;
        private static $style_cache = [];
        private static $google_fonts = [];

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
            add_filter('Dragwyb_i18n', [$this, 'localize_i18n_strings']);
            add_filter('Dragwyb/Editor/Localize_Settings', [$this, 'editor_toolbars_localize']);
            add_action('admin_head', [$this, 'render_dynamic_style_container']);
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
            if (isset(self::$form_id) && self::$form_id) {
                echo '<div id="' . esc_attr(self::Current_Page) . '-editor-wrapper" ><div id="' . esc_attr(self::Current_Page) . '-editor-container" ></div></div>';
            } else {
                $post_type = Dragwyb_Post::POST_TYPE;

                printf(
                    '<h1>%s</h1>',
                    sprintf(__('Failed to create the %s.', 'dragwyb-form-builder'), esc_html($post_type))
                );
            }
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
            $builder_page = DRAGWYB_PREFIX . '-form-builder';
            $screen_id = DRAGWYB_PREFIX . '-form_page_' . DRAGWYB_PREFIX . '-form-builder';
            $current_screen = get_current_screen();

            if (!isset($current_screen) && $current_screen->id !== $screen_id && !isset($_GET['page']) || $_GET['page'] !== $builder_page) {
                return;
            }

            $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;

            $post_type = Dragwyb_Post::POST_TYPE;

            if (!isset($form_id) || !$form_id) {
                $post_id = wp_insert_post([
                    'post_title'   => 'New Form',
                    'post_status'  => 'draft',
                    'post_type'    => $post_type,
                    'post_author'  => get_current_user_id(),
                ]);

                self::$form_id = $post_id;
            } else {
                $form = get_post((int) $form_id);

                if (!isset($form) || !isset($form->post_type)) {
                    self::$form_id = false;
                } else if ($form->post_type !== $post_type) {
                    self::$form_id = false;
                } else {
                    self::$form_id = (int) $form_id;
                }
            }

            if (!isset(self::$form_id) || !self::$form_id) {
                return;
            } else {
                global $post;

                $post = get_post((int) self::$form_id);

                setup_postdata($post);
            }

            !defined("DRAGWYB_EDITOR") && define('DRAGWYB_EDITOR', true);


            Dragwyb_Init::core_script();
            $this->external_libs();

            do_action('Dragwyb/before_enqueue/editor_scripts');

            wp_enqueue_script('dragwyb-form-core');

            $js_dependencies = apply_filters('Dragwyb/Editor/scripts/dependencies', array('jquery', 'dragwyb-form-core', 'jquery-ui-resizable', 'wp-element', 'wp-components', 'wp-i18n'));

            $style_dependencies = apply_filters('Dragwyb/Editor/style/dependencies', array('wp-components'));

            // Enqueue React and dependencies
            wp_enqueue_script(
                'dragwyb-form-editor',
                DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editor/editor.js',
                $js_dependencies,
                DRAGWYB_FORM_BUILDER_VERSION,
                true
            );

            wp_enqueue_style(
                'dragwyb-form-editor',
                DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editor/editor.css',
                $style_dependencies,
                DRAGWYB_FORM_BUILDER_VERSION
            );

            $localize_data = [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('dragwyb_editor'),
                'formId' => (int) self::$form_id,
                'editorContainer' => esc_html(self::Current_Page) . '-editor-container',
                'formData' => $this->get_form_data((int) self::$form_id),
                'controlTypes' => $this->get_control_types(),
                'formTypes' => $this->get_form_types(),
                'adminUrl' => admin_url('admin.php?page=dragwyb-form-overview'),
                'faIconsList' => $this->get_fa_icons_list(),
                'previewUrl' => home_url('/?post_type=' . Dragwyb_Post::POST_TYPE . '&p=' . self::$form_id . '&preview_id=' . Form_Preview::generate_key(self::$form_id)),
            ];

            $localize_data = apply_filters('Dragwyb/Editor/Localize_Settings', $localize_data);
            // Localize data
            wp_localize_script('dragwyb-form-editor', 'DragwybEditor', $localize_data);

            do_action('Dragwyb/after_enqueue/editor_scripts');
        }

        public function render_dynamic_style_container()
        {
            $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;

            echo '<style id="dragwyb-form-' . $form_id . '"></style>';
        }

        private function get_fa_icons_list(): array
        {
            $icons = Icons_Helper::get_icons_list_group();

            return $icons;
        }

        private function external_libs(): void
        {
            wp_enqueue_style(
                'dragwyb-font-awesome',
                DRAGWYB_FORM_BUILDER_URL . 'assets/font-awesome/v5/all.min.css',
                [],
                '6.7.2'
            );

            // @simonwep/pickr@1.9.1 style
            wp_enqueue_style(
                'dragwyb-pickr',
                DRAGWYB_FORM_BUILDER_URL . 'assets/lib/pickr/css/index.css',
                [],
                '6.7.2'
            );

            // @simonwep/pickr@1.9.1 script
            wp_enqueue_script(
                'dragwyb-pickr',
                DRAGWYB_FORM_BUILDER_URL . 'assets/lib/pickr/js/index.js',
                [],
                '1.9.1',
                true
            );

            add_filter('Dragwyb/Editor/scripts/dependencies', function ($dependencies) {
                $dependencies[] = 'dragwyb-pickr';

                return $dependencies;
            });

            add_filter('Dragwyb/Editor/style/dependencies', function ($dependencies) {
                $dependencies[] = 'dragwyb-pickr';

                return $dependencies;
            });
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
                'status' => $form ? $form->post_status : '',
            ];
        }

        public function editor_toolbars_localize($data): array
        {
            $form_id = isset($data['formId']) ? absint($data['formId']) : 0;

            if (!$form_id) {
                return $data;
            }

            if (!isset($data['formData'])) $data['formData'] = array();
            if (!isset($data['EditorToolbars'])) $data['EditorToolbars'] = array();
            if (!isset($data['EditorToolbars'])) $data['EditorToolbars'] = array();
            if (!isset($data['frontendInitialData'])) $data['frontendInitialData'] = array();

            $frontend = Frontend_Render::instance();
            $frontend->init($form_id);
            $style_cache = $frontend->get_generated_css();

            $toolbar_obj = Toolbars::instance();
            $default_toolbar = $toolbar_obj->defaultToolbar();
            $toolbars = $toolbar_obj->get_toolbars();

            if (isset($style_cache['css']) && is_array($style_cache['css']) && !empty($style_cache['css'])) {
                $data['frontendInitialData']['css'] = $style_cache['css'];
            }

            if (isset($style_cache['google_fonts']) && is_array($style_cache['google_fonts']) && !empty($style_cache['google_fonts'])) {
                $data['frontendInitialData']['googleFonts'] = $style_cache['google_fonts'];
            }

            if ($toolbars && !empty($toolbars)) {
                $toolbars_keys = array_keys($toolbars);

                foreach ($toolbars_keys as $key) {
                    if (isset($data['formData'][$key]) || isset($data[$key])) {
                        continue;
                    }

                    $toolbars[$key]->enqueue_assets();

                    if (!isset($data['EditorToolbars']['toolbars'])) {
                        $data['EditorToolbars']['toolbars'] = array();
                    }

                    if (!isset($data['EditorToolbars']['toolbars'][$key])) {
                        $data['EditorToolbars']['toolbars'][$key] = array('name' => $toolbars[$key]->get_toolbar_name(), 'icon' => $toolbars[$key]->get_toolbar_icon());
                    }

                    $toolbar_data = array();

                    if ($key === 'fields') {
                        $toolbar_data = $frontend->get_fields_values();
                    } else {
                        $toolbar_data = $frontend->get_toolbars_values($key);
                    }

                    $toolbar_settings = $frontend->get_toolbar_data($key);

                    $data['formData'][$key] = $toolbar_data;
                    $data[$key] = $toolbar_settings;
                }
            }

            if (isset($data['EditorToolbars']['toolbars'][$default_toolbar])) {
                $data['EditorToolbars']['Default'] = sanitize_text_field($default_toolbar);
            }

            return $data;
        }

        /**
         * Get field types configuration
         */
        private function get_control_types(): array
        {
            $controls = new Controls();

            $controls_data = $controls->get_controls();

            $controls = [];

            foreach ($controls_data as $key => $control) {
                $control->enqueue_assets();

                $name = $control->get_name();

                $controls[$key] = ['label' => esc_html($name)];
            }

            return $controls;
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

        public function localize_i18n_strings($strings): array
        {
            $localize_strings = [
                'fields' => __('Fields', 'dragwyb-form-builder'),
                'fieldSettings' => __('Field Settings', 'dragwyb-form-builder'),
                'formSettings' => __('Form Settings', 'dragwyb-form-builder'),
                'save' => __('Save Form', 'dragwyb-form-builder'),
                'preview' => __('Preview', 'dragwyb-form-builder'),
                'formTitle' => __('Form Title', 'dragwyb-form-builder'),
                'settings' => __('Settings', 'dragwyb-form-builder'),
                'general' => __('General', 'dragwyb-form-builder'),
                'style' => __('Style', 'dragwyb-form-builder'),
                'advance' => __('Advance', 'dragwyb-form-builder'),
                'display' => __('Display', 'dragwyb-form-builder'),
                'validation' => __('Validation', 'dragwyb-form-builder'),
                'notifications' => __('Notifications', 'dragwyb-form-builder'),
                'confirmations' => __('Confirmations', 'dragwyb-form-builder'),
                'confirmation_email' => __('Confirmation Email', 'dragwyb-form-builder'),
                'confirmation_message' => __('Confirmation Message', 'dragwyb-form-builder'),
                'confirmation_subject' => __('Confirmation Subject', 'dragwyb-form-builder'),
                'confirmation_message' => __('Confirmation Message', 'dragwyb-form-builder'),
                'emptyForm' => __("Start building your form by dragging fields from the sidebar or simply click to add them.", 'dragwyb-form-builder')
            ];

            return array_merge($localize_strings, $strings);
        }

        private function set_style_selector_cache($style): void
        {
            self::$style_cache[] = $style;
        }
    }
}
