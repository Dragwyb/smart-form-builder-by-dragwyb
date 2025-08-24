<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Admin\Dragwyb_Editor;

use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;
use Dragwyb\Form_Builder\Includes\Modules\Modules;
use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Dragwyb_Init;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbars;
use Dragwyb\Form_Builder\Includes\Toolbars\Toolbar_Base;

if (!defined("ABSPATH")) {
    die("You can't access this page");
}


if (!class_exists('Dragwyb_Builder_Editor')) {
    class Dragwyb_Builder_Editor
    {
        private static $form_id = null;
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
            add_filter('Dragwyb_i18n', [$this, 'localize_i18n_strings']);
            add_filter('Dragwyb/Editor/Localize_Settings', [$this, 'editor_toolbars_localize']);
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
                $post_type = Dragwyb_Post::post_type();

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

            $post_type = Dragwyb_Post::post_type();

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

            do_action('Dragwyb/before_enqueue/editor_scripts');

            wp_enqueue_script('dragwyb-form-core');

            $dependencies = array('dragwyb-form-core');

            // Enqueue React and dependencies
            wp_enqueue_script(
                'dragwyb-form-editor',
                DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editor/editor.js',
                array_merge($dependencies, ['wp-element', 'wp-components', 'wp-i18n']),
                DRAGWYB_FORM_BUILDER_VERSION,
                true
            );

            wp_enqueue_style(
                'dragwyb-form-editor',
                DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editor/editor.css',
                ['wp-components'],
                DRAGWYB_FORM_BUILDER_VERSION
            );

            wp_enqueue_style(
                'dragwyb-font-awesome',
                DRAGWYB_FORM_BUILDER_URL . 'assets/font-awesome/v6/all.min.css',
                [],
                '6.7.2'
            );
            // wp_enqueue_style(
            //     'dragwyb-font-awesome',
            //     DRAGWYB_FORM_BUILDER_URL . 'assets/font-awesome/v5/all.min.css',
            //     [],
            //     '5.15.4'
            // );

            $localize_data = [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('dragwyb_editor'),
                'formId' => (int) self::$form_id,
                'editorContainer' => esc_html(self::Current_Page) . '-editor-container',
                'formData' => $this->get_form_data((int) self::$form_id),
                'controlTypes' => $this->get_control_types(),
                'formTypes' => $this->get_form_types(),
                'adminUrl' => admin_url('admin.php?page=dragwyb-form-overview'),
            ];

            $localize_data = apply_filters('Dragwyb/Editor/Localize_Settings', $localize_data);
            // Localize data
            wp_localize_script('dragwyb-form-editor', 'DragwybEditor', $localize_data);

            do_action('Dragwyb/after_enqueue/editor_scripts');
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
            $toolbar_data = array();

            $toolbar_obj=new Toolbars();
            $default_toolbar=$toolbar_obj->defaultToolbar();
            $toolbars=$toolbar_obj->get_toolbars();
            $form_data=[];

            if(isset($data['formId'])){
                $form_data=get_post_meta($data['formId'], '_dragwyb_form_data', true);

                if(!empty($form_data) && !isset($data['formData'])){
                    $data['formData']=array();
                }
            }

            if(count($toolbars) < 1){
                return array();
            }

            foreach($toolbars as $key => $toolbar){
                if($toolbar instanceof Toolbar_Base){
                    $settings=$toolbar->get_toolbar_settings();
                    $name=$toolbar->get_toolbar_name();
                    $icon=$toolbar->get_toolbar_icon();
                    $toolbar->enqueue_assets();

                    if($settings){
                        if(!isset($data[$key]))
                        $data[$key]=$settings;

                        $toolbar_data[$key]=array('name'=>$name, 'icon'=>$icon);
                    }

                    if(isset($form_data[$key])){
                        $toolbar->set_toolbar_data($form_data[$key]);
                        $sanitize_toolbar_data=$toolbar->get_toolbar_data();

                        $data['formData'][$key]=$sanitize_toolbar_data;
                    }
                }
            }   

            $toolbar_data=array('toolbars'=>$toolbar_data);
            
            if(isset($toolbar_data['toolbars'][$default_toolbar])){
                $toolbar_data['Default']=sanitize_text_field($default_toolbar);
            }

            $data=array_merge($data, array('EditorToolbars'=>$toolbar_data));
            
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
    }
}
