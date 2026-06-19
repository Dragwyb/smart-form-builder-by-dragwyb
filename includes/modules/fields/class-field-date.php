<?php

declare(strict_types=1);

namespace Dragwyb\Form_Builder\Includes\Modules\Fields;

use Dragwyb\Form_Builder\Includes\Controls\Controls;
use Dragwyb\Form_Builder\Includes\Rest_Routes\Form_Submission_Handler;

class Field_Date extends Field_Base {

	protected function register_field_controls(): void {
		// ==============================================================
		// CONTENT TAB
		// ==============================================================

		$this->start_section(
			'section_content_general',
			array(
				'label' => __( 'General Settings', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::ContentTab,
			)
		);

		$this->add_control(
			'label',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Label', 'smart-form-builder-by-dragwyb' ),
				'default' => __( 'Select Date', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'label_icon',
			array(
				'type'  => Controls::ICON,
				'label' => __( 'Label Icon', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'placeholder',
			array(
				'type'    => Controls::TEXT,
				'label'   => __( 'Placeholder', 'smart-form-builder-by-dragwyb' ),
				'default' => 'YYYY-MM-DD',
			)
		);

		// Set a date range limit
		$this->add_control(
			'min_date',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Min Date', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Earliest allowed date.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'max_date',
			array(
				'type'        => Controls::TEXT,
				'label'       => __( 'Max Date', 'smart-form-builder-by-dragwyb' ),
				'description' => __( 'Latest allowed date.', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->add_control(
			'help_text',
			array(
				'type'  => Controls::TEXTAREA,
				'label' => __( 'Help Text', 'smart-form-builder-by-dragwyb' ),
				'rows'  => 3,
			)
		);

		$this->add_control(
			'required',
			array(
				'type'  => Controls::SWITCHER,
				'label' => __( 'Required', 'smart-form-builder-by-dragwyb' ),
			)
		);

		$this->end_section();

		// Label Style
		$this->start_section(
			'section_style_label',
			array(
				'label' => __( 'Label Appearance', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::StyleTab,
			)
		);

		$this->add_control(
			'label_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-label-color: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'label_spacing',
			array(
				'type'      => Controls::SLIDER,
				'label'     => __( 'Bottom Margin', 'smart-form-builder-by-dragwyb' ),
				'range'     => array(
					'px' => array(
						'min' => 0,
						'max' => 50,
					),
				),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-label-spacing: {{VALUE}}{{UNIT}};' ),
			)
		);

		$this->end_section();

		// Input Style
		$this->start_section(
			'section_style_input',
			array(
				'label' => __( 'Input Box Style', 'smart-form-builder-by-dragwyb' ),
				'tab'   => self::StyleTab,
			)
		);

		$this->start_tabs( 'tabs_input_style' );

		$this->start_tab( 'tab_input_normal', array( 'label' => __( 'Normal', 'smart-form-builder-by-dragwyb' ) ) );

		$this->add_control(
			'input_bg_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Background Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-input-bg: {{VALUE}};' ),
			)
		);

		$this->add_control(
			'input_text_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Text Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-input-color: {{VALUE}};' ),
			)
		);

		$this->add_group_control(
			'input_border',
			array(
				'type'     => Controls::GROUP_BORDER,
				'selector' => '{{WRAPPER}}',
				'prefix'   => 'input',
			)
		);

		$this->end_tab();

		$this->start_tab( 'tab_input_focus', array( 'label' => __( 'Focus', 'smart-form-builder-by-dragwyb' ) ) );

		$this->add_control(
			'input_focus_border_color',
			array(
				'type'      => Controls::COLOR,
				'label'     => __( 'Active Border Color', 'smart-form-builder-by-dragwyb' ),
				'selectors' => array( '{{WRAPPER}}' => '--dragwyb-input-focus-border: {{VALUE}};' ),
			)
		);

		$this->end_tab();

		$this->end_tabs();

		$this->add_control(
			'input_padding',
			array(
				'type'       => Controls::DIMENSIONS,
				'label'      => __( 'Inner Padding', 'smart-form-builder-by-dragwyb' ),
				'size_units' => array( 'px', 'em', '%' ),
				'selectors'  => array( '{{WRAPPER}}' => '--dragwyb-input-pt: {{TOP}}{{UNIT}}; --dragwyb-input-pr: {{RIGHT}}{{UNIT}}; --dragwyb-input-pb: {{BOTTOM}}{{UNIT}}; --dragwyb-input-pl: {{LEFT}}{{UNIT}};' ),
				'separator'  => 'before',
			)
		);

		$this->end_section();
	}

	protected function init(): void {
		$this->type     = 'date';
		$this->name     = __( 'Date Field', 'smart-form-builder-by-dragwyb' );
		$this->icon     = 'far fa-calendar';
		$this->category = 'standard-fields';
		$this->keywords = array( 'calendar', 'day', 'month', 'year' );
	}

	protected function render_field() {
		$settings    = $this->get_field_settings();
		$id          = $this->get_the_id();
		$field_id    = $this->field_key_exist( $settings, 'field_id', uniqid( 'date_' ) );
		$label       = $this->field_key_exist( $settings, 'label', 'Select Date' );
		$placeholder = $this->field_key_exist( $settings, 'placeholder', 'YYYY-MM-DD' );
		$required    = $this->field_key_exist( $settings, 'required', '' ) === 'yes';
		$classes     = $this->field_key_exist( $settings, 'css_classes', '' );

		$this->add_field_attributes(
			'wrapper',
			array(
				'id'    => $this->field_wrapper_id( $id ),
				'class' => $this->field_wrapper_class( $classes, $settings ) . ' dragwyb-no-float',
			)
		);

		$this->add_field_attributes(
			'input',
			array(
				'type'        => 'date',
				'id'          => $field_id,
				'name'        => $field_id,
				'placeholder' => $placeholder,
				'class'       => 'dragwyb-field-input',
			)
		);
		?>
		<div <?php $this->render_field_attributes( 'wrapper' ); ?>>
			<?php if ( ! empty( $label ) ) : ?>
				<label for="<?php echo esc_attr( $field_id ); ?>" class="dragwyb-label">
					<?php echo esc_html( $label ); ?>
					<?php if ( $required ) : ?>
						<span class="required">*</span>
					<?php endif; ?>
				</label>
			<?php endif; ?>

			<input 
			<?php
			$this->render_field_attributes( 'input' );
			echo $required ? 'required' : '';
			?>
			/>
		</div>

		<?php
	}

	public function validate( $value, $field_id, $form_config, Form_Submission_Handler $error_handler ): void {
		if ( ! isset( $form_config['fields'][ $field_id ] ) ) {
			$error_handler->add_error( $field_id, __( 'Invalid field.', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		$field_attr = isset( $form_config['fields'][ $field_id ]['attributes'] ) ? $form_config['fields'][ $field_id ]['attributes'] : array();

		if ( empty( $value ) && isset( $field_attr['required'] ) && 'yes' == $field_attr['required'] ) {
			$error_handler->add_error( $field_id, __( 'This field is required', 'smart-form-builder-by-dragwyb' ) );
			return;
		}

		if ( ! empty( $value ) ) {
			$date = \DateTime::createFromFormat( 'Y-m-d|', $value );
			if ( ! $date || $date->format( 'Y-m-d' ) !== $value ) {
				$error_handler->add_error( $field_id, __( 'Invalid date format', 'smart-form-builder-by-dragwyb' ) );
				return;
			}

			// Check min date
			if ( isset( $field_attr['min_date'] ) && ! empty( $field_attr['min_date'] ) ) {
				try {
					$min_date = new \DateTime( $field_attr['min_date'] );
					$min_date->setTime( 0, 0, 0 );
					if ( $date < $min_date ) {
						$error_handler->add_error( $field_id, __( 'Value is below minimum', 'smart-form-builder-by-dragwyb' ) );
						return;
					}
				} catch ( \Exception $e ) {
					// Gracefully ignore invalid configuration
				}
			}

			// Check max date
			if ( isset( $field_attr['max_date'] ) && ! empty( $field_attr['max_date'] ) ) {
				try {
					$max_date = new \DateTime( $field_attr['max_date'] );
					$max_date->setTime( 0, 0, 0 );
					if ( $date > $max_date ) {
						$error_handler->add_error( $field_id, __( 'Value exceeds maximum', 'smart-form-builder-by-dragwyb' ) );
					}
				} catch ( \Exception $e ) {
					// Gracefully ignore invalid configuration
				}
			}
		}
	}

	/**
	 * Sanitize the field value.
	 *
	 * @param mixed $value The value to sanitize.
	 * @return mixed Sanitized value.
	 */
	public function sanitize( $value = null ) {
		if ( $value ) {
			$date = \DateTime::createFromFormat( 'Y-m-d|', $value );
			return $date ? $date->format( 'Y-m-d' ) : null;
		}

		return null;
	}
}
