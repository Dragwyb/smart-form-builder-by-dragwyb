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

    protected function init(): void{}

    protected function register_controls(): void {
        {
            $this->start_section('texts_form_settings', [
                'label' => 'Form Settings',
            ]);
    
            $this->start_tabs('text_tabs');
    
            $this->start_tab('text_normal', [
                'label' => 'Normal',
            ]);
    
            $this->add_control('text_color', [
                'type' => Controls::COLOR,
                'label' => __('Field Label Color', 'dragwyb-form-builder'),
                'default' => '',
            ]);
    
            $this->end_tab();
    
            $this->start_tab('text_hover', [
                'label' => 'Hover',
            ]);
    
            $this->add_control('text_hover_color', [
                'type' => Controls::COLOR,
                'label' => __('Field Label Color', 'dragwyb-form-builder'),
                'default' => '',
                'selectoR' => array(
                    '{{WRAPPER}} .form-text input: {color: {{VALUE}}}',
                )
            ]);
    
            $this->end_tab();
    
            $this->end_tabs();
    
            $this->add_control('text_label', [
                'type' => Controls::TEXT,
                'label' => __('Field Label', 'dragwyb-form-builder'),
                'default' => 'Enter Your Label',
            ]);
    
            $this->add_control('text_placeholder', [
                'type' => Controls::TEXT,
                'label' => __('Placeholder', 'dragwyb-form-builder'),
                'default' => '',
                'conditions' => [
                    'text_label' => 'aniket',
                ]
            ]);
            $this->add_control('text_required', [
                'type' => Controls::CHECKBOX,
                'label' => __('Required', 'dragwyb-form-builder'),
                'default' => false,
            ]);
            $this->add_control('text_css_class', [
                'type' => Controls::TEXT,
                'label' => __('CSS Class', 'dragwyb-form-builder'),
                'default' => '',
            ]);
    
            $this->end_section();
    
            $this->start_section('text_form_style', [
                'label' => 'Form Style',
            ]);
            $this->add_control('text_style', [
                'type' => Controls::TEXT,
                'label' => __('CSS Class', 'dragwyb-form-builder'),
                'default' => '',
            ]);
            $this->end_section();
    
            $this->start_section('text_form_style_tab', [
                'label' => 'Form Style',
            ]);
            $this->add_control('text_style_tab', [
                'type' => Controls::TEXT,
                'label' => __('CSS Class', 'dragwyb-form-builder'),
                'default' => '',
            ]);
            $this->end_section();
            $this->start_section('text_form_style_tab_two', [
                'label' => 'Form Style',
            ]);
            $this->add_control('text_style_tab_two', [
                'type' => Controls::TEXT,
                'label' => __('CSS Class', 'dragwyb-form-builder'),
                'default' => '',
            ]);
            $this->end_section();
        }
    }
}
