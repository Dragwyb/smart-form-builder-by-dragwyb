<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Templates {
    private const TEMPLATE_POST_TYPE = 'dragwyb_template';

    public function __construct() {
        add_action('init', [$this, 'register_template_post_type']);
        add_action('admin_menu', [$this, 'add_templates_menu']);
        add_action('wp_ajax_dragwyb_load_template', [$this, 'ajax_load_template']);
    }

    public function register_template_post_type(): void {
        register_post_type(self::TEMPLATE_POST_TYPE, [
            'labels' => [
                'name' => __('Form Templates', 'dragwyb-form-builder'),
                'singular_name' => __('Form Template', 'dragwyb-form-builder'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'capability_type' => 'post',
            'supports' => ['title', 'editor'],
        ]);

        $this->register_default_templates();
    }

    public function add_templates_menu(): void {
        add_submenu_page(
            'edit.php?post_type=dragwyb_form',
            __('Form Templates', 'dragwyb-form-builder'),
            __('Templates', 'dragwyb-form-builder'),
            'edit_posts',
            'dragwyb-templates',
            [$this, 'render_templates_page']
        );
    }

    public function render_templates_page(): void {
        $templates = $this->get_templates();
        include DRAGWYB_FORM_BUILDER_PATH . 'templates/admin/templates-page.php';
    }

    public function ajax_load_template(): void {
        check_ajax_referer('dragwyb_form_builder', 'nonce');

        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied', 'dragwyb-form-builder')]);
        }

        $template_id = intval($_POST['template_id'] ?? 0);
        if (!$template_id) {
            wp_send_json_error(['message' => __('Invalid template ID', 'dragwyb-form-builder')]);
        }

        $template_data = get_post_meta($template_id, '_template_data', true);
        if (!$template_data) {
            wp_send_json_error(['message' => __('Template not found', 'dragwyb-form-builder')]);
        }

        wp_send_json_success(['template_data' => $template_data]);
    }

    private function register_default_templates(): void {
        $templates = [
            'contact' => [
                'title' => __('Contact Form', 'dragwyb-form-builder'),
                'fields' => [
                    [
                        'type' => 'text',
                        'label' => __('Name', 'dragwyb-form-builder'),
                        'required' => true,
                    ],
                    [
                        'type' => 'email',
                        'label' => __('Email', 'dragwyb-form-builder'),
                        'required' => true,
                    ],
                    [
                        'type' => 'textarea',
                        'label' => __('Message', 'dragwyb-form-builder'),
                        'required' => true,
                    ],
                ],
            ],
            'registration' => [
                'title' => __('User Registration', 'dragwyb-form-builder'),
                'fields' => [
                    [
                        'type' => 'text',
                        'label' => __('Username', 'dragwyb-form-builder'),
                        'required' => true,
                    ],
                    [
                        'type' => 'email',
                        'label' => __('Email', 'dragwyb-form-builder'),
                        'required' => true,
                    ],
                    [
                        'type' => 'password',
                        'label' => __('Password', 'dragwyb-form-builder'),
                        'required' => true,
                    ],
                    [
                        'type' => 'password',
                        'label' => __('Confirm Password', 'dragwyb-form-builder'),
                        'required' => true,
                    ],
                ],
            ],
            'survey' => [
                'title' => __('Basic Survey', 'dragwyb-form-builder'),
                'fields' => [
                    [
                        'type' => 'text',
                        'label' => __('Name', 'dragwyb-form-builder'),
                        'required' => true,
                    ],
                    [
                        'type' => 'email',
                        'label' => __('Email', 'dragwyb-form-builder'),
                        'required' => true,
                    ],
                    [
                        'type' => 'radio',
                        'label' => __('How did you hear about us?', 'dragwyb-form-builder'),
                        'required' => true,
                        'options' => [
                            ['label' => __('Search Engine', 'dragwyb-form-builder'), 'value' => 'search'],
                            ['label' => __('Social Media', 'dragwyb-form-builder'), 'value' => 'social'],
                            ['label' => __('Friend', 'dragwyb-form-builder'), 'value' => 'friend'],
                            ['label' => __('Other', 'dragwyb-form-builder'), 'value' => 'other'],
                        ],
                    ],
                    [
                        'type' => 'textarea',
                        'label' => __('Additional Comments', 'dragwyb-form-builder'),
                        'required' => false,
                    ],
                ],
            ],
        ];

        foreach ($templates as $slug => $template) {
            $existing = get_page_by_path($slug, OBJECT, self::TEMPLATE_POST_TYPE);
            if ($existing) {
                continue;
            }

            $post_data = [
                'post_title' => $template['title'],
                'post_name' => $slug,
                'post_type' => self::TEMPLATE_POST_TYPE,
                'post_status' => 'publish',
            ];

            $template_id = wp_insert_post($post_data);
            if (!is_wp_error($template_id)) {
                update_post_meta($template_id, '_template_data', $template['fields']);
            }
        }
    }

    private function get_templates(): array {
        $args = [
            'post_type' => self::TEMPLATE_POST_TYPE,
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ];

        return get_posts($args);
    }
} 