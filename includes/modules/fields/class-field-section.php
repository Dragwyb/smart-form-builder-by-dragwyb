<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Section extends Field_Base
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function init(): void
    {
        $this->type = 'section';
        $this->name = __('Section Break', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-heading';
        $this->category = 'structure';
    }

    protected function register_field_controls(): void
    {
        $this->start_section('section_content_general', [
            'label' => __('Section Settings', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_control('title', [
            'type'    => Controls::TEXT,
            'label'   => __('Section Title', 'smart-form-builder-by-dragwyb'),
            'default' => __('Section Title', 'smart-form-builder-by-dragwyb'),
            'dynamic' => ['active' => true],
        ]);

        $this->add_control('description', [
            'type'        => Controls::TEXTAREA,
            'label'       => __('Description', 'smart-form-builder-by-dragwyb'),
            'rows'        => 3,
        ]);

        $this->end_section();

        $this->start_section('section_style_title', [
            'label' => __('Title Style', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('title_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Title Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-section-title-color: {{VALUE}};'],
        ]);

        $this->add_control('title_margin', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Margin', 'smart-form-builder-by-dragwyb'),
            'size_units' => ['px', 'em', '%'],
            'selectors'  => ['{{WRAPPER}}' => '--dragwyb-section-title-mt: {{TOP}}{{UNIT}}; --dragwyb-section-title-mr: {{RIGHT}}{{UNIT}}; --dragwyb-section-title-mb: {{BOTTOM}}{{UNIT}}; --dragwyb-section-title-ml: {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_section();

        $this->start_section('section_style_description', [
            'label' => __('Description Style', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('description_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Description Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-section-desc-color: {{VALUE}};'],
        ]);

        $this->add_control('description_margin', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Margin', 'smart-form-builder-by-dragwyb'),
            'size_units' => ['px', 'em', '%'],
            'selectors'  => ['{{WRAPPER}}' => '--dragwyb-section-desc-mt: {{TOP}}{{UNIT}}; --dragwyb-section-desc-mr: {{RIGHT}}{{UNIT}}; --dragwyb-section-desc-mb: {{BOTTOM}}{{UNIT}}; --dragwyb-section-desc-ml: {{LEFT}}{{UNIT}};'],
        ]);

        $this->end_section();

        $this->start_section('section_style_divider', [
            'label' => __('Divider Style', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('divider_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Divider Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => ['{{WRAPPER}}' => '--dragwyb-section-divider-color: {{VALUE}};'],
        ]);

        $this->end_section();
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id = $this->get_the_id();
        $title    = $this->field_key_exist($settings, 'title', 'Section Title');
        $description = $this->field_key_exist($settings, 'description', '');
        $classes  = $this->field_key_exist($settings, 'css_classes', '');
?>
        <div id="<?php echo esc_attr($this->field_wrapper_id($id)); ?>" class="<?php echo esc_attr($this->field_wrapper_class($classes)); ?>">
            <div class="dragwyb-section-break">
                <?php if (!empty($title)) : ?>
                    <h3 class="dragwyb-section-title"><?php echo esc_html($title); ?></h3>
                <?php endif; ?>
                <?php if (!empty($description)) : ?>
                    <p class="dragwyb-section-description"><?php echo wp_kses_post($description); ?></p>
                <?php endif; ?>
                <hr class="dragwyb-section-divider" />
            </div>
        </div>
<?php
    }

    public function validate($value, $field_id, $form_config, Form_Submission_Handler $error_handler): void
    {
        // Section field does not submit data.
    }

    public function sanitize($default = '', $value = null)
    {
        return '';
    }
}
