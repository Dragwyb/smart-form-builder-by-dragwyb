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
use Dragwyb\Form_Builder\Includes\Categories\Categories;
use Dragwyb\Form_Builder\Includes\Categories\Categories\Category_Base;

if (!defined("ABSPATH")) {
    die("You can't access this page");
}


if (!class_exists('Dragwyb_Builder_Editor')) {
    class Dragwyb_Builder_Editor
    {
        private static $form_id = null;
        private const Current_Page = DRAGWYB_PREFIX . '-form-builder';
        private static ?self $instance = null;
        private static $initial_load = false;
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
        }

        public function init($screen)
        {
            if (gettype($screen) === 'object' && $screen(self::Current_Page)) {

                // Update page title
                add_action('admin_enqueue_scripts', [$this, 'enqueue_editor_assets']);
                add_action('Dragwyb_Menu_Page', [$this, 'render_editor'], 1);
                add_filter('admin_title', [$this, 'update_page_title']);
                add_action('admin_head', [$this, 'remove_default_wp_content']);
            }
        }

        public function allowed_page($pages)
        {
            return array_merge([self::Current_Page], $pages);
        }

        public function render_editor($screen)
        {
            if (gettype($screen) === 'object' && $screen(self::Current_Page)) {
                $this->builder_output();
            }
        }

        public function update_page_title($title)
        {
            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No nonce is required for form id check
            $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;

            if (isset($form_id) && $form_id && is_numeric($form_id) && function_exists('get_the_title')) {
                return get_the_title((int) $form_id);
            }
            return $title;
        }

        public function builder_output()
        {
            if (isset(self::$form_id) && self::$form_id) {
                echo '<div id="' . esc_attr(self::Current_Page) . '-editor-wrapper" ><div id="' . esc_attr(self::Current_Page) . '-editor-container" ></div></div>';
            } else {
                $post_type = Dragwyb_Post::POST_TYPE;

                printf(
                    '<h1>%s</h1>',
                    // translators: %s is the post type name
                    sprintf(esc_html__('Failed to create the %s.', 'smart-form-builder-by-dragwyb'), esc_html($post_type))
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

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No nonce is required for admin dashboard pages check
            if (!isset($current_screen) && $current_screen->id !== $screen_id && !isset($_GET['page']) || $_GET['page'] !== $builder_page) {
                return;
            }

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No nonce is required for form id check
            $form_id = isset($_GET['form_id']) ? absint($_GET['form_id']) : 0;

            $post_type = Dragwyb_Post::POST_TYPE;

            if (!isset($form_id) || !$form_id) {
                $post_id = wp_insert_post([
                    'post_status'  => 'draft',
                    'post_type'    => $post_type,
                    'post_author'  => get_current_user_id(),
                ]);

                self::$initial_load = true;
                self::$form_id = $post_id;

                wp_update_post([
                    'ID' => $post_id,
                    'post_title' => 'Form #' . $post_id,
                ]);
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

            $js_dependencies = apply_filters('Dragwyb/Editor/scripts/dependencies', array('jquery', 'dragwyb-form-core', 'jquery-ui-resizable', 'wp-element', 'wp-components', 'wp-i18n', 'clipboard'));

            $style_dependencies = apply_filters('Dragwyb/Editor/style/dependencies', array('wp-components', 'dragwyb-form-editor-global'));

            // Enqueue React and dependencies
            wp_enqueue_script(
                'dragwyb-form-editor',
                esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editor/editor.js'),
                $js_dependencies,
                esc_attr(DRAGWYB_FORM_BUILDER_VERSION),
                true
            );

            // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- No nonce is required for iframe mode check already returned with this check
            if (isset($_GET['dragwyb_iframe_mode'])) {
                return;
            }

            wp_enqueue_style(
                'dragwyb-form-editor-global',
                esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/css/editor-global.css'),
                [],
                esc_attr(DRAGWYB_FORM_BUILDER_VERSION)
            );

            wp_enqueue_style(
                'dragwyb-form-editor',
                esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editor/editor.css'),
                $style_dependencies,
                esc_attr(DRAGWYB_FORM_BUILDER_VERSION)
            );

            $localize_data = [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'pluginUrl' => esc_url(DRAGWYB_FORM_BUILDER_URL),
                'pluginPath' => DRAGWYB_FORM_BUILDER_PATH,
                'nonce' => wp_create_nonce('dragwyb_editor'),
                'formId' => (int) self::$form_id,
                'editorContainer' => esc_html(self::Current_Page) . '-editor-container',
                'formData' => $this->get_form_data((int) self::$form_id),
                'controlTypes' => $this->get_control_types(),
                'fieldCategories' => $this->get_registered_categories(),
                'adminUrl' => admin_url('admin.php?page=dragwyb-form-overview'),
                'faIconsList' => $this->get_fa_icons_list(),
                'previewUrl' => home_url('/?post_type=' . Dragwyb_Post::POST_TYPE . '&p=' . self::$form_id . '&preview_id=' . Form_Preview::generate_key(self::$form_id)),
            ];

            $localize_data = apply_filters('Dragwyb/Editor/Localize_Settings', $localize_data);
            // Localize data
            wp_localize_script('dragwyb-form-editor', 'DragwybEditor', $localize_data);

            do_action('Dragwyb/after_enqueue/editor_scripts');
        }

        private function get_fa_icons_list(): array
        {
            $icons = Icons_Helper::get_icons_list_group();

            return $icons;
        }

        private function external_libs(): void
        {
            // font-awesome@5.15.4
            wp_enqueue_style(
                'dragwyb-font-awesome',
                esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/font-awesome/v5/all.min.css'),
                [],
                '5.15.4'
            );

            // @simonwep/pickr@1.9.1 style
            wp_enqueue_style(
                'dragwyb-pickr',
                esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/lib/pickr/css/index.css'),
                [],
                '1.9.1'
            );

            // @simonwep/pickr@1.9.1 script
            wp_enqueue_script(
                'dragwyb-pickr',
                esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/lib/pickr/js/index.js'),
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

            $form_data = [
                'id' => $form_id,
                'title' => $form ? $form->post_title : '',
                'status' => $form ? $form->post_status : '',
            ];

            if (self::$initial_load === true) {
                $form_data['addSubmitButton'] = true;
            } else {
                $form_saved_data = get_post_meta($form_id, '_dragwyb_form_data', true);

                if (empty($form_saved_data)) {
                    $form_data['addSubmitButton'] = true;
                }
            }

            return $form_data;
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

            $data['formData']['rootContainers'] = array();

            $frontend = Frontend_Render::instance();
            $frontend->init($form_id);
            $style_cache = $frontend->get_generated_css();
            $root_containers = $frontend->get_root_containers();

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

                    if (!empty($toolbar_data)) {
                        $data['formData'][$key] = $toolbar_data;
                    }

                    if (!empty($toolbar_settings)) {
                        $data[$key] = $toolbar_settings;
                    }
                }
            }

            if (isset($data['EditorToolbars']['toolbars'][$default_toolbar])) {
                $data['EditorToolbars']['Default'] = sanitize_text_field($default_toolbar);
            }

            if ($root_containers && !empty($root_containers) && is_array($root_containers)) {
                $data['formData']['rootContainers'] = $root_containers;
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

        private function get_registered_categories(): array
        {
            $categories_object = Categories::instance();

            $categories = $categories_object->get_categories();

            $reigster_categories = array();

            foreach ($categories as $categorie) {
                if (!$categorie instanceof Category_Base) {
                    continue;
                }

                $categorie_id = $categorie->get_id();
                $categorie_name = $categorie->get_name();
                $categorie_icon = $categorie->get_icon();

                $reigster_categories[$categorie_id] = array('name' => $categorie_name, 'icon' => $categorie_icon);
            }

            return $reigster_categories;
        }

        public function localize_i18n_strings($strings): array
        {
            $localize_strings = [
                'exit' => __('Exit', 'smart-form-builder-by-dragwyb'),
                'submit' => __('Submit', 'smart-form-builder-by-dragwyb'),
                'fields' => __('Fields', 'smart-form-builder-by-dragwyb'),
                'fieldSettings' => __('Field Settings', 'smart-form-builder-by-dragwyb'),
                'formSettings' => __('Form Settings', 'smart-form-builder-by-dragwyb'),
                'save' => __('Save Form', 'smart-form-builder-by-dragwyb'),
                'preview' => __('Preview', 'smart-form-builder-by-dragwyb'),
                'formTitle' => __('Form Title', 'smart-form-builder-by-dragwyb'),
                'settings' => __('Settings', 'smart-form-builder-by-dragwyb'),
                'general' => __('General', 'smart-form-builder-by-dragwyb'),
                'style' => __('Style', 'smart-form-builder-by-dragwyb'),
                'advance' => __('Advance', 'smart-form-builder-by-dragwyb'),
                'display' => __('Display', 'smart-form-builder-by-dragwyb'),
                'validation' => __('Validation', 'smart-form-builder-by-dragwyb'),
                'notifications' => __('Notifications', 'smart-form-builder-by-dragwyb'),
                'confirmations' => __('Confirmations', 'smart-form-builder-by-dragwyb'),
                'confirmation_email' => __('Confirmation Email', 'smart-form-builder-by-dragwyb'),
                'confirmation_message' => __('Confirmation Message', 'smart-form-builder-by-dragwyb'),
                'confirmation_subject' => __('Confirmation Subject', 'smart-form-builder-by-dragwyb'),
                'confirmation_message' => __('Confirmation Message', 'smart-form-builder-by-dragwyb'),
                'emptyForm' => __("Start building your form by dragging fields from the sidebar or simply click to add them.", 'smart-form-builder-by-dragwyb')
            ];

            return array_merge($localize_strings, $strings);
        }
    }
}
