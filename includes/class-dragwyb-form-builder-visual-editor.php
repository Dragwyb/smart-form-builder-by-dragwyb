<?php
declare(strict_types=1);

class Dragwyb_Form_Builder_Visual_Editor {
    public function __construct() {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('dragwyb_after_style_settings', [$this, 'render_visual_editor']);
    }

    public function enqueue_assets(): void {
        $screen = get_current_screen();
        if ($screen && $screen->base === 'post' && $screen->post_type === 'dragwyb_form') {
            wp_enqueue_script(
                'dragwyb-visual-editor',
                DRAGWYB_FORM_BUILDER_URL . 'assets/js/dragwyb-visual-editor.js',
                ['jquery', 'wp-color-picker'],
                DRAGWYB_FORM_BUILDER_VERSION,
                true
            );

            wp_enqueue_style(
                'dragwyb-visual-editor',
                DRAGWYB_FORM_BUILDER_URL . 'assets/css/dragwyb-visual-editor.css',
                [],
                DRAGWYB_FORM_BUILDER_VERSION
            );
        }
    }

    public function render_visual_editor(): void {
        include DRAGWYB_FORM_BUILDER_PATH . 'templates/admin/visual-editor.php';
    }
} 