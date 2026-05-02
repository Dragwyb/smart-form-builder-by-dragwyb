<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Categories\Categories;
use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Row extends Field_Base
{
    private $fields;
    private $field_module_cache;

    protected function register_scripts()
    {
        $scripts = array();

        if (defined('DRAGWYB_EDITOR')) {
            $scripts = array('dragwyb_editor_fields');
        }

        return $scripts;
    }

    protected function register_style()
    {
        return array();
    }

    public function __construct()
    {
        parent::__construct();
        // The script is typically already registered by base fields, but we ensure it's there
        if (! wp_script_is('dragwyb_editor_fields', 'registered')) {
            wp_register_script('dragwyb_editor_fields', esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editorFields/editorFields.js'), array(), esc_attr(DRAGWYB_FORM_BUILDER_VERSION), true);
        }
    }

    protected function init(): void
    {
        $this->type = 'row';
        $this->name = __('Row', 'smart-form-builder-by-dragwyb');
        $this->icon = 'fas fa-border-all';
        $this->category = Categories::STRUCTURE;
        $this->allow_child = true;
        $this->is_root_container = true;
    }

    protected function register_field_controls(): void
    {
        $this->start_section('section_content_general', [
            'label' => __('Row Settings', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::ContentTab,
        ]);

        $this->add_responsive_control('columns', [
            'type'    => Controls::NUMBER,
            'label'   => __('Columns', 'smart-form-builder-by-dragwyb'),
            'default' => 1,
            'min'     => 1,
            'max'     => 12,
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-row-columns: {{VALUE}};',
            ],
        ]);

        $this->add_responsive_control('rows', [
            'type'    => Controls::NUMBER,
            'label'   => __('Rows', 'smart-form-builder-by-dragwyb'),
            'default' => 1,
            'min'     => 1,
            'max'     => 100,
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-row-rows: {{VALUE}};',
            ],
        ]);

        $this->add_control('gap', [
            'type'      => Controls::SLIDER,
            'label'     => __('Gap', 'smart-form-builder-by-dragwyb'),
            'range'     => ['px' => ['min' => 0, 'max' => 100]],
            'default'   => ['size' => 20, 'unit' => 'px'],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-row-gap: {{VALUE}}{{UNIT}};',
            ],
        ]);

        $this->end_section();

        $this->start_section('section_style_row', [
            'label' => __('Row Style', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('row_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background Color', 'smart-form-builder-by-dragwyb'),
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-row-bg-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('row_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'smart-form-builder-by-dragwyb'),
            'size_units' => ['px', 'em', '%'],
            'selectors'  => [
                '{{WRAPPER}}' => '--dragwyb-row-pt: {{TOP}}{{UNIT}}; --dragwyb-row-pr: {{RIGHT}}{{UNIT}}; --dragwyb-row-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-row-pl: {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control('row_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'smart-form-builder-by-dragwyb'),
            'selector' => '{{WRAPPER}}',
            'prefix' => 'row'
        ]);

        $this->end_section();
    }

    protected function layout_id_controls(): void
    {
        $this->start_section('section_advance_layout', [
            'label' => __('Layout', 'smart-form-builder-by-dragwyb'),
            'tab'   => self::AdvanceTab,
        ]);

        $this->add_responsive_control('field_width', [
            'type'        => Controls::SLIDER,
            'label'       => __('Row Width', 'smart-form-builder-by-dragwyb'),
            'range' => [
                'px' => [
                    'min' => 1,
                    'max' => 1000,
                ],
                '%' => [
                    'min' => 1,
                    'max' => 100,
                ],
                'em' => [
                    'min' => 1,
                    'max' => 100,
                ],
                'rem' => [
                    'min' => 1,
                    'max' => 100,
                ],
            ],
            'units' => ['px', '%', 'em', 'rem'],
            'default' => [
                'size' => 100,
                'unit' => '%',
            ],
            'selectors' => [
                '{{WRAPPER}}' => '--dragwyb-row-width: {{VALUE}}{{UNIT}};',
            ]
        ]);

        $this->add_control('css_classes', [
            'type'        => Controls::TEXT,
            'label'       => __('Custom CSS Classes', 'smart-form-builder-by-dragwyb'),
            'description' => __('Add custom classes to the wrapper.', 'smart-form-builder-by-dragwyb'),
        ]);

        $this->end_section();
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id = $this->get_the_id();
        $classes = $this->field_key_exist($settings, 'css_classes', '');

        $childrens = $this->field_key_exist($settings, 'children', array());

        if (!isset($childrens) || !is_array($childrens)) {
            return;
        }

        $childrens = array_filter($childrens, function ($childrens) {
            return isset($childrens);
        });

        if (empty($childrens)) {
            return;
        }

        // Render a basic row container
?>
        <div id="dragwyb-row-<?php echo esc_attr($id); ?>" class="dragwyb-row <?php echo esc_attr($classes); ?>">
            <?php foreach ($childrens as $children) : ?>
                <?php $this->render_children($children); ?>
            <?php endforeach; ?>
        </div>
<?php
    }

    private function render_children($children)
    {
        $field = $this->get_field_data($children);

        if (!isset($field['_id']) || !$field['type'] || empty($field['_id']) || empty($field['type'])) {
            return;
        }

        $field_data = array();
        $field_data['_id'] = $field['_id'];

        if (isset($field['type'])) {
            if (isset($field['type'])) {
                $type = $field['type'];

                $field_module = $this->get_module($type);

                if (!$field_module instanceof Field_Base) return;

                $field_module->set_the_id(sanitize_text_field($field_data['_id']));
                $field_module->set_frontend_handler($this->frontend_handler);

                if (isset($field['attributes']) && !empty($field['attributes'])) {
                    $attributes = $field['attributes'];
                    $field_data['attributes'] = array();
                    $this->attributes_loop($attributes, $field_module, $field_data);
                    $field_module->set_field_settings($field_data['attributes']);
                } else {
                    $field_module->set_field_settings(array());
                }

                $field_module->render();
            }
        }
    }

    private function attributes_loop($attributes, $field_module, &$field_data): void
    {

        if (!$field_module instanceof Field_Base) return;

        foreach ($attributes as $attribute => $value) {
            if ($field_control = $field_module->get_control($attribute)) {
                if (isset($field_control['type'])) {
                    $control_type = $field_control['type'];

                    $control_obj = $this->get_control_handler($control_type);

                    if (!$control_obj || !$control_obj instanceof Control_Base) {
                        continue;
                    }

                    $control_obj = $control_obj::newInstance();

                    $control_obj->set_value($value, $attribute, $field_control);
                    $filtered_value = $control_obj->get_value();

                    if (isset($filtered_value)) {
                        $field_data['attributes'][$attribute] = $filtered_value;
                    }
                }
            }
        }
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
