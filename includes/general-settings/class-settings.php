<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\General_Settings;

use Dragwyb\Form_Builder\Includes\Controls\Register_Controls_Base;
use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Settings extends Register_Controls_Base
{
    private static $instance = null;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    protected function init(): void {}

    protected function register_controls(): void
    {
        /**
         * 🔹 Label Styling
         */
        $this->start_section('form_label_style', [
            'label' => __('Label Style', 'dragwyb-form-builder'),
        ]);

        $this->add_control('label_color', [
            'type'    => Controls::COLOR,
            'label'   => __('Label Color', 'dragwyb-form-builder'),
            'default' => '#333333',
            'selector' => [
                '{{WRAPPER}} label' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('label_typography', [
            'type'  => Controls::TEXT,
            'label' => __('Label Font (CSS class / font family)', 'dragwyb-form-builder'),
            'default' => '',
        ]);

        $this->end_section();


        /**
         * 🔹 Input Styling
         */
        $this->start_section('form_input_style', [
            'label' => __('Input Fields', 'dragwyb-form-builder'),
        ]);

        $this->add_control('input_text_color', [
            'type'    => Controls::COLOR,
            'label'   => __('Text Color', 'dragwyb-form-builder'),
            'default' => '#000000',
            'selector' => [
                '{{WRAPPER}} input, {{WRAPPER}} textarea, {{WRAPPER}} select' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_bg_color', [
            'type'    => Controls::COLOR,
            'label'   => __('Background Color', 'dragwyb-form-builder'),
            'default' => '#ffffff',
            'selector' => [
                '{{WRAPPER}} input, {{WRAPPER}} textarea, {{WRAPPER}} select' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_border_color', [
            'type'    => Controls::COLOR,
            'label'   => __('Border Color', 'dragwyb-form-builder'),
            'default' => '#cccccc',
            'selector' => [
                '{{WRAPPER}} input, {{WRAPPER}} textarea, {{WRAPPER}} select' => 'border-color: {{VALUE}};',
            ],
        ]);

        $this->end_section();


        /**
         * 🔹 Button Styling
         */
        $this->start_section('form_button_style', [
            'label' => __('Submit Button', 'dragwyb-form-builder'),
        ]);

        $this->start_tabs('button_tabs');

        $this->start_tab('button_normal', [
            'label' => __('Normal', 'dragwyb-form-builder'),
        ]);

        $this->add_control('button_text_color', [
            'type'    => Controls::COLOR,
            'label'   => __('Text Color', 'dragwyb-form-builder'),
            'default' => '#ffffff',
            'selector' => [
                '{{WRAPPER}} button' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('button_bg_color', [
            'type'    => Controls::COLOR,
            'label'   => __('Background Color', 'dragwyb-form-builder'),
            'default' => '#0073e6',
            'selector' => [
                '{{WRAPPER}} button' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->end_tab();

        $this->start_tab('button_hover', [
            'label' => __('Hover', 'dragwyb-form-builder'),
        ]);

        $this->add_control('button_hover_bg', [
            'type'    => Controls::COLOR,
            'label'   => __('Hover Background', 'dragwyb-form-builder'),
            'default' => '#005bb5',
            'selector' => [
                '{{WRAPPER}} button:hover' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->end_tab();

        $this->end_tabs();
        $this->end_section();
    }
}
