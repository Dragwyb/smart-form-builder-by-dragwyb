<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Html extends Field_Base
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function init(): void
    {
        $this->type = 'html';
        $this->name = __('HTML', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-code';
        $this->category = 'structure';
        $this->keywords = array('code', 'markup', 'custom');
    }

    protected function register_field_controls(): void
    {
        $this->start_section('section_content_general', [
            'label' => __('Basic Settings', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_control('raw_html', [
            'type'        => Controls::TEXTAREA,
            'label'       => __('Raw HTML', 'smart-form-builder-by-dragwyb'),
            'rows'        => 8,
            'default'     => '<p>Enter your custom HTML here.</p>',
            'description' => __('Enter standard HTML. Some tags may be stripped for security.', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->end_section();

        $this->start_section('section_style_container', [
            'label' => __('Container Style', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-html-text-color: {{VALUE}};'],
        ]);

        $this->add_control('container_margin', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Margin', 'smart-form-builder-by-dragwyb'),
            'size_units' => ['px', 'em', '%'],
            'selectors'  => ['{{WRAPPER}}' => '--dragwyb-html-mt: {{TOP}}{{UNIT}}; --dragwyb-html-mr: {{RIGHT}}{{UNIT}}; --dragwyb-html-mb: {{BOTTOM}}{{UNIT}}; --dragwyb-html-ml: {{LEFT}}{{UNIT}};'],
        ]);

        $this->add_control('container_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'smart-form-builder-by-dragwyb'),
            'size_units' => ['px', 'em', '%'],
            'selectors'  => ['{{WRAPPER}}' => '--dragwyb-html-pt: {{TOP}}{{UNIT}}; --dragwyb-html-pr: {{RIGHT}}{{UNIT}}; --dragwyb-html-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-html-pl: {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_section();
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id = $this->get_the_id();
        $raw_html = $this->field_key_exist($settings, 'raw_html', '');
        $classes  = $this->field_key_exist($settings, 'css_classes', '');
?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?>">
            <div class="dragwyb-html-content">
                <?php echo wp_kses_post($raw_html); ?>
            </div>
        </div>
<?php
    }

    public function validate($value, $field_id, $form_config, Form_Submission_Handler $error_handler): void
    {
        // HTML field does not submit data.
    }

    public function sanitize($default = '', $value = null)
    {
        return '';
    }
}
