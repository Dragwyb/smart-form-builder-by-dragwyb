<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Categories\Categories;
use Dragwyb\Form_Builder\Includes\Controls\Controls\Control_Base;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Row extends Field_Base {

	public function __construct() {
		parent::__construct();
	}

	protected function init(): void {
		$this->type              = 'row';
		$this->name              = __( 'Row', 'smart-form-builder-by-dragwyb' );
		$this->icon              = 'fas fa-border-all';
		$this->category          = 'structure';
		$this->keywords          = array( 'columns', 'layout', 'grid' );
		$this->allow_child       = true;
		$this->is_root_container = true;
	}

	protected function register_field_controls(): void {
		$this->start_section(
			'section_content_general',
			array(
				'label' => __( 'Row Settings', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::ContentTab,
			)
		);

		$this->add_responsive_control(
			'columns',
			array(
				'type'      => Controls::NUMBER,
				'label'     => __( 'Columns', 'smart-form-builder-by-dragwyb' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 12,
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-row-columns: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'rows',
			array(
				'type'      => Controls::NUMBER,
				'label'     => __( 'Rows', 'smart-form-builder-by-dragwyb' ),
				'default'   => 1,
				'min'       => 1,
				'max'       => 100,
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-row-rows: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'gap',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Column Gap', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'   => array(
					'size' => 20,
					'unit' => 'px',
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-column-gap: {{VALUE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'row_gap',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Row Gap', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'units'     => array( 'px', '%' ),
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-field-row-gap: {{VALUE}}{{UNIT}};',
				),
			)
		);

		$this->end_section();

		$this->start_section(
			'section_style_row',
			array(
				'label' => __( 'Row Style', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::StyleTab,
			)
		);

		$this->add_control(
			'row_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-row-bg-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'row_padding',
			array(
				'type'       => Controls::DIMENSIONS,
				'label'      => __( 'Padding', 'smart-form-builder-by-dragwyb' ),
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array(
					'{{WRAPPER}}' => '--dragwyb-row-pt: {{TOP}}{{UNIT}}; --dragwyb-row-pr: {{RIGHT}}{{UNIT}}; --dragwyb-row-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-row-pl: {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			'row_border',
			array(
				'type'     => Controls::GROUP_BORDER,
				'label'    => __( 'Border', 'smart-form-builder-by-dragwyb' ),
				'selector' => '{{WRAPPER}}',
				'prefix'   => 'row',
			)
		);

		$this->end_section();
	}

	protected function layout_id_controls(): void {
		$this->start_section(
			'section_advance_layout',
			array(
				'label' => __( 'Layout', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::AdvanceTab,
			)
		);

		$this->add_responsive_control(
			'field_width',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Row Width', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px'  => array(
						'min' => 1,
						'max' => 1000,
					),
					'%'   => array(
						'min' => 1,
						'max' => 100,
					),
					'em'  => array(
						'min' => 1,
						'max' => 100,
					),
					'rem' => array(
						'min' => 1,
						'max' => 100,
					),
				),
				'units'     => array( 'px', '%', 'em', 'rem' ),
				'default'   => array(
					'size' => 100,
					'unit' => '%',
				),
				'selectors' => array(
					'{{WRAPPER}}' => '--dragwyb-row-width: {{VALUE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'css_classes',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Custom CSS Classes', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Add custom classes to the wrapper.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->end_section();
	}

	protected function render_field() {
		$settings     = $this->get_field_settings();
		$id           = $this->get_the_id();
		$classes      = $this->field_key_exist( $settings, 'css_classes', '' );
		$is_last_root = $this->field_key_exist( $settings, 'is_last_root', false );

		$childrens = $this->field_key_exist( $settings, 'children', array() );

		if ( ! isset( $childrens ) || ! is_array( $childrens ) ) {
			return;
		}

		$childrens = array_filter(
			$childrens,
			function ( $childrens ) {
				return isset( $childrens );
			}
		);

		if ( empty( $childrens ) ) {
			return;
		}

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => 'dragwyb-row-' . $id,
				'class' => 'dragwyb-row',
			)
		);

		if ( $is_last_root ) {
			$this->add_field_attributes(
				'wrapper',
				array(
					'class' => 'dragwyb-last-row',
				)
			);
		}

		if ( ! empty( $$classes ) ) {
			$this->add_field_attributes(
				'wrapper',
				array(
					'class' => $classes,
				)
			);
		}

		$total_children = count( $childrens );
		$children_index = 1;

		// Render a basic row container
		?>
		<div <?php $this->render_field_attributes( 'wrapper' ); ?>>
			<?php
			foreach ( $childrens as $children ) :
				$is_last_children = ( $children_index === $total_children );
				$this->render_children( $children, $is_last_children );
				++$children_index;
			endforeach;
			?>
		</div>
		<?php
	}

	private function render_children( $children, $is_last_children = false ) {
		$field = $this->get_field_data( $children );

		if ( ! isset( $field['_id'] ) || ! $field['type'] || empty( $field['_id'] ) || empty( $field['type'] ) ) {
			return;
		}

		$field_data        = array();
		$field_data['_id'] = $field['_id'];

		if ( isset( $field['type'] ) ) {
			if ( isset( $field['type'] ) ) {
				$type = $field['type'];

				$field_module = $this->get_module( $type );

				if ( ! $field_module instanceof Field_Base ) {
					return;
				}

				$field_module->set_the_id( sanitize_text_field( $field_data['_id'] ) );
				$field_module->set_frontend_handler( $this->frontend_handler );

				$field_data['attributes'] = array();

				if ( isset( $field['attributes'] ) && ! empty( $field['attributes'] ) ) {
					$attributes = $field['attributes'];
					$this->attributes_loop( $attributes, $field_module, $field_data );
					$field_data['attributes']['is_last_field'] = $is_last_children ? true : false;
					$field_module->set_field_settings( $field_data['attributes'] );
				} else {
					$field_data['attributes']['is_last_field'] = $is_last_children ? true : false;
					$field_module->set_field_settings( $field_data['attributes'] );
				}

				$field_module->render();
			}
		}
	}

	private function attributes_loop( $attributes, $field_module, &$field_data ): void {

		if ( ! $field_module instanceof Field_Base ) {
			return;
		}

		foreach ( $attributes as $attribute => $value ) {
			if ( $field_control = $field_module->get_control( $attribute ) ) {
				if ( isset( $field_control['type'] ) ) {
					$control_type = $field_control['type'];

					$control_obj = $this->get_control_handler( $control_type );

					if ( ! $control_obj || ! $control_obj instanceof Control_Base ) {
						continue;
					}

					$control_obj = $control_obj::newInstance();

					$control_obj->set_value( $value, $attribute, $field_control );
					$filtered_value = $control_obj->get_value();

					if ( isset( $filtered_value ) ) {
						$field_data['attributes'][ $attribute ] = $filtered_value;
					}
				}
			}
		}
	}

	public function validate( $value, $field_id, $settings, Form_Submission_Handler $error_handler ): void {}

	/**
	 * Sanitize the field value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed Sanitized value.
	 */
	public function sanitize( $value = null ) {}
}
