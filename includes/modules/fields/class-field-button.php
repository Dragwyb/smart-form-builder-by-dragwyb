<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Button extends Field_Base
{
    protected function init(): void
    {
        $this->type = 'button';
        $this->name = __('Button', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fa fa-mouse-pointer';
        $this->category = 'structure';
    }

    /**
     * Define settings specific to this button
     */
    protected function register_field_controls(): void
    {
        // ==============================================================
        // CONTENT TAB
        // ==============================================================

        $this->start_section('section_content', [
            'label' => __('Button Settings', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::ContentTab,
        ]);

        // The text displayed on the button
        $this->add_control('text', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'smart-form-builder-by-dragwyb'),
            'default' => __('Submit', 'smart-form-builder-by-dragwyb'),
        ]);

        // Determines if the button submits the form or clears the inputs
        $this->add_control('button_action', [
            'type'    => Controls::SELECT,
            'label'   => __('Action Type', 'smart-form-builder-by-dragwyb'),
            'options' => [
                'submit' => __('Submit Form', 'smart-form-builder-by-dragwyb'),
                'reset'  => __('Clear / Reset', 'smart-form-builder-by-dragwyb'),
            ],
            'label_inline' => true,
            'default' => 'submit',
        ]);

        // Set the alignment of the button within its container
        $this->add_control('alignment', [
            'type'    => Controls::CHOOSE,
            'label'   => __('Alignment', 'smart-form-builder-by-dragwyb'),
            'options' => [
                'left'   => ['title' => 'Left',   'icon' => 'fa fa-align-left'],
                'center' => ['title' => 'Center', 'icon' => 'fa fa-align-center'],
                'right'  => ['title' => 'Right',  'icon' => 'fa fa-align-right'],
                'justify' => ['title' => 'Justified', 'icon' => 'fa fa-align-justify'],
            ],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-align: {{VALUE}};',
            ],
        ]);

        $this->end_section();

        // ==============================================================
        // STYLE TAB
        // ==============================================================

        $this->start_section('section_style', [
            'label' => __('Button Style', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        // Use tabs to allow different styling for Normal and Hover states
        $this->start_tabs('tabs_button_style');

        // -- Normal State --
        $this->start_tab('tab_btn_normal', ['label' => __('Normal', 'smart-form-builder-by-dragwyb')]);

        $this->add_control('bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-btn-bg: {{VALUE}};'],
        ]);

        $this->add_control('text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-btn-color: {{VALUE}};'],
        ]);

        $this->add_group_control('border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}}',
            'prefix' => 'btn'
        ]);

        $this->end_tab();

        // -- Hover State --
        $this->start_tab('tab_btn_hover', ['label' => __('Hover', 'smart-form-builder-by-dragwyb')]);

        $this->add_control('hover_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-btn-hover-bg: {{VALUE}};'],
        ]);

        $this->add_control('hover_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-btn-hover-color: {{VALUE}};'],
        ]);

        $this->add_control('hover_border_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Border Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-btn-hover-border-color: {{VALUE}};'],
        ]);

        $this->end_tab();
        $this->end_tabs();

        // Controls for padding and border radius affect both states
        $this->add_control('padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'smart-form-builder-by-dragwyb'),
            'size_units' => ['px', 'em', '%'],
            'selectors'  => ['{{WRAPPER}}' => '--dragwyb-btn-pt: {{TOP}}{{UNIT}}; --dragwyb-btn-pr: {{RIGHT}}{{UNIT}}; --dragwyb-btn-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-btn-pl: {{LEFT}}{{UNIT}};'],
            'separator'  => 'before',
        ]);

        $this->add_group_control('typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}}',
            'prefix' => 'btn'
        ]);

        $this->add_control('button_width', [
            'type' => Controls::SLIDER,
            'label' => __('Button Width', 'smart-form-builder-by-dragwyb'),
            'units' => ['px', '%', 'em', 'rem', 'vw', 'vh'],
            'range' => [
                'px' => [
                    'min' => 0,
                    'max' => 1000,
                ],
                '%' => [
                    'min' => 0,
                    'max' => 100,
                ],
                'em' => [
                    'min' => 0,
                    'max' => 100,
                ],
                'rem' => [
                    'min' => 0,
                    'max' => 100,
                ]
            ],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-btn-width: {{VALUE}}{{UNIT}};',
            ],
        ]);

        $this->end_section();
    }

    /**
     * Render the button on frontend
     */
    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id = $this->get_the_id();
        $field_id       = $this->field_key_exist($settings, 'field_id', uniqid('btn_'));
        $text     = $this->field_key_exist($settings, 'text', 'Submit');
        $action   = $this->field_key_exist($settings, 'button_action', 'submit');
        $classes  = $this->field_key_exist($settings, 'css_classes', '');

        $btn_class = "dragwyb-btn dragwyb-btn-{$action}";

        $type = ($action === 'reset') ? 'reset' : 'submit';

?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?> dragwyb-no-float">
            <button type="<?php echo esc_attr($type); ?>" id="<?php echo esc_attr($field_id); ?>" class="<?php echo esc_attr($btn_class); ?>">
                <?php echo esc_html($text); ?>
            </button>
        </div>
<?php
    }

    public function validate($value, $field_id, $settings, Form_Submission_Handler $error_handler): void {}

    /**
     * Sanitize the field value.
     *
     * @param string $default The default value.
     * @param mixed $value The value to sanitize.
     * @return mixed Sanitized value.
     */
    public function sanitize($default = '', $value = null) {}
}
