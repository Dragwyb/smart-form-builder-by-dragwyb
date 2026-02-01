<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Field_Text extends Field_Base
{
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
        wp_register_script('dragwyb_editor_fields', esc_url(DRAGWYB_FORM_BUILDER_URL . 'assets/dist/editorFields/editorFields.js'), array(), esc_attr(DRAGWYB_FORM_BUILDER_VERSION), true);
    }

    protected function init(): void
    {
        $this->type = 'text';
        $this->name = __('Text', 'dragwyb-form-builder');
        $this->icon = 'fas fa-font';
    }

    protected function register_controls(): void
    {
        // This section defines the main content settings for the field

        // Start the General Settings section where users define the core field properties
        $this->start_section('section_content_general', [
            'label' => __('General Settings', 'dragwyb-form-builder'),
            'tab'   => self::ContentTab,
        ]);

        // Add a text control for the field label that appears above the input
        $this->add_control('label', [
            'type'    => Controls::TEXT,
            'label'   => __('Label', 'dragwyb-form-builder'),
            'default' => __('Text Field', 'dragwyb-form-builder'),
            'dynamic' => ['active' => true],
        ]);

        // This control sets the placeholder text shown inside the input before typing
        $this->add_control('placeholder', [
            'type'    => Controls::TEXT,
            'label'   => __('Placeholder', 'dragwyb-form-builder'),
            'default' => __('Enter text...', 'dragwyb-form-builder'),
        ]);

        // Allow the user to set a default value that pre-fills the field
        $this->add_control('default_value', [
            'type'    => Controls::TEXT,
            'label'   => __('Default Value', 'dragwyb-form-builder'),
            'default' => '',
        ]);

        // Add a textarea for a short description or help text displayed below the field
        $this->add_control('help_text', [
            'type'        => Controls::TEXTAREA,
            'label'       => __('Help Text', 'dragwyb-form-builder'),
            'rows'        => 3,
            'description' => __('Text that appears below the field to guide the user.', 'dragwyb-form-builder'),
        ]);

        // A switcher control to mark this field as mandatory for validation
        $this->add_control('required', [
            'type'         => Controls::SWITCHER,
            'label'        => __('Required Field', 'dragwyb-form-builder'),
            'return_value' => 'yes',
            'default'      => 'no',
        ]);

        $this->end_section();

        // This section handles the visual styling of the field elements

        // Start the section for styling the field label
        $this->start_section('section_style_label', [
            'label' => __('Label', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('label_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'selectors' => [
                '{{WRAPPER}} .dragwyb-field-label' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('label_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} .dragwyb-field-label',
        ]);

        // Control the bottom spacing to separate the label from the input field
        $this->add_control('label_spacing', [
            'type'      => Controls::SLIDER,
            'label'     => __('Spacing (Bottom)', 'dragwyb-form-builder'),
            'range'     => ['px' => ['min' => 0, 'max' => 50]],
            'selectors' => [
                '{{WRAPPER}} .dragwyb-field-label' => 'margin-bottom: {{SIZE}}{{UNIT}};',
            ],
        ]);

        $this->end_section();

        // Start the section for styling the actual input box
        $this->start_section('section_style_input', [
            'label' => __('Input Field', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        // Use tabs to separate styles for the Normal state and the Focus state
        $this->start_tabs('tabs_input_style');

        // Define styles for the Normal state
        $this->start_tab('tab_input_normal', ['label' => __('Normal', 'dragwyb-form-builder')]);

        $this->add_control('input_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background', 'dragwyb-form-builder'),
            'selectors' => [
                '{{WRAPPER}} input.dragwyb-field-input' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Text Color', 'dragwyb-form-builder'),
            'selectors' => [
                '{{WRAPPER}} input.dragwyb-field-input' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_placeholder_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Placeholder', 'dragwyb-form-builder'),
            'selectors' => [
                '{{WRAPPER}} input.dragwyb-field-input::placeholder' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('input_border', [
            'type'     => Controls::GROUP_BORDER,
            'label'    => __('Border', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} input.dragwyb-field-input',
        ]);

        $this->add_group_control('input_box_shadow', [
            'type'     => Controls::GROUP_BOX_SHADOW,
            'label'    => __('Box Shadow', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} input.dragwyb-field-input',
        ]);

        $this->end_tab();

        // Define styles for the Focus state when the user clicks inside the input
        $this->start_tab('tab_input_focus', ['label' => __('Focus', 'dragwyb-form-builder')]);

        $this->add_control('input_focus_bg_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Background', 'dragwyb-form-builder'),
            'selectors' => [
                '{{WRAPPER}} input.dragwyb-field-input:focus' => 'background-color: {{VALUE}};',
            ],
        ]);

        $this->add_control('input_focus_border_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Border Color', 'dragwyb-form-builder'),
            'selectors' => [
                '{{WRAPPER}} input.dragwyb-field-input:focus' => 'border-color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('input_focus_box_shadow', [
            'type'     => Controls::GROUP_BOX_SHADOW,
            'label'    => __('Box Shadow', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} input.dragwyb-field-input:focus',
        ]);

        $this->end_tab();
        $this->end_tabs();

        // Set the internal padding for the input text
        $this->add_control('input_padding', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Padding', 'dragwyb-form-builder'),
            'size_units' => ['px', 'em', '%'],
            'selectors'  => [
                '{{WRAPPER}} input.dragwyb-field-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator'  => 'before',
        ]);

        // Set border radius to make the input corners rounded
        $this->add_control('input_radius', [
            'type'       => Controls::DIMENSIONS,
            'label'      => __('Border Radius', 'dragwyb-form-builder'),
            'size_units' => ['px', '%'],
            'selectors'  => [
                '{{WRAPPER}} input.dragwyb-field-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ]);

        $this->add_group_control('input_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} input.dragwyb-field-input',
        ]);

        $this->end_section();

        // Start the section for styling the help text description
        $this->start_section('section_style_help', [
            'label' => __('Help Text', 'dragwyb-form-builder'),
            'tab'   => self::StyleTab,
        ]);

        $this->add_control('help_text_color', [
            'type'      => Controls::COLOR,
            'label'     => __('Color', 'dragwyb-form-builder'),
            'selectors' => [
                '{{WRAPPER}} .dragwyb-field-help' => 'color: {{VALUE}};',
            ],
        ]);

        $this->add_group_control('help_text_typography', [
            'type'     => Controls::GROUP_TYPOGRAPHY,
            'label'    => __('Typography', 'dragwyb-form-builder'),
            'selector' => '{{WRAPPER}} .dragwyb-field-help',
        ]);

        $this->end_section();


        // The Advance tab contains layout options and technical identifiers

        $this->start_section('section_advance_layout', [
            'label' => __('Layout & ID', 'dragwyb-form-builder'),
            'tab'   => self::AdvanceTab,
        ]);

        // The Field ID is a unique identifier used for saving data and logic
        $this->add_control('field_id', [
            'type'        => Controls::TEXT,
            'label'       => __('Field ID', 'dragwyb-form-builder'),
            'description' => __('Unique ID for logic and emails (e.g., text_field_1).', 'dragwyb-form-builder'),
            'dynamic'     => ['active' => false],
        ]);

        // Allow users to set the width of the field, for example 50 percent for two columns
        // We display the label inline for a cleaner layout in the settings panel
        $this->add_control('width', [
            'type'         => Controls::SELECT,
            'label'        => __('Field Width', 'dragwyb-form-builder'),
            'label_inline' => true,
            'options'      => [
                '100%' => '100%',
                '50%'  => '50%',
                '33%'  => '33%',
                '25%'  => '25%',
                'auto' => 'Auto',
            ],
            'default'   => '100%',
            'selectors' => [
                '{{WRAPPER}}' => 'width: {{VALUE}};',
            ],
        ]);

        $this->add_control('css_classes', [
            'type'        => Controls::TEXT,
            'label'       => __('Custom CSS Classes', 'dragwyb-form-builder'),
            'description' => __('Add custom classes to the wrapper.', 'dragwyb-form-builder'),
        ]);

        $this->end_section();

        // Placeholder section for conditional logic which will be added in future updates
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

    protected function render_field()
    {
        $settings = $this->get_field_settings();

        $id       = $this->field_key_exist($settings, 'field_id', uniqid('field_'));
        $label    = $this->field_key_exist($settings, 'label', 'Text Field');
        $placeholder = $this->field_key_exist($settings, 'placeholder', ' '); // Space for float logic
        $value    = $this->field_key_exist($settings, 'default_value', '');
        $help     = $this->field_key_exist($settings, 'help_text', '');
        $required = $this->field_key_exist($settings, 'required', '') === 'yes';
        $classes  = $this->field_key_exist($settings, 'css_classes', '');
?>
        <div class="dragwyb-field-wrapper <?php echo esc_attr($classes); ?>">
            <div class="dragwyb-input-group">
                <input
                    type="text"
                    id="<?php echo esc_attr($id); ?>"
                    name="<?php echo esc_attr($id); ?>"
                    value="<?php echo esc_attr($value); ?>"
                    placeholder="<?php echo esc_attr($placeholder); ?>"
                    class="dragwyb-field-input"
                    <?php echo $required ? 'required' : ''; ?> />
                <?php if (!empty($label)) : ?>
                    <label for="<?php echo esc_attr($id); ?>" class="dragwyb-field-label">
                        <?php echo esc_html($label); ?>
                        <?php if ($required) : ?><span class="dragwyb-required">*</span><?php endif; ?>
                    </label>
                <?php endif; ?>
            </div>
            <?php if (!empty($help)) : ?>
                <div class="dragwyb-field-help"><?php echo esc_html($help); ?></div>
            <?php endif; ?>
        </div>
<?php
    }

    public function validate($value): bool
    {
        if (empty($value) && !empty($this->settings['required']['value'])) {
            return false;
        }

        $min_length = (int) ($this->settings['min_length']['value'] ?? 0);
        $max_length = (int) ($this->settings['max_length']['value'] ?? 0);

        if ($min_length && strlen($value) < $min_length) {
            return false;
        }

        if ($max_length && strlen($value) > $max_length) {
            return false;
        }

        return true;
    }

    public function sanitize($value)
    {
        return sanitize_text_field($value);
    }
}
