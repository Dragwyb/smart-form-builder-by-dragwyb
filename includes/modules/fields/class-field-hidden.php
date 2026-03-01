<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;

class Field_Hidden extends Field_Base
{
    protected function init(): void
    {
        $this->type = 'hidden';
        $this->name = __('Hidden Field', 'dragwyb-form-builder');
        $this->icon = 'fas fa-eye-slash';
        $this->category = 'advanced-fields';
    }

    protected function header_controls(): array
    {
        $header_tab = array();
        $tabs = array();

        $tabs = apply_filters('Dragwy/Editor/render_controls/header_tabs', $tabs);

        if ($tabs && is_array($tabs) && count($tabs) > 0) {
            $header_tab['header_controls'] = array(
                'type' => 'tabs',
                'tabs' => $tabs
            );
        }

        return $header_tab;
    }

    protected function tab_condition(&$conditions, $data): array
    {
        if ((isset($data['tab']) && !empty($data['tab']))) {
            $conditions['header_controls'] = $data['tab'];
        }

        return $conditions;
    }

    protected function layout_id_controls(): void {}

    protected function register_field_controls(): void
    {
        // Hidden fields only need basic settings
        $this->start_section('section_content', ['label' => 'Settings']);

        $this->add_control('field_id', [
            'type'        => Controls::TEXT,
            'label'       => __('Field Name / ID', 'dragwyb-form-builder'),
            'description' => __('The name attribute used to identify this data (e.g., source_id).', 'dragwyb-form-builder'),
            'default'     => uniqid('hidden_'),
        ]);

        $this->add_control('default_value', [
            'type'    => Controls::TEXT,
            'label'   => __('Default Value', 'dragwyb-form-builder'),
            'default' => '',
        ]);

        $this->end_section();
    }

    protected function render_field()
    {
        $settings = $this->get_field_settings();
        $id       = $this->field_key_exist($settings, 'field_id', uniqid('hidden_'));
        $value    = $this->field_key_exist($settings, 'default_value', '');

        // Just render the input, no wrapper needed in frontend
?>
        <input type="hidden" name="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($value); ?>" />
<?php
    }

    public function validate($value): bool
    {
        return true;
    }
    public function sanitize($value)
    {
        return sanitize_text_field($value);
    }
    protected function register_scripts()
    {
        return [];
    }
    protected function register_style()
    {
        return [];
    }
}
