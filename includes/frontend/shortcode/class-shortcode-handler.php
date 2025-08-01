<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Frontend\Shortcode;

if (!defined('ABSPATH')) {
    exit;
}

use Dragwyb\Form_Builder\Admin\Dragwyb_Pages\Dragwyb_Post;
use Dragwyb\Form_Builder\Includes\Frontend\Frontend_Render;

class Shortcode_Handler
{

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
        add_shortcode('dragwyb-form', [$this, 'render_shortcode']);
    }

    /**
     * Handles the [dragwyb_form id="123"] shortcode.
     */
    public function render_shortcode($atts)
    {
        $atts = shortcode_atts([
            'id' => 0,
        ], $atts);

        $form_id = absint($atts['id']);
        if (!$form_id || get_post_type($form_id) !== Dragwyb_Post::POST_TYPE) {
            return '<p>' . esc_html__('Form not found or invalid.', 'dragwyb-form-builder') . '</p>';
        }

        // Load form data from post meta
        $form_settings = get_post_meta($form_id, '_dragwyb_form_fields', true);

        if (empty($form_settings) || !is_array($form_settings)) {
            return '<p>' . esc_html__('No fields found in this form.', 'dragwyb-form-builder') . '</p>';
        }

        // Generate form HTML
        $form_html = (new Frontend_Render($form_id, $form_settings))->render();

        return '<div class="dragwyb-form-wrapper" id="dragwyb-form-wrapper-' . esc_attr($form_id) . '">' . wp_kses($form_html, $this->allowed_html_for_form()) . '</div>';
    }

    private function allowed_html_for_form(): array
    {
        $allowed_html = wp_kses_allowed_html('post'); // includes basic tags like <a>, <p>, <br>, <strong>, etc.

        $form_tags = [
            'form' => [
                'action' => true,
                'method' => true,
                'name' => true,
                'id' => true,
                'class' => true,
                'enctype' => true,
                'target' => true,
                'novalidate' => true,
                'autocomplete' => true,
            ],
            'input' => [
                'type' => true,
                'name' => true,
                'value' => true,
                'placeholder' => true,
                'checked' => true,
                'disabled' => true,
                'readonly' => true,
                'required' => true,
                'min' => true,
                'max' => true,
                'step' => true,
                'id' => true,
                'class' => true,
                'size' => true,
                'autocomplete' => true,
            ],
            'select' => [
                'name' => true,
                'id' => true,
                'class' => true,
                'multiple' => true,
                'required' => true,
            ],
            'option' => [
                'value' => true,
                'selected' => true,
            ],
            'textarea' => [
                'name' => true,
                'id' => true,
                'class' => true,
                'placeholder' => true,
                'rows' => true,
                'cols' => true,
                'maxlength' => true,
                'required' => true,
                'readonly' => true,
            ],
            'button' => [
                'type' => true,
                'name' => true,
                'value' => true,
                'id' => true,
                'class' => true,
            ],
            'label' => [
                'for' => true,
                'class' => true,
            ],
            'fieldset' => [
                'id' => true,
                'class' => true,
                'disabled' => true,
            ],
            'legend' => [
                'class' => true,
            ],
            'datalist' => [
                'id' => true,
            ],
        ];

        $form_tags = array_merge_recursive($allowed_html, $form_tags);

        $form_tags = apply_filters('Dragwyb/Frontend/Render/Allowed_Tags', $form_tags);

        // Merge with wp_kses_post default allowed tags
        return $form_tags;
    }
}
