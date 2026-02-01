<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Field_Button extends Field_Base
{
    protected function init(): void
    {
        $this->type = 'button';
        $this->name = __('Button', 'dragwyb-form-builder');
        $this->icon = 'fa fa-mouse-pointer';
    }

    protected function register_scripts(): void {}
    protected function register_style(): void {}

    /**
     * Define settings specific to this button
     */
    protected function register_controls(): void
    {
        // ==============================================================
        // CONTENT TAB
        // ==============================================================

        $this->start_section('section_content', [
            'label' => __('Button Settings', 'dragwyb-form-builder'),
            'tab'   => self::ContentTab,
        ]);

        // The text displayed on the button
        $this->add_control('text', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'dragwyb-form-builder'),
            'default' => __('Submit', 'dragwyb-form-builder'),
        ]);

        // Determines if the button submits the form or clears the inputs
        $this->add_control('button_action', [
            'type'    => Controls::SELECT,
            'label'   => __('Action Type', 'dragwyb-form-builder'),
            'options' => [
                'submit' => __('Submit Form', 'dragwyb-form-builder'),
                'reset'  => __('Clear / Reset', 'dragwyb-form-builder'),
            ],
            'label_inline' => true,
            'default' => 'submit',
        ]);

        // Set the alignment of the button within its container
        $this->add_control('alignment', [
            'type'    => Controls::CHOOSE,
            'label'   => __('Alignment', 'dragwyb-form-builder'),
            'options' => [
                'left'   => ['title' => 'Left',   'icon' => 'fa fa-align-left'],
                'center' => ['title' => 'Center', 'icon' => 'fa fa-align-center'],
                'right'  => ['title' => 'Right',  'icon' => 'fa fa-align-right'],
                'justify' => ['title' => 'Justified', 'icon' => 'fa fa-align-justify'],
            ],
            'default' => 'left',
        ]);

        $this->end_section();

        // ==============================================================
        // STYLE TAB
        // ==============================================================

        $this->start_section('section_style', [
            'label' => __('Button Style', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        // Use tabs to allow different styling for Normal and Hover states
        $this->start_tabs('tabs_button_style');

        // -- Normal State --
        $this->start_tab('tab_btn_normal', ['label' => __('Normal', 'dragwyb-form-builder')]);

        $this->add_control('bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-btn' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-btn' => 'color: {{VALUE}};'],
        ]);

        $this->add_group_control('border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} .dragwyb-btn',
        ]);

        $this->end_tab();

        // -- Hover State --
        $this->start_tab('tab_btn_hover', ['label' => __('Hover', 'dragwyb-form-builder')]);

        $this->add_control('hover_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-btn:hover' => 'background-color: {{VALUE}};'],
        ]);

        $this->add_control('hover_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-btn:hover' => 'color: {{VALUE}};'],
        ]);

        $this->add_control('hover_border_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Border Color', 'dragwyb-form-builder'),
            'selectors' => ['{{WRAPPER}} .dragwyb-btn:hover' => 'border-color: {{VALUE}};'],
        ]);

        $this->end_tab();
        $this->end_tabs();

        // Controls for padding and border radius affect both states
        $this->add_control('padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'dragwyb-form-builder'),
            'size_units' => ['px', 'em', '%'],
            'selectors'  => ['{{WRAPPER}} .dragwyb-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'],
            'separator'  => 'before',
        ]);

        $this->add_group_control('typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} .dragwyb-btn',
        ]);

        $this->end_section();

        // ==============================================================
        // ADVANCE TAB
        // ==============================================================

        $this->start_section('section_advance_layout', [
            'label' => __('Layout & ID', 'dragwyb-form-builder'),
            'tab'   => self::AdvanceTab,
        ]);

        $this->add_control('field_id', [
            'type'        => Controls::TEXT,
            'label'       => __('Button ID', 'dragwyb-form-builder'),
            'default'     => uniqid('btn_'),
            'dynamic'     => ['active' => false],
        ]);

        $this->add_control('width', [
            'type'         => Controls::SELECT,
            'label'        => __('Width', 'dragwyb-form-builder'),
            'label_inline' => true,
            'options'      => [
                'auto' => 'Auto',
                '100%' => 'Full Width',
                '50%'  => '50%',
            ],
            'default'   => 'auto',
            'selectors' => [
                '{{WRAPPER}}' => 'width: {{VALUE}};',
            ],
        ]);

        $this->add_control('css_classes', [
            'type'        => Controls::TEXT,
            'label'       => __('Custom CSS Classes', 'dragwyb-form-builder'),
        ]);

        $this->end_section();

        $this->start_section('section_advance_logic', [
            'label' => __('Conditional Logic', 'dragwyb-form-builder'),
            'tab'   => self::AdvanceTab,
        ]);

        $this->add_control('enable_logic', [
            'type'         => Controls::SWITCHER,
            'label'        => __('Enable Logic', 'dragwyb-form-builder'),
            'default'      => '',
            'return_value' => '',
            'disabled'     => true,
        ]);

        $this->add_control('logic_msg', [
            'type' => Controls::RAW_HTML,
            'raw'  => '<div style="color: hsl(var(--dragwyb-sidebar-foreground)/var(--dragwyb-text-opacity, 1)); font-size: 12px; padding: 10px 0;">' . sprintf(__('%1$sComing Soon%2$s: Advanced Conditional Logic is in development. This feature will allow you to dynamically show or hide fields based on user input.', 'dragwyb-form-builder'), '<strong>', '</strong>') . '</div>',
            'condition' => [
                'enable_logic' => 'yes',
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
        $id       = $this->field_key_exist($settings, 'field_id', uniqid('btn_'));
        $text     = $this->field_key_exist($settings, 'text', 'Submit');
        $action   = $this->field_key_exist($settings, 'button_action', 'submit');
        $align    = $this->field_key_exist($settings, 'alignment', 'left');
        $width    = $this->field_key_exist($settings, 'width', 'auto');
        $classes  = $this->field_key_exist($settings, 'css_classes', '');

        $wrapper_style = "text-align: {$align}; width: {$width};";
        $btn_class = "dragwyb-btn dragwyb-btn-{$action}";
        if ($width === '100%') $btn_class .= ' dragwyb-btn-block';

        $type = ($action === 'reset') ? 'reset' : 'submit';

?>
        <div class="dragwyb-field-wrapper dragwyb-no-float <?php echo esc_attr($classes); ?>" style="<?php echo esc_attr($wrapper_style); ?>">
            <button type="<?php echo esc_attr($type); ?>" id="<?php echo esc_attr($id); ?>" class="<?php echo esc_attr($btn_class); ?>">
                <?php echo esc_html($text); ?>
            </button>
        </div>
<?php
    }

    public function validate($value): bool
    {
        return true;
    }
}
